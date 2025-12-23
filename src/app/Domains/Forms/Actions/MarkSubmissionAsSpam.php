<?php

namespace App\Domains\Forms\Actions;

use App\Domains\Forms\Models\FormSubmission;

class MarkSubmissionAsSpam
{
    public function __invoke(FormSubmission $submission): FormSubmission
    {
        $submission->forceFill([
            'is_spam' => true,
            'status' => FormSubmission::STATUS_SPAM,
        ])->save();

        return $submission;
    }
}
