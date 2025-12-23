<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('contacts.contact_form_title', 'Свяжитесь с нами');
        $this->migrator->add('contacts.contact_form_description', null);
        $this->migrator->add('contacts.contact_form_fields', [
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
        ]);
        $this->migrator->add('contacts.contact_form_email_subject', 'Новая заявка с сайта');
        $this->migrator->add('contacts.contact_form_success_message', 'Спасибо! Мы свяжемся с вами в ближайшее время.');
        $this->migrator->add('contacts.contact_form_enable_turnstile', (bool) env('TURNSTILE_ENABLED', false));
        $this->migrator->add('contacts.contact_form_enable_honeypot', (bool) env('HONEYPOT_ENABLED', true));
    }
};
