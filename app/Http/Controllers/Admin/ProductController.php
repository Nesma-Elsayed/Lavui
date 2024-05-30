<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Ask;
use App\Enums\Status;
use App\Http\Traits\ApiResponseTrait;
use App\Http\Traits\ImageTrait;
use App\Models\ProductTag;
use App\Models\ProductTax;
use App\Models\User;
use Exception;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Exports\ProductExport;
use App\Services\ProductService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Requests\ProductRequest;
use App\Http\Requests\PaginateRequest;
use App\Http\Requests\ChangeImageRequest;
use App\Http\Requests\ProductOfferRequest;
use App\Http\Resources\ProductAdminResource;
use App\Http\Requests\ShippingAndReturnRequest;
use App\Http\Resources\ProductDetailsAdminResource;
use App\Http\Resources\SimpleProductDetailsResource;
use App\Http\Resources\simpleProductWithVariationCountResource;

class ProductController extends AdminController
{
    use ApiResponseTrait, ImageTrait;
    public ProductService $productService;
    public object $product;
    protected array $productFilter = [
        'name',
        'sku',
        'slug',
        'buying_price',
        'selling_price',
        'product_category_id',
        'product_brand_id',
        'text_banner_id',
        'group_id',
        'image_featured',
        'size',
        'arm',
        'bridge',
        'barcode_id',
        'tax_id',
        'unit_id',
        'show_stock_out',
        'status',
        'can_purchasable',
        'refundable',
        'weight',
        'order',
        'except'
    ];
    public function __construct(ProductService $productService)
    {
        parent::__construct();
        $this->productService = $productService;
        $this->middleware(['permission:products'])->only('export', 'generateSku');
        $this->middleware(['permission:products_create'])->only('store', 'uploadImage');
        $this->middleware(['permission:products_edit'])->only('update');
        $this->middleware(['permission:products_delete'])->only('destroy', 'deleteImage');
        $this->middleware(['permission:products_show'])->only('show', 'shippingAndReturn');
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
            $products = ProductAdminResource::collection($products);
            return $this->apiResponse(200, 'All Products', $products);
//            return ProductAdminResource::collection($products);
        } catch (Exception $exception) {
            return $this->apiResponse(422, $exception->getMessage());
        }
    }

    public function show(Product $product): \Illuminate\Foundation\Application|\Illuminate\Http\Response|ProductDetailsAdminResource|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
           $product = new ProductDetailsAdminResource($product);
           return $this->apiResponse(200, 'Product', $product);
        } catch (Exception $exception) {
            return $this->apiResponse(422, $exception->getMessage());
        }
    }

    public function store(ProductRequest $request): \Illuminate\Http\Response|ProductAdminResource|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            $imageFeatureName = $this->uploadImage($request->image_featured, 'products');
