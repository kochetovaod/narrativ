<?php

namespace App\Domains\Menu\Models;

use App\Domains\Shared\Concerns\LogsActivityChanges;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MenuItem extends Model
{
    use HasFactory;
    use LogsActivityChanges;
    use SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'menu_id',
        'parent_id',
        'title',
        'link_type',
        'url',
        'route_name',
        'route_parameters',
        'model_type',
        'model_id',
        'is_visible',
        'open_in_new_tab',
        'sort_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_visible' => 'boolean',
            'open_in_new_tab' => 'boolean',
            'route_parameters' => 'array',
        ];
    }

    /**
     * @return BelongsTo<Menu, MenuItem>
     */
    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    /**
     * @return BelongsTo<MenuItem, MenuItem>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class, 'parent_id');
    }

    /**
     * @return HasMany<MenuItem>
     */
    public function children(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'parent_id')->orderBy('sort_order');
    }

    public function getDepth(): int
    {
        return $this->parent ? 1 + $this->parent->getDepth() : 0;
    }
}
