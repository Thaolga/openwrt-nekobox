<?php
$configFile = "/etc/config/spectra"; 

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!file_exists($configFile)) {
        echo json_encode(["error" => "Config file not found!"]);
        exit;
    }

    $content = file_get_contents($configFile);
    preg_match("/option mode '(\w+)'/", $content, $matches);
    $currentMode = $matches[1] ?? "dark";

    $newMode = ($currentMode === "dark") ? "light" : "dark";
    $updatedContent = preg_replace("/option mode '\w+'/", "option mode '$newMode'", $content);
    
    if (file_put_contents($configFile, $updatedContent) !== false) {
        echo json_encode(["success" => true, "mode" => $newMode]);
    } else {
        echo json_encode(["error" => "Failed to update config!"]);
    }
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    if (!file_exists($configFile)) {
        echo json_encode(["mode" => "dark"]);
    } else {
        $content = file_get_contents($configFile);
        preg_match("/option mode '(\w+)'/", $content, $matches);
        $mode = $matches[1] ?? "dark";
        echo json_encode(["mode" => $mode]);
    }
    exit;
}
?>
