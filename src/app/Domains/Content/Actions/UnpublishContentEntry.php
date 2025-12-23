<?php

namespace App\Domains\Content\Actions;

use Illuminate\Database\Eloquent\Model;

class UnpublishContentEntry
{
    public function __invoke(Model $content): Model
    {
        $content->forceFill([
            'is_published' => false,
        ])->save();

        return $content;
    }
}
