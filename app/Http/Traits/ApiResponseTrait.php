<?php

namespace App\Http\Traits;

trait ApiResponseTrait
{
    private function apiResponse($code = 200, $message = null, $data = null, $errors = null): \Illuminate\Foundation\Application|\Illuminate\Http\Response|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        $array = [
            'status' => $code,
            'message' => $message,
        ];

        if ($data != null) {
            $array['data'] = $data;
        } elseif ($errors != null) {
            $array['errors'] = $errors;
        }
        return response($array, 200);
    }
}
