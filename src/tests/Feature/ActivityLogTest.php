<?php

namespace Tests\Feature;

use App\Models\Form;
use App\Models\FormSubmission;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_changes_are_logged_with_causer(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $service = Service::create([
            'title' => 'Audit service',
            'slug' => 'audit-service',
            'seo_description' => 'Initial description',
        ]);

        $service->update([
            'title' => 'Updated audit service',
            'seo_description' => 'Updated description',
        ]);

        $this->assertDatabaseHas('activity_log', [
            'log_name' => 'audit',
            'subject_type' => Service::class,
            'subject_id' => $service->id,
            'event' => 'created',
            'causer_id' => $user->id,
        ]);

        $updateActivity = Activity::query()
            ->where('subject_type', Service::class)
            ->where('subject_id', $service->id)
            ->where('event', 'updated')
            ->latest()
            ->first();

        $this->assertNotNull($updateActivity);
        $this->assertSame('audit', $updateActivity->log_name);
        $this->assertSame('Updated audit service', data_get($updateActivity->properties, 'attributes.title'));
        $this->assertSame('Initial description', data_get($updateActivity->properties, 'old.seo_description'));
        $this->assertSame($user->id, $updateActivity->causer_id);
    }

    public function test_form_submission_logs_status_without_payload(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $form = Form::create([
            'title' => 'Contact',
            'slug' => 'contact',
            'recipients' => ['team@example.com'],
            'is_active' => true,
        ]);

        $submission = FormSubmission::create([
            'form_id' => $form->id,
            'status' => FormSubmission::STATUS_NEW,
            'payload' => ['message' => 'Hello'],
            'meta' => ['ip' => '127.0.0.1'],
        ]);

        $submission->markAsSpam();

        $activity = Activity::query()
            ->where('subject_type', FormSubmission::class)
            ->where('subject_id', $submission->id)
            ->where('event', 'updated')
            ->latest()
            ->first();

        $this->assertNotNull($activity);
        $this->assertSame('audit', $activity->log_name);
        $this->assertSame(FormSubmission::STATUS_SPAM, data_get($activity->properties, 'attributes.status'));
        $this->assertFalse(array_key_exists('payload', (array) data_get($activity->properties, 'attributes')));
    }

    public function test_user_activity_does_not_store_password(): void
    {
        $user = User::factory()->create();

        $activity = Activity::query()
            ->where('subject_type', User::class)
            ->where('subject_id', $user->id)
            ->where('event', 'created')
            ->first();

        $this->assertNotNull($activity);
        $this->assertFalse(array_key_exists('password', (array) data_get($activity->properties, 'attributes')));
    }
}
