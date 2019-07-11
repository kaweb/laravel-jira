<?php

namespace Kaweb\Jira\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Console\DetectsApplicationNamespace;
use Kaweb\Jira\Jira;

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
        $env = getenv('APP_ENV');

        if (!in_array($env, config('laravel-jira.exception_handling.ignore_envs'))) {

            if ($exception instanceof \ErrorException) {
                // Report to Jira
                $backtrace = '';
                $stacktrace = debug_backtrace(2);

                // Get some config settings quickly
                $project = config('laravel-jira.jira_project');
                $type =  config('laravel-jira.exception_handling.default_type');
                $priority =  config('laravel-jira.exception_handling.default_priority');
                $assignee = config('laravel-jira.exception_handling.default_assignee', 'Unassigned');

                foreach ($stacktrace as $trace) {
                    if (array_key_exists("file", $trace)) {
                        $backtrace .= 'File: ' . $trace['file'] . ' - Line: ' . $trace['line'] . ' ';
                        $backtrace .= 'Class: ' . $trace['class'] . ' Function: ' . $trace['function'] . ' ';
                    }
                }

                /**
                 * Build information for the description or body
                 */
                $url = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
                $body = <<<DESC
h2. {color:red}Fatal Error Encountered{color}
h4. {$exception->getMessage()}
h4. Status Code: {$exception->getCode()}
{{{$exception->getFile()} @ line {$exception->getLine()}}}
---

||URL||ENV||IPV4||
|{$url}|{$env}|{$_SERVER['REMOTE_ADDR']}|
---
h2. Backtrace:
{$backtrace}
DESC;

                $jira = new Jira;

                // Search for existing issue
                $issue = $jira->findIssueBySummary($exception->getMessage(), $project);

                // If we have a ticket for this error already.
                if (!empty($issue)) {
                    //Add a comment to the current ticket
                    $jira->addComment($issue, $body);
                } else {
                    //If not create a new ticket
                    $jira->createIssue(
                        'Fatal Error: ' . $exception->getMessage(),
                        $body,
                        $project,
                        $priority,
                        $type,
                        $assignee
                    );
                }
            }
        }

        // Pass off to the default handler
        $className = $this->defaultHandler();
        if (!empty($className)) {
            $handler = new $className($this->container);

            $handler->report($exception);
        }
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

        if (!empty($className)) {
            $handler = new $className($this->container);

            return $handler->render($request, $exception);
        }
    }

    /**
     * Gets the fully namespaced class name for the next handler to be passed on to for
     * error handling
     *
     * @return string
     */
    protected function defaultHandler(): string
    {
        $defaultHandler = '';

        if (!empty(config('laravel-jira.exception_handling.default_handler'))) {
            $defaultHandler = config('laravel-jira.exception_handling.default_handler');
        }

        if (!$this->doesHandlerResolve($defaultHandler)) {
            $defaultHandler = $this->getAppNamespace() . 'Exceptions\Handler';

            // Check again?
            if (!$this->doesHandlerResolve($defaultHandler)) {
                $defaultHandler = '';

                // This is bad, it means that we couldn't resolve a handler to pass the error
                // to once the error has been raised in Jira it isn't the end of the world
                // however this means that things like custom error pages, Laravel logging
                // etc. will not work.
                // For now I'm going to leave this as is, but need to identify an appropriate
                // action for this class to carry out if a handler cannot be resolved.
            }
        }

        return $defaultHandler;
    }

    /**
     * Checks if the handler class string will resole to a PHP class
     *
     * @param string $handler The fully namespaced class name
     *
     * @return bool
     */
    protected function doesHandlerResolve(string $handler): bool
    {
        if (!empty($handler) && class_exists($handler)) {
            return true;
        }
        return false;
    }
}