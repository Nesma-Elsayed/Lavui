<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Admin\AdminController;
use App\Http\Traits\ApiResponseTrait;
use App\Models\ProductSection;
use Exception;
use App\Http\Requests\PaginateRequest;
use App\Services\ProductSectionService;
use Illuminate\Support\Facades\Auth;

class ProductSectionController extends AdminController
{
    use ApiResponseTrait;
    private ProductSectionService $productSectionService;

    public function __construct(ProductSectionService $productSectionService)
    {
        parent::__construct();
        $this->productSectionService = $productSectionService;
    }

    public function index(PaginateRequest $request)
    {
        try {
            $productSectionsWithProducts = ProductSection::select(
                'product_sections.id',
                'product_sections.name',
                'product_sections.slug',
                'product_sections.status')
                ->with(['products' => function ($query) {
                $query->select(
                    'products.id', 'products.name', 'products.sku', 'products.slug', 'products.selling_price',
                    'products.variation_price', 'products.add_to_flash_sale', 'products.offer_start_date',
                    'products.offer_end_date', 'products.discount', 'products.status')
                    ->with(['wishlist' => fn($query) => $query->where('user_id', Auth::check() ? Auth::user()->id : 0)])
                    ->withReviewRating()
                    ->with('media', 'variations', 'reviews')
                    ->active('products.status')
                    ->whereNull('deleted_at');
            }])->active('product_sections.status')->orderBy('id', 'asc')->get()->map(function ($query) {
                $query->setRelation('products', $query->products->take(8));
                return $query;
            });
            return $this->apiResponse(200, 'All Product sections with products', $productSectionsWithProducts);
        } catch (Exception $exception) {
            return $this->apiResponse(422, $exception->getMessage());
        }
    }

    public function show(ProductSection $productSection)
    {
        try {
            return $this->apiResponse(200, 'Product Section', $productSection);
        } catch (Exception $exception) {
            return $this->apiResponse(422, $exception->getMessage());
        }
    }
}
