<?php

namespace App\Http\Controllers\Frontend;


use App\Enums\Ask;
use App\Enums\Status;
use App\Http\Resources\ProductRelationResource;
use App\Http\Resources\SimpleProductDetailsResource;
use App\Http\Resources\SimpleProductResource;
use App\Http\Traits\ApiResponseTrait;
use App\Models\Product;
use Carbon\Carbon;
use Exception;
use App\Http\Controllers\Controller;
use App\Http\Requests\PaginateRequest;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    use ApiResponseTrait;
    public ProductService $productService;

    protected array $productFilter = [
        'name',
        'sku',
        'slug',
        'buying_price',
        'selling_price',
        'product_category_id',
        'product_brand_id',
        'barcode_id',
        'tax_id',
        'unit_id',
        'show_stock_out',
        'status',
        'can_purchasable',
        'refundable',
        'weight',
        'order',
        'image_featured',
        'except'
    ];
    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    public function index(PaginateRequest $request): \Illuminate\Http\Response|\Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            $requests    = $request->all();
            $method      = $request->get('paginate', 0) == 1 ? 'paginate' : 'get';
            $methodValue = $request->get('paginate', 0) == 1 ? $request->get('per_page', 10) : '*';
            $orderColumn = $request->get('order_column') ?? 'id';
            $orderType   = $request->get('order_type') ?? 'desc';

            $products = Product::with('category', 'brand', 'taxes', 'tags', 'reviews', 'group', 'textBanner')->with(['wishlist' => fn ($query) => $query->where('user_id', Auth::check() ? Auth::user()->id : 0)])->withReviewRating()->where(function ($query) use ($requests) {
                foreach ($requests as $key => $request) {
                    if (in_array($key, $this->productFilter)) {
                        if ($key == "except") {
                            $explodes = explode('|', $request);
                            if (count($explodes)) {
                                foreach ($explodes as $explode) {
                                    $query->where('id', '!=', $explode);
                                }
                            }
                        } else {
                            if ($key == "product_category_id") {
                                $query->where($key, $request);
                            } elseif ($key == "tax_id") {
                                $query->whereHas('taxes', function ($q) use ($key, $request) {
                                    $q->where($key, $request);
                                });
                            } else {
                                $query->where($key, 'like', '%' . $request . '%');
                            }
                        }
                    }
                }
            })->orderBy($orderColumn, $orderType)->$method(
                $methodValue
            );
//            return SimpleProductResource::collection($this->productService->list($request));
            return $this->apiResponse(200, 'All Products', SimpleProductResource::collection($products));
        } catch (Exception $exception) {
            return $this->apiResponse(422, $exception->getMessage());
        }
    }

    public function show(Product $product, Request $request): SimpleProductDetailsResource|\Illuminate\Foundation\Application|\Illuminate\Http\Response|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            $product = Product::with('media', 'videos', 'category', 'unit', 'taxes')
                ->with(['seo' => fn ($query) => $query->with('media')])
                ->withSum('stockItems', 'quantity')
                ->with(['wishlist' => fn ($query) => $query->where('user_id', Auth::check() ? Auth::user()->id : 0)])
//                ->with(['reviews' => fn ($query) => $query->with('user', 'media')->take($request->get('review_limit', 3))])
//                ->withReviewRating()
                ->where(['id' => $product->id, 'status' => Status::ACTIVE])
                ->first();
            return $this->apiResponse(200, 'Product', new SimpleProductDetailsResource($product));
        } catch (Exception $exception) {
            return $this->apiResponse(422, $exception->getMessage());
        }
    }

    public function mostPopularProducts(PaginateRequest $request): \Illuminate\Http\Response|\Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            $method      = $request->get('paginate', 0) == 1 ? 'paginate' : 'get';
            $methodValue = $request->get('paginate', 0) == 1 ? $request->get('per_page', 32) : '*';
            $orderColumn = $request->get('order_column') ?? 'id';
            $orderType   = $request->get('order_type') ?? 'desc';
            $rand        = $request->get('rand', 0) > 0 ? $request->get('rand') : 0;

            $products = Product::select('products.id', 'image_featured','products.name', 'products.sku', 'products.slug', 'products.selling_price', 'products.variation_price', 'products.add_to_flash_sale', 'products.offer_start_date', 'products.offer_end_date', 'products.discount', 'products.status', 'product_category_id', 'product_brand_id')
                ->with(['wishlist' => fn ($query) => $query->where('user_id', Auth::check() ? Auth::user()->id : 0)])
                ->withCount('orderCountable')
                ->where(['status' => Status::ACTIVE])
                ->orderBy('order_countable_count', 'desc')
                ->randAndLimitOrOrderBy($rand, $orderColumn, $orderType)
                ->$method($methodValue);
            return $this->apiResponse(200, 'Most Popular Products', SimpleProductResource::collection($products));
