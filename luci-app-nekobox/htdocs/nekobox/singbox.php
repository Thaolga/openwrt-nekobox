<?php
ob_start();
include './cfg.php';

$dataFilePath = '/etc/neko/proxy_provider/subscription_data.txt';
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

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['setCron'])) {
    $cronExpression = trim($_POST['cronExpression']);
    $shellScriptPath = '/etc/neko/core/update_subscription.sh';
    $logFile = '/etc/neko/tmp/log.txt'; 

    if (preg_match('/^(\*|\d+)( (\*|\d+)){4}$/', $cronExpression)) {
        $cronJob = "$cronExpression $shellScriptPath";
        $currentCrons = shell_exec('crontab -l 2>/dev/null');
        $updatedCrons = preg_replace(
            "/^.*" . preg_quote($shellScriptPath, '/') . ".*$/m",
            '',
            $currentCrons
        );

        $updatedCrons = trim($updatedCrons) . "\n" . $cronJob . "\n";

        $tempCronFile = tempnam(sys_get_temp_dir(), 'cron');
        file_put_contents($tempCronFile, $updatedCrons);
        exec("crontab $tempCronFile");
        unlink($tempCronFile);

        $timestamp = date('[ H:i:s ]');
        file_put_contents($logFile, "$timestamp Cron job successfully set. Sing-box will update at $cronExpression.\n", FILE_APPEND);
        echo "<div class='alert alert-success'>Cron job set: $cronExpression</div>";
    } else {
        $timestamp = date('[ H:i:s ]');
        file_put_contents($logFile, "$timestamp Invalid Cron expression: $cronExpression\n", FILE_APPEND);
        echo "<div class='alert alert-danger'>Invalid Cron expression. Please check the format.</div>";
    }
}
?>

<?php
$shellScriptPath = '/etc/neko/core/update_subscription.sh';
$LOG_FILE = '/etc/neko/tmp/log.txt'; 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['subscribeUrl'])) {
        $SUBSCRIBE_URL = trim($_POST['subscribeUrl']);
        
        if (empty($SUBSCRIBE_URL)) {
            echo "<div class='alert alert-warning'>Subscription URL cannot be empty.</div>";
            exit;
        }
        
        echo "<div class='alert alert-success'>Submitted successfully: Subscription URL saved as $SUBSCRIBE_URL</div>";
    }

    if (isset($_POST['createShellScript'])) {
        $shellScriptContent = <<<EOL
#!/bin/sh

LOG_FILE="$LOG_FILE"
SUBSCRIBE_URL=\$(cat /etc/neko/proxy_provider/subscription.txt | tr -d '\\n\\r')

if [ -z "\$SUBSCRIBE_URL" ]; then
  echo "\$(date '+[ %H:%M:%S ]') Subscription URL is empty or extraction failed." >> "\$LOG_FILE"
  exit 1
fi

echo "\$(date '+[ %H:%M:%S ]') Using subscription URL: \$SUBSCRIBE_URL" >> "\$LOG_FILE"

CONFIG_DIR="/etc/neko/config"
if [ ! -d "\$CONFIG_DIR" ]; then
  mkdir -p "\$CONFIG_DIR"
  if [ \$? -ne 0 ]; then
    echo "\$(date '+[ %H:%M:%S ]') Failed to create config directory: \$CONFIG_DIR" >> "\$LOG_FILE"
    exit 1
  fi
fi

CONFIG_FILE="\$CONFIG_DIR/sing-box.json"
wget -q -O "\$CONFIG_FILE" "\$SUBSCRIBE_URL" >/dev/null 2>&1

if [ \$? -eq 0 ]; then
  echo "\$(date '+[ %H:%M:%S ]') Sing-box configuration file updated successfully. Saved to: \$CONFIG_FILE" >> "\$LOG_FILE"

  sed -i 's/"Proxy"/"DIRECT"/g' "\$CONFIG_FILE"

  if [ \$? -eq 0 ]; then
    echo "\$(date '+[ %H:%M:%S ]') Successfully replaced 'Proxy' with 'DIRECT' in the configuration file." >> "\$LOG_FILE"
  else
    echo "\$(date '+[ %H:%M:%S ]') Failed to replace 'Proxy' with 'DIRECT'. Please check the configuration file." >> "\$LOG_FILE"
    exit 1
  fi
else
  echo "\$(date '+[ %H:%M:%S ]') Configuration file update failed. Please check the URL or network." >> "\$LOG_FILE"
  exit 1
fi
EOL;

        if (file_put_contents($shellScriptPath, $shellScriptContent) !== false) {
            chmod($shellScriptPath, 0755);
            echo "<div class='alert alert-success'>Shell script created successfully! Path: $shellScriptPath</div>";
        } else {
            echo "<div class='alert alert-danger'>Failed to create Shell script. Please check permissions.</div>";
        }
    }
}
?>

