<?php

namespace App\Services;

use App\Http\Traits\ImageTrait;
use Exception;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\PaginateRequest;
use App\Http\Requests\BenefitRequest;
use App\Models\Benefit;

class BenefitService
{
    use ImageTrait;
    protected $productCateFilter = [
        'title',
        'description',
        'icon',
        'status',
    ];

    protected $exceptFilter = [
        'excepts'
    ];

    /**
     * @throws Exception
     */
    public function list()
    {
        try {
//            $requests    = $request->all();
//            $method      = $request->get('paginate', 0) == 1 ? 'paginate' : 'get';
//            $methodValue = $request->get('paginate', 0) == 1 ? $request->get('per_page', 10) : '*';
//            $orderColumn = $request->get('order_column') ?? 'id';
//            $orderType   = $request->get('order_type') ?? 'desc';
//
//            return Benefit::where(function ($query) use ($requests) {
//                foreach ($requests as $key => $request) {
//                    if (in_array($key, $this->productCateFilter)) {
//                        $query->where($key, 'like', '%' . $request . '%');
//                    }
//
//                    if (in_array($key, $this->exceptFilter)) {
//                        $explodes = explode('|', $request);
//                        if (is_array($explodes)) {
//                            foreach ($explodes as $explode) {
//                                $query->where('id', '!=', $explode);
//                            }
//                        }
//                    }
//                }
//            })->orderBy($orderColumn, $orderType)->$method(
//                $methodValue
//            );
            return Benefit::get();

        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function store(BenefitRequest $request)
    {
        try {
            $icon = $this->uploadImage($request->icon,'benefits');
//            $benefit = Benefit::create(
//                $request->validated() + [
//                    'icon' => $icon,
//                    'title' => [
//                        'en' => $request->title_en,
//                        'ar' => $request->title_ar
//                    ],
//                    'description' => [
//                        'en' => $request->description_en,
//                        'ar' => $request->description_ar
//                    ]
//                ]
//            );
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
            ]);
//            if ($request->image) {
//                $benefit->addMediaFromRequest('image')->toMediaCollection('benefit');
//            }
            return $benefit;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function update(BenefitRequest $request, Benefit $benefit): Benefit
    {
        try {
            $icon = $this->uploadImage($request->icon,'benefits');

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
                ]
            );
//            if ($request->image) {
//                $benefit->clearMediaCollection('benefit');
//                $benefit->addMediaFromRequest('image')->toMediaCollection('benefit');
//            }
            return $benefit;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function destroy(Benefit $benefit)
    {
        try {
            $benefit->delete();
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function show(Benefit $Benefit)
    {
        try {
            return $Benefit;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }
}
