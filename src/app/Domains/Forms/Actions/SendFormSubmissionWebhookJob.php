<?php

namespace App\Domains\Forms\Actions;

use App\Domains\Forms\Data\FormSubmissionData;
use App\Domains\Forms\Models\FormSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class SendFormSubmissionWebhookJob implements ShouldQueue
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
        $submission = $this->submission->load('form');

        $webhookUrl = $submission->form?->getSetting('webhook_url');

        if (! is_string($webhookUrl) || ! filter_var($webhookUrl, FILTER_VALIDATE_URL)) {
            return;
        }

        $data = FormSubmissionData::fromModel($submission);

        Http::asJson()->post($webhookUrl, [
            'form' => [
                'id' => $submission->form?->id,
                'title' => $submission->form?->title,
                'slug' => $submission->form?->slug,
            ],
            'submission' => [
                'id' => $data->id,
                'status' => $data->status,
                'status_label' => FormSubmission::STATUS_LABELS[$data->status] ?? $data->status,
                'is_spam' => $data->isSpam,
                'reply_to' => $data->replyTo,
                'payload' => $data->payload,
                'meta' => $data->meta,
                'created_at' => $data->createdAt->toIso8601String(),
            ],
        ])->throw();
    }
}
