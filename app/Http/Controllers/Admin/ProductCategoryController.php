<?php

namespace App\Http\Controllers\Admin;

use App\Http\Resources\ProductCategoryDepthTreeResource;
use App\Http\Traits\ApiResponseTrait;
use App\Http\Traits\ImageTrait;
use App\Models\User;
use Exception;
use App\Services\ProductCategoryService;
use App\Http\Requests\PaginateRequest;
use App\Http\Requests\ProductCategoryRequest;
use App\Http\Resources\ProductCategoryResource;
use App\Models\ProductCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductCategoryController extends AdminController
{
    use ApiResponseTrait, ImageTrait;
    private ProductCategoryService $productCategoryService;

    protected array $productCateFilter = [
        'name',
        'slug',
        'description',
        'status',
        'image'
    ];

    protected array $exceptFilter = [
        'excepts'
    ];
    public function __construct(ProductCategoryService $productCategory)
    {
        parent::__construct();
        $this->productCategoryService = $productCategory;
        $this->middleware(['permission:settings'])->only('store', 'update', 'destroy', 'show');
    }

//    public function depthTree(): \Illuminate\Foundation\Application|\Illuminate\Http\Response|\Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
//    {
//        try {
//            return ProductCategoryDepthTreeResource::collection($this->productCategoryService->depthTree());
//        } catch (Exception $exception) {
//            return response(['status' => false, 'message' => $exception->getMessage()], 422);
//        }
//    }

    public function index(PaginateRequest $request): \Illuminate\Foundation\Application|\Illuminate\Http\Response|\Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            $requests    = $request->all();
            $method      = $request->get('paginate', 0) == 1 ? 'paginate' : 'get';
            $methodValue = $request->get('paginate', 0) == 1 ? $request->get('per_page', 10) : '*';
            $orderColumn = $request->get('order_column') ?? 'id';
            $orderType   = $request->get('order_type') ?? 'desc';

            $allProductCategories = ProductCategory::with('products')->where(function ($query) use ($requests) {
                foreach ($requests as $key => $request) {
                    if (in_array($key, $this->productCateFilter)) {
                        $query->where($key, 'like', '%' . $request . '%');
                    }

                    if (in_array($key, $this->exceptFilter)) {
                        $explodes = explode('|', $request);
                        if (is_array($explodes)) {
                            foreach ($explodes as $explode) {
                                $query->where('id', '!=', $explode);
                            }
                        }
                    }
                }
            })->orderBy($orderColumn, $orderType)->$method(
                $methodValue
            );
            return $this->apiResponse(200, 'All Product Categories', $allProductCategories);
        } catch (Exception $exception) {
            return $this->apiResponse(422, $exception->getMessage());
        }
    }


    public function store(ProductCategoryRequest $request): \Illuminate\Http\Response|ProductCategoryResource|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            $imageName = $this->uploadImage($request->image, 'productCategories');

            $productCategory = ProductCategory::create([
                'name' => [
                    'en' => $request->name_en,
                    'ar' => $request->name_ar
                ],
                'description' => [
                    'en' => $request->description_en,
                    'ar' => $request->description_ar
                ],
                'image' => $imageName,
                'status' => $request->status,
                'slug' => Str::slug($request->name_en),
                'creator_id' => auth()->user()->id,
                'creator_type' => User::class,
                'editor_id' => auth()->user()->id,
                'editor_type' => User::class,
            ]);
            return $this->apiResponse(200, 'Product Category Created Successfully', $productCategory);
        } catch (Exception $exception) {
            return $this->apiResponse(422, $exception->getMessage());
        }
    }

    public function show(ProductCategory $productCategory): \Illuminate\Http\Response|ProductCategoryResource|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return $this->apiResponse(200, 'Product Category', $productCategory);
        } catch (Exception $exception) {
            return $this->apiResponse(422, $exception->getMessage());
        }
    }

    public function update(ProductCategoryRequest $request, ProductCategory $productCategory): \Illuminate\Http\Response|ProductCategoryResource|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            $imageName = $request->hasFile($request->image) ? $this->uploadImage($request->image, 'productCategories', $productCategory->image) : $productCategory->image;

            $productCategory->update([
               'name' => [
                   'en' => $request->name_en,
                   'ar' => $request->name_ar
               ],
               'description' => [
                   'en' => $request->description_en,
                   'ar' => $request->description_ar
               ],
               'image' => $imageName,
               'status' => $request->status,
               'slug' => Str::slug($request->name_en),
               'editor_id' => auth()->user()->id,
               'editor_type' => User::class
            ]);
            return $this->apiResponse(200, 'Product Category Updated Successfully', $productCategory);
        } catch (Exception $exception) {
            return $this->apiResponse(422, $exception->getMessage());
        }
    }

    public function destroy(ProductCategory $productCategory): \Illuminate\Http\Response|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            $checkProduct = $productCategory->products->whereNull('deleted_at');
            if (!blank($checkProduct)) {
                return $this->apiResponse(422, trans('all.message.resource_already_used'));
//                throw new Exception(trans('all.message.resource_already_used'), 422);
            } else {
                $productCategoryImagePath = public_path("uploaded/productCategories" . $productCategory->image);
                if(file_exists($productCategoryImagePath))
                {
                    unlink($productCategoryImagePath);
                }
                DB::statement('SET FOREIGN_KEY_CHECKS=0');
                $productCategory->delete();
                DB::statement('SET FOREIGN_KEY_CHECKS=1');
            }

            return $this->apiResponse(202, 'Product Category Deleted Successfully');
        } catch (Exception $exception) {
            return $this->apiResponse(422, $exception->getMessage());
        }
    }
//
//    public function ancestorsAndSelf(ProductCategory $productCategory): \Illuminate\Foundation\Application|\Illuminate\Http\Response|\Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
//    {
//        try {
//            return ProductCategoryResource::collection($this->productCategoryService->ancestorsAndSelf($productCategory));
//        } catch (Exception $exception) {
//            return response(['status' => false, 'message' => $exception->getMessage()], 422);
//        }
//    }
//
//    public function tree()
//    {
//        try {
//            return $this->productCategoryService->tree()->toTree();
//        } catch (Exception $exception) {
//            return response(['status' => false, 'message' => $exception->getMessage()], 422);
//        }
//    }
}