//            return SimpleProductResource::collection($this->productService->mostPopularProducts($request));
        } catch (Exception $exception) {
            return $this->apiResponse(422, $exception->getMessage());
        }
    }

//    public function categoryWiseProducts(Request $request): \Illuminate\Foundation\Application|\Illuminate\Http\Response|ProductRelationResource|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
//    {
//        try {
//            return new ProductRelationResource($this->productService->categoryWiseProducts($request));
//        } catch (Exception $exception) {
//            return response(['status' => false, 'message' => $exception->getMessage()], 422);
//        }
//    }

    public function flashSaleProducts(PaginateRequest $request): \Illuminate\Foundation\Application|\Illuminate\Http\Response|\Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            $now         = Carbon::now();
            $method      = $request->get('paginate', 0) == 1 ? 'paginate' : 'get';
            $methodValue = $request->get('paginate', 0) == 1 ? $request->get('per_page', 32) : '*';
            $orderColumn = $request->get('order_column') ?? 'id';
            $orderType   = $request->get('order_type') ?? 'desc';
            $rand        = $request->get('rand', 0) > 0 ? $request->get('rand') : 0;

            $flashSaleProducts = Product::select('products.id','image_featured' ,'products.name', 'products.sku', 'products.slug', 'products.selling_price', 'products.variation_price', 'products.add_to_flash_sale', 'products.offer_start_date', 'products.offer_end_date', 'products.discount', 'products.status', 'product_category_id', 'product_brand_id')
                ->with(['wishlist' => fn ($query) => $query->where('user_id', Auth::check() ? Auth::user()->id : 0)])
                ->with('media', 'variations', 'reviews')
                ->active('products.status')
                ->where('products.add_to_flash_sale', Ask::YES)
                ->where('products.offer_start_date', '<=', $now)
                ->where('products.offer_end_date', '>=', $now)
                ->randAndLimitOrOrderBy($rand, $orderColumn, $orderType)
                ->$method($methodValue);
            return $this->apiResponse(200,'Flash Sale Products', SimpleProductResource::collection($flashSaleProducts));
//            return SimpleProductResource::collection($this->productService->flashSaleProducts($request));
        } catch (Exception $exception) {
            return $this->apiResponse(422, $exception->getMessage());
        }
    }

    public function offerProducts(PaginateRequest $request): \Illuminate\Foundation\Application|\Illuminate\Http\Response|\Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            $now         = Carbon::now();
            $method      = $request->get('paginate', 0) == 1 ? 'paginate' : 'get';
            $methodValue = $request->get('paginate', 0) == 1 ? $request->get('per_page', 32) : '*';
            $orderColumn = $request->get('order_column') ?? 'id';
            $orderType   = $request->get('order_type') ?? 'desc';
            $rand        = $request->get('rand', 0) > 0 ? $request->get('rand') : 0;

            $offerProducts = Product::select('products.id','image_featured' ,'products.name', 'products.sku', 'products.slug', 'products.selling_price', 'products.variation_price', 'products.add_to_flash_sale', 'products.offer_start_date', 'products.offer_end_date', 'products.discount', 'products.status', 'product_category_id', 'product_brand_id')
                ->with(['wishlist' => fn ($query) => $query->where('user_id', Auth::check() ? Auth::user()->id : 0)])
                ->with('media', 'variations', 'reviews')
                ->active('products.status')
                ->where('products.offer_start_date', '<=', $now)
                ->where('products.offer_end_date', '>=', $now)
                ->randAndLimitOrOrderBy($rand, $orderColumn, $orderType)
                ->$method($methodValue);
            return $this->apiResponse(200, 'Offer Products', SimpleProductResource::collection($offerProducts));
