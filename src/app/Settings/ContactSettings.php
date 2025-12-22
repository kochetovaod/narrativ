<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class ContactSettings extends Settings
{
    public array $phones = [];

    public ?string $email = null;

    public ?string $address = null;

    public ?string $map_coordinates = null;

    public ?string $working_hours = null;

    public array $social_links = [];

    public static function group(): string
    {
        return 'contacts';
    }

    public static function rules(): array
    {
        return [
            'phones' => ['array'],
            'phones.*' => ['string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'map_coordinates' => ['nullable', 'string', 'max:255'],
            'working_hours' => ['nullable', 'string'],
            'social_links' => ['array'],
            'social_links.*.label' => ['required_with:social_links.*.url', 'string', 'max:255'],
            'social_links.*.url' => ['nullable', 'url', 'max:2048'],
        ];
    }
}
