<?php

namespace ChrisKonnertz\DeepLy\HttpClient;

/**
 * This class uses cURL to execute API calls.
 */
class CurlHttpClient implements HttpClientInterface
{
    
    /*
     * Set this to 0 if you do not want cURL to 
     * try to verify the SSL certificate - it could fail
     */
    const CURLOPT_SSL_VERIFYPEER = 0;

    /**
     * CurlHttpClient constructor.
     */
    public function __construct()
    {
        if (! $this->isCurlAvailable()) {
            throw new \LogicException(
                'Cannot use DeepLy\'s CurlHttpClient class, because the cURL PHP extension is not available'
            );
        }
    }

    /**
     * Executes an API call
     *
     * @param  string $url The URL of the API endpoint
     * @param  array  $params The payload of the request. Will be encoded as JSON
     * @return string The raw result as string (usually contains stringified JSON)
     * @throws CallException Throws a call exception if the call could not be executed
     */
    public function callApi($url, array $params)
    {
        $jsonData = $this->createJsonData($params);

        $curl = curl_init($url);

        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($curl, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        //curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, self::CURLOPT_SSL_VERIFYPEER);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($jsonData)) // Note: We do not need mb_strlen here since JSON encodes Unicode
        );

        $rawResult = curl_exec($curl);

        if ($rawResult === false) {
            throw new CallException('cURL error during DeepLy API call: '.curl_error($curl));
        }

        return $rawResult;
    }

    /**
     * Creates a data array that the API will understand,
     * encodes it as a JSON string and returns it.
     *
     * @param array $params The payload of the request. Will be encoded as JSON
     * @return string
     */
    protected function createJsonData(array $params)
    {
        $data = [
            'jsonrpc' => '2.0', // Set the protocol version. @see https://en.wikipedia.org/wiki/JSON-RPC
            'method' => 'LMT_handle_jobs', // Set the method of the API call
            'params' => $params // Set the parameters
        ];

        $jsonData = json_encode($data);

        return $jsonData;
    }

    /**
     * Returns true if the cURL extension is available, otherwise false
     *
     * @return bool
     */
    protected function isCurlAvailable()
    {
        return (in_array('curl', get_loaded_extensions()));
    }

}