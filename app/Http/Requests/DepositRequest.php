<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DepositRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'account_type' => 'required|in:phone,card,email',
            'account_id' => 'required|string',
            'loyalty_points_rule' => 'required|integer',
            'description' => 'nullable|string',
            'payment_id' => 'required|string',
            'payment_amount' => 'required|numeric',
            'payment_time' => 'required|integer',
        ];
    }
}
