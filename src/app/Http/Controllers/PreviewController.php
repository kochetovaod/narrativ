<?php

namespace App\Http\Controllers;

use App\Models\Capability;
use App\Models\News;
use App\Models\Page;
use App\Models\PortfolioProject;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Service;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class PreviewController extends Controller
{
    public function __invoke(Request $request, string $type, string $slug): JsonResponse
    {
        $modelClass = $this->resolveModelClass($type);

        abort_unless($modelClass, 404);

        $query = $modelClass::query();

        if (method_exists($modelClass, 'scopeWithDrafts')) {
            $query = $query->withDrafts();
        }

        if (in_array(SoftDeletes::class, class_uses_recursive($modelClass), true)) {
            $query = $query->withTrashed();
        }

        /** @var Model|null $record */
        $record = $query
            ->where(function ($query) use ($slug) {
                $query->where('slug', $slug);

                if (is_numeric($slug)) {
                    $query->orWhereKey($slug);
                }
            })
            ->first();

        abort_unless($record instanceof Model, 404);

        return response()
            ->json([
                'type' => $type,
                'id' => $record->getKey(),
                'slug' => $record->getAttribute('slug'),
            ])
            ->header('X-Robots-Tag', 'noindex, nofollow, noarchive');
    }

    /**
     * @return class-string<Model>|null
     */
    protected function resolveModelClass(string $type): ?string
    {
        $map = [
            'services' => Service::class,
            'products' => Product::class,
            'product_categories' => ProductCategory::class,
            'news' => News::class,
            'portfolio_projects' => PortfolioProject::class,
            'capabilities' => Capability::class,
            'pages' => Page::class,
        ];

        return Arr::get($map, $type);
    }
}
