<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StartRoomRequest extends FormRequest
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
            'room_id' => 'required|string',
        ];
    }
}
