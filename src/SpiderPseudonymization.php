<?php

namespace YourOrg\SpiderModule;

use YourOrg\SpiderModule\Services\ApiService;
use YourOrg\SpiderModule\Services\PseudonymizationService;
use YourOrg\SpiderModule\Services\LoggingService;
use Exception;

class SpiderPseudonymization extends \ExternalModules\AbstractExternalModule {
    private $apiService;
    private $pseudonymizationService;
    private $loggingService;

    public function __construct() {
        parent::__construct();

        $this->apiService = new ApiService(
            $this->getSystemSetting('spider_api_url'),
            $this->getSystemSetting('spider_api_key')
        );

        $this->pseudonymizationService = new PseudonymizationService($this->apiService);
        $this->loggingService = new LoggingService();
    }

    public function redcap_save_record($project_id, $record, $instrument, $event_id) {
        try {
            // Get the field where pseudonym should be stored
            $pseudonymField = $this->getProjectSetting('pseudonym_field');

            // Check if pseudonymization is needed
            if ($this->shouldPseudonymize($record, $pseudonymField)) {
                // Generate pseudonym
                $pseudonym = $this->pseudonymizationService->generatePseudonym($record);

                // Save pseudonym to REDCap
                $this->savePseudonymToRedcap($project_id, $record, $pseudonym, $pseudonymField);

                // Log the action
                $this->loggingService->logPseudonymization($record, $pseudonym);
            }
        } catch (Exception $e) {
            $this->loggingService->logError($e->getMessage());
            // Optionally show error to user
            REDCap::logEvent("Pseudonymization Error", $e->getMessage());
        }
    }

    private function shouldPseudonymize($record, $pseudonymField) {
        // Check if the record already has a pseudonym
        $data = REDCap::getData('array', $record);
        return empty($data[$record][$pseudonymField]);
    }

    private function savePseudonymToRedcap($project_id, $record, $pseudonym, $field) {
        $saveData = [
            'record_id' => $record,
            $field => $pseudonym
        ];

        $result = REDCap::saveData('json', json_encode([$saveData]));

        if (!empty($result['errors'])) {
            throw new Exception("Error saving pseudonym: " . json_encode($result['errors']));
        }
    }
}