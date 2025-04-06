<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransferRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'value' => 'required',
            'payer' => 'required|exists:users,id',
            'payee' => 'required|exists:users,id',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
