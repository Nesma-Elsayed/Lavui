<?php

namespace App\Http\Controllers\Admin;

use App\Http\Traits\ApiResponseTrait;
use App\Http\Traits\ImageTrait;
use App\Models\User;
use Exception;
use App\Models\ProductSection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProductSectionExport;
use App\Http\Requests\PaginateRequest;
use App\Services\ProductSectionService;
use App\Http\Requests\ProductSectionRequest;

class ProductSectionController extends AdminController
{
    use ImageTrait, ApiResponseTrait;
    private ProductSectionService $productSectionService;

    protected array $productCateFilter = [
        'name',
        'slug',
        'status',
    ];

    protected array $exceptFilter = [
        'excepts'
    ];

    public function __construct(ProductSectionService $productSectionService)
    {
        parent::__construct();
        $this->productSectionService = $productSectionService;
        $this->middleware(['permission:product-sections'])->only('index', 'export');
        $this->middleware(['permission:product-sections_create'])->only('store');
        $this->middleware(['permission:product-sections_edit'])->only('update');
        $this->middleware(['permission:product-sections_delete'])->only('destroy');
        $this->middleware(['permission:product-sections_show'])->only('show');
    }

    public function index(PaginateRequest $request)
    {
        try {
            $requests    = $request->all();
            $method      = $request->get('paginate', 0) == 1 ? 'paginate' : 'get';
            $methodValue = $request->get('paginate', 0) == 1 ? $request->get('per_page', 10) : '*';
            $orderColumn = $request->get('order_column') ?? 'id';
            $orderType   = $request->get('order_type') ?? 'desc';

            $allProductSections = ProductSection::where(function ($query) use ($requests) {
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
             return $this->apiResponse(200, 'All Product Sections', $allProductSections);
        } catch (Exception $exception) {
            return $this->apiResponse(422, $exception->getMessage());
        }
    }

    public function store(ProductSectionRequest $request)
    {
        try {
            $image = $this->uploadImage($request->image, 'productSections');

            $productSection = ProductSection::create([
                'name' => [
                    'en' => $request->name_en,
                    'ar' => $request->name_ar
                ],
                'title' => [
                  'en' => $request->title_en,
                  'ar' => $request->title_ar
                ],
                'description' => [
                    'en' => $request->description_en,
                    'ar' => $request->description_ar
                ],
                'slug' => Str::slug($request->name_en),
                'image' => $image,
                'status' => $request->status,
                'creator_id' => auth()->user()->id,
                'creator_type' => User::class,
                'editor_id' => auth()->user()->id,
                'editor_type' => User::class,
            ]);

            return $this->apiResponse(200, 'Product section created successfully.', $productSection);

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

    public function update(ProductSectionRequest $request, ProductSection $productSection)
    {
        try {
            $imageName = $request->hasFile('image') ? $this->uploadImage($request->image, 'productSections', "uploaded/productSections/".$productSection->image) : $productSection->image;

            $productSection->update([
                'name' => [
                    'en' => $request->name_en,
                    'ar' => $request->name_ar
                ],
                'title' => [
                    'en' => $request->title_en,
                    'ar' => $request->title_ar
                ],
                'description' => [
                    'en' => $request->description_en,
                    'ar' => $request->description_ar
                ],
                'slug' => Str::slug($productSection->getTranslation('name', 'en')),
                'image' => $imageName,
                'status' => $request->status,
                'editor_id' => auth()->user()->id,
                'editor_type' => User::class,
            ]);
            return $this->apiResponse(200, 'Product Section Updated Successfully', $productSection);

        } catch (Exception $exception) {
            return $this->apiResponse(422, $exception->getMessage());
        }
    }

    public function destroy(ProductSection $productSection)
    {
        try {
            $imagePath = public_path("uploaded/productSections/" . $productSection->image);
            if(file_exists($imagePath)) {
                unlink($imagePath);
            }
//            $productSection->productSectionProducts()->delete();
            $productSection->delete();

            return  $this->apiResponse(202, 'Product Section Deleted successfully');
        } catch (Exception $exception) {
            return $this->apiResponse(422, $exception->getMessage());
        }
    }

    public function export(PaginateRequest $request)
    {
        try {
            return Excel::download(new ProductSectionExport($this->productSectionService, $request), 'Product_section.xlsx');
        } catch (Exception $exception) {
            return $this->apiResponse(422, $exception->getMessage());
        }
    }
}
