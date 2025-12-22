<?php

namespace App\Observers;

use App\Contracts\RequiresMediaCustomProperties;
use Illuminate\Support\Facades\Validator;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaObserver
{
    public function saving(Media $media): void
    {
        $model = $media->model;

        if (! $model instanceof RequiresMediaCustomProperties) {
            return;
        }

        $rules = $model->requiredMediaCustomProperties($media->collection_name);

        if ($rules === []) {
            return;
        }

        Validator::make(
            $media->custom_properties ?? [],
            $rules,
            [],
            [
                'alt' => 'Alt',
                'title' => 'Title',
            ],
        )->validate();
    }
}
