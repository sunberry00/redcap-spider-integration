<?php
namespace Imi\SpiderIntegration;

use ExternalModules\AbstractExternalModule;

class SpiderIntegration extends AbstractExternalModule {
    public function redcap_every_page_top($project_id) {
        // Add JavaScript only on data entry forms
        if (PAGE === 'DataEntry/index.php') {
            $this->includeJs();
        }
    }

    private function includeJs() {
        // Get settings
        $pseudonymField = $this->getProjectSetting('pseudonym-field');
        $restServiceUrl = $this->getProjectSetting('rest-service-url');
        
        // Add necessary JavaScript
        echo "<script src='{$this->getUrl('js/pseudonym-generator.js')}'></script>";
        
        // Pass configuration to JavaScript
        echo "<script>
            var pseudonymConfig = {
                moduleUrl: '" . $this->getUrl('ajax/generate_pseudonym.php') . "',
                fieldName: '" . htmlspecialchars($pseudonymField, ENT_QUOTES) . "',
                restServiceUrl: '" . htmlspecialchars($restServiceUrl, ENT_QUOTES) . "'
            };
        </script>";
    }

    // Method to get uploaded file content
    public function getP12Certificate() {
        $edocId = $this->getProjectSetting('p12-certificate');
        if (empty($edocId)) {
            return null;
        }
        
        // Get file path from REDCap's edocs table
        $sql = "SELECT stored_name, doc_name FROM redcap_edocs_metadata WHERE doc_id = ?";
        $result = $this->query($sql, [$edocId]);
        $file = $result->fetch_assoc();
        
        if ($file) {
            $filePath = EDOC_PATH . $file['stored_name'];
            return file_get_contents($filePath);
        }
        return null;
    }
}
?>