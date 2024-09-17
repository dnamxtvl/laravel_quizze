<?php

namespace App\Http\Requests;

use App\Enums\Room\RoomTypeEnum;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class AdminCreateRoomRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'type' => 'required|in:' . RoomTypeEnum::KAHOOT->value . ',' . RoomTypeEnum::HOMEWORK->value,
        ];
    }

    public function messages(): array
    {
        return [
            'type.in' => 'Type room không hợp lệ!',
        ];
    }

    protected function prepareForValidation(): void
    {
        $typeRoom = $this->input(key: 'type');
        if ($typeRoom == RoomTypeEnum::HOMEWORK->value) {
            $startTime = $this->input(key: 'start_time');
            $endTime = $this->input(key: 'end_time');
            $validator = Validator::make(
                [
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                ], [
                    'start_time' => 'required|date_format:Y-m-d H:i:s|after:now',
                    'end_time' => 'required|date_format:Y-m-d H:i:s|after:now',
                ]
            );

            if ($validator->fails()) {
                throw new HttpResponseException(
                    response()->json(data: [
                        'errors' => $validator->errors(),
                    ], status: ResponseAlias::HTTP_UNPROCESSABLE_ENTITY)
                );
            }

            if (Carbon::parse($endTime)->lt(Carbon::parse($startTime))) {
                throw new HttpResponseException(
                    response()->json(data: [
                        'message' => 'Thoi gian ket thuc phai lon hon thoi gian bat dau',
                    ], status: ResponseAlias::HTTP_BAD_REQUEST)
                );
            }

            if (abs(Carbon::parse($endTime)->diffInMinutes(Carbon::parse($startTime))) < config('room.min_time_duration') ||
                abs(Carbon::parse($endTime)->diffInMinutes(Carbon::parse($startTime))) > config('room.max_time_duration')) {
                throw new HttpResponseException(
                    response()->json(data: [
                        'message' => 'Thời gian chơi phải nằm trong khoảng ' . config('room.min_time_duration') .
                            ' đến ' . config('room.max_time_duration') . ' phút',
                    ], status: ResponseAlias::HTTP_BAD_REQUEST)
                );
            }
        }
    }
}
