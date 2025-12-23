<?php

namespace App\Domains\Forms\Actions;

use App\Domains\Forms\Models\Form;
use App\Domains\Forms\Models\FormSubmission;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class SubmitFormAction
{
    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $meta
     * @param  array<string, UploadedFile|array<int, UploadedFile>|null>  $files
     */
    public function __invoke(Form $form, array $data, array $meta = [], array $files = []): FormSubmission
    {
        $fields = $form->fields()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $rules = [];

        foreach ($fields as $field) {
            $baseRules = [$field->is_required ? 'required' : 'nullable'];

            $selectOptions = collect($field->options)
                ->pluck('value')
                ->filter()
                ->values()
                ->all();

            $baseRules[] = match ($field->type) {
                'email' => 'email|max:255',
                'number' => 'numeric',
                'file' => 'file',
                'checkbox' => 'boolean',
                default => 'string',
            };

            $rules[$field->name] = array_filter(array_merge(
                $baseRules,
                (array) ($field->validation_rules ?? []),
                [$field->type === 'select' && ! empty($selectOptions) ? Rule::in($selectOptions) : null],
            ));
        }

        $validatorData = array_merge(
            Arr::only($data, $fields->pluck('name')->all()),
            Arr::only($files, $fields->pluck('name')->all()),
        );

        $validated = Validator::make($validatorData, $rules)->validate();

        $payload = [];
        $replyToField = $form->getSetting('email_reply_to_field');
        $replyTo = null;

        foreach ($fields as $field) {
            $value = $validated[$field->name] ?? null;

            if ($field->type === 'checkbox') {
                $value = (bool) $value;
            }

            if ($field->type === 'file' && ($files[$field->name] ?? null) instanceof UploadedFile) {
                /** @var UploadedFile $file */
                $file = $files[$field->name];

                $value = $file->getClientOriginalName();
            }

            $payload[$field->name] = $value;

            if ($replyToField && $field->name === $replyToField && is_string($value)) {
                $replyTo = $value;
            }
        }

        $submission = $form->submissions()->create([
            'status' => FormSubmission::STATUS_NEW,
            'payload' => $payload,
            'meta' => $meta,
            'reply_to' => $replyTo,
            'is_spam' => false,
        ]);

        foreach ($fields as $field) {
            if ($field->type !== 'file') {
                continue;
            }

            $file = $files[$field->name] ?? null;

            if ($file instanceof UploadedFile && $file->isValid()) {
                $media = $submission
                    ->addMedia($file)
                    ->usingName($file->getClientOriginalName())
                    ->toMediaCollection('attachments');

                $submission->attachMediaToSubmission($media, $field->name);
            }
        }

        SendFormSubmissionNotificationJob::dispatch($submission);

        return $submission;
    }
}
