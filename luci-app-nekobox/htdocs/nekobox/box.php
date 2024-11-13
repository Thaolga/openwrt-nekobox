<?php
ob_start();
include './cfg.php';

$dataFilePath = '/tmp/subscription_data.txt';
$lastSubscribeUrl = '';

if (file_exists($dataFilePath)) {
    $fileContent = file_get_contents($dataFilePath);
    $lastPos = strrpos($fileContent, 'Subscription Link Address:');
    if ($lastPos !== false) {
        $urlSection = substr($fileContent, $lastPos);
        $httpPos = strpos($urlSection, 'http');
        if ($httpPos !== false) {
            $endPos = strpos($urlSection, 'Custom Template URL:', $httpPos);
            if ($endPos !== false) {
                $lastSubscribeUrl = trim(substr($urlSection, $httpPos, $endPos - $httpPos));
            } else {
                $lastSubscribeUrl = trim(substr($urlSection, $httpPos));
            }
        }
    }
}
?>

<!doctype html>
<html lang="en" data-bs-theme="<?php echo substr($neko_theme, 0, -4) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Box - Neko</title>
    <link rel="icon" href="./assets/img/nekobox.png">
    <link href="./assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="./assets/css/custom.css" rel="stylesheet">
    <link href="./assets/theme/<?php echo $neko_theme ?>" rel="stylesheet">
    <script type="text/javascript" src="./assets/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="./assets/js/feather.min.js"></script>
    <script type="text/javascript" src="./assets/bootstrap/bootstrap.bundle.min.js"></script>
    <script type="text/javascript" src="./assets/js/jquery-2.1.3.min.js"></script>
    <script type="text/javascript" src="./assets/js/neko.js"></script>
</head>
<body>
<style>
@media (max-width: 767px) {
    .row a {
        font-size: 9px; 
    }
}

.table-responsive {
    width: 100%;
}
</style>
<div class="container-sm container-bg callout border border-3 rounded-4 col-11">
    <div class="row">
        <a href="./index.php" class="col btn btn-lg">🏠 Home</a>
        <a href="./mihomo_manager.php" class="col btn btn-lg">📂 Mihomo</a>
        <a href="./singbox_manager.php" class="col btn btn-lg">🗂️ Sing-box</a>
        <a href="./box.php" class="col btn btn-lg">💹 Template</a>
        <a href="./filekit.php" class="col btn btn-lg">📦 File Assistant</a>
