<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class BrandSettings extends Settings
{
    public ?string $company_name = null;

    public ?array $logo = null;

    public ?array $favicon = null;

    public static function group(): string
    {
        return 'brand';
    }

    public static function rules(): array
    {
        return [
            'company_name' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'array'],
            'logo.path' => ['nullable', 'string', 'max:2048'],
            'logo.alt' => ['required_with:logo.path', 'string', 'max:255'],
            'logo.title' => ['required_with:logo.path', 'string', 'max:255'],
            'favicon' => ['nullable', 'array'],
            'favicon.path' => ['nullable', 'string', 'max:2048'],
            'favicon.alt' => ['required_with:favicon.path', 'string', 'max:255'],
            'favicon.title' => ['required_with:favicon.path', 'string', 'max:255'],
        ];
    }
}
