<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ClientSaveRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {

        $clientId = isset($this->input('client')['id']) ? (int)$this->input('client')['id'] : 0; 

        return [
            "client.name" => [
                "required",
                "max:100",
                "regex:/^[a-z ,.'-]+$/i"
            ],
            "client.personal_no" => [
                "required",
                "digits:10",
                Rule::unique("clients", "personal_no")->ignore($clientId)
            ],
            "client.card_no" => [
                "required",
                "digits:9",
                Rule::unique("clients", "card_no")->ignore($clientId)
            ],
            "client.phones.*" => [
                "nullable",
                "regex:/(^$|^(\d| |-|\+|\(|\))+$)/",
            ],
        ];
    }


    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            "client.name" => "Invalid name.",
            "client.personal_no.digits" => "10 digits expected.",
            "client.personal_no.unique" => "Personal No. already in use.",
            "client.card_no.digits" => "9 digits expected.",
            "client.card_no.unique" => "Card No. already in use.",
            "client.phones.*" => "Invalid phone number. Leave it empty or use only digits, spaces, -, +, (, )",
        ];
    }
}
