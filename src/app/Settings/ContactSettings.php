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

    public string $contact_form_title = 'Свяжитесь с нами';

    public ?string $contact_form_description = null;

    public array $contact_form_fields = [
        'name' => [
            'label' => 'Ваше имя',
            'placeholder' => 'Иван Иванов',
            'required' => true,
        ],
        'email' => [
            'label' => 'Email',
            'placeholder' => 'you@example.com',
            'required' => true,
        ],
        'phone' => [
            'label' => 'Телефон',
            'placeholder' => '+7 (999) 123-45-67',
            'required' => false,
        ],
    ];

    public string $contact_form_email_subject = 'Новая заявка с сайта';

    public string $contact_form_success_message = 'Спасибо! Мы свяжемся с вами в ближайшее время.';

    public bool $contact_form_enable_turnstile = false;

    public bool $contact_form_enable_honeypot = true;

    public static function group(): string
    {
        return 'contacts';
    }

    public static function defaults(): array
    {
        return [
            'contact_form_enable_turnstile' => (bool) config('services.turnstile.enabled', false),
            'contact_form_enable_honeypot' => (bool) config('services.honeypot.enabled', true),
        ];
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
            'contact_form_title' => ['required', 'string', 'max:255'],
            'contact_form_description' => ['nullable', 'string'],
            'contact_form_fields' => ['required', 'array'],
            'contact_form_fields.name.label' => ['required', 'string', 'max:255'],
            'contact_form_fields.name.placeholder' => ['nullable', 'string', 'max:255'],
            'contact_form_fields.name.required' => ['boolean'],
            'contact_form_fields.email.label' => ['required', 'string', 'max:255'],
            'contact_form_fields.email.placeholder' => ['nullable', 'string', 'max:255'],
            'contact_form_fields.email.required' => ['boolean'],
            'contact_form_fields.phone.label' => ['required', 'string', 'max:255'],
            'contact_form_fields.phone.placeholder' => ['nullable', 'string', 'max:255'],
            'contact_form_fields.phone.required' => ['boolean'],
            'contact_form_email_subject' => ['required', 'string', 'max:255'],
            'contact_form_success_message' => ['required', 'string'],
            'contact_form_enable_turnstile' => ['boolean'],
            'contact_form_enable_honeypot' => ['boolean'],
        ];
    }
}
