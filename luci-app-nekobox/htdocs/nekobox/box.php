<?php
ob_start();
include './cfg.php';
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
    <script src="./assets/js/feather.min.js"></script>
    <script src="./assets/js/jquery-2.1.3.min.js"></script>
    <script src="./assets/js/neko.js"></script>
</head>
<body>
<div class="container-sm container-bg callout border border-3 rounded-4 col-11">
    <div class="row">
        <a href="./index.php" class="col btn btn-lg">🏠 Home</a>
        <a href="./upload.php" class="col btn btn-lg">📂 Mihomo</a>
        <a href="./upload_sb.php" class="col btn btn-lg">🗂️ Sing-box</a>
        <a href="./box.php" class="col btn btn-lg">💹 Template</a>
        <a href="./nekobox.php" class="col btn btn-lg">📦 File Assistant</a>
<div class="outer-container">
    <div class="container">
        <h1 class="title text-center">Sing-box Subscription Conversion Template</h1>
        <div class="alert alert-info">
            <h4 class="alert-heading">Help Information</h4>
                <p>Please select a template to generate the configuration file: choose the corresponding template based on the subscription node information, otherwise, it will not start.</p>
                <ul>
                    <li><strong>Default Template 1</strong>：Hong Kong, Taiwan, Singapore, Japan, United States, South Korea.</li>
                    <li><strong>Default Template 2</strong>：Singapore, Japan, United States, South Korea.</li>
                    <li><strong>Default Template 3</strong>：Hong Kong, Singapore, Japan, United States</li>
                    <li><strong>Default Template 4</strong>：Hong Kong, Japan, United States.</li>
                    <li><strong>Default Template 5</strong>：No region, universal.</li>
            </ul>
        </div>
        <form method="post" action="">
            <div class="mb-3">
                <label for="subscribeUrl" class="form-label">Subscription Link Address:</label>
                <input type="text" class="form-control" id="subscribeUrl" name="subscribeUrl" required>
            </div>
            <fieldset class="mb-3">
                <legend class="form-label">Select a template</legend>
                <div class="form-check">
                    <input type="radio" class="form-check-input" id="useDefaultTemplate" name="templateOption" value="default" checked>
                    <label class="form-check-label" for="useDefaultTemplate">Use default template</label>
                </div>
                <div class="row">
                    <div class="col">
                        <input type="radio" class="form-check-input" id="useDefaultTemplate1" name="defaultTemplate" value="mixed" checked>
                        <label class="form-check-label" for="useDefaultTemplate1">Default template 1</label>
                    </div>
                    <div class="col">
                        <input type="radio" class="form-check-input" id="useDefaultTemplate2" name="defaultTemplate" value="second">
                        <label class="form-check-label" for="useDefaultTemplate2">Default template 2</label>
                    </div>
                    <div class="col">
                        <input type="radio" class="form-check-input" id="useDefaultTemplate3" name="defaultTemplate" value="fakeip">
                        <label class="form-check-label" for="useDefaultTemplate3">Default template 3</label>
                    </div>
                    <div class="col">
                        <input type="radio" class="form-check-input" id="useDefaultTemplate4" name="defaultTemplate" value="tun">
                        <label class="form-check-label" for="useDefaultTemplate4">Default template 4</label>
                    </div>
                    <div class="col">
                        <input type="radio" class="form-check-input" id="useDefaultTemplate5" name="defaultTemplate" value="ip">
                        <label class="form-check-label" for="useDefaultTemplate5">Default template 5</label>
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
        $configFilePath = '/etc/neko/config/config.json';

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['generateConfig'])) {
            $subscribeUrl = trim($_POST['subscribeUrl']);
            $customTemplateUrl = trim($_POST['customTemplateUrl']);
            $dataContent = "Subscription Link Address: " . $subscribeUrl . "\n" . "Custom Template URL: " . $customTemplateUrl . "\n";
            file_put_contents($dataFilePath, $dataContent, FILE_APPEND);
            $subscribeUrlEncoded = urlencode($subscribeUrl);

            if ($_POST['templateOption'] === 'custom' && !empty($customTemplateUrl)) {
                $templateUrlEncoded = urlencode($customTemplateUrl);
            } else {
                $defaultTemplates = [
                    'mixed' => "https://raw.githubusercontent.com/Thaolga/Rules/main/Clash/json/config_1.json",
                    'second' => "https://raw.githubusercontent.com/Thaolga/Rules/main/Clash/json/config_2.json",
                    'fakeip' => "https://raw.githubusercontent.com/Thaolga/Rules/main/Clash/json/config_3.json",
                    'tun' => "https://raw.githubusercontent.com/Thaolga/Rules/main/Clash/json/config_4.json",
                    'ip' => "https://raw.githubusercontent.com/Thaolga/Rules/main/Clash/json/config_5.json"
                ];
                $templateUrlEncoded = urlencode($defaultTemplates[$_POST['defaultTemplate']] ?? $defaultTemplates['mixed']);
            }

            $completeSubscribeUrl = "https://sing-box-subscribe-doraemon.vercel.app/config/{$subscribeUrlEncoded}&file={$templateUrlEncoded}";
            $tempFilePath = '/tmp/config.json';
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
                echo "<div class='alert alert-success'>Saved data has been cleared.</div>";
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
</body>
</html>
