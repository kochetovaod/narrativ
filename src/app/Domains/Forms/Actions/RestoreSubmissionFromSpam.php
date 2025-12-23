<?php

namespace App\Domains\Forms\Actions;

use App\Domains\Forms\Models\FormSubmission;

class RestoreSubmissionFromSpam
{
    public function __invoke(FormSubmission $submission): FormSubmission
    {
        $submission->forceFill([
            'is_spam' => false,
            'status' => $submission->status === FormSubmission::STATUS_SPAM
                ? FormSubmission::STATUS_NEW
                : $submission->status,
        ])->save();

        return $submission;
    }
}
