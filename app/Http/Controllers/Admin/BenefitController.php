<?php

namespace App\Http\Controllers\Admin;

use App\Http\Traits\ApiResponseTrait;
use App\Http\Traits\ImageTrait;
use Exception;
use App\Http\Requests\BenefitRequest;
use App\Models\Benefit;

class BenefitController extends AdminController
{
    use ImageTrait, ApiResponseTrait;


    public function __construct()
    {
        parent::__construct();
        $this->middleware(['permission:settings'])->only('store', 'update', 'destroy', 'show');
    }

    public function index()
    {
        try {
            $benefits = Benefit::get();
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
                'creator_id' => auth()->user()->id
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
                    'editor_id' => auth()->user()->id
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
