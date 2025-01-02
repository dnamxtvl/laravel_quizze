<?php

namespace App\Services\Implement;

use App\DTOs\Auth\AdminLoginDTOs;
use App\DTOs\Auth\AdminLoginResponseDataDTO;
use App\DTOs\Auth\TypeCodeOTPEnum;
use App\DTOs\Auth\UserDeviceInformationDTO;
use App\DTOs\Auth\UserLoginHistoryDTO;
use App\Enums\Auth\AuthExceptionEnum;
use App\Enums\Auth\TypeUserHistoryLoginEnum;
use App\Enums\Exception\ExceptionCodeEnum;
use App\Enums\User\UserStatusEnum;
use App\Events\EmailNotVerifyEvent;
use App\Exceptions\Auth\EmailVerifiedException;
use App\Exceptions\Auth\InvalidOTPException;
use App\Exceptions\Auth\LoginWrongPasswordManyException;
use App\Exceptions\Auth\OTPExpiredException;
use App\Exceptions\User\EmailNotVerifiedException;
use App\Models\EmailVerifyOTP;
use App\Models\User;
use App\Repository\Interface\BlockUserLoginTemporaryRepositoryInterface;
use App\Repository\Interface\EmailVerifyOTPRepositoryInterface;
use App\Repository\Interface\UserLoginHistoryRepositoryInterface;
use App\Repository\Interface\UserRepositoryInterface;
use App\Services\Interface\AuthServiceInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Symfony\Component\CssSelector\Exception\InternalErrorException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

