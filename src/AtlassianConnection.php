<?php

namespace Kaweb\Jira;

use Illuminate\Support\Facades\Log;

/**
 * Provides basic Atlassian connection functions
 *
 * @package Kaweb\Jira
 * @since   1.0
 */
class AtlassianConnection
{
    /**
     * @param String          $path        Path to api endpoint
     * @param array|\stdClass $parameters  Query string paramters as Array or Object
     * @param bool            $fullPath    Used if passing in Self paths that are returned in the API response
     *
     * @return mixed
     */
    public function get($path, $parameters = [], $fullPath = false)
    {
        return $this->send($path, 'GET', $parameters, $fullPath);
    }

    /**
     * @param String          $path        Path to api endpoint
     * @param array|\stdClass $data        Data to be sent with the post request
     * @param bool            $fullPath    Used if passing in Self paths that are returned in the API response
     *
     * @return mixed
     */
    public function post($path, $data, $fullPath = false)
    {
        return $this->send($path, 'POST', $data, $fullPath);
    }

    /**
     * Send request to JIRA
     *
     * @param string $path        method URL path
     * @param string $method      request's HTTP method
     * @param array  $data        Array to convert into a JSON document. Will only be used for post functions
     * @param bool   $fullPath    Used if passing in Self paths that are returned in the API response
     *
     * @return mixed
     */
    public function send($path, $method = 'GET', $data = null, $fullPath = false)
    {
        $headers = [
            'Accept: application/json',
            'Content-Type: application/json'
        ];

        $url = config('Jira.path');
        $usrPassword = config('Jira.user.username') . ':' . config('Jira.user.password');

        if ($method == 'GET' && $data) {
            $path .= "?" . http_build_query($data);
        }

        if(!$fullPath) {
            $path = $url . $path;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $path);
        curl_setopt($ch, CURLOPT_VERBOSE, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_USERPWD, $usrPassword);

        if (in_array($method, ['POST','PUT'])) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        if ($method == 'POST') {
            curl_setopt($ch, CURLOPT_POST, 1);
        }

        $result = curl_exec($ch);
        $ch_error = curl_error($ch);

        if ($ch_error) {
            Log::error($ch_error);
            $return = $ch_error;
        } else {
            $return = $result;
        }

        curl_close($ch);

        return json_decode($return);
    }
}