<div class="outer-container">
    <div class="container">
        <h1 class="title text-center" style="margin-top: 3rem; margin-bottom: 2rem;">Sing-box Subscription Conversion Template</h1>
        <div class="alert alert-info">
            <h4 class="alert-heading">Help Information</h4>
            <p>Please select a template to generate the configuration file: Choose the corresponding template based on the subscription node information. If you select a template with regional grouping, please ensure that your nodes include the following lines</p>
            <ul>
                <li><strong>Default template 1</strong>：No region, no grouping, general</li>
                <li><strong>Default template 2</strong>：No region, with routing rules, general</li>
                <li><strong>Default template 3</strong>：Hong Kong, Japan, United States, grouped with routing rules</li>
                <li><strong>Default template 4</strong>：Hong Kong, Singapore, Japan, United States, grouped with routing rules</li>
                <li><strong>Default template 5</strong>：Singapore, Japan, United States, South Korea, grouped with routing rules</li>
                <li><strong>Default template 6</strong>：Hong Kong, Taiwan, Singapore, Japan, United States, South Korea, grouped with routing rules</li>
            </ul>
        </div>
        <form method="post" action="">
            <div class="mb-3">
                <label for="subscribeUrl" class="form-label">Subscription Link Address:</label>
                <input type="text" class="form-control" id="subscribeUrl" name="subscribeUrl" value="<?php echo htmlspecialchars($lastSubscribeUrl); ?>" required>
            </div>
            <div class="mb-3">
                <label for="customFileName" class="form-label">Custom filename (no extension needed)</label>
                <input type="text" class="form-control" id="customFileName" name="customFileName" placeholder="Enter custom filename">
            </div>
            <fieldset class="mb-3">
                <legend class="form-label">Select a template</legend>
                <div class="row">
                    <div class="col">
                        <input type="radio" class="form-check-input" id="useDefaultTemplate1" name="defaultTemplate" value="1" checked>
                        <label class="form-check-label" for="useDefaultTemplate1">Default template 1</label>
                    </div>
                    <div class="col">
                        <input type="radio" class="form-check-input" id="useDefaultTemplate2" name="defaultTemplate" value="2">
                        <label class="form-check-label" for="useDefaultTemplate2">Default template 2</label>
                    </div>
                    <div class="col">
                        <input type="radio" class="form-check-input" id="useDefaultTemplate3" name="defaultTemplate" value="3">
                        <label class="form-check-label" for="useDefaultTemplate3">Default template 3</label>
                    </div>
                    <div class="col">
                        <input type="radio" class="form-check-input" id="useDefaultTemplate4" name="defaultTemplate" value="4">
                        <label class="form-check-label" for="useDefaultTemplate3">Default template 4</label>
                    </div>
                    <div class="col">
                        <input type="radio" class="form-check-input" id="useDefaultTemplate5" name="defaultTemplate" value="5">
                        <label class="form-check-label" for="useDefaultTemplate3">Default template 5</label>
                    </div>
                    <div class="col">
                        <input type="radio" class="form-check-input" id="useDefaultTemplate6" name="defaultTemplate" value="6">
                        <label class="form-check-label" for="useDefaultTemplate3">Default template 6</label>
                    </div>
                </div>
                <div class="mt-3">
                    <input type="radio" class="form-check-input" id="useCustomTemplate" name="templateOption" value="custom">
                    <label class="form-check-label" for="useCustomTemplate">Use Custom Template URL:</label>
                    <input type="text" class="form-control" id="customTemplateUrl" name="customTemplateUrl" placeholder="Enter Custom Template URL">
                </div>
            </fieldset>
            <div class="mb-3">
                <button type="submit" name="generateConfig" class="btn btn-info">Generate Configuration File</button>
            </div>
        </form>
        <?php
        $dataFilePath = '/tmp/subscription_data.txt';
        $configFilePath = '/etc/neko/config/sing-box.json';
        $downloadedContent = ''; 

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['generateConfig'])) {
            $subscribeUrl = trim($_POST['subscribeUrl']);
            $customTemplateUrl = trim($_POST['customTemplateUrl']);
            $templateOption = $_POST['templateOption'] ?? 'default';
            $dataContent = "Subscription Link Address: " . $subscribeUrl . "\n" . "Custom Template URL: " . $customTemplateUrl . "\n";
            file_put_contents($dataFilePath, $dataContent, FILE_APPEND);
            $subscribeUrlEncoded = urlencode($subscribeUrl);
            
            $customFileName = trim($_POST['customFileName']);
            if (empty($customFileName)) {
               $customFileName = 'sing-box';  
            }

            if (substr($customFileName, -5) !== '.json') {
                $customFileName .= '.json';
            }

            if ($templateOption === 'custom' && !empty($customTemplateUrl)) {
                $templateUrlEncoded = urlencode($customTemplateUrl);
            } else {
                $defaultTemplates = [
                    '1' => "https://raw.githubusercontent.com/Thaolga/Rules/main/Clash/json/config_7.json",
                    '2' => "https://raw.githubusercontent.com/Thaolga/Rules/main/Clash/json/config_6.json",
                    '3' => "https://raw.githubusercontent.com/Thaolga/Rules/main/Clash/json/config_9.json",
                    '4' => "https://raw.githubusercontent.com/Thaolga/Rules/main/Clash/json/config_10.json",
                    '5' => "https://raw.githubusercontent.com/Thaolga/Rules/main/Clash/json/config_11.json",
                    '6' => "https://raw.githubusercontent.com/Thaolga/Rules/main/Clash/json/config_8.json"
                ];

                $templateUrlEncoded = urlencode($defaultTemplates[$_POST['defaultTemplate']] ?? $defaultTemplates['mixed']);
            }

            $completeSubscribeUrl = "https://sing-box-subscribe-doraemon.vercel.app/config/{$subscribeUrlEncoded}&file={$templateUrlEncoded}";
            $tempFilePath = '/tmp/' . $customFileName;
            $command = "wget -O " . escapeshellarg($tempFilePath) . " " . escapeshellarg($completeSubscribeUrl);
            exec($command, $output, $returnVar);
            $logMessages = [];

            if ($returnVar !== 0) {
                $logMessages[] = "Unable to download content: " . htmlspecialchars($completeSubscribeUrl);
            } else {
                $downloadedContent = file_get_contents($tempFilePath);
                if ($downloadedContent === false) {
                    $logMessages[] = "Unable to read the downloaded file content";
                } else {
                    $configFilePath = '/etc/neko/config/' . $customFileName; 
                    if (file_put_contents($configFilePath, $downloadedContent) === false) {
                        $logMessages[] = "Unable to save the modified content to: " . $configFilePath;
                    } else {
                        $logMessages[] = "Configuration file generated and saved successfully: " . $configFilePath;
                        $logMessages[] = "Generated and downloaded subscription URL: <a href='" . htmlspecialchars($completeSubscribeUrl) . "' target='_blank'>" . htmlspecialchars($completeSubscribeUrl) . "</a>";
                    }
                }
            }

            echo "<div class='result-container'>";
            echo "<form method='post' action=''>";
            echo "<div class='mb-3'>";
            echo "<textarea id='configContent' name='configContent' class='form-control' style='height: 300px;'>" . htmlspecialchars($downloadedContent) . "</textarea>";
            echo "</div>";
            echo "<div class='text-center'>";
            echo "<button class='btn btn-info' type='button' onclick='copyToClipboard()'><i class='fas fa-copy'></i> Copy to Clipboard</button>";
            echo "<input type='hidden' name='saveContent' value='1'>";
            echo "<button class='btn btn-success' type='submit'>Save Changes</button>";
            echo "</div>";
            echo "</form>";
            echo "</div>";
            echo "<div class='alert alert-info' style='word-wrap: break-word; overflow-wrap: break-word;'>";
            foreach ($logMessages as $message) {
            echo $message . "<br>";
            }
            echo "</div>";
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['saveContent'])) {
            if (isset($_POST['configContent'])) {
                $editedContent = trim($_POST['configContent']);
                if (file_put_contents($configFilePath, $editedContent) === false) {
                    echo "<div class='alert alert-danger'>Unable to save the modified content to: " . htmlspecialchars($configFilePath) . "</div>";
                } else {
                    echo "<div class='alert alert-success'>Content successfully saved to: " . htmlspecialchars($configFilePath) . "</div>";
                }
            }
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['clearData'])) {
            if (file_exists($dataFilePath)) {
                file_put_contents($dataFilePath, '');
                echo "<div class='alert alert-success'>Saved data has been cleared</div>";
            }
        }

        if (file_exists($dataFilePath)) {
            $savedData = file_get_contents($dataFilePath);
            echo "<div class='card'>";
            echo "<div class='card-body'>";
            echo "<h2 class='card-title'>Saved data</h2>";
            echo "<pre>" . htmlspecialchars($savedData) . "</pre>";
            echo "<form method='post' action=''>";
            echo "<button class='btn btn-danger' type='submit' name='clearData'>Clear data</button>";
            echo "</form>";
            echo "</div>";
            echo "</div>";
        }
        ?>
    </div>