//            $newArrivalImageName = $request->new_arrival_image ? $this->uploadImage($request->new_arrival_image, 'products') : null;
//            $productTrendImageName = $request->product_trend_image ? $this->uploadImage($request->product_trend_image, 'products') : null;
//            $productStoryImageName = $request->product_story_image ? $this->uploadImage($request->product_story_image, 'products') : null;

            // DB::transaction => 2 syntax
            // Observer design pattern
            DB::transaction(function () use ($request, $imageFeatureName) {
                $this->product = Product::create([
                    'slug' => Str::slug($request->name_en),
                    'variation_price' => $request->selling_price,
                    'image_featured' => $imageFeatureName,
                    'name' => [
                        'en' => $request->name_en,
                        'ar' => $request->name_ar
                    ],
                    'sku' => $request->sku,
                    'size' => $request->size,
                    'arm' => $request->arm,
                    'bridge' => $request->bridge,
                    'product_category_id' => $request->product_category_id,
                    'barcode_id' => $request->barcode_id,
                    'buying_price' => $request->buying_price,
                    'selling_price' => $request->selling_price,
                    'product_brand_id' => $request->product_brand_id,
                    'status' => $request->status,
                    'can_purchasable' => $request->can_purchasable,
                    'show_stock_out' => $request->show_stock_out,
                    'refundable' => $request->refundable,
                    'maximum_purchase_quantity' => $request->maximum_purchase_quantity,
                    'low_stock_quantity_warning' => $request->low_stock_quantity_warning,
                    'unit_id' => $request->unit_id,
                    'double_price_for_two_lens' => $request->double_price_for_two_lens,
                    'description' => [
                        'en' => $request->description_en,
                        'ar' => $request->description_ar
                    ],
                    'group_id' => $request->group_id,
                    'text_banner_id' => $request->text_banner_id,
                    'creator_id' => auth()->user()->id,
                    'creator_type' => User::class,
                    'editor_id' => auth()->user()->id,
                    'editor_type' => User::class,
                ]);

                /// DB::transaction.
//                // New Arrival
//                if ($request->new_arrival_image) {
//                    foreach ($request->tags as $tagItem) {
//                        ProductTag::create([
//                            'product_id' => $this->product->id,
//                            'name'       => $tagItem
//                        ]);
//                    }
//                }

//                // Product Story
//                if ($request->product_story_image) {
//                    foreach ($request->tags as $tagItem) {
//                        ProductTag::create([
//                            'product_id' => $this->product->id,
//                            'name'       => $tagItem
//                        ]);
//                    }
//                }

//                // Product Trend
//                if ($request->product_trend_image) {
//                    foreach ($request->tags as $tagItem) {
//                        ProductTag::create([
//                            'product_id' => $this->product->id,
//                            'name'       => $tagItem
//                        ]);
//                    }
//                }

                if ($request->tags) {
                    foreach ($request->tags as $tagItem) {
                        ProductTag::create([
                            'product_id' => $this->product->id,
                            'name'       => $tagItem,
                            'creator_id' => auth()->user()->id,
                            'creator_type' => User::class,
                            'editor_id' => auth()->user()->id,
                            'editor_type' => User::class,
                        ]);
                    }
                }

                if ($request->tax_id) {
                    ProductTax::create([
                        'product_id' => $this->product->id,
                        'tax_id'     => $request->tax_id,
                        'creator_id' => auth()->user()->id,
                        'creator_type' => User::class,
                        'editor_id' => auth()->user()->id,
                        'editor_type' => User::class,
                    ]);
                }
            });
            return $this->apiResponse(200, 'Product Created Successfully', new ProductAdminResource($this->product));
        } catch (Exception $exception) {
            return $this->apiResponse(422, $exception->getMessage());
        }
    }

    public function update(ProductRequest $request, Product $product): \Illuminate\Http\Response|ProductAdminResource|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            $imageFeatureName = $request->hasFile('image_featured') ? $this->uploadImage($request->image_featured, 'products',"uploaded/products/" . $product->image_featured) : $product->image_featured;
//            $newArrivalImageName = $request->hasFile('new_arrival_image') ? $this->uploadImage($request->new_arrival_image, 'products',"uploaded/products/" . $product->new_arrival_image) : $product->new_arrival_image;
            DB::transaction(function () use ($request, $product, $imageFeatureName) {
                $product->update([
                    'slug' => Str::slug($request->name_en),
                    'image_featured' => $imageFeatureName,
                    'name' => [
                        'en' => $request->name_en,
                        'ar' => $request->name_ar
                    ],
                    'sku' => $request->sku,
                    'size' => $request->size,
                    'arm' => $request->arm,
                    'bridge' => $request->bridge,
                    'product_category_id' => $request->product_category_id,
                    'barcode_id' => $request->barcode_id,
                    'buying_price' => $request->buying_price,
                    'selling_price' => $request->selling_price,
                    'product_brand_id' => $request->product_brand_id,
                    'status' => $request->status,
                    'can_purchasable' => $request->can_purchasable,
                    'show_stock_out' => $request->show_stock_out,
                    'refundable' => $request->refundable,
                    'maximum_purchase_quantity' => $request->maximum_purchase_quantity,
                    'low_stock_quantity_warning' => $request->low_stock_quantity_warning,
                    'unit_id' => $request->unit_id,
                    'double_price_for_two_lens' => $request->double_price_for_two_lens,
                    'description' => [
                        'en' => $request->description_en,
                        'ar' => $request->description_ar
                    ],
                    'group_id' => $request->group_id,
                    'text_banner_id' => $request->text_banner_id,
                    'editor_id' => auth()->user()->id,
                    'editor_type' => User::class,
                ]);

                if ($request->tags) {
                    $product->tags()->delete();
                    foreach ($request->tags as $tagItem) {
                        ProductTag::create([
                            'product_id' => $product->id,
                            'name'       => $tagItem
                        ]);
                    }
                }

                if ($request->tax_id) {
                    $product->taxes()->delete();
                    ProductTax::create([
                        'product_id' => $product->id,
                        'tax_id'     => $request->tax_id
                    ]);
                }

                if (!$request->tax_id) {
                    $product->taxes()->delete();
                }

                if ($product->variations) {
                    $this->product = Product::find($product->id);
                    $checkMinPrice = $product->variations->min('price');
                    if ($checkMinPrice) {
                        $this->product->variation_price = $checkMinPrice;
                        $this->product->save();
                    }
                }
            });
            return $this->apiResponse(200, 'Product Updated Successfully', new ProductAdminResource($product));
