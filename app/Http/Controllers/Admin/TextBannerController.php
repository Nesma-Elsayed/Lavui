<?php

namespace App\Http\Controllers\Admin;


use App\Http\Requests\TextBannerRequest;
use App\Http\Traits\ApiResponseTrait;
use App\Models\TextBanner;
use Exception;

class TextBannerController extends AdminController
{
    use ApiResponseTrait;
    public function __construct()
    {
        parent::__construct();
        $this->middleware(['permission:settings'])->only('store', 'update', 'destroy', 'show');
    }

    public function index()
    {
        try {
            $textBanners = TextBanner::get();
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
                'creator_id' => auth()->user()->id
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
               'editor_id' => auth()->user()->id
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