<!doctype html>
<html lang="en" data-bs-theme="<?php echo substr($neko_theme, 0, -4) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>singbox - Nekobox</title>
    <link rel="icon" href="./assets/img/nekobox.png">
    <link href="./assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="./assets/css/custom.css" rel="stylesheet">
    <link href="./assets/theme/<?php echo $neko_theme ?>" rel="stylesheet">
    <link href="./assets/bootstrap/bootstrap-icons.css" rel="stylesheet">
    <script type="text/javascript" src="./assets/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="./assets/js/feather.min.js"></script>
    <script type="text/javascript" src="./assets/bootstrap/bootstrap.bundle.min.js"></script>
    <script type="text/javascript" src="./assets/js/jquery-2.1.3.min.js"></script>
    <script type="text/javascript" src="./assets/js/neko.js"></script>
    <?php include './ping.php'; ?>
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
        <a href="./index.php" class="col btn btn-lg"><i class="bi bi-house-door"></i> Home</a>
        <a href="./mihomo_manager.php" class="col btn btn-lg"><i class="bi bi-folder"></i> Manager</a>
        <a href="./singbox.php" class="col btn btn-lg"><i class="bi bi-shop"></i> Sing-box</a>
        <a href="./subscription.php" class="col btn btn-lg"><i class="bi bi-bank"></i> Singbox</a>
        <a href="./mihomo.php" class="col btn btn-lg"><i class="bi bi-building"></i> Mihomo</a>
<div class="outer-container">
    <div class="container" style="padding-left: 2.4em; padding-right: 2.4em;">
        <h1 class="title text-center" style="margin-top: 3rem; margin-bottom: 2rem;">Sing-box  Template I</h1>
        <div class="alert alert-info">
            <h4 class="alert-heading">Help Information</h4>
            <ul>
                <li><strong>Template 1</strong>：No region, no grouping</li>
                <li><strong>Template 2</strong>：No region, with routing rules</li>
                <li><strong>Template 3</strong>：Hong Kong, Taiwan, Singapore, Japan, United States, South Korea, grouped with routing rules</li>
            </ul>
        </div>
        <form method="post" action="">
            <div class="mb-3">
                <label for="subscribeUrl" class="form-label">Subscription Link Address:</label>
                <input type="text" class="form-control" id="subscribeUrl" name="subscribeUrl" value="<?php echo htmlspecialchars($lastSubscribeUrl); ?>" placeholder="Enter the subscription link" required>
            </div>
            <div class="mb-3">
                <label for="customFileName" class="form-label">Custom filename (Default: sing-box.json)</label>
                <input type="text" class="form-control" id="customFileName" name="customFileName" placeholder="sing-box.json">
            </div>
            <fieldset class="mb-3">
                <legend class="form-label">Select a template</legend>
                <div class="row">
                    <div class="col">
                        <input type="radio" class="form-check-input" id="useDefaultTemplate0" name="defaultTemplate" value="0" checked>
                        <label class="form-check-label" for="useDefaultTemplate0">Default</label>
                    </div>
                    <div class="col">
                        <input type="radio" class="form-check-input" id="useDefaultTemplate1" name="defaultTemplate" value="1" checked>
                        <label class="form-check-label" for="useDefaultTemplate1">Template 1</label>
                    </div>
                    <div class="col">
                        <input type="radio" class="form-check-input" id="useDefaultTemplate2" name="defaultTemplate" value="2">
                        <label class="form-check-label" for="useDefaultTemplate2">Template 2</label>
                    </div>
                    <div class="col">
                        <input type="radio" class="form-check-input" id="useDefaultTemplate3" name="defaultTemplate" value="3">
                        <label class="form-check-label" for="useDefaultTemplate3">Template 3</label>
                    </div>
                    <div class="col">
                        <input type="radio" class="form-check-input" id="useDefaultTemplate4" name="defaultTemplate" value="4">
                        <label class="form-check-label" for="useDefaultTemplate4">Template 4</label>
                    </div>
                    <div class="col">
                        <input type="radio" class="form-check-input" id="useDefaultTemplate5" name="defaultTemplate" value="5">
                        <label class="form-check-label" for="useDefaultTemplate5">Template 5</label>
                    </div>
                </div>
                <div class="mt-3">
                    <input type="radio" class="form-check-input" id="useCustomTemplate" name="templateOption" value="custom">
                    <label class="form-check-label mb-3" for="useCustomTemplate">Use Custom Template URL:</label>
                    <input type="text" class="form-control" id="customTemplateUrl" name="customTemplateUrl" placeholder="Enter Custom Template URL">
                </div>
            </fieldset>
            <div class="row mb-4"> 
                <div class="col-auto">
                    <form method="post" action="">
                        <button type="submit" name="generateConfig" class="btn btn-info">
                            <i class="bi bi-file-earmark-text"></i> Generate a configuration file
                        </button>
                    </form>
                </div>
                <div class="col-auto">
                    <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#cronModal">
                        <i class="bi bi-clock"></i> Set up a scheduled task
                    </button>
                </div>
                <div class="col-auto">
                    <form method="post" action="">
                        <button type="submit" name="createShellScript" class="btn btn-primary">
                            <i class="bi bi-terminal"></i> Generate an update script
                        </button>
                    </form>
                </div>
            </div>
        <div class="modal fade" id="cronModal" tabindex="-1" aria-labelledby="cronModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
          <div class="modal-dialog modal-lg">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="cronModalLabel">Set up a scheduled task</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
              </div>
              <form method="post" action="">
                <div class="modal-body">
                  <div class="mb-3">
                    <label for="cronExpression" class="form-label">Cron expression</label>
                    <input type="text" class="form-control" id="cronExpression" name="cronExpression" value="0 2 * * *" required>
                  </div>
                  <div class="alert alert-info">
                    <strong>Hint:</strong> Cron expression format：
                    <ul>
                      <li><code>Minute Hour Day Month Weekday</code></li>
                      <li>Example: Every day at 2 AM: <code>0 2 * * *</code></li>
                      <li>Every Monday at 3 AM: <code>0 3 * * 1</code></li>
                    </ul>
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                  <button type="submit" name="setCron" class="btn btn-primary">Save</button>
                </div>
              </form>
            </div>
          </div>
        </div>
