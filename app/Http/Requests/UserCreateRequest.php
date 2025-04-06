<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserCreateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required',
            'email' => 'required|unique:users',
            'password' => 'required',
            'user_type_id' => 'required|exists:user_types,id',
            'balance' => 'required',
            'cpf_cnpj' => 'required|unique:users',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'cpf_cnpj' => preg_replace("/\D/", "", $this->cpf_cnpj),
        ]);
    }
}
