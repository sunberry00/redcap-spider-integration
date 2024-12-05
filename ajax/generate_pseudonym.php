<?php
// Include REDCap's connect file
require_once APP_PATH_DOCROOT . 'ProjectGeneral/header.php';

// Get module instance
$module = ExternalModules\ExternalModules::getModuleInstance('pseudonym_generator');

// Validate input
$firstName = trim($_POST['firstName'] ?? '');
$lastName = trim($_POST['lastName'] ?? '');
$birthdate = trim($_POST['birthdate'] ?? '');

if (empty($firstName) || empty($lastName) || empty($birthdate)) {
    echo json_encode([
        'success' => false, 
        'error' => 'All fields are required'
    ]);
    exit;
}

try {
    // Get certificate and password from module settings
    $certContent = $module->getP12Certificate();
    $certPassword = $module->getProjectSetting('p12-password');
    $restServiceUrl = $module->getProjectSetting('rest-service-url');

    if (!$certContent || !$certPassword || !$restServiceUrl) {
        throw new Exception('Module configuration is incomplete');
    }

    // Create temporary certificate file
    $tempCertFile = tempnam(sys_get_temp_dir(), 'cert');
    file_put_contents($tempCertFile, $certContent);

    // Set up cURL options for the REST service call
    $ch = curl_init($restServiceUrl);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode([
            'firstName' => $firstName,
            'lastName' => $lastName,
            'birthdate' => $birthdate
        ]),
        CURLOPT_SSLCERT => $tempCertFile,
        CURLOPT_SSLCERTPASSWD => $certPassword,
        CURLOPT_SSLCERTTYPE => 'P12',
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json'
        ]
    ]);

    // Make the request
    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    // Clean up temporary file
    unlink($tempCertFile);

    if ($error) {
        throw new Exception("cURL Error: $error");
    }

    // For testing purposes, generate a dummy pseudonym if REST service is not available
    if (!$response) {
        $dummyPseudonym = 'PSN_' . substr(md5($firstName . $lastName . $birthdate), 0, 10);
        echo json_encode([
            'success' => true,
            'pseudonym' => $dummyPseudonym,
            'note' => 'Using dummy pseudonym (REST service not available)'
        ]);
        exit;
    }

    // Parse response
    $data = json_decode($response, true);
    if (!isset($data['pseudonym'])) {
        throw new Exception('Invalid response from REST service');
    }

    echo json_encode([
        'success' => true,
        'pseudonym' => $data['pseudonym']
    ]);

} catch (Exception $e) {
    error_log("Pseudonym Generator Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>