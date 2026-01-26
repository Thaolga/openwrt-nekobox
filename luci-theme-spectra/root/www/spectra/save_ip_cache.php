<?php
header('Content-Type: application/json');

$cacheFile = __DIR__ . '/lib/ip_cache.json';
$input = file_get_contents('php://input');

if (!$input) {
    echo json_encode(['status'=>'error','message'=>'No input data']);
    exit;
}

$data = json_decode($input, true);
if (!$data) {
    echo json_encode(['status'=>'error','message'=>'Invalid JSON']);
    exit;
}

if (isset($data['ip'])) $data = [$data];

$cache = [];
if (file_exists($cacheFile)) {
    $content = file_get_contents($cacheFile);
    $cache = json_decode($content, true) ?: [];
}

$ipIndex = [];
foreach ($cache as $item) {
    if (isset($item['ip'])) $ipIndex[$item['ip']] = $item;
}

foreach ($data as $item) {
    if (!isset($item['ip'])) continue;
    $ip = $item['ip'];

    if (!isset($ipIndex[$ip])) {
        $ipIndex[$ip] = [];
    }

    $ipIndex[$ip]['ip'] = $ip;
    $ipIndex[$ip]['country'] = $item['country'] ?? $ipIndex[$ip]['country'] ?? '';
    $ipIndex[$ip]['region'] = $item['region'] ?? $ipIndex[$ip]['region'] ?? '';
    $ipIndex[$ip]['city'] = $item['city'] ?? $ipIndex[$ip]['city'] ?? '';
    $ipIndex[$ip]['isp'] = $item['isp'] ?? $ipIndex[$ip]['isp'] ?? '';
    $ipIndex[$ip]['asn'] = $item['asn'] ?? $ipIndex[$ip]['asn'] ?? '';
    $ipIndex[$ip]['asn_organization'] = $item['asn_organization'] ?? $ipIndex[$ip]['asn_organization'] ?? '';
    $ipIndex[$ip]['country_code'] = $item['country_code'] ?? $ipIndex[$ip]['country_code'] ?? '';
    $ipIndex[$ip]['timezone'] = $item['timezone'] ?? $ipIndex[$ip]['timezone'] ?? '';
    $ipIndex[$ip]['latitude'] = $item['latitude'] ?? $ipIndex[$ip]['latitude'] ?? '';
    $ipIndex[$ip]['longitude'] = $item['longitude'] ?? $ipIndex[$ip]['longitude'] ?? '';
    
    $language = $item['language'] ?? 'zh-CN';
    
    if (!isset($ipIndex[$ip]['translations'])) {
        $ipIndex[$ip]['translations'] = [];
    }
    
    $ipIndex[$ip]['translations'][$language] = [
        'translatedCountry' => $item['translatedCountry'] ?? '',
        'translatedRegion' => $item['translatedRegion'] ?? '',
        'translatedCity' => $item['translatedCity'] ?? '',
        'translatedISP' => $item['translatedISP'] ?? '',
        'translatedASNOrg' => $item['translatedASNOrg'] ?? ''
    ];
    
    $ipIndex[$ip]['default_language'] = $language;
    $ipIndex[$ip]['last_updated'] = date('Y-m-d H:i:s');
}

$cache = array_values($ipIndex);
if (count($cache) > 500) {
    $cache = array_slice($cache, -500);
}

$tempFile = $cacheFile . '.tmp';
file_put_contents($tempFile, json_encode($cache, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
rename($tempFile, $cacheFile);

echo json_encode(['status'=>'ok','message'=>'Cache saved','count'=>count($cache)]);
?>