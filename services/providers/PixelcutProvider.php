<?php
class PixelcutProvider {
    private $apiKey;
    private $apiUrl;
    private $timeout;

    public function __construct($config) {
        $this->apiKey = $config['api_key'];
        $this->apiUrl = $config['api_url'];
        $this->timeout = $config['api_timeout_seconds'] ?? 30;
    }

    public function removeBackground($localImagePath) {
        if (!file_exists($localImagePath)) {
            throw new Exception('Source image file not found.');
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'X-API-KEY: ' . $this->apiKey
        ]);

        $cfile = new CURLFile($localImagePath, mime_content_type($localImagePath), basename($localImagePath));
        curl_setopt($ch, CURLOPT_POSTFIELDS, [
            'image' => $cfile,
            'format' => 'png'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            write_log('cURL Error: ' . $error, 'ERROR');
            throw new Exception('Failed to connect to background removal service.');
        }

        if ($httpCode !== 200) {
            write_log("API Error HTTP $httpCode: $response", 'ERROR');
            $errData = json_decode($response, true);
            $errMsg = $errData['error']['message'] ?? 'API processing failed.';
            throw new Exception($errMsg);
        }

        $responseData = json_decode($response, true);
        if (isset($responseData['result_url'])) {
            return file_get_contents($responseData['result_url']);
        } elseif (isset($responseData['image_url'])) {
            return file_get_contents($responseData['image_url']);
        }

        return $response;
    }
}