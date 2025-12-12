<?php
$files = [
    '/usr/lib/lua/luci/view/passwall/node_list/node_list.htm',
    '/usr/lib/lua/luci/view/passwall2/node_list/node_list.htm'
];

$js = <<<'EOD'

<!-- Passwall Card Layout JS - Auto Inject -->
<script defer src="/luci-static/spectra/js/card_layout.js"></script>
<!-- End Passwall Card Layout JS -->

EOD;

$markerStart = '<!-- Passwall Card Layout JS - Auto Inject -->';
$markerEnd   = '<!-- End Passwall Card Layout JS -->';

$action = isset($_GET['action']) ? $_GET['action'] : 'add';

foreach ($files as $file) {

    if (!file_exists($file)) continue;

    $content = file_get_contents($file);


    if ($action === "remove") {

        $pattern = '/' . preg_quote($markerStart, '/') . '.*?' . preg_quote($markerEnd, '/') . '\s*/s';
        $newContent = preg_replace($pattern, '', $content);

        if ($newContent !== $content) {
            file_put_contents($file, $newContent);
        }

        continue;
    }

    if (strpos($content, $markerStart) !== false) {
        continue;
    }

    file_put_contents($file, rtrim($content, "\n") . "\n\n" . $js . "\n");
}

exit;
?>
