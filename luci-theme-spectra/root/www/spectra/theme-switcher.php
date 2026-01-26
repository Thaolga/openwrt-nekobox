<?php
$configFileSpectra = "/etc/config/spectra"; 
$configFileArgon = "/etc/config/argon"; 

function toggleModeInFile($configFile) {
    if (!file_exists($configFile)) {
        return ["error" => "Config file not found!"];
    }

    $content = file_get_contents($configFile);
    preg_match("/option mode '(\w+)'/", $content, $matches);
    $currentMode = $matches[1] ?? "dark";

    $newMode = ($currentMode === "dark") ? "light" : "dark";
    $updatedContent = preg_replace("/option mode '\w+'/", "option mode '$newMode'", $content);
    
    if (file_put_contents($configFile, $updatedContent) !== false) {
        return $newMode;
    } else {
        return false;
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $spectraMode = toggleModeInFile($configFileSpectra);
    if ($spectraMode !== false) {
        file_put_contents($configFileArgon, str_replace("option mode '$spectraMode'", "option mode '$spectraMode'", file_get_contents($configFileSpectra)));
        echo json_encode(["success" => true, "mode" => $spectraMode]);
    } else {
        echo json_encode(["error" => "Failed to update spectra config!"]);
    }
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    if (!file_exists($configFileSpectra)) {
        echo json_encode(["mode" => "dark"]);
    } else {
        $content = file_get_contents($configFileSpectra);
        preg_match("/option mode '(\w+)'/", $content, $matches);
        $mode = $matches[1] ?? "dark";
        echo json_encode(["mode" => $mode]);
    }
    exit;
}
?>
