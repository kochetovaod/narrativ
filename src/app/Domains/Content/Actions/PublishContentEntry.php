<?php

namespace App\Domains\Content\Actions;

use Illuminate\Database\Eloquent\Model;

class PublishContentEntry
{
    public function __invoke(Model $content): Model
    {
        $content->forceFill([
            'is_published' => true,
            'published_at' => $content->published_at ?? now(),
        ])->save();

        return $content;
    }
}
