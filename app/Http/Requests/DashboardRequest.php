<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class DashboardRequest extends FormRequest
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
            'year_of_gamer_chart' => 'required|numeric|min:' . now()->setYears(100)->year . '|max:' . now()->addYears(100)->year,
            'year_of_room_chart' => 'required|numeric|min:' . now()->setYears(100)->year . '|max:' . now()->addYears(100)->year,
        ];
    }

    public function prepareForValidation(): void
    {
        $this->merge([
            'year_of_gamer_chart' => $this->year_of_gamer_chart ?? now()->year,
            'year_of_room_chart' => $this->year_of_room_chart ?? now()->year,
        ]);
    }

    public function messages(): array
    {
        return [
            'year_of_gamer_chart.required' => 'Năm thống kê không hợp lệ',
            'year_of_room_chart.required' => 'Năm thống kê không hợp lệ',
            'year_of_gamer_chart.min' => 'Năm thống kê không hợp lệ',
            'year_of_gamer_chart.max' => 'Năm thống kê không hợp lệ',
            'year_of_room_chart.min' => 'Năm thống kê không hợp lệ',
            'year_of_room_chart.max' => 'Năm thống kê không hợp lệ',
        ];
    }
}
