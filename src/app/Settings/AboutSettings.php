<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class AboutSettings extends Settings
{
    public ?string $text = null;

    public static function group(): string
    {
        return 'about';
    }

    public static function rules(): array
    {
        return [
            'text' => ['nullable', 'string'],
        ];
    }
}
