<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateGameSettingRequest extends FormRequest
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
            'name' => 'required|string|max:'.config('validation.gamer.name.max_length'),
            'token' => 'required|string',
            'gamer_id' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Name không được để trống',
            'name.string' => 'Name phải là chuỗi',
            'name.max' => 'Name không được vượt quá '.config('validation.gamer.name.max_length').' ký tự',
        ];
    }
}
