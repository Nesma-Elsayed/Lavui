<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\PaginateRequest;
use App\Http\Traits\ApiResponseTrait;
use App\Http\Traits\ImageTrait;
use App\Models\User;
use Exception;
use App\Http\Requests\BenefitRequest;
use App\Models\Benefit;

class BenefitController extends AdminController
{
    use ImageTrait, ApiResponseTrait;

    protected array $productCateFilter = [
        'title',
        'description',
        'icon',
        'status'
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

            $benefits = Benefit::where(function ($query) use ($requests) {
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
//            $benefits = Benefit::get();
            return $this->apiResponse(200, 'All Benefits', $benefits);
        } catch (Exception $exception) {
            return $this->apiResponse(422, $exception->getMessage());
        }
    }

    public function store(BenefitRequest $request)
    {
        try {
            $icon = $this->uploadImage($request->icon,'benefits');

            $benefit = Benefit::create([
                'icon' => $icon,
                'title' => [
                    'en' => $request->title_en,
                    'ar' => $request->title_ar
                ],
                'description' => [
                    'en' => $request->description_en,
                    'ar' => $request->description_ar
                ],
                'status' => $request->status,
                'creator_id' => auth()->user()->id,
                'creator_type' => User::class,
                'editor_id' => auth()->user()->id,
                'editor_type' => User::class,
            ]);

            return $this->apiResponse(200, 'Benefit Created Successfully', $benefit);
        } catch (Exception $exception) {
            return $this->apiResponse(422, $exception->getMessage());
        }
    }

    public function show(Benefit $benefit)
    {
        try {
            return $this->apiResponse(200, 'Benefit', $benefit);
        } catch (Exception $exception) {
            return $this->apiResponse(422, $exception->getMessage());
        }
    }

    public function update(
        BenefitRequest $request,
        Benefit $benefit
    )
    {
        try {
            $icon = $request->hasFile('icon') ? $this->uploadImage($request->icon, 'benefits',"uploaded/benefits/".$benefit->icon) : $benefit->icon;

            $benefit->update(
                [
                    'icon' => $icon,
                    'title' => [
                        'en' => $request->title_en,
                        'ar' => $request->title_ar
                    ],
                    'description' => [
                        'en' => $request->description_en,
                        'ar' => $request->description_ar
                    ],
                    'status' => $request->status,
                    'editor_id' => auth()->user()->id,
                    'editor_type' => User::class
                ]
            );
            return $this->apiResponse(200, 'Benefit Updated Successfully', $benefit);

        } catch (Exception $exception) {
            return $this->apiResponse(422, $exception->getMessage());
        }
    }

    public function destroy(Benefit $benefit)
    {
        try {
            $iconImagePath = public_path("uploaded/benefits/" . $benefit->icon);
            if(file_exists($iconImagePath)) {
                unlink($iconImagePath);
            }
            $benefit->delete();

            return $this->apiResponse(202, 'Benefit Deleted Successfully');
        } catch (Exception $exception) {
            return $this->apiResponse(422, $exception->getMessage());
        }
    }
}
