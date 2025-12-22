<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('contacts.phones', []);
        $this->migrator->add('contacts.email', null);
        $this->migrator->add('contacts.address', null);
        $this->migrator->add('contacts.map_coordinates', null);
        $this->migrator->add('contacts.working_hours', null);
        $this->migrator->add('contacts.social_links', []);

        $this->migrator->add('about.text', null);

        $this->migrator->add('brand.company_name', null);
        $this->migrator->add('brand.logo', null);
        $this->migrator->add('brand.favicon', null);

        $this->migrator->add('seo.default_title', null);
        $this->migrator->add('seo.default_description', null);
    }
};
