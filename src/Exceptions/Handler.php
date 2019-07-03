<?php

namespace Kaweb\Jira\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Console\DetectsApplicationNamespace;

class Handler extends ExceptionHandler
{
    use DetectsApplicationNamespace;

    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        $className = $this->defaultHandler();
        $handler = new $className($this->container);

        $handler->report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        $className = $this->defaultHandler();
        $handler = new $className($this->container);

        return $handler->render($request, $exception);
    }

    /**
     * Gets the fully namespaced class name for the next handler to be passed on to for
     * error handling
     *
     * @return string
     */
    protected function defaultHandler()
    {
        if (!empty(config('laravel-jira.exception_handling.default_handler'))) {
            $nextClass = config('laravel-jira.exception_handling.default_handler');
        } else {
            $nextClass = $this->getAppNamespace() . 'Exceptions\Handler';
        }

        return $nextClass;
    }
}