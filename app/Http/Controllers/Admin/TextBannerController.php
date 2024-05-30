<?php

namespace App\Http\Controllers\Admin;


use App\Http\Requests\PaginateRequest;
use App\Http\Requests\TextBannerRequest;
use App\Http\Traits\ApiResponseTrait;
use App\Models\TextBanner;
use App\Models\User;
use Exception;

class TextBannerController extends AdminController
{
    use ApiResponseTrait;
    protected array $productCateFilter = [
        'name',
        'status',
    ];

    protected array $exceptFilter = [
        'excepts'
    ];
    public function __construct()
    {
        parent::__construct();
        $this->middleware(['permission:settings'])->only('store', 'update', 'destroy', 'show');
    }

    public function index(PaginateRequest $request)
    {
        try {
            $requests    = $request->all();
            $method      = $request->get('paginate', 0) == 1 ? 'paginate' : 'get';
            $methodValue = $request->get('paginate', 0) == 1 ? $request->get('per_page', 10) : '*';
            $orderColumn = $request->get('order_column') ?? 'id';
            $orderType   = $request->get('order_type') ?? 'desc';

            $textBanners = TextBanner::where(function ($query) use ($requests) {
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
//            $textBanners = TextBanner::get();
            return $this->apiResponse(200, 'All TextBanners', $textBanners);
        } catch (Exception $exception) {
            return $this->apiResponse(422, $exception->getMessage());
        }
    }

    public function store(TextBannerRequest $request)
    {
        try {
            $textBanner = TextBanner::create([
                'name' => [
                    'en' => $request->name_en,
                    'ar' => $request->name_ar
                ],
                'status' => $request->status,
                'creator_id' => auth()->user()->id,
                'creator_type' => User::class,
                'editor_id' => auth()->user()->id,
                'editor_type' => User::class,
            ]);
            return $this->apiResponse(200, 'TextBanner Created Successfully', $textBanner);
        } catch (Exception $exception) {
            return $this->apiResponse(422, $exception->getMessage());
        }
    }

    public function show(TextBanner $textBanner)
    {
        try {
            return $this->apiResponse(200, 'TextBanner', $textBanner);
        } catch (Exception $exception) {
            return $this->apiResponse(422, $exception->getMessage());
        }
    }

    public function update(TextBannerRequest $request, TextBanner $textBanner)
    {
        try {
            $textBanner->update([
               'name' => [
                   'en' => $request->name_en,
                   'ar' => $request->name_ar
               ],
               'status' => $request->status,
               'editor_id' => auth()->user()->id,
               'editor_type' => User::class

            ]);
            return $this->apiResponse(200, 'TextBanner Updated Successfully', $textBanner);
        } catch (Exception $exception) {
            return $this->apiResponse(422, $exception->getMessage());
        }
    }

    public function destroy(TextBanner $textBanner)
    {
        try {
            $textBanner->delete();
            return $this->apiResponse(202, 'TextBanner Deleted Successfully');
        } catch (Exception $exception) {
            return $this->apiResponse(422, $exception->getMessage());
        }
    }
}
