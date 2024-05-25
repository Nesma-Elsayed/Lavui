<?php

namespace App\Http\Controllers\Admin;

use App\Http\Resources\ProductCategoryDepthTreeResource;
use App\Http\Traits\ApiResponseTrait;
use App\Http\Traits\ImageTrait;
use Exception;
use App\Services\ProductCategoryService;
use App\Http\Requests\PaginateRequest;
use App\Http\Requests\ProductCategoryRequest;
use App\Http\Resources\ProductCategoryResource;
use App\Models\ProductCategory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductCategoryController extends AdminController
{
    use ApiResponseTrait, ImageTrait;
    private ProductCategoryService $productCategoryService;

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
            $allProductCategories = ProductCategory::get();
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
                'creator_id' => auth()->user()->id
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
                'editor_id' => auth()->user()->id
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
