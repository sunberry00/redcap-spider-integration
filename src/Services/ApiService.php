<?php

namespace YourOrg\SpiderModule\Services;

use Exception;

class ApiService {
    private $apiUrl;
    private $apiKey;

    public function __construct($apiUrl, $apiKey) {
        $this->apiUrl = $apiUrl;
        $this->apiKey = $apiKey;
    }

    public function callSpiderApi($data) {
        $ch = curl_init($this->apiUrl);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->apiKey,
                'Content-Type: application/json',
                'Accept: application/json'
            ]
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($error = curl_error($ch)) {
            throw new Exception("API Call Failed: " . $error);
        }

        curl_close($ch);

        if ($httpCode !== 200) {
            throw new Exception("API returned error code: " . $httpCode);
        }

        return json_decode($response, true);
    }
}