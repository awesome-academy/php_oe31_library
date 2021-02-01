<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AuthorRequest extends FormRequest
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
        $id = $this->route('author');

        return [
            'name' => [
                'max:255 |
                required', Rule::unique('authors')->ignore($id),
            ],
            'description' => 'max:255',
            'date_of_born' => 'date|nullable|before:tomorrow',
            'date_of_death' => 'date|nullable|after:date_of_born',
        ];
    }
}
