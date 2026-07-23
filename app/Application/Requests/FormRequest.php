<?php
namespace App\Application\Requests;

use App\Shared\Core\Validation\Validator;

abstract class FormRequest
{
    protected array $data;

    public function __construct()
    {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $this->data = Validator::sanitize(array_merge($_GET, $_POST, $input));
        $this->validate();
    }

    protected function validate(): void
    {
        $rules = $this->rules();
        if (!empty($rules)) {
            Validator::validate($this->data, $rules);
        }
    }

    abstract protected function rules(): array;

    public function all(): array
    {
        return $this->data;
    }
    
    public function get(string $key, $default = null)
    {
        return $this->data[$key] ?? $default;
    }
}