<?php

namespace App\Domains\Forms\Data;

use App\Domains\Forms\Models\FormSubmission;
use Illuminate\Support\Carbon;

class FormSubmissionData
{
    public function __construct(
        public readonly int $id,
        public readonly string $status,
        public readonly bool $isSpam,
        public readonly ?string $replyTo,
        public readonly array $payload,
        public readonly ?array $meta,
        public readonly Carbon $createdAt,
    ) {
    }

    public static function fromModel(FormSubmission $submission): self
    {
        return new self(
            $submission->id,
            $submission->status,
            (bool) $submission->is_spam,
            $submission->reply_to,
            $submission->payload ?? [],
            $submission->meta,
            $submission->created_at,
        );
    }

    public function toExportRow(): array
    {
        return [
            'id' => $this->id,
            'status' => FormSubmission::STATUS_LABELS[$this->status] ?? $this->status,
            'is_spam' => $this->isSpam ? 'yes' : 'no',
            'reply_to' => $this->replyTo,
            'payload' => json_encode($this->payload),
            'meta' => json_encode($this->meta),
            'created_at' => $this->createdAt,
        ];
    }
}
