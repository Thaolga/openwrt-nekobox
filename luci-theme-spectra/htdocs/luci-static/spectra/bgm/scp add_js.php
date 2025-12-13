<?php
$files = [
    '/usr/lib/lua/luci/view/passwall/node_list/node_list.htm',
    '/usr/lib/lua/luci/view/passwall2/node_list/node_list.htm'
];

$js = <<<'EOD'

<!-- Passwall Card Layout JS - Auto Inject -->
<script defer src="/luci-static/spectra/js/card_layout.js"></script>
<script>
function saveCardOrder(group) {
    const translations = languageTranslations[currentLang] || languageTranslations['zh'];
    const container = document.querySelector(`.cards-container[data-group="${group}"]`);
    if (!container) return;
    
    const cards = container.querySelectorAll('.node-card');
    if (!cards.length) return;
    
    const btn = document.getElementById("save_order_btn_" + group);
    if (btn) btn.disabled = true;
    
    const ids = [];
    cards.forEach(card => {
        const id = card.getAttribute('data-id');
        if (id) ids.push(id);
    });
    
    XHR.get('<%=api.url("save_node_order")%>', {
        group: group,
        ids: ids.join(",")
    },
    function(x, result) {
        if (btn) btn.disabled = false;
        if (x && x.status === 200) {
            const successMsg = translations['node_order_save_success'] || "Save current page order successfully.";
            if (typeof showLogMessage === 'function') {
                showLogMessage(successMsg);
            }
            if (typeof colorVoiceEnabled !== 'undefined' && colorVoiceEnabled) {
                speakMessage(successMsg);
            }
        } else {
            const errorMsg = "<%:Save failed!%>";
            if (typeof showLogMessage === 'function') {
                showLogMessage(errorMsg);
            }
            if (typeof colorVoiceEnabled !== 'undefined' && colorVoiceEnabled) {
                speakMessage(errorMsg);
            }
        }
    });
}
</script>
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
