<?php

namespace App\Domains\Shared\Concerns;

use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

trait LogsActivityChanges
{
    use LogsActivity;

    /**
     * @var list<string>
     */
    protected static array $recordEvents = [
        'created',
        'updated',
        'deleted',
        'restored',
        'forceDeleted',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName(config('activitylog.default_log_name', 'default'))
            ->logOnly($this->getActivitylogAttributes())
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * @return list<string>
     */
    protected function getActivitylogAttributes(): array
    {
        if (property_exists($this, 'activitylogAttributes') && is_array($this->activitylogAttributes)) {
            return $this->activitylogAttributes;
        }

        if (property_exists($this, 'fillable') && is_array($this->fillable)) {
            return $this->fillable;
        }

        return ['*'];
    }
}
