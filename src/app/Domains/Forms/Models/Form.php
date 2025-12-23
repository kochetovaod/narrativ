<?php

namespace App\Domains\Forms\Models;

use App\Domains\Shared\Concerns\LogsActivityChanges;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Wildside\Userstamps\Userstamps;

class Form extends Model
{
    use HasFactory;
    use LogsActivityChanges;
    use SoftDeletes;
    use Userstamps;

    public const DEFAULT_SETTINGS = [
        'enable_turnstile' => false,
        'enable_honeypot' => true,
        'allow_files' => false,
        'rate_limit_per_ip' => 5,
        'rate_limit_per_form' => 50,
        'rate_limit_decay_seconds' => 60,
        'webhook_url' => null,
        'email_template' => null,
        'email_reply_to_field' => null,
    ];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'slug',
        'recipients',
        'success_message',
        'settings',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'recipients' => 'array',
            'settings' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function fields(): HasMany
    {
        return $this->hasMany(FormField::class)->orderBy('sort_order');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(FormSubmission::class)->latest();
    }

    public function getSettingsWithDefaults(): array
    {
        return array_merge(self::DEFAULT_SETTINGS, $this->settings ?? []);
    }

    public function getSetting(string $key, mixed $default = null): mixed
    {
        return data_get($this->getSettingsWithDefaults(), $key, $default);
    }
}