//            return new ProductAdminResource($this->productService->update($request, $product));
        } catch (Exception $exception) {
            return $this->apiResponse(422, $exception->getMessage());
        }
    }

    public function destroy(Product $product): \Illuminate\Http\Response|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            $this->productService->destroy($product);
            return response('', 202);
        } catch (Exception $exception) {
            return $this->apiResponse(422, $exception->getMessage());
        }
    }

//    public function uploadImage(ChangeImageRequest $request, Product $product): \Illuminate\Foundation\Application|\Illuminate\Http\Response|ProductDetailsAdminResource|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
//    {
//        try {
//            return new ProductDetailsAdminResource($this->productService->uploadImage($request, $product));
//        } catch (Exception $exception) {
//            return response(['status' => false, 'message' => $exception->getMessage()], 422);
//        }
//    }

    public function deleteImage(Product $product, $index): \Illuminate\Foundation\Application|\Illuminate\Http\Response|ProductDetailsAdminResource|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return new ProductDetailsAdminResource($this->productService->deleteImage($product, $index));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function export(PaginateRequest $request): \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return Excel::download(new ProductExport($this->productService, $request), 'Product.xlsx');
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function generateSku(): \Illuminate\Foundation\Application|\Illuminate\Http\Response|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return response(['data' => ['product_sku' => $this->productService->generateSku()]], 200);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function shippingAndReturn(ShippingAndReturnRequest $request, Product $product): \Illuminate\Foundation\Application|\Illuminate\Http\Response|ProductAdminResource|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            DB::transaction(function () use ($request, $product) {
                $product->update($request->validated());
            });
            return $this->apiResponse(200, 'Shipping and return', new ProductAdminResource($product));
        } catch (Exception $exception) {
            return $this->apiResponse(422, $exception->getMessage());
        }
    }
    public function productOffer(ProductOfferRequest $request, Product $product): \Illuminate\Foundation\Application|\Illuminate\Http\Response|ProductAdminResource|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            DB::transaction(function () use ($request, $product) {
                $this->product              = $product;
                $product->add_to_flash_sale = $request->add_to_flash_sale;
                $product->discount          = $request->discount;
                $product->offer_start_date  = date('Y-m-d H:i:s', strtotime($request->offer_start_date));
                $product->offer_end_date    = date('Y-m-d H:i:s', strtotime($request->offer_end_date));
                $product->save();
            });
            return $this->apiResponse(200,  'Product Offer',new ProductAdminResource($product));
        } catch (Exception $exception) {
            return $this->apiResponse(422, $exception->getMessage());
        }
    }
    public function purchasableProducts(): \Illuminate\Foundation\Application|\Illuminate\Http\Response|\Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            $product = Product::select('id', 'name', 'buying_price', 'can_purchasable', 'status', 'sku')
                ->with('productTaxes')
                ->with('variations')
                ->where('can_purchasable', ASK::YES)
                ->where('status', Status::ACTIVE)
                ->orderBy('name', 'asc')
                ->get();
            return $this->apiResponse(200, 'purchasable Products', simpleProductWithVariationCountResource::collection($product));
//            return  simpleProductWithVariationCountResource::collection($this->productService->purchasableProducts());
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function simpleProducts(): \Illuminate\Foundation\Application|\Illuminate\Http\Response|\Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            $product = Product::select('id', 'name', 'buying_price', 'status', 'sku')
                ->with('productTaxes')
                ->with('variations')
                ->orderBy('name', 'asc')
                ->get();
            return $this->apiResponse(200, 'Simple Product', simpleProductWithVariationCountResource::collection($product));
//            return  simpleProductWithVariationCountResource::collection($this->productService->simpleProducts());
        } catch (Exception $exception) {
            return $this->apiResponse(422, $exception->getMessage());
        }
    }

    public function posProduct(Product $product, Request $request): SimpleProductDetailsResource|\Illuminate\Foundation\Application|\Illuminate\Http\Response|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            $product = Product::with('media', 'videos', 'category', 'unit', 'taxes')
                ->with(['seo' => fn ($query) => $query->with('media')])
                ->withSum('stockItems', 'quantity')
                ->with(['wishlist' => fn ($query) => $query->where('user_id', Auth::check() ? Auth::user()->id : 0)])
                ->with(['reviews' => fn ($query) => $query->with('user', 'media')->take($request->get('review_limit', 3))])
//                ->withReviewRating()
                ->where(['id' => $product->id, 'status' => Status::ACTIVE])
                ->first();
            return $this->apiResponse(200, 'Pos Product', new SimpleProductDetailsResource($product));
//            return new SimpleProductDetailsResource($this->productService->showWithRelation($product, $request));
        } catch (Exception $exception) {
            return $this->apiResponse(422, $exception->getMessage());
        }
    }
}
