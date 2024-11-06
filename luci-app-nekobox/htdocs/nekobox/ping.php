<?php
ob_start();
include './cfg.php';
$translate = [
];
$lang = $_GET['lang'] ?? 'en';
?>

<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($lang); ?>">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-dns-prefetch-control" content="on">
    <link rel="dns-prefetch" href="//cdn.jsdelivr.net">
    <link rel="dns-prefetch" href="//whois.pconline.com.cn">
    <link rel="dns-prefetch" href="//forge.speedtest.cn">
    <link rel="dns-prefetch" href="//api-ipv4.ip.sb">
    <link rel="dns-prefetch" href="//api.ipify.org">
    <link rel="dns-prefetch" href="//api.ttt.sh">
    <link rel="dns-prefetch" href="//qqwry.api.skk.moe">
    <link rel="dns-prefetch" href="//d.skk.moe">
    <link rel="preconnect" href="https://forge.speedtest.cn">
    <link rel="preconnect" href="https://whois.pconline.com.cn">
    <link rel="preconnect" href="https://api-ipv4.ip.sb">
    <link rel="preconnect" href="https://api.ipify.org">
    <link rel="preconnect" href="https://api.ttt.sh">
    <link rel="preconnect" href="https://qqwry.api.skk.moe">
    <link rel="preconnect" href="https://d.skk.moe">
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

.container-sm.container-bg.callout.border {
  padding: 12px 15px; 
  min-height: 70px; 
  display: flex;
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
 .site-icon[onclick*="youtube"],
 .site-icon[onclick*="github"] {
   display: none !important;
 }
}
</style>
<?php if (in_array($lang, ['zh-cn', 'en', 'auto'])): ?>
    <div id="status-bar-component" class="container-sm container-bg callout border">
        <div class="row align-items-center">
            <div class="col-auto">
                <div class="img-con">
                    <img src="./assets/neko/img/loading.svg" id="flag" title="Flag" onclick="IP.getIpipnetIP()">
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
                    <div class="site-icon mx-1" onclick="pingHost('baidu', 'Baidu')">
                        <img src="./assets/neko/img/site_icon_01.png" id="baidu-normal" class="status-icon" style="display: none;">
                        <img src="./assets/neko/img/site_icon1_01.png" id="baidu-gray" class="status-icon">
                    </div>
                    <div class="site-icon mx-1" onclick="pingHost('google', 'Google')">
                        <img src="./assets/neko/img/site_icon_03.png" id="google-normal" class="status-icon" style="display: none;">
                        <img src="./assets/neko/img/site_icon1_03.png" id="google-gray" class="status-icon">
                    </div>
                    <div class="site-icon mx-1" onclick="pingHost('youtube', 'YouTube')">
                        <img src="./assets/neko/img/site_icon_04.png" id="youtube-normal" class="status-icon" style="display: none;">
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
<script src="./assets/neko/js/jquery.min.js"></script>
<script type="text/javascript">
const _IMG = './assets/neko/';
const translate = <?php echo json_encode($translate, JSON_UNESCAPED_UNICODE); ?>;
let cachedIP = null;
let cachedInfo = null;
let random = parseInt(Math.random() * 100000000);

const checkSiteStatus = {
    sites: {
        baidu: 'https://www.baidu.com',
        //taobao: 'https://www.taobao.com',
        google: 'https://www.google.com',
        youtube: 'https://www.youtube.com',
        github: 'https://www.github.com'
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
    fetchIP: async () => {
        try {
            const [ipifyResp, ipsbResp, chinaIpResp] = await Promise.all([
                IP.get('https://api.ipify.org?format=json', 'json'),
                IP.get('https://api-ipv4.ip.sb/geoip', 'json'),
                IP.get('https://myip.ipip.net', 'text')  
            ]);

            const ipData = ipifyResp.data.ip || ipsbResp.data.ip;
            cachedIP = ipData;
            document.getElementById('d-ip').innerHTML = ipData;
            return ipData;
        } catch (error) {
            console.error("Error fetching IP:", error);
            throw error;
        }
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
        try {
            const [ipsbResp, chinaIpResp] = await Promise.all([
                IP.get(`https://api.ip.sb/geoip/${ip}`, 'json'),
                IP.get(`https://myip.ipip.net`, 'text')
            ]);
            
            cachedIP = ip;
            cachedInfo = ipsbResp.data;

            let chinaIpInfo = null;
            try {
                if(chinaIpResp.data) {
                    chinaIpInfo = chinaIpResp.data;
                }
            } catch(e) {
                console.error("Error parsing China IP info:", e);
            }
            
            const mergedData = {
                ...ipsbResp.data,
               // chinaIpInfo: chinaIpInfo
            };
            
            IP.updateUI(mergedData, elID);
        } catch (error) {
            console.error("Error in Ipip function:", error);
            document.getElementById(elID).innerHTML = "Failed to obtain IP information";
        }
    },

    updateUI: (data, elID) => {
        try {
            if (!data || !data.country_code) {
                document.getElementById('d-ip').innerHTML = "Unable to obtain IP information";
                return;
            }

            let country = translate[data.country] || data.country || "Unknown";
            let isp = translate[data.isp] || data.isp || "";
            let asnOrganization = translate[data.asn_organization] || data.asn_organization || "";

            if (data.country === 'Taiwan') {
                country = (navigator.language === 'en') ? 'China Taiwan' : 'China Taiwan';
            }

            const countryAbbr = data.country_code.toLowerCase();
            const isChinaIP = ['cn', 'hk', 'mo', 'tw'].includes(countryAbbr);
        
            let firstLineInfo = `<div style="white-space: nowrap;">`;
        
            firstLineInfo += cachedIP + ' ';
        
            let ipLocation = isChinaIP ? 
                '<span style="color: #00FF00;">[国内 IP]</span> ' : 
                '<span style="color: #FF0000;">[境外 IP]</span> ';
           // firstLineInfo += ipLocation;
        
            if (data.chinaIpInfo) {
                firstLineInfo += `[${data.chinaIpInfo}]`;
            }
            firstLineInfo += `</div>`;
        
            document.getElementById('d-ip').innerHTML = firstLineInfo;
            document.getElementById('ipip').innerHTML = `${country} ${isp} ${asnOrganization}`;
            document.getElementById('ipip').style.color = '#FF00FF';
            $("#flag").attr("src", _IMG + "flags/" + countryAbbr + ".png");
        } catch (error) {
            console.error("Error in updateUI:", error);
            document.getElementById('d-ip').innerHTML = "Update IP information failed";
        }
    },

    getIpipnetIP: async () => {
        if(IP.isRefreshing) return;
    
        try {
            IP.isRefreshing = true;
            document.getElementById('d-ip').innerHTML = "Checking...";
            document.getElementById('ipip').innerHTML = "Loading...";
            $("#flag").attr("src", _IMG + "img/loading.svg");
        
            const ip = await IP.fetchIP();
            await IP.Ipip(ip, 'ipip');
        } catch (error) {
            console.error("Error in getIpipnetIP function:", error);
            document.getElementById('ipip').innerHTML = "Failed to get IP information";
        } finally {
            IP.isRefreshing = false;
        }
    }
};

IP.getIpipnetIP();
checkSiteStatus.check();
setInterval(() => checkSiteStatus.check(), 30000);  
setInterval(IP.getIpipnetIP, 180000);
</script>
</body>
</html>