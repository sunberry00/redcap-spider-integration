<?php

namespace YourOrg\SpiderModule\Models;

class PseudonymRecord {
    private $recordId;
    private $pseudonym;
    private $timestamp;

    public function __construct($recordId, $pseudonym, $timestamp) {
        $this->recordId = $recordId;
        $this->pseudonym = $pseudonym;
        $this->timestamp = $timestamp;
    }

    public function getRecordId() {
        return $this->recordId;
    }

    public function getPseudonym() {
        return $this->pseudonym;
    }

    public function getTimestamp() {
        return $this->timestamp;
    }

    public function toArray() {
        return [
            'record_id' => $this->recordId,
            'pseudonym' => $this->pseudonym,
            'timestamp' => $this->timestamp
        ];
    }
}