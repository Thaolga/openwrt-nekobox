<?php
$default_url = 'https://raw.githubusercontent.com/Thaolga/Rules/main/Clash/songs.txt';

$message = '';  

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['new_url'])) {
        $new_url = $_POST['new_url'];  
        $file_path = 'url_config.txt';  
        if (file_put_contents($file_path, $new_url)) {
            $message = 'URL updated successfully';
        } else {
            $message = 'Failed to update URL';
        }
    }

    if (isset($_POST['reset_default'])) {
        $file_path = 'url_config.txt';  
        if (file_put_contents($file_path, $default_url)) {
            $message = 'Default URL restored successfully';
        } else {
            $message = 'Failed to restore default URL';
        }
    }
}
else {
    $new_url = file_exists('url_config.txt') ? file_get_contents('url_config.txt') : $default_url;
}
?>
<?php
ob_start();
include './cfg.php';
$translate = [
];
$lang = $_GET['lang'] ?? 'en';
?>
<style>
.img-con {
  width: 65px;  
  height: 55px; 
  display: flex;
  justify-content: center;
  overflow: visible;
}

#flag {
 width: auto;
 height: auto;
  max-width: 65px; 
  max-height: 55px;
  object-fit: contain;
}

.status-icon {
  width: 58px; 
  height: 58px; 
  object-fit: contain; 
  display: block;
}

.status-icons {
  display: flex;
  height: 55px;
  margin-left: auto;
}

.site-icon {
  display: flex;
  justify-content: center;
  height: 55px;
  margin: 0 6px; 
}

.mx-1 {
  margin: 0 4px;
}

.site-icon[onclick*="github"] .status-icon {
  width: 61px; 
  height: 59px;
}

.site-icon[onclick*="github"] {
  width: 60px;
  height: 57px;
  display: flex;
  justify-content: center;
}

.site-icon[onclick*="openai"] .status-icon {
  width: 62px; 
  height: 64px;
  margin-top: -2px;
}

.site-icon[onclick*="openai"] {
  width: 62px;
  height: 64px;
  display: flex;
  justify-content: center;
}

.container-sm.container-bg.callout.border {
  padding: 12px 15px; 
  min-height: 70px; 
  margin-bottom: 15px;
}

.row.align-items-center {
  width: 100%;
  margin: 0;
  display: flex;
  gap: 15px; 
  height: 55px; /
}

.col-3 {
  height: 55px;
  display: flex;
  flex-direction: column;
  justify-content: center;
}

.col.text-center {
  position: static; 
  left: auto;
  transform: none;
}

.container-sm .row .col-4 {
  position: static !important;
  order: 2 !important; 
  width: 100% !important;
  padding-left: 54px !important;
  margin-top: 5px !important;
  text-align: left !important;
}

#ping-result {
  font-weight: bold;
}

#d-ip {
  color: #09B63F;
  font-weight: 700 !important;
}

#d-ip > .ip-main {
    font-size: 15px !important;
}

#d-ip .badge-primary {
    font-size: 13px !important;
}

.info.small {
 color: #ff69b4;
 font-weight: 600;
 white-space: nowrap;
}

.site-icon, .img-con {
 cursor: pointer !important;
 transition: all 0.2s ease !important;
 position: relative !important;
 user-select: none !important;
}

.site-icon:hover, .img-con:hover {
 transform: translateY(-2px) !important;
}

.site-icon:active, .img-con:active {
 transform: translateY(1px) !important;
 opacity: 0.8 !important;
}

@media (max-width: 1206px) {
 .site-icon[onclick*="baidu"],
 .site-icon[onclick*="taobao"], 
 .site-icon[onclick*="google"],
 .site-icon[onclick*="openai"],
 .site-icon[onclick*="youtube"],
 .site-icon[onclick*="github"] {
   display: none !important;
 }
}
</style>

<?php if (in_array($lang, ['zh-cn', 'en', 'auto'])): ?>
    <div id="status-bar-component" class="container-sm container-bg callout border border-3 rounded-4 col-11">
        <div class="row align-items-center">
            <div class="col-auto">
                <div class="img-con">
                    <img src="./assets/neko/img/loading.svg" id="flag" title="Click to refresh the IP address" onclick="IP.getIpipnetIP()">
                </div>
            </div>
            <div class="col-3">
                <p id="d-ip" class="ip-address mb-0">Checking...</p>
                <p id="ipip" class="info small mb-0"></p>
            </div>
            <div class="col text-center"> 
                <p id="ping-result" class="mb-0"></p>
            </div>
            <div class="col-auto ms-auto">
                <div class="status-icons d-flex">
                    <div class="site-icon mx-1" onclick="pingHost('google', 'Google')">
                        <img src="./assets/neko/img/site_icon_03.png" id="google-normal" class="status-icon" title="Testing Google latency" style="display: none;">
                        <img src="./assets/neko/img/site_icon1_03.png" id="google-gray" class="status-icon">
                    </div>
                    <div class="site-icon mx-1" onclick="pingHost('openai', 'OpenAI')">
                        <img src="./assets/neko/img/site_icon_06.png" id="openai-normal" title="Testing OpenAI  latency"  class="status-icon" style="display: none;">
                        <img src="./assets/neko/img/site_icon1_06.png" id="openai-gray" class="status-icon">
                    </div>
                    <div class="site-icon mx-1" onclick="pingHost('youtube', 'YouTube')">
                        <img src="./assets/neko/img/site_icon_04.png" id="youtube-normal" class="status-icon" title="Testing YouTube latency" style="display: none;">
                        <img src="./assets/neko/img/site_icon1_04.png" id="youtube-gray" class="status-icon">
                    </div>
                    <div class="site-icon mx-1" onclick="pingHost('github', 'GitHub')">
                        <img src="./assets/neko/img/site_icon_05.png" id="github-normal" title="Testing GitHub latency" class="status-icon" style="display: none;">
                        <img src="./assets/neko/img/site_icon1_05.png" id="github-gray" class="status-icon">
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
<style>
    #leafletMap {
        width: 100%;
        height: 400px;
        position: relative;
    }

    #leafletMap.fullscreen {
        width: 100vw;
        height: 100vh;
        position: fixed;
        top: 0;
        left: 0;
        z-index: 9999;
    }

    .fullscreen-btn,
    .exit-fullscreen-btn {
        position: absolute;
        top: 10px;
        right: 10px;
        background-color: #fff;
        border: 1px solid #ccc;
        padding: 5px;
        cursor: pointer;
        border-radius: 50%;
        font-size: 20px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        z-index: 10000;
    }

    .exit-fullscreen-btn {
        display: none;
    }

    #d-ip {
        display: flex;
        align-items: center;
        gap: 5px;  
        flex-wrap: nowrap;  
    }

    svg.feather {
        width: 20px !important;
        height: 20px !important;
        vertical-align: middle !important;
        margin-right: 5px !important;
        stroke: #FF00FF !important; 
        fill: none !important;
    }

    #dropArea {
        border: 2px dashed #007bff;
        padding: 20px;
        border-radius: 10px;
        text-align: center;
        background-color: #f8f9fa;
    }

    #dropArea.dragging {
        background-color: #e9ecef;
    }

    #dropArea p {
        color: #ff69b4; 
    }

    #uploadIcon {
        font-size: 50px;
        color: #007bff;
        cursor: pointer;
        margin-bottom: 20px;
        transition: color 0.3s;
    }

    #uploadIcon:hover {
        color: #0056b3; 
    }

    #submitBtnModal {
        display: none;
        padding: 10px 20px;
        font-size: 16px;
        border: none;
        background-color: #28a745;
        color: white;
        border-radius: 5px;
        cursor: pointer;
        transition: background-color 0.3s;
    }

    #submitBtnModal:hover {
        background-color: #218838;
    }

    .popup {
        display: none; 
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: rgba(255, 255, 255, 0.8);
        backdrop-filter: blur(10px);
        color: #333;
        padding: 20px;
        border-radius: 12px;
        z-index: 1000;
        text-align: center;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        width: 820px;
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
    }

    .popup h3 {
        grid-column: span 3;
        text-align: center;
        margin-bottom: 10px;
    }

    .popup button {
        padding: 12px;
        font-size: 14px;
        cursor: pointer;
        border: none;
        border-radius: 8px;
        background-color: rgba(0, 0, 0, 0.1);
        color: #333;
        transition: background 0.3s, transform 0.2s;
    }

    .popup button:hover {
        background: rgba(0, 0, 0, 0.2);
        transform: scale(1.05);
    }

    .popup button:active {
        transform: scale(0.95);
    }

    .popup button:last-child {
        grid-column: span 3;
        justify-self: center;
        width: 80%;
        background: rgba(255, 0, 0, 0.2);
        color: red;
    }

    .popup button:last-child:hover {
        background: rgba(255, 0, 0, 0.4);
    }

    label[for="newPath"], label[for="permissions"], .form-text {
        color: white !important;
    }

    .container-bg {
      border-radius: 12px;
      box-shadow: var(--bs-shadow-medium);
      padding: 2rem;
      margin-top: 2rem;
      margin-bottom: 2rem;
    }

@media (max-width: 768px) {
    .d-flex.justify-content-between.gap-2 {
        width: 100%;
        display: flex;
        justify-content: space-between; 
        gap: 5px; 
        padding-left: 0.7em; 
    }

    .d-flex.justify-content-between.gap-2 .btn {
        flex: 1; 
        min-width: 0; 
        text-align: center;
    }
}


