<?php

namespace App\Shared\Core\Validation;

use App\Shared\Core\Exceptions\ApiException;

class Validator
{
    public static function isEmpty($var): bool
    {
        return empty($var);
    }

    public static function isSet(&$var): bool
    {
        return isset($var);
    }

    public static function length(string $str): int
    {
        return strlen($str);
    }

    public static function filterVar($variable, int $filter = FILTER_DEFAULT, array|int $options = 0)
    {
        return filter_var($variable, $filter, $options);
    }

    public static function pregMatch(string $pattern, string $subject, array &$matches = null, int $flags = 0, int $offset = 0): int|false
    {
        if (func_num_args() > 2) {
            return preg_match($pattern, $subject, $matches, $flags, $offset);
        }
        return preg_match($pattern, $subject);
    }

        public static function sanitize(array $data): array
    {
        $clean = [];
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $clean[$key] = htmlspecialchars(strip_tags(trim($value)), ENT_QUOTES, 'UTF-8');
            } else {
                $clean[$key] = $value;
            }
        }
        return $clean;
    }

    public static function validate(array $data, array $rules): array
    {
        $errors = [];
        $validated = [];

        foreach ($rules as $field => $ruleString) {
            $value = $data[$field] ?? null;
            $fieldRules = explode('|', $ruleString);

            foreach ($fieldRules as $rule) {
                if ($rule === 'required' && (is_null($value) || $value === '')) {
                    $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' is required.';
                    break;
                }

                if (!is_null($value) && $value !== '') {
                    if ($rule === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $errors[$field] = 'Please enter a valid email address.';
                    }

                    if (str_starts_with($rule, 'min:')) {
                        $min = (int)substr($rule, 4);
                        if (strlen($value) < $min) {
                            $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . " must be at least {$min} characters.";
                        }
                    }

                    if (str_starts_with($rule, 'max:')) {
                        $max = (int)substr($rule, 4);
                        if (strlen($value) > $max) {
                            $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . " must not exceed {$max} characters.";
                        }
                    }

                    if ($rule === 'numeric' && !is_numeric($value)) {
                        $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' must be a number.';
                    }
                }
            }
            $validated[$field] = $value;
        }

        if (!self::isEmpty($errors)) {
            throw new \App\Shared\Core\Exceptions\ValidationException('Validation failed', ['errors' => $errors]);
        }

        return $validated;
    }
}
