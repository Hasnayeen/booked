<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Bus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateBusRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $operatorId = request()->get('operator_id') ?? Auth::user()?->operator_id;
        $busId = $this->route('bus') ?? $this->route('id');

        return Bus::updateRules($operatorId, $busId);
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'bus_number.unique' => 'This bus number already exists for your operator.',
            'total_seats.min' => 'Bus must have at least 1 seat.',
            'total_seats.max' => 'Bus cannot have more than 100 seats.',
            'category.required' => 'Please select a bus category.',
            'type.required' => 'Please select AC or Non-AC type.',
        ];
    }
}
