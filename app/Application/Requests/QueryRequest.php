<?php
namespace App\Application\Requests;

class QueryRequest extends FormRequest
{
    protected function rules(): array
    {
        return [
            'page' => 'numeric|min:1',
            'per_page' => 'numeric|min:1|max:100',
            'order' => 'in:asc,desc,ASC,DESC'
        ];
    }
}