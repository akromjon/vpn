<?php

namespace App\Exceptions;

use Akromjon\Telegram\App\Telegram;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
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

            if ($e instanceof \Exception) {

                $telegram = Telegram::set(config('telegram.token'));

                $telegram->sendErrorMessage(config('telegram.chat_id'), $e);

            }

        });

    }

    public function render($request, Throwable $e)
    {
        if ($e instanceof MethodNotAllowedHttpException) {

            return response()->json([
                'message' => 'Method not allowed!'
            ], 405);

        }

        //Symfony\\Component\\HttpKernel\\Exception\\NotFoundHttpException

        if ($e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {

            return response()->json([
                'message' => 'Not found!'
            ], 404);

        }

        if ($e instanceof ClientNotFoundException) {

            return response()->json([
                'message' => $e->getMessage()
            ], 404);

        }

        return parent::render($request, $e);
    }

}
