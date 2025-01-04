<?php

namespace App\Services\Implement;

use App\DTOs\Auth\AdminLoginDTOs;
use App\DTOs\Auth\AdminLoginResponseDataDTO;
use App\DTOs\Auth\RegisterParamsDTO;
use App\DTOs\Auth\SaveEmailVerifyOTPDTO;
use App\DTOs\Auth\TypeCodeOTPEnum;
use App\DTOs\Auth\UserDeviceInformationDTO;
use App\DTOs\Auth\UserLoginHistoryDTO;
use App\Enums\Auth\AuthExceptionEnum;
use App\Enums\Auth\TypeUserHistoryLoginEnum;
use App\Enums\Exception\ExceptionCodeEnum;
use App\Enums\User\UserStatusEnum;
use App\Events\EmailNotVerifyEvent;
use App\Exceptions\Auth\EmailAlreadyExistsException;
use App\Exceptions\Auth\EmailVerifiedException;
use App\Exceptions\Auth\InvalidOTPException;
use App\Exceptions\Auth\LoginWrongPasswordManyException;
use App\Exceptions\Auth\OTPExpiredException;
use App\Exceptions\User\EmailNotVerifiedException;
use App\Jobs\SendEmailLinkForgotPassword;
use App\Jobs\SendEmailVerifyNotification;
use App\Models\EmailVerifyOTP;
use App\Models\User;
use App\Repository\Interface\BlockUserLoginTemporaryRepositoryInterface;
use App\Repository\Interface\EmailVerifyOTPRepositoryInterface;
use App\Repository\Interface\UserLoginHistoryRepositoryInterface;
use App\Repository\Interface\UserRepositoryInterface;
use App\Services\Interface\AuthServiceInterface;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Contracts\Auth\PasswordBroker as PasswordBrokerAlias;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
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

        $this->checkValidUserAfterVerifyOTP(user: $user);

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

        if ($emailVerifyOTP->type == TypeCodeOTPEnum::FORGET_PASSWORD->value) {
            if (! Password::tokenExists(user: $user, token: $emailVerifyOTP->token)) {
                throw new NotFoundHttpException(message: 'Token không tồn tại hoặc đã hết hạn!');
            }
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

    /**
     * @throws InternalErrorException
     */
    public function register(RegisterParamsDTO $registerParams): array
    {
        $user = $this->userRepository->findByEmail(email: $registerParams->getEmail());
        if ($user) {
            throw new EmailAlreadyExistsException(code: AuthExceptionEnum::EMAIL_ALREADY_EXISTS->value);
        }

        DB::beginTransaction();
        try {
            $user = $this->userRepository->create(registerParams: $registerParams);
            $token = hash('sha256', Str::uuid() . Str::random(40) . $user->id);
            $code = rand(100000, 999999);

            $saveEmailVerifyDTO = new SaveEmailVerifyOTPDTO(
                code: $code,
                userId: $user->id,
                expiredAt: now()->addHour(),
                type: TypeCodeOTPEnum::VERIFY_EMAIL,
                token: $token,
            );
            $newEmailVerifyOTP = $this->emailVerifyOTPRepository->save(saveEmailVerify: $saveEmailVerifyDTO);
            SendEmailVerifyNotification::dispatch($user, $code);
            DB::commit();

            return [$newEmailVerifyOTP->id, $token];
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error(message: $e->getMessage());
            throw new InternalErrorException(message: $e->getMessage());
        }
    }

    /**
     * @throws InternalErrorException
     */
    public function verifyOTPAfterRegister(string $otpId, string $token, string $code): AdminLoginResponseDataDTO
    {
        $verifyEmailOTP = $this->emailVerifyOTPRepository->findById(emailVerifyOtpId: $otpId);
        if (is_null($verifyEmailOTP) || $verifyEmailOTP->token != $token) {
            throw new NotFoundHttpException(message: 'Token không hợp lệ!');
        }

        $user = $verifyEmailOTP->user;
        if (is_null($user)) {
            throw new BadRequestHttpException(message: 'Tài khoản này đã bị xóa khỏi hệ thống!');
        }

        $this->checkValidUserAfterVerifyOTP(user: $user);

        DB::beginTransaction();
        try {
            $this->verifyEmailOTP(code: $code, type: TypeCodeOTPEnum::VERIFY_EMAIL, user: $user);
            $this->emailVerifyOTPRepository->deleteByUserIdAndType(userId: $user->id, type: TypeCodeOTPEnum::VERIFY_EMAIL);
            DB::commit();
            Auth::login($user);

            return $this->generateToken();
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error(message: $e->getMessage());
            throw new InternalErrorException(message: $e->getMessage());
        }
    }

    private function checkValidUserAfterVerifyOTP(User $user): void
    {
        if ($user->disabled) {
            throw new BadRequestHttpException(message: 'Tài khoản này đã bị vô hiệu hóa!');
        }

        if ($user->email_verified_at) {
            throw new EmailVerifiedException(code: AuthExceptionEnum::EMAIL_VERIFIED->value);
        }
    }

    public function resendVerifyEmail(string $otpId): void
    {
        $verifyEmailOTP = $this->emailVerifyOTPRepository->findById(emailVerifyOtpId: $otpId);
        if (is_null($verifyEmailOTP)) {
            throw new NotFoundHttpException(message: 'Token không hợp lệ!');
        }

        $user = $verifyEmailOTP->user;
        if (is_null($user)) {
            throw new BadRequestHttpException(message: 'Tài khoản này đã bị xóa khỏi hệ thống!');
        }

        $this->checkValidUserAfterVerifyOTP(user: $user);
        event(new EmailNotVerifyEvent(
            user: $user,
            verifyCode: rand(100000, 999999),
            type: TypeCodeOTPEnum::tryFrom($verifyEmailOTP->type))
        );
    }

    public function forgotPassword(string $email): void
    {
        $user = $this->userRepository->findByEmail(email: $email);
        if (is_null($user)) {
            throw new NotFoundHttpException(message: 'Email chưa được đăng ký!');
        }

        if ($user->disabled) {
            throw new BadRequestHttpException(message: 'Tài này đã bị vô hiệu hóa!');
        }

        SendEmailLinkForgotPassword::dispatchSync($email);
    }

    /**
     * @throws InternalErrorException
     */
    public function resetPassword(string $userId, string $token, string $password): void
    {
        $user = $this->userRepository->findById($userId);
        if (is_null($user)) {
            throw new NotFoundHttpException(message: 'User đã bị xóa khỏi hệ thống!');
        }

        if ($user->disabled) {
            throw new BadRequestHttpException(message: 'Tài khoản ' . $user->email . ' đã bị vô hiệu hóa!');
        }

        $status = Password::reset(
            ['email' => $user->email, 'password' => $password, 'password_confirmation' => $password, 'token' => $token],
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();
                event(new PasswordReset($user));
            }
        );

        if ($status !== PasswordBrokerAlias::PASSWORD_RESET) {
            throw new InternalErrorException(message: 'Link đặt lại mật khẩu không hợp lệ hoặc đã hết hạn!');
        }
    }
}
