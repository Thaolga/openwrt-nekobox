<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$configFile = dirname(__FILE__) . '/api_keys.config.php';

$action = $_GET['action'] ?? '';
$input = json_decode(file_get_contents('php://input'), true);

switch ($action) {
    case 'get':
        if (file_exists($configFile)) {
            $keys = include $configFile;
            echo json_encode([
                'success' => true,
                'keys' => $keys
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Configuration file does not exist',
                'keys' => [
                    'spotify' => ['client_id' => '', 'client_secret' => ''],
                    'youtube' => ['api_key' => ''],
                    'soundcloud' => ['client_id' => '']
                ]
            ]);
        }
        break;
        
    case 'save':
        if (isset($input['keys'])) {
            $keys = $input['keys'];
            
            $configContent = "<?php\nreturn [\n";
            
            // Spotify
            $configContent .= "    'spotify' => [\n";
            $configContent .= "        'client_id' => '" . addslashes(trim($keys['spotify']['client_id'] ?? '')) . "',\n";
            $configContent .= "        'client_secret' => '" . addslashes(trim($keys['spotify']['client_secret'] ?? '')) . "',\n";
            $configContent .= "    ],\n";
            
            // YouTube
            $configContent .= "    'youtube' => [\n";
            $configContent .= "        'api_key' => '" . addslashes(trim($keys['youtube']['api_key'] ?? '')) . "',\n";
            $configContent .= "    ],\n";
            
            // SoundCloud
            $configContent .= "    'soundcloud' => [\n";
            $configContent .= "        'client_id' => '" . addslashes(trim($keys['soundcloud']['client_id'] ?? '')) . "',\n";
            $configContent .= "    ],\n";
            
            $configContent .= "];\n";
            
            try {
                if (file_put_contents($configFile, $configContent, LOCK_EX)) {
                    chmod($configFile, 0644);
                    echo json_encode([
                        'success' => true,
                        'message' => 'API keys saved successfully'
                    ]);
                } else {
                    $error = error_get_last();
                    echo json_encode([
                        'success' => false,
                        'message' => 'Failed to write file: ' . ($error['message'] ?? 'Unknown error')
                    ]);
                }
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Save failed: ' . $e->getMessage()
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'No key data provided'
            ]);
        }
        break;
        
    default:
        echo json_encode([
            'success' => false,
            'message' => 'Invalid action'
        ]);
        break;
}
?>