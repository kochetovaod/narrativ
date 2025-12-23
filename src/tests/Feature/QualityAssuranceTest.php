<?php

namespace Tests\Feature;

use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\ProductAttribute;
use App\Domains\Catalog\Models\ProductAttributeValue;
use App\Domains\Catalog\Models\ProductCategory;
use App\Domains\Content\Models\Service;
use App\Domains\Forms\Models\Form;
use App\Domains\Forms\Models\FormSubmission;
use App\Domains\Menu\Models\Menu;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class QualityAssuranceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::enableForeignKeyConstraints();
    }

    public function test_published_scope_and_slug_validation(): void
    {
        Service::create([
            'title' => 'Published service',
            'slug' => 'published-service',
            'is_published' => true,
            'published_at' => Carbon::now()->subDay(),
        ]);

        Service::create([
            'title' => 'Future service',
            'slug' => 'future-service',
            'is_published' => true,
            'published_at' => Carbon::now()->addDay(),
        ]);

        Service::create([
            'title' => 'Draft service',
            'slug' => 'draft-service',
            'is_published' => false,
        ]);

        $this->assertSame([
            'published-service',
        ], Service::published()->pluck('slug')->all());

        $this->expectException(ValidationException::class);

        Service::create([
            'title' => 'Duplicate slug',
            'slug' => 'published-service',
            'is_published' => true,
        ]);
    }

    public function test_media_requires_custom_properties_for_images(): void
    {
        Storage::fake('public');

        config([
            'filesystems.default' => 'public',
            'media-library.disk_name' => 'public',
        ]);

        $service = Service::create([
            'title' => 'Media test',
            'slug' => 'media-test',
            'is_published' => true,
        ]);

        try {
            $service->addMediaFromString('image-data')
                ->usingFileName('image.jpg')
                ->toMediaCollection('images');

            $this->fail('Media without required custom properties should not be saved.');
        } catch (ValidationException $exception) {
            $this->assertStringContainsString('alt', $exception->getMessage());
        }

        $media = $service->addMediaFromString('image-data')
            ->usingFileName('image-with-meta.jpg')
            ->withCustomProperties([
                'alt' => 'Alt text',
                'title' => 'Title text',
            ])
            ->toMediaCollection('images');

        $this->assertSame('Alt text', $media->getCustomProperty('alt'));
        $this->assertSame('Title text', $media->getCustomProperty('title'));
    }

    public function test_form_settings_defaults_and_spam_flags(): void
    {
        $form = Form::create([
            'title' => 'Contact',
            'slug' => 'contact',
            'recipients' => ['team@example.com'],
            'settings' => [
                'enable_turnstile' => true,
                'rate_limit_per_ip' => 10,
            ],
            'is_active' => true,
        ]);

        $settings = $form->getSettingsWithDefaults();

        $this->assertTrue($settings['enable_turnstile']);
        $this->assertTrue($settings['enable_honeypot']);
        $this->assertEquals(10, $settings['rate_limit_per_ip']);
        $this->assertEquals(Form::DEFAULT_SETTINGS['rate_limit_per_form'], $settings['rate_limit_per_form']);

        $submission = FormSubmission::create([
            'form_id' => $form->id,
            'status' => FormSubmission::STATUS_NEW,
            'payload' => ['name' => 'John'],
            'meta' => ['ip' => '127.0.0.1'],
            'reply_to' => 'user@example.com',
        ]);

        $submission->markAsSpam();
        $submission->refresh();

        $this->assertTrue($submission->is_spam);
        $this->assertSame(FormSubmission::STATUS_SPAM, $submission->status);

        $submission->markAsNotSpam();
        $submission->refresh();

        $this->assertFalse($submission->is_spam);
        $this->assertSame(FormSubmission::STATUS_NEW, $submission->status);
    }

    public function test_menu_hierarchy_and_casts(): void
    {
        $menu = Menu::create([
            'name' => 'Header',
            'slug' => 'header',
        ]);

        $parent = $menu->items()->create([
            'title' => 'Home',
            'link_type' => 'url',
            'url' => 'https://example.com',
            'is_visible' => true,
            'open_in_new_tab' => true,
            'sort_order' => 1,
        ]);

        $child = $menu->items()->create([
            'title' => 'News',
            'link_type' => 'route',
            'route_name' => 'news.index',
            'parent_id' => $parent->id,
            'is_visible' => false,
            'sort_order' => 5,
        ]);

        $this->assertSame([$parent->id], $menu->rootItems()->pluck('id')->all());
        $this->assertSame($child->id, $parent->children()->first()->id);
        $this->assertTrue($parent->fresh()->open_in_new_tab);
        $this->assertFalse($child->fresh()->is_visible);
    }

    public function test_catalog_attribute_values_cascade_on_force_delete(): void
    {
        $category = ProductCategory::create([
            'title' => 'Equipment',
            'slug' => 'equipment',
            'is_published' => true,
        ]);

        $product = Product::create([
            'product_category_id' => $category->id,
            'title' => 'Robot Arm',
            'slug' => 'robot-arm',
            'is_published' => true,
        ]);

        $attribute = ProductAttribute::create([
            'title' => 'Voltage',
            'slug' => 'voltage',
            'type' => 'select',
        ]);

        $value = ProductAttributeValue::create([
            'product_attribute_id' => $attribute->id,
            'value' => '220V',
        ]);

        $value->products()->attach($product->id, [
            'product_attribute_id' => $attribute->id,
            'number_value' => 220,
        ]);

        $this->assertSame(1, DB::table('product_attribute_value_product')->where('product_id', $product->id)->count());

        $product->forceDelete();

        $this->assertSame(0, DB::table('product_attribute_value_product')->where('product_id', $product->id)->count());
    }

    public function test_userstamp_foreign_keys_use_set_null(): void
    {
        $foreignKeys = collect(DB::select("PRAGMA foreign_key_list('services')"));

        $this->assertNotEmpty($foreignKeys->where('table', 'users'));
        $this->assertTrue(
            $foreignKeys
                ->whereIn('from', ['created_by', 'updated_by', 'deleted_by'])
                ->every(fn ($fk) => strtoupper($fk->on_delete) === 'SET NULL')
        );
    }
}
