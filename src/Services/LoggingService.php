<?php

namespace YourOrg\SpiderModule\Services;

class LoggingService {
    public function logPseudonymization($record, $pseudonym) {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'record' => $record,
            'pseudonym' => $pseudonym,
            'user' => USERID
        ];

        REDCap::logEvent(
            "SPIDER Pseudonymization",
            json_encode($logEntry),
            "",
            $record,
            null,
            PROJECT_ID
        );
    }

    public function logError($message) {
        REDCap::logEvent(
            "SPIDER Error",
            $message,
            "",
            null,
            null,
            PROJECT_ID
        );
    }
}