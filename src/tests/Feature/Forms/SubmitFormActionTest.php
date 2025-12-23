<?php

namespace Tests\Feature\Forms;

use App\Domains\Forms\Actions\SendFormSubmissionNotificationJob;
use App\Domains\Forms\Actions\SubmitFormAction;
use App\Domains\Forms\Models\Form;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class SubmitFormActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_submission_and_dispatches_notification(): void
    {
        Bus::fake();

        $form = Form::create([
            'title' => 'Feedback',
            'slug' => 'feedback',
            'recipients' => ['admin@example.com'],
            'settings' => [
                'email_reply_to_field' => 'email',
            ],
            'is_active' => true,
        ]);

        $form->fields()->create([
            'type' => 'checkbox',
            'label' => 'Согласие',
            'name' => 'consent',
            'is_required' => true,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $form->fields()->create([
            'type' => 'email',
            'label' => 'Email',
            'name' => 'email',
            'is_required' => true,
            'is_active' => true,
            'sort_order' => 2,
        ]);

        $form->fields()->create([
            'type' => 'textarea',
            'label' => 'Сообщение',
            'name' => 'message',
            'is_required' => false,
            'is_active' => true,
            'sort_order' => 3,
        ]);

        $action = app(SubmitFormAction::class);

        $submission = $action(
            $form,
            [
                'email' => 'user@example.com',
                'message' => 'Hello team',
                'consent' => true,
            ],
            ['ip' => '127.0.0.1'],
        );

        $this->assertSame(['consent', 'email', 'message'], array_keys($submission->payload));
        $this->assertTrue($submission->payload['consent']);
        $this->assertSame('user@example.com', $submission->reply_to);
        $this->assertSame('127.0.0.1', $submission->meta['ip']);

        Bus::assertDispatched(SendFormSubmissionNotificationJob::class, function ($job) use ($submission) {
            return $job->submission->is($submission);
        });
    }
}