</div>
<script src="./assets/bootstrap/jquery.min.js"></script>
<script>
    function copyToClipboard() {
        const copyText = document.getElementById("configContent");
        copyText.select();
        document.execCommand("copy");
        alert("Copied to clipboard");
    }
</script>

<script>
document.addEventListener('DOMContentLoaded', (event) => {
    const savedFileName = localStorage.getItem('customFileName');

    if (savedFileName) {
        document.getElementById('customFileName').value = savedFileName;
        }
    });

document.getElementById('customFileName').addEventListener('input', function() {
    const customFileName = this.value.trim();
    localStorage.setItem('customFileName', customFileName);
    });

document.addEventListener("DOMContentLoaded", function () {
    const savedTemplate = localStorage.getItem("selectedTemplate");
    const customTemplateUrl = localStorage.getItem("customTemplateUrl");

    if (savedTemplate) {
        const templateInput = document.querySelector(`input[name="defaultTemplate"][value="${savedTemplate}"]`);
        if (templateInput) {
            templateInput.checked = true;
        }
    }

    if (customTemplateUrl) {
        document.getElementById("customTemplateUrl").value = customTemplateUrl;
        document.getElementById("useCustomTemplate").checked = true;
    }

    document.querySelectorAll('input[name="defaultTemplate"]').forEach(input => {
        input.addEventListener("change", function () {
            localStorage.setItem("selectedTemplate", this.value);
            localStorage.removeItem("customTemplateUrl"); 
        });
    });

    document.getElementById("customTemplateUrl").addEventListener("input", function () {
        localStorage.setItem("customTemplateUrl", this.value);
        localStorage.setItem("selectedTemplate", "custom"); 
    });

    document.getElementById("useCustomTemplate").addEventListener("change", function () {
        localStorage.setItem("selectedTemplate", "custom");
    });
});
</script>
</body>
</html>
