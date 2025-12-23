<?php

namespace App\Domains\Forms\Actions;

use App\Domains\Forms\Mail\FormSubmissionNotification;
use App\Domains\Forms\Models\FormSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendFormSubmissionNotificationJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public FormSubmission $submission)
    {
    }

    public function handle(): void
    {
        $submission = $this->submission->load(['form', 'form.fields', 'media']);

        $recipients = collect($submission->form?->recipients ?? [])
            ->filter(fn ($email) => filter_var($email, FILTER_VALIDATE_EMAIL))
            ->values()
            ->all();

        if (empty($recipients)) {
            return;
        }

        Mail::to($recipients)->send(new FormSubmissionNotification($submission));
    }
}