<script>
    document.querySelectorAll('input[name="defaultTemplate"]').forEach((elem) => {
        elem.addEventListener('change', function () {
            const customTemplateDiv = document.getElementById('customTemplateUrlDiv');
            if (this.value === 'custom') {
                customTemplateDiv.style.display = 'block';
            } else {
                customTemplateDiv.style.display = 'none';
            }
        });
    });
</script>
        <?php
        $dataFilePath = '/etc/neko/proxy_provider/subscription_data.txt';
        $configFilePath = '/etc/neko/config/sing-box.json';
        $downloadedContent = ''; 
        $fixedFileName = 'subscription.txt';

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['generateConfig'])) {
            $subscribeUrl = trim($_POST['subscribeUrl']);
            $customTemplateUrl = trim($_POST['customTemplateUrl']);
            $templateOption = $_POST['templateOption'] ?? 'default';
            $currentTime = date('Y-m-d H:i:s');
            $dataContent = $currentTime . " | Subscription Link Address: " . $subscribeUrl . "\n";            
            $customFileName = trim($_POST['customFileName']);
            if (empty($customFileName)) {
               $customFileName = 'sing-box';  
            }

            if (substr($customFileName, -5) !== '.json') {
                $customFileName .= '.json';
            }

            $currentData = file_exists($dataFilePath) ? file_get_contents($dataFilePath) : '';
            $logEntries = array_filter(explode("\n\n", trim($currentData)));
            if (!in_array(trim($dataContent), $logEntries)) {
                $logEntries[] = trim($dataContent);
            }

            while (count($logEntries) > 10) {
                array_shift($logEntries);
            }

            file_put_contents($dataFilePath, implode("\n\n", $logEntries) . "\n\n");

            $subscribeUrlEncoded = urlencode($subscribeUrl);
            if ($templateOption === 'custom' && !empty($customTemplateUrl)) {
                $templateUrlEncoded = urlencode($customTemplateUrl);
            } else {
                $defaultTemplates = [
                    '1' => "https://raw.githubusercontent.com/Thaolga/Rules/main/Clash/json/config_7.json",
                    '2' => "https://raw.githubusercontent.com/Thaolga/Rules/main/Clash/json/config_6.json",
                    '3' => "https://raw.githubusercontent.com/Thaolga/Rules/main/Clash/json/config_8.json",
                    '4' => "https://raw.githubusercontent.com/Thaolga/Rules/main/Clash/json/config_12.json",
                    '5' => "https://raw.githubusercontent.com/Thaolga/Rules/main/Clash/json/config_2.json"
                ];

                $templateUrlEncoded = urlencode($defaultTemplates[$_POST['defaultTemplate']] ?? '');
            }

            if (empty($templateUrlEncoded)) {
                $completeSubscribeUrl = "https://sing-box-subscribe-doraemon.vercel.app/config/{$subscribeUrlEncoded}";
            } else {
                $completeSubscribeUrl = "https://sing-box-subscribe-doraemon.vercel.app/config/{$subscribeUrlEncoded}&file={$templateUrlEncoded}";
            }

            $tempFilePath = '/etc/neko/' . $customFileName;
            $logMessages = [];
            $command = "wget -O " . escapeshellarg($tempFilePath) . " " . escapeshellarg($completeSubscribeUrl);
            exec($command, $output, $returnVar);

            if ($returnVar !== 0) {
                $command = "curl -s -L -o " . escapeshellarg($tempFilePath) . " " . escapeshellarg($completeSubscribeUrl);
                exec($command, $output, $returnVar);

            if ($returnVar !== 0) {
                $logMessages[] = "Unable to download content: " . htmlspecialchars($completeSubscribeUrl);
                }
            }

            if ($returnVar === 0) {
                $downloadedContent = file_get_contents($tempFilePath);
                if ($downloadedContent === false) {
                    $logMessages[] = "Unable to read the downloaded file content";
                } else {

                    $downloadedContent = preg_replace_callback(
                        '/\{\s*"tag":\s*"(.*?)",\s*"type":\s*"selector",\s*"outbounds":\s*\[\s*"Proxy"\s*\]\s*\}/s',
                        function ($matches) {
                            return str_replace('"Proxy"', '"DIRECT"', $matches[0]);
                        },
                        $downloadedContent
                    );

                    if (isset($_POST['defaultTemplate']) && $_POST['defaultTemplate'] == '0') {
                $replacement = '
  "clash_api": {
      "external_ui": "/etc/neko/ui/",
      "external_controller": "0.0.0.0:9090",
      "secret": "Akun",
      "external_ui_download_url": ""
    },';  

                $downloadedContent = preg_replace('/"clash_api":\s*\{.*?\},/s', $replacement, $downloadedContent);
            }

                    $tmpFileSavePath = '/etc/neko/proxy_provider/' . $fixedFileName;  
                    if (file_put_contents($tmpFileSavePath, $completeSubscribeUrl) === false) {
                        $logMessages[] = "Unable to save subscription URL to file: " . $tmpFileSavePath;
                    } else {
                        $logMessages[] = "The subscription URL has been successfully saved to the file: " . $tmpFileSavePath;
                    }

                    $configFilePath = '/etc/neko/config/' . $customFileName; 
                    if (file_put_contents($configFilePath, $downloadedContent) === false) {
                        $logMessages[] = "Unable to save the modified content to: " . $configFilePath;
                    } else {
                        $logMessages[] = "Configuration file generated and saved successfully: " . $configFilePath;
                    }

                    if (file_exists($tempFilePath)) {
                        unlink($tempFilePath); 
                        $logMessages[] = "Temporary file has been cleaned: " . $tempFilePath;
                    } else {
                        $logMessages[] = "Temporary file not found for cleaning: " . $tempFilePath;
                    }
                }
            }
            echo "<div class='result-container'>";
            echo "<form method='post' action=''>";
            echo "<div class='mb-3'>";
            echo "<textarea id='configContent' name='configContent' class='form-control' style='height: 300px;'>" . htmlspecialchars($downloadedContent) . "</textarea>";
            echo "</div>";
            echo "<div class='text-center' mb-3>";
            echo "<button class='btn btn-info me-3' type='button' onclick='copyToClipboard()'><i class='bi bi-clipboard'></i> Copy to Clipboard</button>";
            echo "<input type='hidden' name='saveContent' value='1'>";
            echo "<button class='btn btn-success' type='submit'><i class='bi bi-save'></i>Save Changes</button>";
            echo "</div>";
            echo "</form>";
            echo "</div>";
            echo "<div class='alert alert-info mt-3' style='word-wrap: break-word; overflow-wrap: break-word;'>";
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
