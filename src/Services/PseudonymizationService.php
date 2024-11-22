<?php

namespace YourOrg\SpiderModule\Services;

use YourOrg\SpiderModule\Models\PseudonymRecord;

class PseudonymizationService {
    private $apiService;

    public function __construct(ApiService $apiService) {
        $this->apiService = $apiService;
    }

    public function generatePseudonym($recordId) {
        $data = [
            'record_id' => $recordId,
            'timestamp' => time(),
            'type' => 'generate_pseudonym'
        ];

        $response = $this->apiService->callSpiderApi($data);

        if (!isset($response['pseudonym'])) {
            throw new Exception("Invalid API response: missing pseudonym");
        }

        return new PseudonymRecord(
            $recordId,
            $response['pseudonym'],
            $response['timestamp'] ?? time()
        );
    }
}