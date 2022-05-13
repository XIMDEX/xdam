<?php

declare(strict_types=1);

namespace App\Http\Requests\Resource\Book\Unit;

use Illuminate\Foundation\Http\FormRequest;

class RetriveUnitLinkRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'isbn' => 'required|string',
            'unit' => 'required|int'
        ];
    }

    public function validationData()
    {
        return [
            "isbn" => $this->isbn,
            "unit" => $this->unit
        ];
    }
}