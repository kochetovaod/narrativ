<?php

namespace App\Domains\Forms\Mail;

use App\Domains\Forms\Models\FormSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class FormSubmissionNotification extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(public FormSubmission $submission)
    {
        $this->submission->loadMissing(['form', 'form.fields', 'media']);
    }

    public function build(): self
    {
        $this->subject(__('Новая заявка: :title', ['title' => $this->submission->form?->title ?? 'Форма']))
            ->markdown('emails.forms.submission-notification', [
                'form' => $this->submission->form,
                'submission' => $this->submission,
                'payload' => $this->submission->orderedPayload(),
                'meta' => $this->submission->meta ?? [],
            ]);

        foreach ($this->submission->getMedia('attachments') as $media) {
            $this->attach($media->getPath(), [
                'as' => $media->file_name,
                'mime' => $media->mime_type,
            ]);
        }

        return $this;
    }
}