//            return SimpleProductResource::collection($this->productService->offerProducts($request));
        } catch (Exception $exception) {
            return $this->apiResponse(422, $exception->getMessage());
        }
    }

    public function wishlistProducts(PaginateRequest $request): \Illuminate\Foundation\Application|\Illuminate\Http\Response|\Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            $method      = $request->get('paginate', 0) == 1 ? 'paginate' : 'get';
            $methodValue = $request->get('paginate', 0) == 1 ? $request->get('per_page', 32) : '*';
            $orderColumn = $request->get('order_column') ?? 'id';
            $orderType   = $request->get('order_type') ?? 'desc';
            $rand        = $request->get('rand', 0) > 0 ? $request->get('rand') : 0;

            $wishListProducts = Product::select('products.id', 'products.name', 'products.sku', 'products.slug', 'products.selling_price', 'products.variation_price', 'products.add_to_flash_sale', 'products.offer_start_date', 'products.offer_end_date', 'products.discount', 'products.status')
                ->with('media', 'variations', 'reviews', 'wishlist')
                ->whereHas('wishlist', function ($query) {
                    return $query->where('user_id', Auth::user()->id);
                })
                ->active('products.status')
                ->randAndLimitOrOrderBy($rand, $orderColumn, $orderType)
                ->$method($methodValue);
            return $this->apiResponse(200, 'Wish List Products', $wishListProducts);
//            return SimpleProductResource::collection($this->productService->wishlistProducts($request));
        } catch (Exception $exception) {
            return $this->apiResponse(422, $exception->getMessage());
        }
    }

    public function relatedProducts(Product $product, PaginateRequest $request): \Illuminate\Foundation\Application|\Illuminate\Http\Response|\Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            $productTags = $product->tags;
            $method      = $request->get('paginate', 0) == 1 ? 'paginate' : 'get';
            $methodValue = $request->get('paginate', 0) == 1 ? $request->get('per_page', 32) : '*';
            $orderColumn = $request->get('order_column') ?? 'id';
            $orderType   = $request->get('order_type') ?? 'desc';
            $rand        = $request->get('rand', 0) > 0 ? $request->get('rand') : 0;

            if (count($productTags) > 0) {
                $relatedProducts = Product::select('products.id','image_featured' ,'products.name', 'products.sku', 'products.slug', 'products.selling_price', 'products.variation_price', 'products.add_to_flash_sale', 'products.offer_start_date', 'products.offer_end_date', 'products.discount', 'products.status', 'product_category_id', 'product_brand_id')
                    ->with(['wishlist' => fn ($query) => $query->where('user_id', Auth::check() ? Auth::user()->id : 0)])
                    ->with('media', 'variations', 'reviews', 'tags')
                    ->active('products.status')
                    ->whereHas('tags', function ($query) use ($productTags) {
                        if (count($productTags) > 0) {
                            $i = 0;
                            foreach ($productTags as $productTag) {
                                if ($i === 0) {
                                    $query->where('name', 'like', '%' . $productTag->name . '%');
//                                    dd($query);
                                } else {
                                    $query->orWhere('name', 'like', '%' . $productTag->name . '%');
                                }
                                $i++;
                            }
                        }
                        return $query;
                    })
                    ->whereNot('id', $product->id)
                    ->randAndLimitOrOrderBy($rand, $orderColumn, $orderType)
                    ->$method($methodValue);

                return $this->apiResponse(200, 'Related Products', SimpleProductResource::collection($relatedProducts));
            } else {
               return $this->apiResponse(200, 'No related data', collect([]));
            }
//            return SimpleProductResource::collection($this->productService->relatedProducts($product, $request));
        } catch (Exception $exception) {
            return $this->apiResponse(422, $exception->getMessage());
        }
    }
}
