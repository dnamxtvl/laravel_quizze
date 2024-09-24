<?php

namespace App\Http\Requests;

use App\Enums\Room\RoomStatusEnum;
use App\Enums\Room\RoomTypeEnum;
use Illuminate\Foundation\Http\FormRequest;

class GetListRoomReportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => 'nullable|integer|in:' . implode(',', array_map(fn($enum) => $enum->value, RoomTypeEnum::cases())),
            'status' => 'nullable|integer|in:' . implode(',', array_map(fn($enum) => $enum->value, RoomStatusEnum::cases())),
            'code' => 'nullable|integer',
            'start_time' => 'nullable|date_format:Y-m-d H:i:s',
            'end_time' => 'nullable|date_format:Y-m-d H:i:s',
        ];
    }

    public function messages(): array
    {
        return [
            'type.in' => 'Type room không hợp lệ!',
            'status.in' => 'Status room không hợp lệ!',
            'code.integer' => 'Code phải là số',
            'start_time.date_format' => 'Khoảng thời gian không hợp lệ',
            'end_time.date_format' => 'Khoảng thời gian không hợp lệ',
        ];
    }
}