readonly class AuthService implements AuthServiceInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private BlockUserLoginTemporaryRepositoryInterface $userLoginTemporaryRepository,
        private UserLoginHistoryRepositoryInterface $userLoginHistoryRepository,
        private EmailVerifyOTPRepositoryInterface $emailVerifyOTPRepository,
    ) {}

    public function login(AdminLoginDTOs $credentials, UserDeviceInformationDTO $userDeviceInformation): AdminLoginResponseDataDTO
    {
        $user = $this->userRepository->findByEmail(email: $credentials->getEmail());
        if (is_null($user)) {
            throw new BadRequestHttpException(message: 'Email chưa được đăng ký', code: ExceptionCodeEnum::INVALID_CREDENTIALS->value);
        }

        if ($user->disabled === UserStatusEnum::DISABLE->value) {
            throw new BadRequestHttpException(message: 'Tài khoản đã bị vô hiệu hóa!', code: AuthExceptionEnum::ACCOUNT_CLOSED->value);
        }

        if ($this->checkIsBlockUserLogin(ip: $userDeviceInformation->getIp(), userId: $user->id)) {
            throw new LoginWrongPasswordManyException(
                code: AuthExceptionEnum::LOGIN_WRONG_PASSWORD_MANY->value
            );
        }
        $adminCredentials = [
            'email' => $credentials->getEmail(),
            'password' => $credentials->getPassword(),
        ];

        if (! Auth::attempt(credentials: $adminCredentials)) {
            $this->userLoginWrongPasswordAction(userDeviceInformation: $userDeviceInformation, userId: $user->id);
            throw new BadRequestHttpException(message: 'Sai email hoặc mật khẩu!', code: ExceptionCodeEnum::INVALID_CREDENTIALS->value);
        }

        $user = Auth::user();
        /** @var User $user */
        if (is_null($user->email_verified_at)) {
            event(new EmailNotVerifyEvent(user: $user, verifyCode: rand(100000, 999999), type: TypeCodeOTPEnum::VERIFY_EMAIL));
            throw new EmailNotVerifiedException(code: ExceptionCodeEnum::UNVERIFIED_ACCOUNT->value);
        }

        $user->latest_ip_login = $userDeviceInformation->getIp();
        $user->latest_login = now();
        $user->save();

        if ($user->latest_ip_login != $userDeviceInformation->getIp() ||
            ! $user->userLoginHistories()->count()) {
            $userLoginHistory = new userLoginHistoryDTO(
                userId: $user->id,
                ip: $userDeviceInformation->getIp(),
                device: $userDeviceInformation->getDevice(),
                type: TypeUserHistoryLoginEnum::LOGIN_SUCCESS_NEW_IP
            );
            $this->userLoginHistoryRepository->save(userLoginHistoryDTO: $userLoginHistory);
        }

        $user->tokens()->delete();

        return $this->generateToken();
    }

    public function logout(): void
    {
        $user = Auth::user();
        /** @var User $user */
        $user->tokens()->delete();
    }

    private function checkIsBlockUserLogin(string $ip, string $userId): bool
    {
        $isBlock = false;
        $lockUserLoginTemporary = $this->userLoginTemporaryRepository->findByUserAndIp(ip: $ip, userId: $userId);
        if ($lockUserLoginTemporary && ! now()->gt(date: $lockUserLoginTemporary->expired_at)) {
            $isBlock = true;
        }

        return $isBlock;
    }

    private function userLoginWrongPasswordAction(UserDeviceInformationDTO $userDeviceInformation, string $userId): void
    {
        $userLoginHistory = new UserLoginHistoryDTO(
            userId: $userId,
            ip: $userDeviceInformation->getIp(),
            device: $userDeviceInformation->getDevice(),
            type: TypeUserHistoryLoginEnum::WRONG_PASSWORD
        );

        $this->userLoginHistoryRepository->save(userLoginHistoryDTO: $userLoginHistory);
        $countLoginWrongPassword = $this->userLoginHistoryRepository->getQuery(
            filters: [
                'ip' => $userDeviceInformation->getIp(),
                'type' => TypeUserHistoryLoginEnum::WRONG_PASSWORD->value,
                'user_id' => $userId,
            ]
        )->where('created_at', '>', now()->subHour())->count();

        if ($countLoginWrongPassword >= config('auth.max_wrong_password')) {
            $this->userLoginTemporaryRepository->save(
                ip: $userDeviceInformation->getIp(),
                userId: $userId,
                expiredAt: now()->addHour()
            );
        }
    }

    /**
     * @throws InternalErrorException
     */
    public function verifyOTPAfterLogin(string $code, string $email): AdminLoginResponseDataDTO
    {
        $user = $this->userRepository->findByEmail(email: $email);
        if (is_null($user)) {
            throw new NotFoundHttpException(message: 'Không tìm thấy người dùng!');
        }

        DB::beginTransaction();
        try {
            $this->verifyEmailOTP(code: $code, type: TypeCodeOTPEnum::VERIFY_EMAIL, user: $user);
            Auth::login(user: $user);
            DB::commit();

            return $this->generateToken();
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error(message: $e->getMessage());
            throw new InternalErrorException(message: $e->getMessage());
        }
    }

    private function verifyEmailOTP(string $code, TypeCodeOTPEnum $type, User $user): void
    {
        if ($type == TypeCodeOTPEnum::VERIFY_EMAIL && $user->email_verified_at) {
            throw new EmailVerifiedException(code: AuthExceptionEnum::EMAIL_VERIFIED->value);
        }

        $emailVerifyOTP = $this->emailVerifyOTPRepository->findByCondition(
            filters: ['user_id' => $user->id, 'code' => $code, 'type' => $type->value]
        );

        if (is_null($emailVerifyOTP)) {
            throw new InvalidOTPException(code: AuthExceptionEnum::INVALID_CODE->value);
        }

        $this->isValidOTP(user: $user, emailVerifyOTP: $emailVerifyOTP);
        if ($type == TypeCodeOTPEnum::VERIFY_EMAIL) {
            $this->userRepository->verifyEmail(user: $user);
        }
        $this->emailVerifyOTPRepository->deleteByUserIdAndType(userId: $user->id, type: $type);
    }

    private function isValidOTP(User $user, EmailVerifyOTP $emailVerifyOTP): void
    {
        if (now()->gt(date: $emailVerifyOTP->expired_at)) {
            throw new OTPExpiredException(code: AuthExceptionEnum::OTP_EXPIRED->value);
        }

        if (! Password::tokenExists(user: $user, token: $emailVerifyOTP->token)) {
            throw new NotFoundHttpException(message: 'Token không tồn tại hoặc đã hết hạn!');
        }
    }

    private function generateToken(): AdminLoginResponseDataDTO
    {
        $user = Auth::user();
        /** @var User $user */
        $user->tokens()->delete();
        $tokenResult = $user->createToken('authToken')->plainTextToken;

        return new AdminLoginResponseDataDTO(
            user: $user,
            token: $tokenResult,
            expiresAt: now()->addMinutes(config(key: 'sanctum.expiration')),
        );
    }
}
