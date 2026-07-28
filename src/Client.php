<?php

namespace jblond\aspsms;

use CurlHandle;
use JsonException;

class Client
{
    /**
     * @var false|CurlHandle
     */
    protected false|CurlHandle $curl;

    /**
     * @var string
     */
    protected string $data = '';

    /**
     * Client constructor.
     */
    public function __construct()
    {
        $this->curl = curl_init();
        curl_setopt_array(
            $this->curl,
            [
                CURLOPT_HEADER => false,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_CONNECTTIMEOUT => 0,
                CURLOPT_TIMEOUT => 600,
            ]
        );
    }

    /**
     * Evaluate if the response was successful
     * @param string $data
     * @return bool
     * @throws JsonException
     */
    protected function analyseResponse(string $data): bool
    {
        $response = json_decode($data, true, 512, JSON_THROW_ON_ERROR);
        return $response['errorCode'] === 1;
    }

    /**
     * @param array $input
     * @throws JsonException
     */
    protected function setData(array $input): void
    {
        $this->data = json_encode($input, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
    }

    /**
     * @param string $url
     * @return bool|string
     */
    public function get(string $url): bool|string
    {
        curl_setopt_array(
            $this->curl,
            [
                CURLOPT_URL => $url,
            ]
        );
        return $this->sendRequest();
    }

    /**
     * @param string $url
     * @param array $data
     * @param callable|null $errorHandler Optional callback invoked with the raw response
     *                                    when the request was not successful.
     * @return bool
     * @throws JsonException
     */
    public function post(string $url, array $data, ?callable $errorHandler = null): bool
    {
        $this->setData($data);
        curl_setopt_array(
            $this->curl,
            [
                CURLOPT_POST => true,
                CURLOPT_URL => $url,
                CURLOPT_POSTFIELDS => $this->data,
                CURLOPT_HTTPHEADER => ['Content-type: application/json']
            ]
        );
        $response = $this->sendRequest();
        $success = $this->analyseResponse($response);
        if (!$success && $errorHandler !== null) {
            $errorHandler($response);
        }
        return $success;
    }

    /**
     * @return bool|string
     */
    protected function sendRequest(): bool|string
    {
        $response = curl_exec($this->curl);
        if ($response === false) {
            $response = curl_error($this->curl);
        }
        return $response;
    }
}
