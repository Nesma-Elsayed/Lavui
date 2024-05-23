<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponseTrait;
use App\Models\Benefit;
use Exception;

class BenefitController extends Controller
{
    use ApiResponseTrait;

    public function index()
    {
        try {
            $benefits = Benefit::get();
            return $this->apiResponse(200, 'All Benefits', $benefits);
        } catch (Exception $exception) {
            return $this->apiResponse(422, $exception->getMessage());
        }
    }
}