@media (max-width: 768px) {
    .modal-dialog {
        max-width: 100% !important;
        margin: 30px auto;
    }

    .table thead {
        display: none;
    }

    .table tbody,
    .table tr,
    .table td {
        display: block;
        width: 100%;
    }

    .table tr {
        margin-bottom: 10px;
        border: 1px solid #dee2e6;
        border-radius: 5px;
        padding: 10px;
        background: #f8f9fa;
    }

    .table td::before {
        content: attr(data-label);
        font-weight: bold;
        display: block;
        margin-bottom: 5px;
    }

    .table td img,
    .table td video {
        display: block;
        margin: 0 auto;
    }

    .table td .btn-container {
        display: flex;
        justify-content: space-between;
        gap: 10px;
    }

    .table td .btn {
        flex: 1;
        text-align: center;
        padding: 10px;
        font-size: 14px;
        min-width: 0;
    }


@media (max-width: 768px) {
    .control-toggle {
        display: none;
    }
}
</style>
<link href="./assets/bootstrap/video-js.css" rel="stylesheet" />
<script src="./assets/bootstrap/video.js"></script>
<link rel="stylesheet" href="./assets/bootstrap/all.min.css">
<link href="./assets/bootstrap/bootstrap-icons.css" rel="stylesheet">
<script src="./assets/neko/js/jquery.min.js"></script>
<link rel="stylesheet" href="./assets/bootstrap/leaflet.css" />
<script src="./assets/bootstrap/leaflet.js"></script>
<script type="text/javascript">
const _IMG = './assets/neko/';
const translate = <?php echo json_encode($translate, JSON_UNESCAPED_UNICODE); ?>;
let cachedIP = null;
let cachedInfo = null;
let random = parseInt(Math.random() * 100000000);

const sitesToPing = {
    google: { url: 'https://www.google.com', name: 'Google' },
    youtube: { url: 'https://www.youtube.com', name: 'YouTube' },
    github: { url: 'https://www.github.com', name: 'GitHub' },
    openai : { url: 'https://www.openai.com', name: 'OpenAI' }
}

async function checkAllPings() {
    const pingResults = {};
    for (const [key, site] of Object.entries(sitesToPing)) {
        const { url, name } = site;
        try {
            const startTime = performance.now();
            await fetch(url, { mode: 'no-cors', cache: 'no-cache' });
            const endTime = performance.now();
            const pingTime = Math.round(endTime - startTime);
            pingResults[key] = { name, pingTime };
        } catch (error) {
            pingResults[key] = { name, pingTime: 'Timeout' };
        }
    }
    return pingResults;
}

const checkSiteStatus = {
    sites: {
        google: 'https://www.google.com',
        youtube: 'https://www.youtube.com',
        github: 'https://www.github.com',
        openai: 'https://www.openai.com'
    },
    
    check: async function() {
        for (let [site, url] of Object.entries(this.sites)) {
            try {
                const response = await fetch(url, {
                    mode: 'no-cors',
                    cache: 'no-cache'
                });
                
                document.getElementById(`${site}-normal`).style.display = 'inline';
                document.getElementById(`${site}-gray`).style.display = 'none';
            } catch (error) {
                document.getElementById(`${site}-normal`).style.display = 'none';
                document.getElementById(`${site}-gray`).style.display = 'inline';
            }
        }
    }
};

async function pingHost(site, siteName) {
    const url = checkSiteStatus.sites[site];
    const resultElement = document.getElementById('ping-result');

    try {
        resultElement.innerHTML = `<span style="font-size: 22px">Testing the connection latency to ${siteName}...`;
        resultElement.style.color = '#87CEFA';        
        const startTime = performance.now();
        await fetch(url, {
            mode: 'no-cors',
            cache: 'no-cache'
        });
        const endTime = performance.now();
        const pingTime = Math.round(endTime - startTime);      
        resultElement.innerHTML = `<span style="font-size: 22px">${siteName} Connection latency: ${pingTime}ms</span>`;
        if(pingTime <= 100) {
                resultElement.style.color = '#09B63F'; 
        } else if(pingTime <= 200) {
                resultElement.style.color = '#FFA500'; 
        } else {
                resultElement.style.color = '#ff6b6b'; 
        }
    } catch (error) {
        resultElement.innerHTML = `<span style="font-size: 22px">${siteName} Connection timeout`;
        resultElement.style.color = '#ff6b6b';
    }
}

let IP = {
    isRefreshing: false,
    lastGeoData: null, 
    ipApis: [
        {url: 'https://api.ipify.org?format=json', type: 'json', key: 'ip'},
        {url: 'https://api-ipv4.ip.sb/geoip', type: 'json', key: 'ip'},
        {url: 'https://myip.ipip.net', type: 'text'},
        {url: 'http://pv.sohu.com/cityjson', type: 'text'},
        {url: 'https://ipinfo.io/json', type: 'json', key: 'ip'},
        {url: 'https://ipapi.co/json/', type: 'json'},
        {url: 'https://freegeoip.app/json/', type: 'json'}
    ],

    fetchIP: async () => {
        let error;
        for(let api of IP.ipApis) {
            try {
                const response = await IP.get(api.url, api.type);
                if(api.type === 'json') {
                    const ipData = api.key ? response.data[api.key] : response.data;
                    cachedIP = ipData;
                    document.getElementById('d-ip').innerHTML = ipData;
                    return ipData;
                } else {
                    const ipData = response.data.match(/\d+\.\d+\.\d+\.\d+/)?.[0];
                    if(ipData) {
                        cachedIP = ipData;
                        document.getElementById('d-ip').innerHTML = ipData;
                        return ipData;
                    }
                }
            } catch(e) {
                error = e;
                console.error(`Error with ${api.url}:`, e);
                continue;
            }
        }
        throw error || new Error("All IP APIs failed");
    },

    get: (url, type) =>
        fetch(url, { 
            method: 'GET',
            cache: 'no-store'
        }).then((resp) => {
            if (type === 'text')
                return Promise.all([resp.ok, resp.status, resp.text(), resp.headers]);
            else
                return Promise.all([resp.ok, resp.status, resp.json(), resp.headers]);
        }).then(([ok, status, data, headers]) => {
            if (ok) {
                return { ok, status, data, headers };
            } else {
                throw new Error(JSON.stringify(data.error));
            }
        }).catch(error => {
            console.error("Error fetching data:", error);
            throw error;
        }),

    Ipip: async (ip, elID) => {
        const geoApis = [
            {url: `https://api.ip.sb/geoip/${ip}`, type: 'json'},
            {url: 'https://myip.ipip.net', type: 'text'},
            {url: `http://ip-api.com/json/${ip}`, type: 'json'},
            {url: `https://ipinfo.io/${ip}/json`, type: 'json'},
            {url: `https://ipapi.co/${ip}/json/`, type: 'json'},
            {url: `https://freegeoip.app/json/${ip}`, type: 'json'}
        ];

        let geoData = null;
        let error;

        for(let api of geoApis) {
            try {
                const response = await IP.get(api.url, api.type);
                geoData = response.data;
                break;
            } catch(e) {
                error = e;
                console.error(`Error with ${api.url}:`, e);
                continue;
            }
        }

        if(!geoData) {
            throw error || new Error("All Geo APIs failed");
        }

        cachedIP = ip;
        IP.lastGeoData = geoData; 
        
        IP.updateUI(geoData, elID);
    },

    updateUI: async (data, elID) => {
        try {
            const country = data.country || "Unknown";
            const region = data.region || "";
            const city = data.city || "";
            const isp = data.isp || "";
            const asnOrganization = data.asn_organization || "";

            let location = `${region && city && region !== city ? `${region} ${city}` : region || city || ''}`;

            let displayISP = isp;
            let displayASN = asnOrganization;

            if (isp && asnOrganization && asnOrganization.includes(isp)) {
                displayISP = '';  
            } else if (isp && asnOrganization && isp.includes(asnOrganization)) {
                displayASN = '';  
            }

            let locationInfo = `<span style="margin-left: 8px; position: relative; top: -4px;">${location} ${displayISP} ${data.asn || ''} ${displayASN}</span>`;

            const isHidden = localStorage.getItem("ipHidden") === "true";

            let simpleDisplay = `
                <div class="ip-main" style="cursor: pointer; position: relative; top: -4px;" onclick="IP.showDetailModal()" title="Click to view IP details">
                    <div style="display: flex; align-items: center; justify-content: flex-start; gap: 10px; ">
                        <div style="display: flex; align-items: center; gap: 5px;">
                            <span id="ip-address">${isHidden ? '***.***.***.***.***' : cachedIP}</span> 
                            <span class="badge badge-primary" style="color: #333;">${country}</span>
                        </div>
                    </div>
                </div>
                <span id="toggle-ip" style="cursor: pointer; position: relative; top: -3px;  text-indent: 1ch; padding-top: 2px;" title="Click to hide/show IP">
                    <i class="fa ${isHidden ? 'bi-eye-slash' : 'bi-eye'}"></i>  
                </span>
                <span class="control-toggle" style="cursor: pointer; margin-left: 10px; display: inline-flex; align-items: center; position: relative; top: -1px;"" onclick="togglePopup()" title="Open Settings">
                    <i class="bi bi-gear" style="font-size: 0.8rem; margin-right: 5px;"></i>  
                </span>
            `;

            document.getElementById('d-ip').innerHTML = simpleDisplay;
            document.getElementById('ipip').innerHTML = locationInfo;

            const countryCode = data.country_code || 'unknown';
            const flagSrc = (countryCode === 'TW') ? _IMG + "flags/cn.png"  : (countryCode !== 'unknown') ? _IMG + "flags/" + countryCode.toLowerCase() + ".png"  : './assets/neko/flags/cn.png';
            $("#flag").attr("src", flagSrc);

            document.getElementById('toggle-ip').addEventListener('click', () => {
                const ipElement = document.getElementById('ip-address');
                const iconElement = document.getElementById('toggle-ip').querySelector('i');

                if (ipElement.textContent === cachedIP) {
                    ipElement.textContent = '***.***.***.***.***';
                    iconElement.classList.remove('bi-eye');
                    iconElement.classList.add('bi-eye-slash');  
                    localStorage.setItem("ipHidden", "true");  
                } else {
                    ipElement.textContent = cachedIP;  
                    iconElement.classList.remove('bi-eye-slash');
                    iconElement.classList.add('bi-eye');  
                    localStorage.setItem("ipHidden", "false");  
                }
            });

        } catch (error) {
            console.error("Error in updateUI:", error);
            document.getElementById('d-ip').innerHTML = "Failed to update IP information";
            $("#flag").attr("src", "./assets/neko/flags/mo.png");
        }
    },

    showDetailModal: async () => {
        const data = IP.lastGeoData;
        if (!data) return;

        let country = data.country || "Unknown";
        let region = data.region || "";
        let city = data.city || "";
        let isp = data.isp || "";
        let asnOrganization = data.asn_organization || "";
        let timezone = data.timezone || "";
        let asn = data.asn || "";

        let areaDisplay = [country, region, city].filter(Boolean).join(" ");
        if (region === city) {
            areaDisplay = `${country} ${region}`; 
        }

        let ipSupport;
        const ipv4Regex = /^(\d{1,3}\.){3}\d{1,3}$/;
        const ipv6Regex = /^[a-fA-F0-9:]+$/;

        if (ipv4Regex.test(cachedIP)) {
            ipSupport = 'IPv4 Support';
        } else if (ipv6Regex.test(cachedIP)) {
            ipSupport = 'IPv6 Support';
        } else {
            ipSupport = 'IPv4 or IPv6 support not detected';
        }

        const pingResults = await checkAllPings();
        const delayInfoHTML = Object.entries(pingResults).map(([key, { name, pingTime }]) => {
            let color = '#ff6b6b'; 
            if (typeof pingTime === 'number') {
                color = pingTime <= 100 ? '#09B63F' : pingTime <= 200 ? '#FFA500' : '#ff6b6b';
            }
            return `<span style="margin-right: 20px; font-size: 18px; color: ${color};">${name}: ${pingTime === 'Timeout' ? 'Timeout' : `${pingTime}ms`}</span>`;
        }).join('');

        let lat = data.latitude || null;
        let lon = data.longitude || null;

        if (!lat || !lon) {
            try {
                const response = await fetch(`https://ipapi.co/${cachedIP}/json/`);
                const geoData = await response.json();
                lat = geoData.latitude;
                lon = geoData.longitude;
            } catch (error) {
                console.error("Failed to retrieve IP geolocation:", error);
            }
        }

        const modalHTML = `
            <div class="modal fade custom-modal" id="ipDetailModal" tabindex="-1" role="dialog" aria-labelledby="ipDetailModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
                <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="ipDetailModalLabel">IP Detailed Information</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="ip-details">
                                <div class="detail-row">
                                    <span class="detail-label">IP Support:</span>
                                    <span class="detail-value">${ipSupport}</span>
                            </div>
                                <div class="detail-row">
                                    <span class="detail-label">IP Address:</span>
                                    <span class="detail-value">${cachedIP}</span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Region:</span>
                                    <span class="detail-value">${areaDisplay}</span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Carrier:</span>
                                    <span class="detail-value">${isp}</span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">ASN:</span>
                                    <span class="detail-value">${asn} ${asnOrganization}</span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Time Zone:</span>
                                    <span class="detail-value">${timezone}</span>
                                </div>
                                ${data.latitude && data.longitude ? `
                                <div class="detail-row">
                                    <span class="detail-label">Latitude and Longitude:</span>
                                    <span class="detail-value">${data.latitude}, ${data.longitude}</span>
                                </div>` : ''}
                                ${lat && lon ? `
                                <div class="detail-row" style="height: 400px; margin-top: 20px;">
                                    <div id="leafletMap" style="width: 100%; height: 100%;"></div>
                                </div>` : ''}
                                <h5 style="margin-top: 15px; display: inline-block; white-space: nowrap;">Latency Information:</h5>
                                <div class="detail-row" style="display: flex; flex-wrap: wrap;">
                                    ${delayInfoHTML}
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        $('#ipDetailModal').remove();
        $('body').append(modalHTML);
        $('#ipDetailModal').modal('show');

        setTimeout(() => {
            if (lat && lon) {
                const map = L.map('leafletMap').setView([lat, lon], 10);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

                const popupContent = city || region || "Current location";
                L.marker([lat, lon]).addTo(map)
                    .bindPopup(popupContent)
                    .openPopup();

                const fullscreenButton = document.createElement('button');
                fullscreenButton.classList.add('fullscreen-btn');
                fullscreenButton.innerHTML = '🗖';  
                document.getElementById('leafletMap').appendChild(fullscreenButton);

                const exitFullscreenButton = document.createElement('button');
                exitFullscreenButton.classList.add('exit-fullscreen-btn');
                exitFullscreenButton.innerHTML = '❎';  
                document.getElementById('leafletMap').appendChild(exitFullscreenButton);

                fullscreenButton.onclick = function() {
                    const mapContainer = document.getElementById('leafletMap');
                    mapContainer.classList.add('fullscreen');  
                    fullscreenButton.style.display = 'none';  
                    exitFullscreenButton.style.display = 'block';  
                    map.invalidateSize();
                };

                exitFullscreenButton.onclick = function() {
                    const mapContainer = document.getElementById('leafletMap');
                    mapContainer.classList.remove('fullscreen');  
                    fullscreenButton.style.display = 'block';  
                    exitFullscreenButton.style.display = 'none';  
                    map.invalidateSize();
                };
            }
        }, 500);
    },

    getIpipnetIP: async () => {
        if(IP.isRefreshing) return;
    
        try {
            IP.isRefreshing = true;
            document.getElementById('d-ip').innerHTML = `
                <div class="ip-main">
                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                    Checking...
                </div>
            `;
            document.getElementById('ipip').innerHTML = "";
            $("#flag").attr("src", _IMG + "img/loading.svg");
        
            const ip = await IP.fetchIP();
            await IP.Ipip(ip, 'ipip');
        } catch (error) {
            console.error("Error in getIpipnetIP function:", error);
            document.getElementById('ipip').innerHTML = "The IP information retrieval failed";
        } finally {
            IP.isRefreshing = false;
        }
    }
};

const style = document.createElement('style');
style.textContent = `
.ip-main {
    font-size: 14px;
    padding: 5px;
    transition: all 0.3s;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.badge-primary {
    color: #ff69b4 !important;
    background-color: #f8f9fa !important;
    border: 1px solid #dee2e6;
}

#ipip {
    margin-left: -3px;
}

.ip-main:hover {
    background: #f0f0f0;
    border-radius: 4px;
}

.ip-details {
    font-size: 18px !important;
    line-height: 1.6;
}

.detail-row {
    margin-bottom: 12px;
    display: flex;
    align-items: center; 
}

.detail-label {
    font-weight: bold;
    margin-right: 10px;  
    white-space: nowrap;
}

.detail-value {
    color: #333;
    flex: 1;
    white-space: nowrap; 
}

.modal-content {
    border-radius: 8px;
}

.modal-header {
    background: #f8f9fa;
    border-radius: 8px 8px 0 0;
}

.modal-body {
    padding: 20px;
}
.custom-modal .modal-header {
    background-color: #fff;
    color: #fff;
    padding: 16px 20px;
    border-bottom: 1px solid #ddd;
    border-top-left-radius: 8px;
    border-top-right-radius: 8px;
}

.custom-modal .custom-close {
    color: #fff;
    font-size: 1.5rem;
    opacity: 0.7;
}

.custom-modal .custom-close:hover {
    color: #ddd;
    opacity: 1;
}

.custom-modal .modal-body {
    padding: 20px;
    font-size: 1rem;
    color: #333;
    line-height: 1.6;
}

.custom-modal .detail-row {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px solid #eee;
}

.custom-modal .detail-label {
    font-weight: 600;
    color: #555;
}

.custom-modal .detail-value {
    font-weight: 400;
    color: #333;
}

.custom-modal .modal-footer {
    background-color: #f7f7f7;
    padding: 12px 16px;
    display: flex;
    justify-content: flex-end;
    border-top: 1px solid #ddd;
}

.custom-modal .custom-close-btn {
    background-color: #007bff;
    color: #fff;
    border: none;
    padding: 8px 16px;
    font-size: 1rem;
    border-radius: 4px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.custom-modal .custom-close-btn:hover {
    background-color: #0056b3;
}

`;
document.head.appendChild(style);
IP.getIpipnetIP();
if(typeof checkSiteStatus !== 'undefined') {
    checkSiteStatus.check();
    setInterval(() => checkSiteStatus.check(), 30000);
}

setInterval(IP.getIpipnetIP, 180000);
</script>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        var video = document.getElementById('background-video');
        var popup = document.getElementById('popup');

        popup.style.display = "none";
        
        var savedMuteState = localStorage.getItem("videoMuted");
        if (savedMuteState !== null) {
            video.muted = savedMuteState === "true";
        }

        var savedObjectFit = localStorage.getItem("videoObjectFit");
        if (savedObjectFit) {
            video.style.objectFit = savedObjectFit;
        } else {
            video.style.objectFit = "cover"; 
        }

        updateButtonStates();
    });

    var longPressTimer;
    var touchStartTime = 0;

    document.addEventListener('touchstart', function (event) {
        var touch = event.touches[0];
        touchStartTime = new Date().getTime();
    
        if (touch.clientY < window.innerHeight / 2) {
            longPressTimer = setTimeout(function () {
                togglePopup();
            }, 1000); 
        }
    });

    function togglePopup() {
        var popup = document.getElementById('popup');
    
        if (popup.style.display === "none" || popup.style.display === "") {
            popup.style.display = "grid"; 
        } else {
            popup.style.display = "none"; 
        }
    }

    function toggleAudio() {
        var video = document.getElementById('background-video');
        video.muted = !video.muted;
        localStorage.setItem("videoMuted", video.muted);
        updateButtonStates();
    }

    function toggleFullScreen() {
        if (!document.fullscreenElement) {
            document.documentElement.requestFullscreen();
        } else {
            document.exitFullscreen();
        }
        updateButtonStates();
    }

    function toggleObjectFit() {
        var video = document.getElementById('background-video');
        var objectFitBtn = document.getElementById('object-fit-btn');

        switch (video.style.objectFit) {
            case "contain":
                video.style.objectFit = "cover";
                objectFitBtn.textContent = "🔲 Normal Display";
                localStorage.setItem("videoObjectFit", "cover");
                break;
            case "cover":
                video.style.objectFit = "fill";
                objectFitBtn.textContent = "🖼️ Fill";
                localStorage.setItem("videoObjectFit", "fill");
                break;
            case "fill":
                video.style.objectFit = "none";
                objectFitBtn.textContent = "🔲 Do Not Scale";
                localStorage.setItem("videoObjectFit", "none");
                break;
            case "none":
                video.style.objectFit = "scale-down";
                objectFitBtn.textContent = "🖼️ Scale Down";
                localStorage.setItem("videoObjectFit", "scale-down");
                break;
            case "scale-down":
                video.style.objectFit = "contain";
                objectFitBtn.textContent = "🖼️ Full Screen";
                localStorage.setItem("videoObjectFit", "contain");
                break;
            default:
                video.style.objectFit = "cover"; 
                objectFitBtn.textContent = "🔲 Normal Display";
                localStorage.setItem("videoObjectFit", "cover");
                break;
        }
    }

  function updateButtonStates() {
        var video = document.getElementById('background-video');
        var audioBtn = document.getElementById('audio-btn');
        var fullscreenBtn = document.getElementById('fullscreen-btn');

        audioBtn.textContent = video.muted ? "🔇 Mute" : "🔊 Unmute";
        fullscreenBtn.textContent = document.fullscreenElement ? "📴 Exit Fullscreen" : "⛶ Enter Fullscreen";
    }

    document.addEventListener("keydown", function(event) {
        if (event.ctrlKey && event.shiftKey && event.key === "S") {
            togglePopup();
        }
    });

    document.addEventListener("fullscreenchange", updateButtonStates);
</script>

<div class="popup" id="popup">
    <h3>🔧 Control Panel</h3>
    <button onclick="toggleAudio()" id="audio-btn">🔊 Toggle Audio</button>
    <button onclick="toggleObjectFit()" id="object-fit-btn">🔲 Toggle Video Display Mode</button>
    <button onclick="toggleFullScreen()" id="fullscreen-btn">⛶ Toggle Fullscreen</button>
    <button id="clear-cache-btn">🗑️ Clear Cache</button>
    <button type="button" data-bs-toggle="modal" data-bs-target="#urlModal">🔗 Customize Playlist</button>
    <button type="button" data-bs-toggle="modal" data-bs-target="#keyHelpModal">⌨️ Keyboard Shortcuts</button>
    <button type="button" data-bs-toggle="modal" data-bs-target="#singboxModal">🎤 Sing-box Startup Tips</button>
    <button id="openPlayerButton"  data-bs-toggle="modal" data-bs-target="#audioPlayerModal">🎶 Music Player</button>
    <button id="startCheckBtn">🌐 Start Website Check</button>
    <button id="toggleModal"><i class="fas fa-arrows-alt-h"></i> Modify Page Width</button>
    <button id="toggleAnimationBtn">🖥️ Start Block Animation</button>
    <button id="toggleSnowBtn">❄️ Start Snow Animation</button>
    <button id="toggleLightAnimationBtn">💡 Start Light Animation</button>
    <button id="toggleLightEffectBtn">✨ Start Light Effect Animation</button>
    <button type="button" data-bs-toggle="modal" data-bs-target="#colorModal"><i class="bi-palette"></i> Theme Editor</button>
    <button type="button" data-bs-toggle="modal" data-bs-target="#filesModal"><i class="bi-camera-video"></i> Set Background</button>
    <button onclick="togglePopup()">❌ Close</button>
</div>

<div class="modal fade" id="singboxModal" tabindex="-1" aria-labelledby="singboxModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="singboxModalLabel">Sing-box Startup Tips</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
           <ul>
                <li>If startup fails, go to File Management ⇨ Update Database ⇨ Download cache.db Cache Data.</li>
                <li>If unable to connect to the network after startup, go to Firewall Settings ⇨ Outbound/Inbound/Forward ⇨ Accept ⇨ Save Application.</li>
           </ul>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('keydown', function(event) {
    if (event.ctrlKey && event.shiftKey && event.code === 'KeyC') {
        clearCache();
        event.preventDefault();  
    }
});

document.getElementById('clear-cache-btn').addEventListener('click', function() {
    clearCache();
});

function clearCache() {
    location.reload(true); 

    localStorage.clear();
    sessionStorage.clear();

    sessionStorage.setItem('cacheCleared', 'true');

    showNotification('Cache cleared');
    speakMessage('Cache cleared');
}

function showNotification(message) {
    var notification = document.createElement('div');
    notification.style.position = 'fixed';
    notification.style.top = '10px';
    notification.style.right = '30px';
    notification.style.backgroundColor = '#4CAF50';
    notification.style.color = '#fff';
    notification.style.padding = '10px';
    notification.style.borderRadius = '5px';
    notification.style.zIndex = '9999';
    notification.innerText = message;

    document.body.appendChild(notification);

    setTimeout(function() {
        notification.style.display = 'none';
    }, 5000); 
}

window.addEventListener('load', function() {
    if (sessionStorage.getItem('cacheCleared') === 'true') {
        showNotification('Cache cleared');
        speakMessage('Cache cleared');
        sessionStorage.removeItem('cacheCleared'); 
    }
});
</script>

<style>
#audioPlayerModal .modal-content {
  background: #222;
  color: #fff;
  border-radius: 12px;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.5);
}

#audioPlayerModal .modal-header {

}

#audioPlayerModal .modal-title {
  font-size: 18px;
  font-weight: bold;
}

#audioPlayerModal .close {
  color: #fff;
  opacity: 0.8;
}

#audioPlayerModal .close:hover {
  opacity: 1;
}

.audio-player-container {
  padding: 20px;

}

.audio-player-container button {
  margin: 8px;
  padding: 10px 15px;
  font-size: 16px;
  border: none;
  border-radius: 8px;
  transition: all 0.3s ease-in-out;
  cursor: pointer;
}

.audio-player-container .btn-primary {
  background: #ff5733; 
  color: white;
}

.audio-player-container .btn-primary {
  background: #FF5722 !important; 
  color: white !important;
}

.audio-player-container .btn-primary:hover {
  background: #e64a19 !important; 
}

.audio-player-container .btn-secondary {
  background: #9C27B0 !important; 
  color: white !important;
}

.audio-player-container .btn-secondary:hover {
  background: #8E24AA !important; 
}

.audio-player-container .btn-info {
  background: #00BCD4 !important; 
  color: white !important;
}

.audio-player-container .btn-info:hover {
  background: #0097A7 !important; 
}

.audio-player-container .btn-warning {
  background: #FF9800 !important; 
  color: black !important;
}

.audio-player-container .btn-warning:hover {
  background: #FB8C00 !important; 
}

.audio-player-container .btn-dark {
  background: #8BC34A !important; 
  color: white !important;
}

.audio-player-container .btn-dark:hover {
  background: #7CB342 !important; 
}

#modalLoopButton {
  color: white !important;
  background-color: #f39c12 !important; 
}

#modalLoopButton:hover {
  background-color: #f5b041 !important; 
  color: white !important; 
}

.track-name {
  margin-top: 15px;
  font-size: 16px;
  font-weight: bold;
  color: #1db954;
  text-align: center;
}

#tooltip {
  position: absolute;
  bottom: 20px;
  left: 50%;
  transform: translateX(-50%);
  padding: 10px 15px;
  background: rgba(0, 0, 0, 0.75);
  color: #fff;
  font-size: 14px;
  border-radius: 8px;
  white-space: nowrap;
  text-align: center;
  visibility: hidden;
  opacity: 0;
  transition: opacity 0.3s ease-in-out, visibility 0.3s ease-in-out;
  box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.3);
  z-index: 1050;
}

#tooltip.show {
  visibility: visible;
  opacity: 1;
}

.datetime-container {
  text-align: center;
  margin-bottom: 15px;
  font-size: 16px;
  font-weight: bold;
  color: #ffcc00;
}

#dateDisplay,
#timeDisplay {
  margin: 0 10px;
}

#timeDisplay {
  font-style: italic;
}

#audioElement {
  margin-top: 20px;
  width: 100%;
  max-width: 600px; /* Limit the audio player width */
  display: block;
  margin-left: auto;
  margin-right: auto; /* Center the audio player */
}

@media (max-width: 768px) {
  .audio-player-container {
    flex-direction: column;
    align-items: center;
  }

  .audio-player-container button {
    width: 100%;
    margin: 5px 0;
  }
}

#playlistCollapse {
    max-height: 700px; 
    overflow-y: auto;  
    overflow-x: hidden; 
    background-color: rgba(0, 0, 0, 0.8); 
    backdrop-filter: blur(10px); 
    border-radius: 8px; 
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2); 
    padding: 10px; 
}

#playlistCollapse h3 {
    font-size: 1.25rem;
    font-weight: bold;
    color: #fff; 
    text-align: center;
    margin-bottom: 15px;
}

#trackList .list-group-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 15px; 
    margin-bottom: 8px; 
    background-color: rgba(255, 255, 255, 0.1); 
    border: 1px solid rgba(255, 255, 255, 0.2); 
    border-radius: 5px; 
    transition: background-color 0.3s, transform 0.2s; 
}

#trackList .list-group-item.active {
    background-color: #007bff;
    color: white; 
    font-weight: bold; 
}

#trackList .list-group-item:hover {
    background-color: #0056b3; 
    color: white; 
    transform: scale(1.05); 
}

#playlistCollapse::-webkit-scrollbar {
    width: 8px; 
}

#playlistCollapse::-webkit-scrollbar-thumb {
    background-color: #007bff; 
    border-radius: 4px;
}

#playlistCollapse::-webkit-scrollbar-track {
    background-color: rgba(255, 255, 255, 0.1); 
}

#trackList .list-group-item .track-name {
    flex-grow: 1;
    font-size: 1rem;
    color: #fff; 
    text-overflow: ellipsis; 
    overflow: hidden;
    white-space: nowrap;
}
</style>

<div class="modal fade" id="audioPlayerModal" tabindex="-1" aria-labelledby="audioPlayerModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="audioPlayerModalLabel">Music Player</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="datetime-container">
          <span id="dateDisplay"></span> 
          <span id="timeDisplay"></span>
        </div>
        <audio id="audioElement" controls>
          <source id="audioSource" type="audio/mp3" src="your-audio-file.mp3">
          Your browser does not support the audio element.
        </audio>
        <div class="audio-player-container text-center">
          <button id="modalPlayPauseButton" class="btn btn-primary">▶ Play</button>
          <button id="modalPrevButton" class="btn btn-secondary">⏪ Previous</button>
          <button id="modalNextButton" class="btn btn-secondary">⏩ Next</button>
          <button id="modalRewindButton" class="btn btn-dark">⏪ Rewind</button>
          <button id="modalFastForwardButton" class="btn btn-info">⏩ Fast Forward</button>
          <button id="modalLoopButton" class="btn btn-warning">🔁 Loop</button>
          <div class="track-name" id="trackName">No Songs</div>
        </div>
        <button class="btn btn-outline-primary mt-3" type="button" data-bs-toggle="collapse" data-bs-target="#playlistCollapse">
          📜 Show/Hide Playlist
        </button>
        <div id="playlistCollapse" class="collapse mt-3">
          <h3>Playlist</h3>
          <ul id="trackList" class="list-group"></ul>
        </div>
        <div id="tooltip"></div>
      </div>
    </div>
  </div>
</div>

<script>
const audioPlayer = document.getElementById('audioElement');
let songs = [];
let currentSongIndex = 0;
let isPlaying = false;
let isReportingTime = false;
let isLooping = false;
let hasModalShown = false;

const logBox = document.createElement('div');
logBox.style.position = 'fixed';
logBox.style.top = '90%';
logBox.style.left = '20px';
logBox.style.padding = '10px';
logBox.style.backgroundColor = 'green';
logBox.style.color = 'white';
logBox.style.borderRadius = '5px';
logBox.style.zIndex = '9999';
logBox.style.maxWidth = '250px';
logBox.style.fontSize = '14px';
logBox.style.display = 'none';
logBox.style.maxWidth = '300px';
logBox.style.wordWrap = 'break-word';
document.body.appendChild(logBox);

function showLogMessage(message) {
    const decodedMessage = decodeURIComponent(message);
    logBox.textContent = decodedMessage;
    logBox.style.display = 'block';
    logBox.style.animation = 'scrollUp 8s ease-out forwards';
    logBox.style.width = 'auto';
    logBox.style.maxWidth = '300px';

    setTimeout(() => {
        logBox.style.display = 'none';
    }, 8000);
}

const styleSheet = document.createElement('style');
styleSheet.innerHTML = `
    @keyframes scrollUp {
        0% {
            top: 90%;
        }
        100% {
            top: 50%;
        }
    }
`;
document.head.appendChild(styleSheet);

function loadDefaultPlaylist() {
    fetch('<?php echo $new_url; ?>')
        .then(response => {
            if (!response.ok) {
                throw new Error('Failed to load playlist');
                speakMessage('Failed to load playlist');
            }
            return response.text();
        })
        .then(data => {
            songs = data.split('\n').filter(url => url.trim() !== '');
            if (songs.length === 0) {
                throw new Error('No valid songs in the playlist');
            }
            console.log('Playlist loaded:', songs);
            updateTrackListUI(); 
            restorePlayerState();
            updateTrackName(); 
        })
        .catch(error => {
            console.error('Error loading playlist:', error.message);
        });
}

function updateTrackListUI() {
    const trackListContainer = document.getElementById('trackList');
    trackListContainer.innerHTML = '';

    songs.forEach((song, index) => {
        const trackItem = document.createElement('li');
        trackItem.textContent = `${index + 1}. ${extractSongName(song)}`;
        trackItem.classList.add('list-group-item', 'track-item');
        trackItem.style.cursor = 'pointer';

        trackItem.addEventListener('click', () => {
            currentSongIndex = index;
            loadSong(index);
            if (isPlaying) audioPlayer.play();
            updateTrackName();
            highlightCurrentSong();
        });

        trackListContainer.appendChild(trackItem);
    });

    highlightCurrentSong(); 
}

function extractSongName(url) {
    return decodeURIComponent(url.split('/').pop());
}

function updateTrackName() {
    document.getElementById('trackName').textContent = extractSongName(songs[currentSongIndex]);
}

function highlightCurrentSong() {
    document.querySelectorAll('.track-item').forEach((item, index) => {
        item.classList.toggle('active', index === currentSongIndex);
    });
}

function loadSong(index) {
    if (index >= 0 && index < songs.length) {
        audioPlayer.src = songs[index];
        audioPlayer.addEventListener('loadedmetadata', () => {
            const savedState = JSON.parse(localStorage.getItem('playerState'));
            if (savedState && savedState.currentSongIndex === index) {
                audioPlayer.currentTime = savedState.currentTime || 0;
                if (savedState.isPlaying) {
                    audioPlayer.play().catch(error => {
                        console.error('Failed to resume playback:', error);
                    });
                }
            }
        }, { once: true });
    }
    highlightCurrentSong();
}

const playPauseButton = document.getElementById('modalPlayPauseButton');
playPauseButton.addEventListener('click', function() {
    if (!isPlaying) {
        loadSong(currentSongIndex);
        audioPlayer.play().then(() => {
            isPlaying = true;
            savePlayerState();
            console.log('Playback started');
            speakMessage('Playback started');
            playPauseButton.textContent = '⏸️ Pause';
            updateTrackName();
        }).catch(error => {
            console.log('Playback failed:', error);
        });
    } else {
        audioPlayer.pause();
        isPlaying = false;
        savePlayerState();
        console.log('Playback paused');
        speakMessage('Playback paused');
        playPauseButton.textContent = '▶ Play';
    }
});

document.getElementById('modalPrevButton').addEventListener('click', () => {
    currentSongIndex = (currentSongIndex - 1 + songs.length) % songs.length;
    loadSong(currentSongIndex);
    savePlayerState();
    if (isPlaying) {
        audioPlayer.play();
    }
    updateTrackName();
    const songName = getSongName(songs[currentSongIndex]);
    showLogMessage(`Previous song: ${songName}`);
});

document.getElementById('modalNextButton').addEventListener('click', () => {
    currentSongIndex = (currentSongIndex + 1) % songs.length;
    loadSong(currentSongIndex);
    savePlayerState();
    if (isPlaying) {
        audioPlayer.play();
    }
    updateTrackName();
    const songName = getSongName(songs[currentSongIndex]);
    showLogMessage(`Next song: ${songName}`);
});

function updateTrackName() {
    if (songs.length > 0) {
        const currentSongUrl = songs[currentSongIndex];
        const trackName = extractSongName(currentSongUrl);
        document.getElementById('trackName').textContent = trackName || 'Unknown song';
    } else {
        document.getElementById('trackName').textContent = 'No songs';
    }
}

function extractSongName(url) {
    const parts = url.split('/');
    return decodeURIComponent(parts[parts.length - 1]);
}

audioPlayer.addEventListener('ended', () => {
    currentSongIndex = (currentSongIndex + 1) % songs.length;
    loadSong(currentSongIndex);
    savePlayerState();
    if (isPlaying) {
        audioPlayer.play();
    }
    updateTrackName();
    const songName = getSongName(songs[currentSongIndex]);
    showLogMessage(`Auto switch to: ${songName}`);
});

document.getElementById('modalRewindButton').addEventListener('click', () => {
    audioPlayer.currentTime = Math.max(audioPlayer.currentTime - 10, 0);
    console.log('Rewind 10 seconds');
    savePlayerState();
    showLogMessage('Rewind 10 seconds');
});

document.getElementById('modalFastForwardButton').addEventListener('click', () => {
    audioPlayer.currentTime = Math.min(audioPlayer.currentTime + 10, audioPlayer.duration || Infinity);
    console.log('Fast forward 10 seconds');
    savePlayerState();
    showLogMessage('Fast forward 10 seconds');
});

const loopButton = document.getElementById('modalLoopButton');
loopButton.addEventListener('click', () => {
    isLooping = !isLooping;
    
    if (isLooping) {
        loopButton.textContent = "🔁 Loop";
        console.log('Loop playback');
        showLogMessage('Loop playback');
        speakMessage('Loop playback');
        audioPlayer.loop = true;
    } else {
        loopButton.textContent = "🔄 Sequential";
        console.log('Sequential playback');
        showLogMessage('Sequential playback');
        speakMessage('Sequential playback');
        audioPlayer.loop = false;
    }
});

function getSongName(url) {
    const pathParts = url.split('/');
    return pathParts[pathParts.length - 1];
}

function startHourlyAlert() {
    setInterval(() => {
        const now = new Date();
        const hours = now.getHours();

        if (now.getMinutes() === 0 && !isReportingTime) {
            isReportingTime = true;

            const timeAnnouncement = new SpeechSynthesisUtterance(`It's now ${hours} o'clock`);
            timeAnnouncement.lang = 'en-US';
            speechSynthesis.speak(timeAnnouncement);

            console.log(`Hourly alert: It's now ${hours} o'clock`);
        }

        if (now.getMinutes() !== 0) {
            isReportingTime = false;
        }
    }, 60000);
}

function updateDateTime() {
    const now = new Date();

    const year = now.getFullYear();
    const month = now.getMonth() + 1;
    const day = now.getDate();
    document.getElementById('dateDisplay').textContent = `${year}-${month}-${day}`;

    const timeString = now.toLocaleTimeString('en-US', { hour12: false });

    const hours = now.getHours();
    let ancientTime;
    if (hours >= 23 || hours < 1) ancientTime = '子時';
    else if (hours >= 1 && hours < 3) ancientTime = '丑時';
    else if (hours >= 3 && hours < 5) ancientTime = '寅時';
    else if (hours >= 5 && hours < 7) ancientTime = '卯時';
    else if (hours >= 7 && hours < 9) ancientTime = '辰時';
    else if (hours >= 9 && hours < 11) ancientTime = '巳時';
    else if (hours >= 11 && hours < 13) ancientTime = '午時';
    else if (hours >= 13 && hours < 15) ancientTime = '未時';
    else if (hours >= 15 && hours < 17) ancientTime = '申時';
    else if (hours >= 17 && hours < 19) ancientTime = '酉時';
    else if (hours >= 19 && hours < 21) ancientTime = '戌時';
    else ancientTime = '亥時';

    document.getElementById('timeDisplay').textContent = `${timeString} (${ancientTime})`;
}

setInterval(updateDateTime, 1000);
updateDateTime();

audioPlayer.addEventListener('ended', function() {
    if (isLooping) {
        loadSong(currentSongIndex);
        savePlayerState();
        audioPlayer.play();
    } else {
        currentSongIndex = (currentSongIndex + 1) % songs.length;
        loadSong(currentSongIndex);
        savePlayerState();
        audioPlayer.play();
    }
});

function savePlayerState() {
    const state = {
        currentSongIndex,
        currentTime: audioPlayer.currentTime,
        isPlaying,
        isLooping,
        timestamp: Date.now()
    };
    localStorage.setItem('playerState', JSON.stringify(state));
}

function clearExpiredPlayerState() {
    const state = JSON.parse(localStorage.getItem('playerState'));

    if (state) {
        const currentTime = Date.now();
        const stateAge = currentTime - state.timestamp;

        const expirationTime = 60 * 60 * 1000;

        if (stateAge > expirationTime) {
            localStorage.removeItem('playerState');
            console.log('Player state expired, cleared');
        }
    }
}

setInterval(clearExpiredPlayerState, 10 * 60 * 1000);

function restorePlayerState() {
    const state = JSON.parse(localStorage.getItem('playerState'));
    if (state) {
        currentSongIndex = state.currentSongIndex || 0;
        isLooping = state.isLooping || false;
        loadSong(currentSongIndex);
        if (state.isPlaying) {
            isPlaying = true;
            playPauseButton.textContent = '⏸️ Pause';
            audioPlayer.currentTime = state.currentTime || 0;
            audioPlayer.play().catch(error => {
                console.error('Failed to resume playback:', error);
            });
            playPauseButton.textContent = '⏸️ Pause';
        }
    }
}

document.addEventListener('dblclick', function() {
    const lastShownTime = localStorage.getItem('lastModalShownTime');
    const currentTime = new Date().getTime();

    if (!lastShownTime || (currentTime - lastShownTime) > 4 * 60 * 60 * 1000) {
        if (!hasModalShown) {
            const modal = new bootstrap.Modal(document.getElementById('keyHelpModal'));
            modal.show();
            hasModalShown = true;

            localStorage.setItem('lastModalShownTime', currentTime);
        }
    }
});

loadDefaultPlaylist();
startHourlyAlert();
restorePlayerState();

$('#audioPlayerModal').on('shown.bs.modal', function () {
    updateTrackName();
});

window.addEventListener('keydown', function(event) {
    if (event.key === 'ArrowUp') {
        currentSongIndex = (currentSongIndex - 1 + songs.length) % songs.length;
        loadSong(currentSongIndex);
        savePlayerState();
        if (isPlaying) {
            audioPlayer.play();
        }
        const songName = getSongName(songs[currentSongIndex]);
        showLogMessage(`Previous song: ${songName}`);
        speakMessage('Previous song');
        updateTrackName();
    } else if (event.key === 'ArrowDown') {
        currentSongIndex = (currentSongIndex + 1) % songs.length;
        loadSong(currentSongIndex);
        savePlayerState();
        if (isPlaying) {
            audioPlayer.play();
        }
        const songName = getSongName(songs[currentSongIndex]);
        showLogMessage(`Next song: ${songName}`);
        speakMessage('Next song');
        updateTrackName();
    } else if (event.key === 'ArrowLeft') {
        audioPlayer.currentTime = Math.max(audioPlayer.currentTime - 10, 0);
        console.log('Rewind 10 seconds');
        savePlayerState();
        showLogMessage('Rewind 10 seconds');
    } else if (event.key === 'ArrowRight') {
        audioPlayer.currentTime = Math.min(audioPlayer.currentTime + 10, audioPlayer.duration || Infinity);
        console.log('Fast forward 10 seconds');
        savePlayerState();
        showLogMessage('Fast forward 10 seconds');
    } else if (event.key === 'Escape') {
        localStorage.removeItem('playerState');
        currentSongIndex = 0;
        loadSong(currentSongIndex);
        savePlayerState();
        console.log('Reset to first song');
        showLogMessage('Reset to first song');
        speakMessage('Returned to the first song in the playlist');
        if (isPlaying) {
            audioPlayer.play();
        }
    } else if (event.key === 'F9') {
        if (isPlaying) {
            audioPlayer.pause();
            isPlaying = false;
            savePlayerState();
            console.log('Playback paused');
            showLogMessage('Playback paused');
            speakMessage('Playback paused');
            playPauseButton.textContent = '▶ Play';
        } else {
            audioPlayer.play().then(() => {
                isPlaying = true;
                savePlayerState();
                console.log('Playback started');
                showLogMessage('Playback started');
                speakMessage('Playback started');
                playPauseButton.textContent = '⏸️ Pause';
            }).catch(error => {
                console.log('Playback failed:', error);
            });
        }
    } else if (event.key === 'F2') {
        isLooping = !isLooping;
        const loopButton = document.getElementById('modalLoopButton');
        if (isLooping) {
            loopButton.textContent = "🔁 Loop";
            audioPlayer.loop = true;
            console.log('Loop playback');
            showLogMessage('Loop playback');
            speakMessage('Loop playback');
        } else {
            loopButton.textContent = "🔄 Sequential";
            audioPlayer.loop = false;
            console.log('Sequential playback');
            showLogMessage('Sequential playback');
            speakMessage('Sequential playback');
        }
    }
});
</script>


<div class="modal fade" id="urlModal" tabindex="-1" aria-labelledby="urlModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="urlModalLabel">Update Playlist Link</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="POST">
                    <div class="mb-3">
                        <label for="new_url" class="form-label">Custom Playlist Link (Press Ctrl + Shift + C to clear data, must use download link for proper playback)</label>
                        <input type="text" id="new_url" name="new_url" class="form-control" value="<?php echo htmlspecialchars($new_url); ?>" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Update Link</button>
                    <button type="button" id="resetButton" class="btn btn-secondary ms-2">Restore Default Link</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('keydown', function(event) {
        if (event.ctrlKey && event.shiftKey && event.key === 'V') {
            var urlModal = new bootstrap.Modal(document.getElementById('urlModal'));
            urlModal.show();
        }
    });

    document.getElementById('resetButton').addEventListener('click', function() {
        fetch('', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
        body: 'reset_default=true'
    })
        .then(response => response.text())  
        .then(data => {
            var urlModal = bootstrap.Modal.getInstance(document.getElementById('urlModal'));
            urlModal.hide();

            document.getElementById('new_url').value = '<?php echo $default_url; ?>';

            showNotification('The default link has been successfully restored');
        })
        .catch(error => {
            console.error('An error occurred while restoring the default link:', error);
            showNotification('An error occurred while restoring the default link');
        });
    });

    function showNotification(message) {
        var notification = document.createElement('div');
        notification.style.position = 'fixed';
        notification.style.top = '10px';
        notification.style.right = '30px';
        notification.style.backgroundColor = '#4CAF50';
        notification.style.color = '#fff';
        notification.style.padding = '10px';
        notification.style.borderRadius = '5px';
        notification.style.zIndex = '9999';
        notification.innerText = message;

        document.body.appendChild(notification);

        setTimeout(function() {
            notification.style.display = 'none';
        }, 5000); 
    }
</script>

<div class="modal fade" id="keyHelpModal" tabindex="-1" aria-labelledby="keyHelpModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="keyHelpModalLabel">Keyboard Shortcuts Guide</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <ul>
                    <li><strong>Left Mouse Button:</strong> Double click to open the player interface</li>
                    <li><strong>F9 Key:</strong> Toggle play/pause</li>
                    <li><strong>Up/Down Arrow Keys:</strong> Switch to previous/next track</li>
                    <li><strong>Left/Right Arrow Keys:</strong> Fast forward/rewind 10 seconds</li>
                    <li><strong>ESC Key:</strong> Return to the first track in the playlist</li>
                    <li><strong>F2 Key:</strong> Toggle between loop and sequential play mode</li>
                    <li><strong>F8 Key:</strong> Start website connectivity check</li>
                    <li><strong>Ctrl + F6 Key:</strong> Start/stop snowflake animation</li>
                    <li><strong>Ctrl + F7 Key:</strong> Start/stop square light animation</li>
                    <li><strong>Ctrl + F10 Key:</strong> Start/stop square animation</li>
                    <li><strong>Ctrl + F11 Key:</strong> Start/stop light dot animation</li>
                    <li><strong>Ctrl + Shift + S Key:</strong> Open settings</li>
                    <li><strong>Ctrl + Shift + C Key:</strong> Clear cache data</li>
                    <li><strong>Ctrl + Shift + V Key:</strong> Customize playlist</li>
                    <li><strong>Long press top half of the screen on mobile/tablet:</strong> Open settings</li>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<script>
    const websites = [
        'https://www.baidu.com/', 
        'https://www.cloudflare.com/', 
        'https://openai.com/',
        'https://www.youtube.com/',
        'https://www.google.com/',
        'https://www.facebook.com/',
        'https://www.twitter.com/',
        'https://www.github.com/'
    ];

    function speakMessage(message) {
        const utterance = new SpeechSynthesisUtterance(message);
        utterance.lang = 'en-US';  
        speechSynthesis.speak(utterance);
    }

    function getWebsiteStatusMessage(url, status) {
        const statusMessages = {
            'https://www.baidu.com/': status ? 'Baidu website is accessible.' : 'Cannot access Baidu website, please check your network connection.',
            'https://www.cloudflare.com/': status ? 'Cloudflare website is accessible.' : 'Cannot access Cloudflare website, please check your network connection.',
            'https://openai.com/': status ? 'OpenAI website is accessible.' : 'Cannot access OpenAI website, please check your network connection.',
            'https://www.youtube.com/': status ? 'YouTube website is accessible.' : 'Cannot access YouTube website, please check your network connection.',
            'https://www.google.com/': status ? 'Google website is accessible.' : 'Cannot access Google website, please check your network connection.',
            'https://www.facebook.com/': status ? 'Facebook website is accessible.' : 'Cannot access Facebook website, please check your network connection.',
            'https://www.twitter.com/': status ? 'Twitter website is accessible.' : 'Cannot access Twitter website, please check your network connection.',
            'https://www.github.com/': status ? 'GitHub website is accessible.' : 'Cannot access GitHub website, please check your network connection.',
        };

        return statusMessages[url] || (status ? `${url} website is accessible.` : `Cannot access ${url} website, please check your network connection.`);
    }

    function checkWebsiteAccess(urls) {
        const statusMessages = [];
        let requestsCompleted = 0;

        urls.forEach(url => {
            fetch(url, { mode: 'no-cors' })
                .then(response => {
                    const isAccessible = response.type === 'opaque';  
                    statusMessages.push(getWebsiteStatusMessage(url, isAccessible));
                })
                .catch(() => {
                    statusMessages.push(getWebsiteStatusMessage(url, false));
                })
                .finally(() => {
                    requestsCompleted++;
                    if (requestsCompleted === urls.length) {
                        speakMessage(statusMessages.join(' '));  
                        speakMessage('Website check is complete, thank you for using.'); 
                    }
                });
        });
    }

    setInterval(() => {
        speakMessage('Starting website connectivity check...');
        checkWebsiteAccess(websites);  
    }, 3600000);  

    let isDetectionStarted = false;

    document.addEventListener('keydown', function(event) {
        if (event.key === 'F8' && !isDetectionStarted) {  
            event.preventDefault();  
            speakMessage('Website check has started, checking website connectivity...');
            checkWebsiteAccess(websites);
            isDetectionStarted = true;
        }
    });

    document.getElementById('startCheckBtn').addEventListener('click', function() {
        speakMessage('Website check has started, checking website connectivity...');
        checkWebsiteAccess(websites);
    });
</script>

<style>
    .animated-box {
        width: 50px;
        height: 50px;
        margin: 10px;
        background: linear-gradient(45deg, #ff6b6b, #ffd93d);
        border-radius: 10px;
        position: absolute;
        animation: complex-animation 5s infinite alternate ease-in-out;
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
    }

    @keyframes complex-animation {
        0% {
            transform: rotate(0deg) scale(1);
            background: linear-gradient(45deg, #ff6b6b, #ffd93d);
        }
        25% {
            transform: rotate(45deg) scale(1.2);
            background: linear-gradient(135deg, #42a5f5, #66bb6a);
        }
        50% {
            transform: rotate(90deg) scale(0.8);
            background: linear-gradient(225deg, #ab47bc, #ff7043);
        }
        75% {
            transform: rotate(135deg) scale(1.5);
            background: linear-gradient(315deg, #29b6f6, #8e24aa);
        }
        100% {
            transform: rotate(180deg) scale(1);
            background: linear-gradient(45deg, #ff6b6b, #ffd93d);
        }
    }
</style>

<script>
    (function() {
        let isAnimationActive = localStorage.getItem('animationActive') === 'true';
        let intervalId;

        function createAnimatedBox() {
            const box = document.createElement('div');
            box.className = 'animated-box';
            document.body.appendChild(box);
            const randomX = Math.random() * window.innerWidth;
            const randomY = Math.random() * window.innerHeight;
            box.style.left = randomX + 'px';
            box.style.top = randomY + 'px';
            const randomDuration = Math.random() * 3 + 3;
            box.style.animationDuration = randomDuration + 's';
            setTimeout(() => {
                box.remove();
            }, randomDuration * 1000);
        }

        function startAnimation() {
            intervalId = setInterval(() => {
                createAnimatedBox();
            }, 1000);
            localStorage.setItem('animationActive', 'true');
            isAnimationActive = true;
            updateButtonText();
        }

        function stopAnimation() {
            clearInterval(intervalId);
            localStorage.setItem('animationActive', 'false');
            isAnimationActive = false;
            updateButtonText();
        }

        function showNotification(message) {
            var notification = document.createElement('div');
            notification.style.position = 'fixed';
            notification.style.top = '10px';
            notification.style.right = '30px';
            notification.style.backgroundColor = '#4CAF50';
            notification.style.color = '#fff';
            notification.style.padding = '10px';
            notification.style.borderRadius = '5px';
            notification.style.zIndex = '9999';
            notification.innerText = message;
            document.body.appendChild(notification);

            setTimeout(function() {
                notification.style.display = 'none';
            }, 5000);
        }

        function updateButtonText() {
            document.getElementById('toggleAnimationBtn').innerText = isAnimationActive ? '⏸️ Stop Box Animation' : '▶ Start Box Animation';
        }

        window.addEventListener('keydown', function(event) {
            if (event.ctrlKey && event.key === 'F10') {
                isAnimationActive = !isAnimationActive;
                if (isAnimationActive) {
                    startAnimation();
                    showNotification('Box animation started');
                    speakMessage('Box animation started');
                } else {
                    stopAnimation();
                    showNotification('Box animation stopped');
                    speakMessage('Box animation stopped');
                }
            }
        });

        document.getElementById('toggleAnimationBtn').addEventListener('click', function() {
            if (isAnimationActive) {
                stopAnimation();
                showNotification('⏸️ Box animation stopped');
                speakMessage('Box animation stopped');
            } else {
                startAnimation();
                showNotification('▶ Box animation started');
                speakMessage('Box animation started');
            }
        });

        if (isAnimationActive) {
            startAnimation();
        }
        updateButtonText();
    })();

    function speakMessage(message) {
        const utterance = new SpeechSynthesisUtterance(message);
        utterance.lang = 'en-US';
        speechSynthesis.speak(utterance);
    }
</script>

<style>
    .snowflake {
        position: absolute;
        top: -10px;
        width: 10px;
        height: 10px;
        background-color: white;
        border-radius: 50%;
        animation: fall linear infinite;
    }

    @keyframes fall {
        0% {
            transform: translateY(0) rotate(0deg); 
        }
        100% {
            transform: translateY(100vh) rotate(360deg); 
        }
    }

    .snowflake:nth-child(1) {
        animation-duration: 8s;
        animation-delay: -2s;
        left: 10%;
        width: 12px;
        height: 12px;
    }

    .snowflake:nth-child(2) {
        animation-duration: 10s;
        animation-delay: -3s;
        left: 20%;
        width: 8px;
        height: 8px;
    }

    .snowflake:nth-child(3) {
        animation-duration: 12s;
        animation-delay: -1s;
        left: 30%;
        width: 15px;
        height: 15px;
    }

    .snowflake:nth-child(4) {
        animation-duration: 9s;
        animation-delay: -5s;
        left: 40%;
        width: 10px;
        height: 10px;
    }

    .snowflake:nth-child(5) {
        animation-duration: 11s;
        animation-delay: -4s;
        left: 50%;
        width: 14px;
        height: 14px;
    }

    .snowflake:nth-child(6) {
        animation-duration: 7s;
        animation-delay: -6s;
        left: 60%;
        width: 9px;
        height: 9px;
    }

    .snowflake:nth-child(7) {
        animation-duration: 8s;
        animation-delay: -7s;
        left: 70%;
        width: 11px;
        height: 11px;
    }

    .snowflake:nth-child(8) {
        animation-duration: 10s;
        animation-delay: -8s;
        left: 80%;
        width: 13px;
        height: 13px;
    }

    .snowflake:nth-child(9) {
        animation-duration: 6s;
        animation-delay: -9s;
        left: 90%;
        width: 10px;
        height: 10px;
    }
</style>

<script>
    function createSnowflakes() {
        for (let i = 0; i < 80; i++) {
            let snowflake = document.createElement('div');
            snowflake.classList.add('snowflake');
                
            let size = Math.random() * 10 + 5 + 'px';  
            snowflake.style.width = size;
            snowflake.style.height = size;
                
            let speed = Math.random() * 3 + 2 + 's'; 
            snowflake.style.animationDuration = speed;

            let rotate = Math.random() * 360 + 'deg'; 
            let rotateSpeed = Math.random() * 5 + 2 + 's'; 
            snowflake.style.animationName = 'fall';
            snowflake.style.animationDuration = speed;
            snowflake.style.animationTimingFunction = 'linear';
            snowflake.style.animationIterationCount = 'infinite';

            let leftPosition = Math.random() * 100 + 'vw';  
            snowflake.style.left = leftPosition;

            snowflake.style.animationDelay = Math.random() * 5 + 's';  

            document.body.appendChild(snowflake);
        }
    }

    function stopSnowflakes() {
        let snowflakes = document.querySelectorAll('.snowflake');
        snowflakes.forEach(snowflake => snowflake.remove());
    }

    function showNotification(message) {
        var notification = document.createElement('div');
        notification.style.position = 'fixed';
        notification.style.top = '10px';
        notification.style.right = '30px';
        notification.style.backgroundColor = '#4CAF50';
        notification.style.color = '#fff';
        notification.style.padding = '10px';
        notification.style.borderRadius = '5px';
        notification.style.zIndex = '9999';
        notification.innerText = message;
        document.body.appendChild(notification);
        setTimeout(function() {
            notification.style.display = 'none';
        }, 5000);
    }

    function speakMessage(message) {
        const utterance = new SpeechSynthesisUtterance(message);
        utterance.lang = 'en-US';
        speechSynthesis.speak(utterance);
    }

    function getSnowingState() {
        return localStorage.getItem('isSnowing') === 'true';
    }

    function saveSnowingState(state) {
        localStorage.setItem('isSnowing', state);
    }

    let isSnowing = getSnowingState();

    if (isSnowing) {
        createSnowflakes();  
    }

    function toggleSnowflakes() {
        isSnowing = !isSnowing;
        saveSnowingState(isSnowing);
        if (isSnowing) {
            createSnowflakes();
            showNotification('Snowflake animation started');
            speakMessage('Snowflake animation started');
            document.getElementById('toggleSnowBtn').innerText = '⏸️ Stop Snowflake Animation';
        } else {
            stopSnowflakes();
            showNotification('Snowflake animation stopped');
            speakMessage('Snowflake animation stopped');
            document.getElementById('toggleSnowBtn').innerText = '▶ Start Snowflake Animation';
        }
    }

    window.addEventListener('keydown', function(event) {
        if (event.ctrlKey && event.key === 'F6') {
            toggleSnowflakes();
        }
    });

    document.getElementById('toggleSnowBtn').addEventListener('click', toggleSnowflakes);

    if (isSnowing) {
        document.getElementById('toggleSnowBtn').innerText = '⏸️ Stop Snowflake Animation';
    }

</script>

<style>
.floating-light {
    position: fixed;
    bottom: 0;
    left: 50%;
    width: 50px;
    height: 50px;
    border-radius: 10px;
    box-shadow: 0 0 10px rgba(255, 87, 51, 0.7), 0 0 20px rgba(255, 87, 51, 0.5);
    transform: translateX(-50%);
    animation: float-random 5s ease-in-out infinite;
}

.floating-light.color-1 {
    background-color: #ff5733; 
}

.floating-light.color-2 {
    background-color: #33ff57; 
}

.floating-light.color-3 {
    background-color: #5733ff; 
}

.floating-light.color-4 {
    background-color: #f5f533; 
}

.floating-light.color-5 {
    background-color: #ff33f5; 
}

@keyframes float-random {
    0% {
        transform: translateX(var(--start-x)) translateY(var(--start-y)) rotate(var(--start-rotation));
    }
    100% {
        transform: translateX(var(--end-x)) translateY(var(--end-y)) rotate(var(--end-rotation));
    }
}
</style>
<script>
(function() {
    let isLightAnimationActive = localStorage.getItem('lightAnimationStatus') === 'true'; 
    let intervalId;
    const colors = ['color-1', 'color-2', 'color-3', 'color-4', 'color-5']; 

    if (isLightAnimationActive) {
        startLightAnimation(false);  
    }

    function createLightBox() {
        const lightBox = document.createElement('div');
        const randomColor = colors[Math.floor(Math.random() * colors.length)]; 
        lightBox.classList.add('floating-light', randomColor);
        
        const startX = Math.random() * 100 - 50 + 'vw';  
        const startY = Math.random() * 100 - 50 + 'vh';  
        const endX = Math.random() * 100 - 50 + 'vw';  
        const endY = Math.random() * 100 - 50 + 'vh';  
        const rotation = Math.random() * 360 + 'deg';   

        lightBox.style.setProperty('--start-x', startX);
        lightBox.style.setProperty('--start-y', startY);
        lightBox.style.setProperty('--end-x', endX);
        lightBox.style.setProperty('--end-y', endY);
        lightBox.style.setProperty('--start-rotation', rotation);
        lightBox.style.setProperty('--end-rotation', Math.random() * 360 + 'deg');
        
        document.body.appendChild(lightBox);

        setTimeout(() => {
            lightBox.remove();
        }, 5000); 
    }

    function startLightAnimation(showLog = true) {
        intervalId = setInterval(createLightBox, 400); 
        localStorage.setItem('lightAnimationStatus', 'true');  
        if (showLog) showNotification('Light box animation started');
        document.getElementById('toggleLightAnimationBtn').innerText = '⏸️ Stop Light Animation';
    }

    function stopLightAnimation(showLog = true) {
        clearInterval(intervalId);
        const allLights = document.querySelectorAll('.floating-light');
        allLights.forEach(light => light.remove()); 
        localStorage.setItem('lightAnimationStatus', 'false');  
        if (showLog) showNotification('Light box animation stopped');
        document.getElementById('toggleLightAnimationBtn').innerText = '▶ Start Light Animation';
    }

    function showNotification(message) {
        var notification = document.createElement('div');
        notification.style.position = 'fixed';
        notification.style.top = '10px';
        notification.style.right = '30px';
        notification.style.backgroundColor = '#4CAF50';
        notification.style.color = '#fff';
        notification.style.padding = '10px';
        notification.style.borderRadius = '5px';
        notification.style.zIndex = '9999';
        notification.innerText = message;
        document.body.appendChild(notification);

        setTimeout(function() {
            notification.style.display = 'none';
        }, 5000);
    }

    function speakMessage(message) {
        const utterance = new SpeechSynthesisUtterance(message);
        utterance.lang = 'en-US';
        speechSynthesis.speak(utterance);
    }

    function toggleLightAnimation() {
        isLightAnimationActive = !isLightAnimationActive;
        if (isLightAnimationActive) {
            startLightAnimation();
            speakMessage('Light box animation started');
        } else {
            stopLightAnimation();
            speakMessage('Light box animation stopped');
        }
    }

    window.addEventListener('keydown', function(event) {
        if (event.ctrlKey && event.key === 'F7') {
            toggleLightAnimation();
        }
    });

    document.getElementById('toggleLightAnimationBtn').addEventListener('click', toggleLightAnimation);

    if (isLightAnimationActive) {
        document.getElementById('toggleLightAnimationBtn').innerText = '⏸️ Stop Light Animation';
    }
})();
</script>

<style>
@keyframes lightPulse {
    0% {
        transform: scale(0.5);
        opacity: 1;
    }
    50% {
        transform: scale(1.5);
        opacity: 0.7;
    }
    100% {
        transform: scale(3);
        opacity: 0;
    }
}

.light-point {
    position: fixed;
    width: 10px;
    height: 10px;
    background: radial-gradient(circle, rgba(255, 255, 255, 1), rgba(255, 255, 255, 0.2));
    border-radius: 50%;
    pointer-events: none;
    z-index: 9999;
    animation: lightPulse 3s linear infinite;
}
</style>

<script>
(function () {
    let isLightEffectActive = localStorage.getItem('lightEffectAnimation') === 'true';
    let lightInterval;

    function createLightPoint() {
        const lightPoint = document.createElement('div');
        lightPoint.className = 'light-point';

        const posX = Math.random() * window.innerWidth;
        const posY = Math.random() * window.innerHeight;

        lightPoint.style.left = `${posX}px`;
        lightPoint.style.top = `${posY}px`;

        const colors = ['#ffcc00', '#00ccff', '#ff6699', '#99ff66', '#cc99ff'];
        const randomColor = colors[Math.floor(Math.random() * colors.length)];
        lightPoint.style.background = `radial-gradient(circle, ${randomColor}, rgba(255, 255, 255, 0.1))`;

        document.body.appendChild(lightPoint);
        setTimeout(() => {
            lightPoint.remove();
        }, 3000); 
    }

    function startLightEffect(showLog = true) {
        if (lightInterval) clearInterval(lightInterval);
        lightInterval = setInterval(createLightPoint, 200); 
        localStorage.setItem('lightEffectAnimation', 'true');
        if (showLog) showNotification('Light point animation started');
        document.getElementById('toggleLightEffectBtn').innerText = '⏸️ Stop Light Point Animation';
    }

    function stopLightEffect(showLog = true) {
        clearInterval(lightInterval);
        document.querySelectorAll('.light-point').forEach((light) => light.remove());
        localStorage.setItem('lightEffectAnimation', 'false');
        if (showLog) showNotification('Light point animation stopped');
        document.getElementById('toggleLightEffectBtn').innerText = '▶ Start Light Point Animation';
    }

    function showNotification(message) {
        const notification = document.createElement('div');
        notification.style.position = 'fixed';
        notification.style.top = '10px';
        notification.style.right = '10px';
        notification.style.padding = '10px';
        notification.style.backgroundColor = '#4CAF50';
        notification.style.color = '#fff';
        notification.style.borderRadius = '5px';
        notification.style.zIndex = 9999;
        notification.textContent = message;

        document.body.appendChild(notification);
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }

    function speakMessage(message) {
        const utterance = new SpeechSynthesisUtterance(message);
        utterance.lang = 'en-US';
        speechSynthesis.speak(utterance);
    }

    function toggleLightEffect() {
        isLightEffectActive = !isLightEffectActive;
        if (isLightEffectActive) {
            startLightEffect();
            speakMessage('Light point animation started');
        } else {
            stopLightEffect();
            speakMessage('Light point animation stopped');
        }
    }

    window.addEventListener('keydown', function (event) {
        if (event.ctrlKey && event.key === 'F11') {
            toggleLightEffect();
        }
    });

    document.getElementById('toggleLightEffectBtn').addEventListener('click', toggleLightEffect);

    if (isLightEffectActive) {
        document.getElementById('toggleLightEffectBtn').innerText = '⏸️ Stop Light Point Animation';
        startLightEffect(false);
    }
})();
</script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        feather.replace();
    });
</script>

<div class="modal fade" id="widthModal" tabindex="-1" aria-labelledby="widthModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="widthModalLabel">Adjust Container Width</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <label for="containerWidth" class="form-label">Page Width</label>
        <input type="range" class="form-range" name="containerWidth" id="containerWidth" min="800" max="2400" step="50" value="1800" style="width: 100%;">
        <div id="widthValue" class="mt-2" style="color: #FF00FF;">Current Width: 1800px</div>

        <label for="modalMaxWidth" class="form-label mt-4">Modal Max Width</label>
        <input type="range" class="form-range" name="modalMaxWidth" id="modalMaxWidth" min="1400" max="2400" step="50" value="1400" style="width: 100%;">
        <div id="modalWidthValue" class="mt-2" style="color: #00FF00;">Current Max Width: 1400px</div>

        <div class="form-check mt-3">
            <input class="form-check-input" type="checkbox" id="group1Background">
            <label class="form-check-label" for="group1Background">
                Enable Transparent Dropdown, Form Select, and Info Background
            </label>
        </div>
        <div class="form-check mt-3">
            <input class="form-check-input" type="checkbox" id="bodyBackground">
            <label class="form-check-label" for="bodyBackground">
                Enable Transparent Body Background
            </label>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<script>
const slider = document.getElementById("containerWidth");
const widthValue = document.getElementById("widthValue");
const modalSlider = document.getElementById("modalMaxWidth");
const modalWidthValue = document.getElementById("modalWidthValue");

const group1Checkbox = document.getElementById("group1Background");
const bodyBackgroundCheckbox = document.getElementById("bodyBackground");

function updateSliderColor(value, slider, valueElement) {
    let red = Math.min(Math.max((value - 800) / (2400 - 800) * 255, 0), 255);
    let green = 255 - red;
    
    slider.style.background = `linear-gradient(to right, rgb(${red}, ${green}, 255), rgb(${255 - red}, ${green}, ${255 - red}))`;
    slider.style.setProperty('--thumb-color', `rgb(${red}, ${green}, 255)`);
    valueElement.textContent = `Current Width: ${value}px`;
    valueElement.style.color = `rgb(${red}, ${green}, 255)`;  
}

let savedWidth = localStorage.getItem('containerWidth');
let savedModalWidth = localStorage.getItem('modalMaxWidth');

if (savedWidth) {
    slider.value = savedWidth;
}
if (savedModalWidth) {
    modalSlider.value = savedModalWidth;
}

updateSliderColor(slider.value, slider, widthValue);
updateSliderColor(modalSlider.value, modalSlider, modalWidthValue);

slider.oninput = function() {
    updateSliderColor(slider.value, slider, widthValue);
    localStorage.setItem('containerWidth', slider.value);  

    sendCSSUpdate();
    showNotification(`Page width updated! Current Width: ${slider.value}px`);
};

modalSlider.oninput = function() {
    updateSliderColor(modalSlider.value, modalSlider, modalWidthValue);
    localStorage.setItem('modalMaxWidth', modalSlider.value);  

    sendCSSUpdate();
    showNotification(`Modal max width updated! Current Max Width: ${modalSlider.value}px`);
};

function sendCSSUpdate() {
    const width = slider.value;
    const modalWidth = modalSlider.value;
    const group1 = group1Checkbox.checked ? 1 : 0;
    const bodyBackground = bodyBackgroundCheckbox.checked ? 1 : 0;

    fetch('update-css.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ 
            width: width,
            modalWidth: modalWidth,
            group1: group1,
            bodyBackground: bodyBackground
        })
    }).then(response => response.json())
      .then(data => console.log('CSS updated successfully:', data))
      .catch(error => console.error('Error updating CSS:', error));
}

group1Checkbox.onchange = function() {
    sendCSSUpdate();
    showNotification(group1Checkbox.checked ? "Transparent dropdown, form select, and info background enabled" : "Transparent dropdown, form select, and info background disabled");
};

bodyBackgroundCheckbox.onchange = function() {
    sendCSSUpdate();
    showNotification(bodyBackgroundCheckbox.checked ? "Transparent body background enabled" : "Transparent body background disabled");
};

function showNotification(message) {
    const notification = document.createElement('div');
    notification.style.position = 'fixed';
    notification.style.top = '10px';
    notification.style.right = '10px';
    notification.style.padding = '10px';
    notification.style.backgroundColor = '#4CAF50';
    notification.style.color = '#fff';
    notification.style.borderRadius = '5px';
    notification.style.zIndex = 9999;
    notification.textContent = message;

    document.body.appendChild(notification);
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

const toggleModalButton = document.getElementById("toggleModal");
toggleModalButton.onclick = function() {
    const modal = new bootstrap.Modal(document.getElementById('widthModal'));
    modal.show();
};
</script>

<div class="modal fade" id="colorModal" tabindex="-1" aria-labelledby="colorModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="colorModalLabel">Select Theme Color</h5>
        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      </div>
      <div class="modal-body">
        <form method="POST" action="theme.php" id="themeForm" enctype="multipart/form-data">
          <div class="row">
            <div class="col-md-4 mb-3">
              <label for="primaryColor" class="form-label">Navigation Text Color</label>
              <input type="color" class="form-control" name="primaryColor" id="primaryColor" value="#0ceda2">
            </div>
            <div class="col-md-4 mb-3">
              <label for="secondaryColor" class="form-label">Navigation Hover Text Color</label>
              <input type="color" class="form-control" name="secondaryColor" id="secondaryColor" value="#00ffff">
            </div>
            <div class="col-md-4 mb-3">
              <label for="bodyBgColor" class="form-label">Main Background Color</label>
              <input type="color" class="form-control" name="bodyBgColor" id="bodyBgColor" value="#23407e">
            </div>
            <div class="col-md-4 mb-3">
              <label for="infoBgSubtle" class="form-label">Info Background Color</label>
              <input type="color" class="form-control" name="infoBgSubtle" id="infoBgSubtle" value="#23407e">
            </div>
            <div class="col-md-4 mb-3">
              <label for="backgroundColor" class="form-label">Table Background Color</label>
              <input type="color" class="form-control" name="backgroundColor" id="backgroundColor" value="#20cdd9">
            </div>
            <div class="col-md-4 mb-3">
              <label for="primaryBorderSubtle" class="form-label">Table Text Color</label>
              <input type="color" class="form-control" name="primaryBorderSubtle" id="primaryBorderSubtle" value="#1815d1">
            </div>
            <div class="col-md-4 mb-3">
              <label for="checkColor" class="form-label">Main Title Text Color 1</label>
              <input type="color" class="form-control" name="checkColor" id="checkColor" value="#0eaf3e">
            </div>
            <div class="col-md-4 mb-3">
              <label for="labelColor" class="form-label">Main Title Text Color 2</label>
              <input type="color" class="form-control" name="labelColor" id="labelColor" value="#0eaf3e">
            </div>
            <div class="col-md-4 mb-3">
              <label for="lineColor" class="form-label">Line Text Color</label>
              <input type="color" class="form-control" name="lineColor" id="lineColor" value="#f515f9">
            </div>
            <div class="col-md-4 mb-3">
              <label for="controlColor" class="form-label">Input Text Color 1</label>
              <input type="color" class="form-control" name="controlColor" id="controlColor" value="#0eaf3e">
            </div>
            <div class="col-md-4 mb-3">
              <label for="placeholderColor" class="form-label">Input Text Color 2</label>
              <input type="color" class="form-control" name="placeholderColor" id="placeholderColor" value="#f82af2">
            </div>
            <div class="col-md-4 mb-3">
              <label for="disabledColor" class="form-label">Display Background Color</label>
              <input type="color" class="form-control" name="disabledColor" id="disabledColor" value="#23407e">
            </div>
            <div class="col-md-4 mb-3">
              <label for="logTextColor" class="form-label">Log Text Color</label>
              <input type="color" class="form-control" name="logTextColor" id="logTextColor" value="#f8f9fa">
            </div>
            <div class="col-md-4 mb-3">
              <label for="selectColor" class="form-label">Main Border Background Color</label>
              <input type="color" class="form-control" name="selectColor" id="selectColor" value="#23407e">
            </div>
            <div class="col-md-4 mb-3">
              <label for="radiusColor" class="form-label">Main Border Text Color</label>
              <input type="color" class="form-control" name="radiusColor" id="radiusColor" value="#24f086">
            </div>
            <div class="col-md-4 mb-3">
              <label for="bodyColor" class="form-label">Table Text Color 1</label>
              <input type="color" class="form-control" name="bodyColor" id="bodyColor" value="#04f153">
            </div>
            <div class="col-md-4 mb-3">
              <label for="tertiaryColor" class="form-label">Table Text Color 2</label>
              <input type="color" class="form-control" name="tertiaryColor" id="tertiaryColor" value="#46e1ec">
            </div>
            <div class="col-md-4 mb-3">
              <label for="tertiaryRgbColor" class="form-label">Table Text Color 3</label>
              <input type="color" class="form-control" name="tertiaryRgbColor" id="tertiaryRgbColor" value="#1e90ff">
            </div>
            <div class="col-md-4 mb-3">
              <label for="ipColor" class="form-label">IP Text Color</label>
              <input type="color" class="form-control" name="ipColor" id="ipColor" value="#09B63F">
            </div>
            <div class="col-md-4 mb-3">
              <label for="ipipColor" class="form-label">ISP Text Color</label>
              <input type="color" class="form-control" name="ipipColor" id="ipipColor" value="#ff69b4">
            </div>
            <div class="col-md-4 mb-3">
              <label for="detailColor" class="form-label">IP Detail Text Color</label>
              <input type="color" class="form-control" name="detailColor" id="detailColor" value="#FFFFFF">
            </div>
            <div class="col-md-4 mb-3">
              <label for="outlineColor" class="form-label">Button Color (Cyan)</label>
              <input type="color" class="form-control" name="outlineColor" id="outlineColor" value="#0dcaf0">
            </div>
            <div class="col-md-4 mb-3">
              <label for="successColor" class="form-label">Button Color (Green)</label>
              <input type="color" class="form-control" name="successColor" id="successColor" value="#28a745">
            </div>
            <div class="col-md-4 mb-3">
              <label for="infoColor" class="form-label">Button Color (Blue)</label>
              <input type="color" class="form-control" name="infoColor" id="infoColor" value="#0ca2ed">
            </div>
            <div class="col-md-4 mb-3">
              <label for="warningColor" class="form-label">Button Color (Yellow)</label>
              <input type="color" class="form-control" name="warningColor" id="warningColor" value="#ffc107">
            </div>
            <div class="col-md-4 mb-3">
              <label for="pinkColor" class="form-label">Button Color (Pink)</label>
              <input type="color" class="form-control" name="pinkColor" id="pinkColor" value="#f82af2">
            </div>
            <div class="col-md-4 mb-3">
              <label for="dangerColor" class="form-label">Button Color (Red)</label>
              <input type="color" class="form-control" name="dangerColor" id="dangerColor" value="#dc3545">
            </div>
            <div class="col-md-4 mb-3">
              <label for="heading1Color" class="form-label">Heading Color 1</label>
              <input type="color" class="form-control" name="heading1Color" id="heading1Color" value="#21e4f2">
            </div>
            <div class="col-md-4 mb-3">
              <label for="heading2Color" class="form-label">Heading Color 2</label>
              <input type="color" class="form-control" name="heading2Color" id="heading2Color" value="#65f1fb">
            </div>
            <div class="col-md-4 mb-3">
              <label for="heading3Color" class="form-label">Heading Color 3</label>
              <input type="color" class="form-control" name="heading3Color" id="heading3Color" value="#ffcc00">
            </div>
            <div class="col-md-4 mb-3">
              <label for="heading4Color" class="form-label">Heading Color 4</label>
              <input type="color" class="form-control" name="heading4Color" id="heading4Color" value="#00fbff">
            </div>
            <div class="col-md-4 mb-3">
              <label for="heading5Color" class="form-label">Heading Color 5</label>
              <input type="color" class="form-control" name="heading5Color" id="heading5Color" value="#ba13f6">
            </div>
            <div class="col-md-4 mb-3">
              <label for="heading6Color" class="form-label">Heading Color 6</label>
              <input type="color" class="form-control" name="heading6Color" id="heading6Color" value="#00ffff">
            </div>
          </div>
          <div class="col-12 mb-3">
            <label for="themeName" class="form-label">Custom Theme Name</label>
            <input type="text" class="form-control" name="themeName" id="themeName" value="transparent">
          </div>
      <div class="d-flex flex-wrap justify-content-center align-items-center mb-3 gap-2">
          <button type="submit" class="btn btn-primary">Save Theme</button>
          <button type="button" class="btn btn-success" id="resetButton" onclick="clearCache()">Reset to Default</button>
          <button type="button" class="btn btn-info" id="exportButton">Backup Now</button>
          <button type="button" class="btn btn-warning" id="restoreButton">Restore Backup</button> 
          <input type="file" id="importButton" class="form-control" accept="application/json" style="display: none;"> 
          <button type="button" class="btn btn-pink" data-bs-dismiss="modal">Cancel</button>
      </div>
        </form>
      </div>
    </div>
  </div>
</div>

<style>
    input[type="range"] {
        -webkit-appearance: none;  
        appearance: none;
        width: 100%;
        height: 10px;  
        border-radius: 5px;
        background: linear-gradient(to right, #ff00ff, #00ffff); 
        outline: none;
    }

    input[type="range"]::-webkit-slider-thumb {
        -webkit-appearance: none;
        appearance: none;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: #ff00ff;  
        border: none;
        cursor: pointer;
    }

    input[type="range"]:focus {
        outline: none; 
    }

    input[type="range"]::-moz-range-thumb {
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: #ff00ff;  
        border: none;
        cursor: pointer;
    }

    #widthValue {
        color: #ff00ff;
    }

.file-preview {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

.btn-container {
    display: flex;
    justify-content: center;
    margin-top: 10px;
}

.btn-container .btn {
    margin: 0 5px;
}

.delete-btn {
    color: white !important; 
}

@media (max-width: 768px) {
    .set-background-btn {
        font-size: 12px;
        padding: 5px 10px;
        width: 100px; 
        height: 42px; 
    }
}
</style>

<script>
    document.getElementById('useBackgroundImage').addEventListener('change', function() {
        const container = document.getElementById('backgroundImageContainer');
        container.style.display = this.checked ? 'block' : 'none';
    });
</script>

<script>
    document.getElementById('restoreButton').addEventListener('click', () => {
        document.getElementById('importButton').click();
    });

    document.getElementById('importButton').addEventListener('change', (event) => {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = (e) => {
                const content = e.target.result;
                try {
                    const jsonData = JSON.parse(content); 
                    console.log('Restored backup data:', jsonData);
                    alert('Backup successfully uploaded and parsed!');
                } catch (error) {
                    alert('File format error, please upload a valid JSON file!');
                }
            };
            reader.readAsText(file);
        }
    });
</script>

<script>
    function clearCache() {
        location.reload(true);        
        localStorage.clear();   
        sessionStorage.clear(); 
        sessionStorage.setItem('cacheCleared', 'true'); 
    }

    window.addEventListener('load', function() {
        if (sessionStorage.getItem('cacheCleared') === 'true') {
            sessionStorage.removeItem('cacheCleared'); 
        }
    });
</script>

<div class="modal fade" id="filesModal" tabindex="-1" aria-labelledby="filesModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="filesModalLabel">Upload and Manage Background Images/Videos/Audio</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="mb-4 d-flex justify-content-between align-items-center">
                    <div>
                        <button type="button" class="btn btn-success mr-3" onclick="selectAll()"><i class="fas fa-check-square"></i> Select All</button>
                        <button type="button" class="btn btn-warning mr-3" onclick="deselectAll()"><i class="fas fa-square"></i> Deselect All</button>
                        <button type="button" class="btn btn-danger" onclick="batchDelete()"><i class="fas fa-trash-alt"></i> Batch Delete</button>
                        <span id="selectedCount" class="ms-2" style="display: none;">Selected 0 files, totaling 0 MB</span>
                    </div>
                    <div>
                        <button type="button" class="btn btn-pink mr-3" onclick="sortFiles()"><i class="fas fa-sort"></i> Sort</button>
                        <button type="button" class="btn btn-primary mr-3" data-bs-toggle="modal" data-bs-target="#uploadModal">
                            <i class="fas fa-cloud-upload-alt"></i> Upload File
                        </button>
                        <button type="button" class="btn btn-danger delete-btn" onclick="setBackground('', '', 'remove')"><i class="fas fa-trash"></i> Remove Background</button>
                    </div>
                </div>
                <table class="table table-bordered text-center">
                    <tbody id="fileTableBody">
                        <?php
                        function isImage($file)
                        {
                            $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
                            $fileExtension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                            return in_array($fileExtension, $imageExtensions);
                        }

                        function isVideo($file)
                        {
                            $videoExtensions = ['mp4', 'avi', 'mkv', 'mov', 'wmv'];
                            $fileExtension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                            return in_array($fileExtension, $videoExtensions);
                        }

                        function isAudio($file)
                        {
                            $audioExtensions = ['mp3', 'wav', 'ogg', 'flac', 'aac', 'm4a', 'webm', 'opus'];
                            $fileExtension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                            return in_array($fileExtension, $audioExtensions);
                        }

                        function getFileNameWithoutPrefix($file)
                        {
                            $fileBaseName = pathinfo($file, PATHINFO_FILENAME);
                            $hyphenPos = strpos($fileBaseName, '-');
                            if ($hyphenPos !== false) {
                                return substr($fileBaseName, $hyphenPos + 1) . '.' . pathinfo($file, PATHINFO_EXTENSION);
                            } else {
                                return $file;
                            }
                        }

                        function formatFileSize($size)
                        {
                            if ($size >= 1073741824) {
                                return number_format($size / 1073741824, 2) . ' GB';
                            } elseif ($size >= 1048576) {
                                return number_format($size / 1048576, 2) . ' MB';
                            } elseif ($size >= 1024) {
                                return number_format($size / 1024, 2) . ' KB';
                            } else {
                                return $size . ' bytes';
                            }
                        }

                        $picturesDir = $_SERVER['DOCUMENT_ROOT'] . '/nekobox/assets/Pictures/';
                        $backgroundHistoryFile = $_SERVER['DOCUMENT_ROOT'] . '/nekobox/background_history.txt';
                        $backgroundFiles = [];
                        if (file_exists($backgroundHistoryFile)) {
                            $backgroundFiles = array_filter(array_map('trim', file($backgroundHistoryFile)));
                        }

                        if (is_dir($picturesDir)) {
                            $files = array_diff(scandir($picturesDir), array('..', '.'));
                            usort($files, function ($a, $b) use ($backgroundFiles) {
                                $indexA = array_search($a, $backgroundFiles);
                                $indexB = array_search($b, $backgroundFiles);

                                if ($indexA === false && $indexB === false) {
                                    return 0; 
                                } elseif ($indexA === false) {
                                    return 1; 
                                } elseif ($indexB === false) {
                                    return -1; 
                                } else {
                                    return $indexA - $indexB; 
                                 }
                            });     

                            $fileCount = 0;
                            foreach ($files as $file) {
                                $filePath = $picturesDir . $file;
                                if (is_file($filePath)) {
                                    $fileSize = filesize($filePath);
                                    $formattedFileSize = formatFileSize($fileSize);
                                    $fileUrl = '/nekobox/assets/Pictures/' . $file;
                                    $fileNameWithoutPrefix = getFileNameWithoutPrefix($file);
                                    $fileTitle = "Name: $fileNameWithoutPrefix\nSize: $formattedFileSize";

                                    if ($fileCount % 5 == 0) {
                                        echo "<tr>";
                                    }

                                    echo "<td class='align-middle' data-label='Preview' style='vertical-align: middle;'>
                                            <div class='file-preview mb-2' oncontextmenu='showRenameModal(event, \"" . htmlspecialchars($file, ENT_QUOTES) . "\")'>
                                                <input type='checkbox' class='file-checkbox mb-2' value='" . htmlspecialchars($file, ENT_QUOTES) . "' data-size='$fileSize' onchange='updateSelectedCount()'>";

                                    if (isVideo($file)) {
                                        echo "<video width='200' controls title='$fileTitle'>
                                                  <source src='$fileUrl' type='video/mp4'>
                                                  Your browser does not support the video tag.
                                              </video>";
                                    } elseif (isImage($file)) {
                                        echo "<img src='$fileUrl' alt='$file' style='width: 200px; height: auto;' title='$fileTitle'>";
                                    } elseif (isAudio($file)) {
                                        echo "<audio width='200' controls title='$fileTitle'>
                                                  <source src='$fileUrl' type='audio/mp3'>
                                                  Your browser does not support the audio tag.
                                              </audio>";
                                    } else {
                                        echo "Unknown file type";
                                    }

                                    echo "<div class='btn-container mt-2'>
                                              <a href='?delete=" . htmlspecialchars($file, ENT_QUOTES) . "' class='btn btn-danger me-2 delete-btn' onclick='return confirm(\"Are you sure you want to delete?\")'>Delete</a>";

                                    if (isImage($file)) {
                                        echo "<button type='button' onclick=\"setBackground('" . htmlspecialchars($file, ENT_QUOTES) . "', 'image')\" class='btn btn-primary ms-2 set-background-btn'>Set as Background</button>";
                                    } elseif (isVideo($file)) {
                                        echo "<button type='button' onclick=\"setBackground('" . htmlspecialchars($file, ENT_QUOTES) . "', 'video')\" class='btn btn-primary ms-2 set-background-btn'>Set as Background</button>";
                                    } elseif (isAudio($file)) {
                                        echo "<button type='button' onclick=\"setBackground('" . htmlspecialchars($file, ENT_QUOTES) . "', 'audio')\" class='btn btn-primary ms-2 set-background-btn'>Background Music</button>";
                                    }

                                    echo "</div></div></td>";

                                    if ($fileCount % 5 == 4) {
                                        echo "</tr>";
                                    }

                                    $fileCount++;
                                }
                            }

                            if ($fileCount % 5 != 0) {
                                echo str_repeat("<td></td>", 5 - ($fileCount % 5)) . "</tr>";
                            }
                        }

                        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['oldFileName']) && isset($_POST['newFileName'])) {
                            $oldFileName = $_POST['oldFileName'];
                            $newFileName = $_POST['newFileName'];

                            $oldFilePath = $picturesDir . $oldFileName;
                            $newFilePath = $picturesDir . $newFileName;

                            if (file_exists($oldFilePath)) {
                                if (rename($oldFilePath, $newFilePath)) {
                                    echo "<script>alert('File renamed successfully');</script>";
                                } else {
                                    echo "<script>alert('Failed to rename file');</script>";
                                }
                            } else {
                                echo "<script>alert('File does not exist');</script>";
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="renameModal" tabindex="-1" aria-labelledby="renameModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl">
        <form id="renameForm" method="POST">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="renameModalLabel">Rename File</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="oldFileName" id="oldFileName">
                    <div class="form-group">
                        <label for="newFileName">New File Name</label>
                        <input type="text" class="form-control" id="newFileName" name="newFileName" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="uploadModalLabel"><i class="fas fa-cloud-upload-alt"></i> Upload File</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <h2 class="mb-3">Upload Image/Video</h2>
                <form method="POST" action="download.php" enctype="multipart/form-data">
                    <div id="dropArea" class="mb-3">
                        <i id="uploadIcon" class="fas fa-cloud-upload-alt"></i>
                        <p>Drag and drop files here, or click the icon to select files.</p>
                        <p>PHP upload files have size limits. If the upload fails, you can manually upload files to the /nekobox/assets/Pictures directory.</p>
                    </div>
                    <input type="file" class="form-control mb-3" name="imageFile[]" id="imageFile" multiple style="display: none;">                   
                    <button type="submit" class="btn btn-success mt-3" id="submitBtnModal">
                        Upload Image/Video
                    </button>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" id="updatePhpConfig">Update PHP Upload Limits</button>
            </div>
        </div>
    </div>
</div>
<script>
function batchDelete() {
    const checkboxes = document.querySelectorAll('.file-checkbox:checked');
    if (checkboxes.length === 0) {
        alert("Please select files to delete.");
        return;
    }

    if (!confirm("Are you sure you want to delete the selected files?")) {
        return;
    }

    checkboxes.forEach(checkbox => {
        const fileName = checkbox.value;
        fetch(`?delete=${encodeURIComponent(fileName)}`)
            .then(response => {
                if (response.ok) {
                    checkbox.closest('td').remove(); 
                    updateSelectedCount();
                } else {
                    alert(`Failed to delete file: ${fileName}`);
                }
            })
            .catch(error => console.error('Error:', error));
    });
}

function updateSelectedCount() {
    const checkboxes = document.querySelectorAll('.file-checkbox:checked');
    const selectedCount = checkboxes.length;
    let totalSize = 0;

    checkboxes.forEach(checkbox => {
        totalSize += parseInt(checkbox.getAttribute('data-size'), 10);
    });

    const totalSizeMB = (totalSize / 1048576).toFixed(2); 
    const selectedCountElement = document.getElementById('selectedCount');
    if (selectedCount > 0) {
        selectedCountElement.style.display = 'inline';
        selectedCountElement.innerText = `Selected ${selectedCount} files, totaling ${totalSizeMB} MB`;
    } else {
        selectedCountElement.style.display = 'none';
    }
}

function sortFiles() {
    const tableBody = document.getElementById('fileTableBody');
    const rows = Array.from(tableBody.getElementsByTagName('tr'));
    
    rows.sort((a, b) => {
        const aText = a.getElementsByTagName('td')[0].querySelector('.file-preview img, .file-preview video').title;
        const bText = b.getElementsByTagName('td')[0].querySelector('.file-preview img, .file-preview video').title;
        
        return aText.localeCompare(bText);
    });

    rows.forEach(row => tableBody.appendChild(row));
}

function selectAll() {
    const checkboxes = document.querySelectorAll('.file-checkbox');
    checkboxes.forEach(checkbox => checkbox.checked = true);
    updateSelectedCount();
}

function deselectAll() {
    const checkboxes = document.querySelectorAll('.file-checkbox');
    checkboxes.forEach(checkbox => checkbox.checked = false);
    updateSelectedCount();
}

function showRenameModal(event, fileName) {
    event.preventDefault();
    const modal = new bootstrap.Modal(document.getElementById('renameModal'));
    document.getElementById('oldFileName').value = fileName;
    document.getElementById('newFileName').value = fileName; 
    modal.show();
}
</script>

<script>
document.getElementById("updatePhpConfig").addEventListener("click", function() {
    if (confirm("Are you sure you want to modify PHP upload limits?")) {
        fetch("update_php_config.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" }
        })
        .then(response => response.json())
        .then(data => alert(data.message))
        .catch(error => alert("Request failed: " + error.message));
    }
});
</script>

<script>
    document.getElementById('uploadIcon').addEventListener('click', function() {
        document.getElementById('imageFile').click(); 
    });

    document.getElementById('imageFile').addEventListener('change', function() {
        if (this.files.length > 0) {
            document.getElementById('submitBtnModal').style.display = 'inline-block';
        } else {
            document.getElementById('submitBtnModal').style.display = 'none';
        }
    });

    const dropArea = document.getElementById('dropArea');
    dropArea.addEventListener('dragover', function(event) {
        event.preventDefault(); 
        dropArea.classList.add('dragging'); 
    });

    dropArea.addEventListener('dragleave', function() {
        dropArea.classList.remove('dragging'); 
    });

    dropArea.addEventListener('drop', function(event) {
        event.preventDefault();
        dropArea.classList.remove('dragging'); 

        const files = event.dataTransfer.files;
        document.getElementById('imageFile').files = files; 

        if (files.length > 0) {
            document.getElementById('submitBtnModal').style.display = 'inline-block'; 
        }
    });
</script>

<script>
    const fileInput = document.getElementById('imageFile');
    const dragDropArea = document.getElementById('dragDropArea');
    const submitBtn = document.getElementById('submitBtn');

    dragDropArea.addEventListener('dragover', function(e) {
        e.preventDefault();
        dragDropArea.classList.add('drag-over');
    });

    dragDropArea.addEventListener('dragleave', function(e) {
        e.preventDefault();
        dragDropArea.classList.remove('drag-over');
    });

    dragDropArea.addEventListener('drop', function(e) {
        e.preventDefault();
        dragDropArea.classList.remove('drag-over');
        
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            fileInput.files = files;  
        }
    });

    fileInput.addEventListener('change', function(e) {
        const files = e.target.files;
        if (files.length > 0) {
            submitBtn.disabled = false;
        } else {
            submitBtn.disabled = true;
        }
        updateDragDropText();
    });

    function updateDragDropText() {
        if (fileInput.files.length > 0) {
            dragDropArea.querySelector('p').textContent = `${fileInput.files.length} files selected`;
        } else {
            dragDropArea.querySelector('p').textContent = 'Drag files here or click to select files';
        }
    }

</script>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uploadedFilePath = '';

    $allowedTypes = [
        'image/jpeg', 'image/png', 'image/gif', 'image/bmp', 'image/webp',
        'video/mp4', 'video/avi', 'video/mkv', 'video/mov', 'video/wmv', 'video/3gp',
        'audio/mp3', 'audio/wav', 'audio/ogg', 'audio/flac', 'audio/aac', 'audio/m4a', 'audio/webm', 'audio/opus'
    ];

    if (isset($_FILES['imageFile']) && $_FILES['imageFile']['error'] === UPLOAD_ERR_OK) {
        $targetDir = $_SERVER['DOCUMENT_ROOT'] . '/nekobox/assets/Pictures/';
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $fileExtension = strtolower(pathinfo($_FILES['imageFile']['name'], PATHINFO_EXTENSION));

        if (in_array($fileExtension, $allowedTypes)) {
            $targetFile = $targetDir . basename($_FILES['imageFile']['name']);
            if (move_uploaded_file($_FILES['imageFile']['tmp_name'], $targetFile)) {
                $uploadedFilePath = '/nekobox/assets/Pictures/' . basename($_FILES['imageFile']['name']);
            }
        } else {
            echo "<script>alert('Unsupported file type!');</script>";
        }
    }
}

if (isset($_GET['delete'])) {
    $fileToDelete = $_GET['delete'];
    $picturesDir = $_SERVER['DOCUMENT_ROOT'] . '/nekobox/assets/Pictures/';
    $filePath = $picturesDir . $fileToDelete;
    if (file_exists($filePath)) {
        unlink($filePath);
        echo "<script>alert('File deleted!'); location.reload();</script>";
        exit;
    }
}
?>

<script>
function setBackground(filename, type, action = 'set') {
    const bodyData = 'filename=' + encodeURIComponent(filename) + '&type=' + type;

    if (action === 'set') {
        fetch('/nekobox/set_background.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=set&' + bodyData
        })
        .then(response => response.text())
        .then(data => {
            sessionStorage.setItem('notificationMessage', data);
            sessionStorage.setItem('notificationType', 'success');
            location.reload(); 
        })
        .catch(error => {
            console.error('Error:', error);
            sessionStorage.setItem('notificationMessage', "Operation failed, please try again later.");
            sessionStorage.setItem('notificationType', 'error');
            location.reload(); 
        });
    }

    else if (action === 'remove') {
        fetch('/nekobox/set_background.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=remove'
        })
        .then(response => response.text())
        .then(data => {
            sessionStorage.setItem('notificationMessage', data);
            sessionStorage.setItem('notificationType', 'success');
            location.reload(); 
        })
        .catch(error => {
            console.error('Error:', error);
            sessionStorage.setItem('notificationMessage', "Failed to delete, please try again later.");
            sessionStorage.setItem('notificationType', 'error');
            location.reload(); 
        });
    }
}

function showNotification(message, type = 'success') {
    var notification = document.createElement('div');
    notification.style.position = 'fixed';
    notification.style.top = '10px';
    notification.style.left = '30px'; 
    notification.style.padding = '10px';
    notification.style.borderRadius = '5px';
    notification.style.zIndex = '9999';
    notification.style.color = '#fff'; 
    notification.innerText = message;

    if (type === 'success') {
        notification.style.backgroundColor = '#4CAF50'; 
    } else if (type === 'error') {
        notification.style.backgroundColor = '#F44336'; 
    }

    document.body.appendChild(notification);

    setTimeout(function() {
        notification.style.display = 'none';
    }, 5000); 
}

window.addEventListener('load', function() {
    var message = sessionStorage.getItem('notificationMessage');
    var type = sessionStorage.getItem('notificationType');

    if (message) {
        showNotification(message, type); 
        sessionStorage.removeItem('notificationMessage');
        sessionStorage.removeItem('notificationType');
    }
});
</script>

<script>
  document.addEventListener("DOMContentLoaded", function() {
    const colorInputs = document.querySelectorAll('input[type="color"]');
    
    colorInputs.forEach(input => {
      if (localStorage.getItem(input.name)) {
        input.value = localStorage.getItem(input.name);
      }

      input.addEventListener('input', function() {
        localStorage.setItem(input.name, input.value);
      });
    });

    const useBackgroundImageCheckbox = document.getElementById('useBackgroundImage');
    const backgroundImageContainer = document.getElementById('backgroundImageContainer');

    const savedBackgroundImageState = localStorage.getItem('useBackgroundImage');
    if (savedBackgroundImageState === 'true') {
      useBackgroundImageCheckbox.checked = true;
      backgroundImageContainer.style.display = 'block';
    } else {
      useBackgroundImageCheckbox.checked = false;
      backgroundImageContainer.style.display = 'none';
    }

    useBackgroundImageCheckbox.addEventListener('change', function() {
      if (useBackgroundImageCheckbox.checked) {
        backgroundImageContainer.style.display = 'block';
      } else {
        backgroundImageContainer.style.display = 'none';
      }

      localStorage.setItem('useBackgroundImage', useBackgroundImageCheckbox.checked);
    });

    document.getElementById('resetButton').addEventListener('click', function() {
      document.getElementById('primaryColor').value = '#0ceda2';
      document.getElementById('secondaryColor').value = '#00ffff';
      document.getElementById('bodyBgColor').value = '#23407e';
      document.getElementById('infoBgSubtle').value = '#23407e';
      document.getElementById('backgroundColor').value = '#20cdd9';
      document.getElementById('primaryBorderSubtle').value = '#1815d1';
      document.getElementById('checkColor').value = '#0eaf3e';
      document.getElementById('labelColor').value = '#0eaf3e';
      document.getElementById('lineColor').value = '#f515f9';
      document.getElementById('controlColor').value = '#0eaf3e';
      document.getElementById('placeholderColor').value = '#f82af2';
      document.getElementById('disabledColor').value = '#23407e';
      document.getElementById('logTextColor').value = '#f8f9fa';
      document.getElementById('selectColor').value = '#23407e';
      document.getElementById('radiusColor').value = '#14b863';
      document.getElementById('bodyColor').value = '#04f153';
      document.getElementById('tertiaryColor').value = '#46e1ec';
      document.getElementById('ipColor').value = '#09b63f';
      document.getElementById('ipipColor').value = '#ff69b4';
      document.getElementById('detailColor').value = '#FFFFFF';
      document.getElementById('outlineColor').value = '#0dcaf0';
      document.getElementById('successColor').value = '#28a745';
      document.getElementById('infoColor').value = '#0ca2ed';
      document.getElementById('warningColor').value = '#ffc107';
      document.getElementById('pinkColor').value = '#f82af2';
      document.getElementById('dangerColor').value = '#dc3545';
      document.getElementById('tertiaryRgbColor').value = '#1e90ff';
      document.getElementById('heading1Color').value = '#21e4f2';
      document.getElementById('heading2Color').value = '#65f1fb';
      document.getElementById('heading3Color').value = '#ffcc00';
      document.getElementById('heading4Color').value = '#00fbff';
      document.getElementById('heading5Color').value = '#ba13f6';
      document.getElementById('heading6Color').value = '#00ffff';   
      localStorage.clear();
    });

    document.getElementById('exportButton').addEventListener('click', function() {
      const settings = {
        primaryColor: document.getElementById('primaryColor').value,
        secondaryColor: document.getElementById('secondaryColor').value,
        bodyBgColor: document.getElementById('bodyBgColor').value,
        infoBgSubtle: document.getElementById('infoBgSubtle').value,
        backgroundColor: document.getElementById('backgroundColor').value,
        primaryBorderSubtle: document.getElementById('primaryBorderSubtle').value,
        checkColor: document.getElementById('checkColor').value,
        labelColor: document.getElementById('labelColor').value,
        lineColor: document.getElementById('lineColor').value,
        controlColor: document.getElementById('controlColor').value,
        placeholderColor: document.getElementById('placeholderColor').value,
        disabledColor: document.getElementById('disabledColor').value,
        logTextColor: document.getElementById('logTextColor').value,
        selectColor: document.getElementById('selectColor').value,
        radiusColor: document.getElementById('radiusColor').value,
        bodyColor: document.getElementById('bodyColor').value,
        tertiaryColor: document.getElementById('tertiaryColor').value,
        tertiaryRgbColor: document.getElementById('tertiaryRgbColor').value,
        ipColor: document.getElementById('ipColor').value,
        ipipColor: document.getElementById('ipipColor').value,
        detailColor: document.getElementById('detailColor').value,
        outlineColor: document.getElementById('outlineColor').value,
        successColor: document.getElementById('successColor').value,
        infoColor: document.getElementById('infoColor').value,
        warningColor: document.getElementById('warningColor').value,
        pinkColor: document.getElementById('pinkColor').value,
        dangerColor: document.getElementById('dangerColor').value,
        heading1Color: document.getElementById('heading1Color').value,
        heading2Color: document.getElementById('heading2Color').value,
        heading3Color: document.getElementById('heading3Color').value,
        heading4Color: document.getElementById('heading4Color').value,
        heading5Color: document.getElementById('heading5Color').value,
        heading6Color: document.getElementById('heading6Color').value,
        useBackgroundImage: document.getElementById('useBackgroundImage').checked,
        backgroundImage: document.getElementById('backgroundImage').value
      };

      const blob = new Blob([JSON.stringify(settings)], { type: 'application/json' });
      const link = document.createElement('a');
      link.href = URL.createObjectURL(blob);
      link.download = 'theme-settings.json';
      link.click();
    });

    document.getElementById('importButton').addEventListener('change', function(event) {
      const file = event.target.files[0];
      if (file && file.type === 'application/json') {
        const reader = new FileReader();
        reader.onload = function(e) {
          const settings = JSON.parse(e.target.result);

          document.getElementById('primaryColor').value = settings.primaryColor;
          document.getElementById('secondaryColor').value = settings.secondaryColor;
          document.getElementById('bodyBgColor').value = settings.bodyBgColor;
          document.getElementById('infoBgSubtle').value = settings.infoBgSubtle;
          document.getElementById('backgroundColor').value = settings.backgroundColor;
          document.getElementById('primaryBorderSubtle').value = settings.primaryBorderSubtle;
          document.getElementById('checkColor').value = settings.checkColor;
          document.getElementById('labelColor').value = settings.labelColor;
          document.getElementById('lineColor').value = settings.lineColor;
          document.getElementById('controlColor').value = settings.controlColor;
          document.getElementById('placeholderColor').value = settings.placeholderColor;
          document.getElementById('disabledColor').value = settings.disabledColor;
          document.getElementById('logTextColor').value = settings.logTextColor;
          document.getElementById('selectColor').value = settings.selectColor;
          document.getElementById('radiusColor').value = settings.radiusColor;
          document.getElementById('bodyColor').value = settings.bodyColor;
          document.getElementById('tertiaryColor').value = settings.tertiaryColor;
          document.getElementById('tertiaryRgbColor').value = settings.tertiaryRgbColor;
          document.getElementById('ipColor').value = settings.ipColor;
          document.getElementById('ipipColor').value = settings.ipipColor;
          document.getElementById('detailColor').value = settings.detailColor;
          document.getElementById('outlineColor').value = settings.outlineColor;
          document.getElementById('successColor').value = settings.successColor;
          document.getElementById('infoColor').value = settings.infoColor;
          document.getElementById('warningColor').value = settings.warningColor;
          document.getElementById('pinkColor').value = settings.pinkColor;
          document.getElementById('dangerColor').value = settings.dangerColor;
          document.getElementById('heading1Color').value = settings.heading1Color;
          document.getElementById('heading2Color').value = settings.heading2Color;
          document.getElementById('heading3Color').value = settings.heading3Color;
          document.getElementById('heading4Color').value = settings.heading4Color;
          document.getElementById('heading5Color').value = settings.heading5Color;
          document.getElementById('heading6Color').value = settings.heading6Color;
          document.getElementById('useBackgroundImage').checked = settings.useBackgroundImage;

          const backgroundImageContainer = document.getElementById('backgroundImageContainer');
          backgroundImageContainer.style.display = settings.useBackgroundImage ? 'block' : 'none';
          document.getElementById('backgroundImage').value = settings.backgroundImage || '';

          localStorage.setItem('primaryColor', settings.primaryColor);
          localStorage.setItem('secondaryColor', settings.secondaryColor);
          localStorage.setItem('bodyBgColor', settings.bodyBgColor);
          localStorage.setItem('infoBgSubtle', settings.infoBgSubtle);
          localStorage.setItem('backgroundColor', settings.backgroundColor);
          localStorage.setItem('primaryBorderSubtle', settings.primaryBorderSubtle);
          localStorage.setItem('checkColor', settings.checkColor);
          localStorage.setItem('labelColor', settings.labelColor);
          localStorage.setItem('lineColor', settings.lineColor);
          localStorage.setItem('controlColor', settings.controlColor);
          localStorage.setItem('placeholderColor', settings.placeholderColor);
          localStorage.setItem('disabledColor', settings.disabledColor);
          localStorage.setItem('logTextColor', settings.logTextColor);
          localStorage.setItem('selectColor', settings.selectColor);
          localStorage.setItem('radiusColor', settings.radiusColor);
          localStorage.setItem('bodyColor', settings.bodyColor);
          localStorage.setItem('tertiaryColor', settings.tertiaryColor);
          localStorage.setItem('tertiaryRgbColor', settings.tertiaryRgbColor);
          localStorage.setItem('ipColor', settings.ipColor);
          localStorage.setItem('ipipColor', settings.ipipColor);
          localStorage.setItem('detailColor', settings.detailColor);
          localStorage.setItem('outlineColor', settings.outlineColor);
          localStorage.setItem('successColor', settings.successColor);
          localStorage.setItem('infoColor', settings.infoColor);
          localStorage.setItem('warningColor', settings.warningColor);
          localStorage.setItem('pinkColor', settings.pinkColor);
          localStorage.setItem('dangerColor', settings.dangerColor);
          localStorage.setItem('heading1Color', settings.heading1Color);
          localStorage.setItem('heading2Color', settings.heading2Color);
          localStorage.setItem('heading3Color', settings.heading3Color);
          localStorage.setItem('heading4Color', settings.heading4Color);
          localStorage.setItem('heading5Color', settings.heading5Color);
          localStorage.setItem('heading6Color', settings.heading6Color);
          localStorage.setItem('useBackgroundImage', settings.useBackgroundImage);
          localStorage.setItem('backgroundImage', settings.backgroundImage);
        };
        reader.readAsText(file);
      }
    });
  });
</script>


