<?php

namespace App\Exceptions;

use App\Http\Traits\ApiResponseTrait;
use HttpException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\UnauthorizedException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    use ApiResponseTrait;

    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }


    public function render($request, Throwable $e): \Illuminate\Http\Response|JsonResponse|\Illuminate\Http\RedirectResponse|\Symfony\Component\HttpFoundation\Response
    {

        if ($e instanceof UnauthorizedException) {
            return $this->apiResponse(403, 'User does not have the right permissions.');
        }

        if ($e instanceof ModelNotFoundException) {
            return $this->apiResponse(404, 'No query results for model.');
        }

        if ($e instanceof MethodNotAllowedHttpException) {
            return $this->apiResponse(405, 'Method not support for the route.');
        }

        if ($e instanceof NotFoundHttpException) {
            return $this->apiResponse(404, 'The specified URL cannot be found.');
        }

        if ($e instanceof HttpException) {
            return $this->apiResponse(422, $e->getMessage());
        }

        if ($e instanceof QueryException) {
            return $this->apiResponse(422, $e->getMessage());
        }

        if ($e instanceof ValidationException) {
            return $this->apiResponse(422, 'Validation Error', null, $e->errors());
        }

        return parent::render($request, $e);
    }
}
