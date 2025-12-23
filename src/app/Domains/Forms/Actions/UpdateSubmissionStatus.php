<?php

namespace App\Domains\Forms\Actions;

use App\Domains\Forms\Models\FormSubmission;
use InvalidArgumentException;

class UpdateSubmissionStatus
{
    public function __invoke(FormSubmission $submission, string $status): FormSubmission
    {
        if (! array_key_exists($status, FormSubmission::STATUS_LABELS)) {
            throw new InvalidArgumentException('Unknown submission status: ' . $status);
        }

        $submission->update(['status' => $status]);

        return $submission;
    }
}
