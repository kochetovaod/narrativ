<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class SeoSettings extends Settings
{
    public ?string $default_title = null;

    public ?string $default_description = null;

    public static function group(): string
    {
        return 'seo';
    }

    public static function rules(): array
    {
        return [
            'default_title' => ['nullable', 'string', 'max:255'],
            'default_description' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
