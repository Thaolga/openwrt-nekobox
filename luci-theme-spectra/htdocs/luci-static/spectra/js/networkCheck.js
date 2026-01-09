const _IMG = '/luci-static/ipip/';
const translate = {};

let cachedIP = null;
let translationEnabled = localStorage.getItem('translationEnabled') !== 'false';
let currentLanguage = 'zh-CN';

const translationsIP = {
    zh: {
        unknown: '未知',
        clickRefresh: '点击刷新 IP 地址',
        failedUpdate: '更新 IP 信息失败',
        checking: '获取中...',
        failed: '获取 IP 信息失败',
        translate: '翻译'
    },

    en: {
        unknown: 'Unknown',
        clickRefresh: 'Click to refresh IP address',
        failedUpdate: 'Failed to update IP information',
        checking: 'Checking...',
        failed: 'Failed to get IP information',
        translate: 'Translate'
    },

    hk: {
        unknown: '未知',
        clickRefresh: '點擊刷新 IP 地址',
        failedUpdate: '更新 IP 資訊失敗',
        checking: '獲取中...',
        failed: '獲取 IP 資訊失敗',
        translate: '翻譯'
    },

    ko: {
        unknown: '알 수 없음',
        clickRefresh: 'IP 주소 새로고침',
        failedUpdate: 'IP 정보 업데이트 실패',
        checking: '가져오는 중...',
        failed: 'IP 정보를 가져오지 못했습니다',
        translate: '번역'
    },

    ja: {
        unknown: '不明',
        clickRefresh: 'IP アドレスを更新',
        failedUpdate: 'IP 情報の更新に失敗しました',
        checking: '取得中...',
        failed: 'IP 情報の取得に失敗しました',
        translate: '翻訳'
    },

    vi: {
        unknown: 'Không xác định',
        clickRefresh: 'Nhấn để làm mới địa chỉ IP',
        failedUpdate: 'Cập nhật thông tin IP thất bại',
        checking: 'Đang lấy...',
        failed: 'Lấy thông tin IP thất bại',
        translate: 'Dịch'
    },

    th: {
        unknown: 'ไม่ทราบ',
        clickRefresh: 'คลิกเพื่อรีเฟรชที่อยู่ IP',
        failedUpdate: 'อัปเดตข้อมูล IP ล้มเหลว',
        checking: 'กำลังดึงข้อมูล...',
        failed: 'ไม่สามารถดึงข้อมูล IP',
        translate: 'แปล'
   },

    ru: {
        unknown: 'Неизвестно',
        clickRefresh: 'Нажмите, чтобы обновить IP-адрес',
        failedUpdate: 'Не удалось обновить информацию об IP',
        checking: 'Получение...',
        failed: 'Не удалось получить информацию об IP',
        translate: 'Перевести'
    },

    ar: {
        unknown: 'غير معروف',
        clickRefresh: 'اضغط لتحديث عنوان الـ IP',
        failedUpdate: 'فشل تحديث معلومات الـ IP',
        checking: 'جارٍ الجلب...',
        failed: 'فشل في جلب معلومات الـ IP',
        translate: 'ترجمة'
    },

    es: {
        unknown: 'Desconocido',
        clickRefresh: 'Haga clic para actualizar la dirección IP',
        failedUpdate: 'Error al actualizar la información de IP',
        checking: 'Obteniendo...',
        failed: 'Error al obtener la información de IP',
        translate: 'Traducir'
    },

    de: {
        unknown: 'Unbekannt',
        clickRefresh: 'Klicken, um die IP-Adresse zu aktualisieren',
        failedUpdate: 'Aktualisieren der IP-Informationen fehlgeschlagen',
        checking: 'Wird abgerufen...',
        failed: 'Abrufen der IP-Informationen fehlgeschlagen',
        translate: 'Übersetzen'
    },

    fr: {
        unknown: 'Inconnu',
        clickRefresh: 'Cliquez pour actualiser l’adresse IP',
        failedUpdate: 'Échec de la mise à jour des informations IP',
        checking: 'Récupération...',
        failed: 'Échec de la récupération des informations IP',
        translate: 'Traduire'
    },

    bn: {
        unknown: 'অজানা',
        clickRefresh: 'IP ঠিকানা রিফ্রেশ করতে ক্লিক করুন',
        failedUpdate: 'IP তথ্য আপডেট ব্যর্থ হয়েছে',
        checking: 'আনছে...',
        failed: 'IP তথ্য আনতে ব্যর্থ হয়েছে',
        translate: 'অনুবাদ'
    }
};

async function initIPDisplay() {
    const lang = await getRealTimeLanguage();
    const text = translationsIP[lang] || translationsIP['zh'];
    const modalText = modalTranslations[lang] || modalTranslations['zh'];

    const flag = document.getElementById('flag');
    if (flag) flag.title = text.clickRefresh || 'Click to Refresh';
}

document.addEventListener('DOMContentLoaded', () => {
    initIPDisplay();
});

async function getCurrentLanguage() {
    try {
        const response = await fetch('/spectra/lib/language.txt', {cache: 'no-store'});
        if (response.ok) {
            const lang = await response.text();
            return lang.trim() || 'zh-CN';
        }
    } catch (error) {
        console.error('Failed to read language file:', error);
    }
    return 'zh-CN';
}

async function getRealTimeLanguage() {
    return await getCurrentLanguage();
}

async function onLanguageChange() {
    //console.log('Language changed, refreshing display');
    
    const keys = Object.keys(localStorage);
    keys.forEach(key => {
        if (key.startsWith('trans_')) {
            localStorage.removeItem(key);
        }
    });
    
    if (cachedIP) {
        await myAppIP.Ipip(cachedIP, 'ipip');
    }
}

function setupLanguageChangeListener() {
    let lastLanguage = 'zh-CN';

    setInterval(async () => {
        const currentLang = await getRealTimeLanguage();
        if (currentLang !== lastLanguage) {
            //console.log('Language changed detected:', currentLang);
            lastLanguage = currentLang;
            onLanguageChange();
        }
    }, 1000);
}

document.addEventListener('DOMContentLoaded', function() {
    const savedState = localStorage.getItem('translationEnabled');
    if (savedState !== null) {
        translationEnabled = savedState === 'true';
    }
    updateTranslationToggleColor();
});

function updateTranslationToggleColor() {
    const existingToggles = document.querySelectorAll('.translation-toggle');
    existingToggles.forEach(toggle => {
        const icon = toggle.querySelector('i');
        if (translationEnabled) {
            icon.style.color = '#28a745';
        } else {
            icon.style.color = '#6c757d';
        }
    });
}

function toggleTranslation() {
    translationEnabled = !translationEnabled;
    localStorage.setItem('translationEnabled', translationEnabled);
    
    updateTranslationToggleColor();
    
    const translations = languageTranslations[currentLang] || languageTranslations['zh'];
    const translationMsg = translationEnabled 
        ? translations['translation_enabled'] 
        : translations['translation_disabled'];
    
    if (typeof showLogMessage === 'function') {
        showLogMessage(encodeURIComponent(translationMsg), 'info');
    }
    
    if (typeof speakMessage === 'function') {
        speakMessage(translationMsg);
    }
    
    if (cachedIP) {
        myAppIP.Ipip(cachedIP, 'ipip');
    }
}

function setupHoverEffect() {
    const ipContainer = document.getElementById('d-ip');
    const toggle = document.querySelector('.translation-toggle');
    if (ipContainer && toggle) {
        ipContainer.addEventListener('mouseenter', () => toggle.style.opacity = '1');
        ipContainer.addEventListener('mouseleave', () => toggle.style.opacity = '0');
    }
}

async function myAppTranslate(text) {
    if (!translationEnabled) return text;
    if (!text || typeof text !== 'string' || text.trim() === '') return text;

    const targetLanguage = await getRealTimeLanguage();
    const langMap = {
        'zh':'zh-CN','zh-tw':'zh-TW','zh-CN':'zh-CN','hk':'zh-TW', 'zh-mo': 'zh-TW',
        'en':'en','ja':'ja','ko':'ko','fr':'fr','de':'de','es':'es',
        'it':'it','pt':'pt','ru':'ru','ar':'ar','hi':'hi','bn':'bn',
        'ms':'ms','id':'id','vi':'vi','th':'th','nl':'nl','pl':'pl',
        'tr':'tr','sv':'sv','no':'no','fi':'fi','da':'da','cs':'cs',
        'he':'he','el':'el','hu':'hu','ro':'ro','sk':'sk','bg':'bg','uk':'uk'
    };
    const targetLang = langMap[targetLanguage] || targetLanguage.split('-')[0];
    if (targetLang.startsWith('en')) return text;

    const cacheKey = `trans_${text}_${targetLang}`;
    const cachedTranslation = localStorage.getItem(cacheKey);
    if (cachedTranslation) return cachedTranslation;

    const apis = [
        { 
            url: `https://api.mymemory.translated.net/get?q=${encodeURIComponent(text)}&langpair=en|${targetLang}`, 
            method: 'GET', 
            parseResponse: d => d && d.responseData && d.responseData.translatedText ? d.responseData.translatedText : text
        },
        { 
            url: `https://translate.googleapis.com/translate_a/single?client=gtx&sl=en&tl=${targetLang}&dt=t&q=${encodeURIComponent(text)}`, 
            method: 'GET', 
            parseResponse: d => Array.isArray(d) && d[0]?.[0]?.[0] ? d[0][0][0] : text
        }
    ];

    for(const api of apis){
        try{
            const response = await fetch(api.url, {
                method: api.method,
                headers: api.headers || {},
                body: api.body || null,
                signal: AbortSignal.timeout(3000)
            });
            
            if(response.ok){
                const data = await response.json();
                const translatedText = api.parseResponse(data);
                
                if (translatedText && translatedText !== text) {
                    localStorage.setItem(cacheKey, translatedText);
                    return translatedText;
                }
            }
        } catch(e){ 
            console.warn(`Translation API ${api.url} failed:`, e);
            continue; 
        }
    }
    return text;
}

async function saveIPCache(data) {
    if (!data.ip) return;
    data.language = await getRealTimeLanguage();
    
    const saveData = {
        ip: data.ip,
        country: data.country || '',
        region: data.region || '',
        city: data.city || '',
        isp: data.isp || '',
        asn: data.asn || '',
        asn_organization: data.asn_organization || '',
        country_code: data.country_code || '',
        timezone: data.timezone || data.time_zone || '',
        latitude: data.latitude || data.lat || '',
        longitude: data.longitude || data.lon || data.lng || '',
        language: data.language,
        translatedCountry: data.translatedCountry || '',
        translatedRegion: data.translatedRegion || '',
        translatedCity: data.translatedCity || '',
        translatedISP: data.translatedISP || '',
        translatedASNOrg: data.translatedASNOrg || ''
    };
    
    console.log('Saving cache with language:', saveData.language);
    
    try {
        const response = await fetch('/spectra/save_ip_cache.php', {
            method: 'POST',
            headers: {'Content-Type':'application/json'},
            body: JSON.stringify(saveData)
        });
            const result = await response.json();
            console.log('Save result:', result);
    } catch (err) { 
        console.error("Failed to save IP cache:", err); 
    }
}

async function getCachedIPInfo(ip) {
    try {
        const resp = await fetch('/spectra/lib/ip_cache.json', {cache:'no-store'});
        const cache = await resp.json();
        const item = cache.find(item=>item.ip===ip);
        if (!item) return null;
        
        const currentLang = await getRealTimeLanguage();
        
        //console.log('Cache check - Current language:', currentLang);
        //console.log('Available translations:', item.translations ? Object.keys(item.translations) : 'none');
        
        if (item.translations && item.translations[currentLang]) {
            //console.log('Using translation for language:', currentLang);
            return {
                ...item,
                ...item.translations[currentLang],
                language: currentLang
            };
        }
        
        console.log('No translation for current language, returning base data');
        return {
            ...item,
            language: currentLang
        };
        
    } catch (err) { 
        console.log('Cache read error:', err);
        return null; 
    }
}

let myAppIP = {
    isRefreshing:false,
    lastGeoData:null,
    ipApis:[
        {url:'https://api.ipify.org?format=json',type:'json',key:'ip'},
        {url:'https://api-ipv4.ip.sb/geoip',type:'json',key:'ip'},
        {url:'https://myip.ipip.net',type:'text'},
        {url:'http://pv.sohu.com/cityjson',type:'text'},
        {url:'https://ipinfo.io/json',type:'json',key:'ip'},
        {url:'https://ipapi.co/json/',type:'json'},
        {url:'https://freegeoip.app/json/',type:'json'},
        {url: ip => `https://ipapi.com/${ip}/json`, type: 'json', concurrent: true},
        {url: ip => `https://api.ip.sb/geoip/${ip}`, type: 'json', concurrent: true},
        {url: ip => `https://ipwho.is/${ip}`, type: 'json', concurrent: true},
        {url: ip => `https://api.ipapi.is/?q=${ip}`, type: 'json', concurrent: true}
    ],

    get:(url,type)=>fetch(url,{method:'GET',cache:'no-store'})
        .then(resp=>type==='text'?Promise.all([resp.ok,resp.status,resp.text(),resp.headers]):Promise.all([resp.ok,resp.status,resp.json(),resp.headers]))
        .then(([ok,status,data,headers])=>ok?{ok,status,data,headers}:Promise.reject(data.error))
        .catch(e=>{console.error("Error fetching data:",e);throw e;}),

    concurrentGeoQuery: async function(ip) {
        const concurrentApis = this.ipApis.filter(api => api.concurrent);
        if (!concurrentApis.length) throw new Error("No concurrent APIs configured");

        return new Promise((resolve, reject) => {
            let resolved = false;
            let completed = 0;

            concurrentApis.forEach(api => {
                const apiUrl = typeof api.url === 'function' ? api.url(ip) : api.url;
                this.get(apiUrl, api.type)
                    .then(resp => {
                        if (!resolved && resp.data) {
                            resolved = true;
                            resolve(resp.data);
                        }
                    })
                    .catch(err => console.warn("Concurrent API failed:", apiUrl, err))
                    .finally(() => {
                        completed++;
                        if (completed === concurrentApis.length && !resolved) {
                            reject(new Error("All concurrent APIs failed"));
                        }
                    });
            });
        });
    },

    fetchIP: async ()=>{
        let error;
        for(let api of myAppIP.ipApis){
            if (api.concurrent) continue;
            
            try{
                const response = await myAppIP.get(api.url,api.type);
                let ipData = api.type==='json' ? (api.key?response.data[api.key]:response.data) : response.data.match(/\d+\.\d+\.\d+\.\d+/)?.[0];
                if(ipData){ cachedIP=ipData; document.getElementById('d-ip').innerHTML=ipData; return ipData; }
            }catch(e){ error=e; continue; }
        }
        throw error||new Error("All IP APIs failed");
    },

    Ipip: async(ip,elID)=>{
        //console.log('Ipip called with IP:', ip);
        let cachedData = await getCachedIPInfo(ip);
        const currentLang = await getRealTimeLanguage();       
        //console.log('Current language:', currentLang);
        
        if(cachedData && cachedData.translatedCountry) { 
            //console.log('Using cached data with translation');
            myAppIP.lastGeoData = cachedData; 
            await myAppIP.updateUI(cachedData,elID); 
            return; 
        }
        
        if(cachedData) {
            console.log('Cached data found but no translation for current language, re-translating');
            
            const baseData = {
                country: cachedData.country || '',
                region: cachedData.region || '',
                city: cachedData.city || '',
                isp: cachedData.isp || '',
                asn_organization: cachedData.asn_organization || '',
                asn: cachedData.asn || '',
                country_code: cachedData.country_code || '',
                timezone: cachedData.timezone || '',
                latitude: cachedData.latitude || '',
                longitude: cachedData.longitude || ''
            };
            
            myAppIP.lastGeoData = {...baseData};
            
            if(translationEnabled){
                console.log('Starting translation for language:', currentLang);
                myAppIP.lastGeoData.translatedCountry = await myAppTranslate(baseData.country);
                myAppIP.lastGeoData.translatedRegion  = await myAppTranslate(baseData.region);
                myAppIP.lastGeoData.translatedCity    = await myAppTranslate(baseData.city);
                myAppIP.lastGeoData.translatedISP     = await myAppTranslate(baseData.isp);
                myAppIP.lastGeoData.translatedASNOrg  = await myAppTranslate(baseData.asn_organization);
                myAppIP.lastGeoData.language = currentLang;
                console.log('Translation completed');
            }
            
            myAppIP.lastGeoData.ip = ip;
            myAppIP.lastGeoData.asn = baseData.asn;
            myAppIP.lastGeoData.country_code = baseData.country_code;          
            await myAppIP.updateUI(myAppIP.lastGeoData,elID);
            saveIPCache(myAppIP.lastGeoData);
            return;
        }

        try {
            const geoData = await this.concurrentGeoQuery(ip);
            cachedIP=ip;
            myAppIP.lastGeoData=geoData;

            const standardizedData = {
                ip: geoData.ip || ip,
                country: geoData.country || geoData.country_name || '',
                region: geoData.region || geoData.region_name || geoData.state || '',
                city: geoData.city || '',
                isp: geoData.isp || geoData.connection?.isp || geoData.org || '',
                asn: geoData.asn || geoData.connection?.asn || '',
                asn_organization: geoData.org || geoData.connection?.org || geoData.connection?.isp || '',
                country_code: geoData.country_code || geoData.countryCode || geoData.country_code || '',
                timezone: geoData.timezone || geoData.time_zone || '',
                latitude: geoData.latitude || geoData.lat || '',
                longitude: geoData.longitude || geoData.lon || geoData.lng || ''
            };

            if(translationEnabled){
                standardizedData.translatedCountry = await myAppTranslate(standardizedData.country||'');
                standardizedData.translatedRegion  = await myAppTranslate(standardizedData.region||'');
                standardizedData.translatedCity    = await myAppTranslate(standardizedData.city||'');
                standardizedData.translatedISP     = await myAppTranslate(standardizedData.isp||'');
                standardizedData.translatedASNOrg  = await myAppTranslate(standardizedData.asn_organization||'');
                standardizedData.language = currentLang;
            }

            await myAppIP.updateUI(standardizedData,elID);
            saveIPCache(standardizedData);
            
        } catch(concurrentError) {
            console.log("Concurrent query failed, falling back to sequential:", concurrentError);
            
            const geoApis = [
                {url:`https://api.ip.sb/geoip/${ip}`,type:'json'},
                {url:'https://myip.ipip.net',type:'text'},
                {url:`http://ip-api.com/json/${ip}`,type:'json'},
                {url:`https://ipinfo.io/${ip}/json`,type:'json'},
                {url:`https://ipapi.co/${ip}/json`,type:'json'},
                {url:`https://freegeoip.app/json/${ip}`,type:'json'}
            ];

            let geoData=null,error;
            for(let api of geoApis){
                try{ 
                    const response = await myAppIP.get(api.url,api.type); 
                    geoData=response.data; 
                    break; 
                }
                catch(e){ error=e; continue; }
            }
            if(!geoData) throw error||new Error("All Geo APIs failed");

            cachedIP=ip;
            myAppIP.lastGeoData=geoData;

            geoData.timezone = geoData.timezone || geoData.time_zone || '';
            geoData.latitude = geoData.latitude || geoData.lat || '';
            geoData.longitude = geoData.longitude || geoData.lon || geoData.lng || '';

            if(translationEnabled){
                geoData.translatedCountry = await myAppTranslate(geoData.country||'');
                geoData.translatedRegion  = await myAppTranslate(geoData.region||'');
                geoData.translatedCity    = await myAppTranslate(geoData.city||'');
                geoData.translatedISP     = await myAppTranslate(geoData.isp||'');
                geoData.translatedASNOrg  = await myAppTranslate(geoData.asn_organization||'');
                geoData.language = currentLang;
            }

            await myAppIP.updateUI(geoData,elID);
            saveIPCache(geoData);
        }
    },

    updateUI: async(data,elID)=>{
        try{
            const lang = await getRealTimeLanguage();
            const text = translationsIP[lang] || translationsIP['zh'];
            const country = translationEnabled && data.translatedCountry ? data.translatedCountry : data.country || text.unknown;
            const region  = translationEnabled && data.translatedRegion  ? data.translatedRegion  : data.region  || "";
            const city    = translationEnabled && data.translatedCity    ? data.translatedCity    : data.city    || "";
            const isp     = translationEnabled && data.translatedISP     ? data.translatedISP     : data.isp     || "";
            const asnOrg  = translationEnabled && data.translatedASNOrg  ? data.translatedASNOrg  : data.asn_organization || "";

            let location = region && city && region!==city ? `${region} ${city}` : region||city||'';
            let displayISP = isp;
            let displayASN = asnOrg;
            if(isp && asnOrg && asnOrg.includes(isp)) displayISP=''; 
            else if(isp && asnOrg && isp.includes(asnOrg)) displayASN='';

            document.getElementById('d-ip').innerHTML = `
                <div class="ip-main" style="cursor:pointer;" onclick="myAppIP.showDetailModal()">
                    ${cachedIP} <span class="badge-primary" style="color:#fd7e14;background:#f8f9fa;border-radius:4px;padding:2px 6px;border:1px solid #ddd;">${country}</span>
                </div>`;
            document.getElementById('ipip').innerHTML = `<span style="margin-left:8px; position: relative; top: -3px;">${location} ${displayISP} ${data.asn||''} ${displayASN}</span>`;

            const countryCode = data.country_code||'unknown';
            const flagSrc = (countryCode!=='unknown')?_IMG+"flags/"+countryCode.toLowerCase()+".svg":'/luci-static/ipip/flags/cn.svg';
            $("#flag").attr("src",flagSrc);

        } catch(e){
            console.error("updateUI error:",e);
            document.getElementById('d-ip').innerHTML = text.failedUpdate;   
            $("#flag").attr("src","/luci-static/ipip/flags/cn.svg");
        }
    },

    getIpipnetIP: async ()=>{
        if(myAppIP.isRefreshing) return;
        myAppIP.isRefreshing = true;
        const lang = await getRealTimeLanguage();
        const text = translationsIP[lang] || translationsIP['zh'];

        document.getElementById('d-ip').textContent = text.checking;
        document.getElementById('ipip').innerHTML="";

        const flag = document.getElementById("flag");
        const loading = document.getElementById("loading");

        loading.style.display = 'flex';
        flag.style.visibility = 'hidden';

        try {
            const ip = await myAppIP.fetchIP();
            await myAppIP.Ipip(ip, 'ipip');
        } catch (e) {
            console.error("getIpipnetIP error:", e);
            document.getElementById('ipip').innerHTML = text.failed;
        } finally {
            loading.style.display = 'none';
            flag.style.visibility = 'visible';
            myAppIP.isRefreshing = false;
        }
    },

    showDetailModal: async function() {
        if (!myAppIP.lastGeoData) return;
            const lang = await getRealTimeLanguage();
            const text = translationsIP[lang] || translationsIP['zh'];
            const data = myAppIP.lastGeoData;
            const country = translationEnabled && data.translatedCountry ? data.translatedCountry : data.country || text.unknown;
            const region = translationEnabled && data.translatedRegion ? data.translatedRegion : data.region || "";
            const city = translationEnabled && data.translatedCity ? data.translatedCity : data.city || "";
            const isp = translationEnabled && data.translatedISP ? data.translatedISP : data.isp || "";
            const asnOrg = translationEnabled && data.translatedASNOrg ? data.translatedASNOrg : data.asn_organization || "";
            const timezone = data.timezone || data.time_zone || 'N/A';
            const latitude = data.latitude || data.lat || 'N/A';
            const longitude = data.longitude || data.lon || data.lng || 'N/A';

            let locationParts = [country];
    
            if (region && region !== country) {
                locationParts.push(region);
            }
    
            if (city && city !== region && city !== country) {
                locationParts.push(city);
            }
    
            const locationDisplay = locationParts.join(' ');

            let countryCode = data.country_code || 'unknown';
            if (countryCode.toUpperCase() === 'TW') {
                countryCode = 'CN';
            } else if (countryCode.toUpperCase() === 'HK' || countryCode.toUpperCase() === 'MO') {
                countryCode = 'CN';
            }

        const translations = {
            'zh': {
                title: 'IP 地址详情',
                ip: 'IP 地址',
                location: '地理位置',
                isp: '网络服务商',
                asn: '自治系统号',
                countryCode: '国家代码',
                timezone: '时区',
                coordinates: '坐标',
                translation: '翻译',
                enabled: '已启用',
                disabled: '已禁用',
                close: '关闭',
                refresh: '刷新',
                mapView: '地图视图',
                viewOnGoogleMaps: '在Google地图中查看',
                latitude: '纬度',
                longitude: '经度'
            },
            'en': {
                title: 'IP Address Details',
                ip: 'IP Address',
                location: 'Location',
                isp: 'ISP',
                asn: 'ASN',
                countryCode: 'Country Code',
                timezone: 'Timezone',
                coordinates: 'Coordinates',
                translation: 'Translation',
                enabled: 'Enabled',
                disabled: 'Disabled',
                close: 'Close',
                refresh: 'Refresh',
                mapView: 'Map View',
                viewOnGoogleMaps: 'View on Google Maps',
                latitude: 'Latitude',
                longitude: 'Longitude'
            },
            'ja': {
                title: 'IPアドレス詳細',
                ip: 'IPアドレス',
                location: '所在地',
                isp: 'ISP',
                asn: 'AS番号',
                countryCode: '国コード',
                timezone: 'タイムゾーン',
                coordinates: '座標',
                translation: '翻訳',
                enabled: '有効',
                disabled: '無効',
                close: '閉じる',
                refresh: '更新',
                mapView: '地図表示',
                viewOnGoogleMaps: 'Googleマップで表示',
                latitude: '緯度',
                longitude: '経度'
            },
            'hk': {
                title: 'IP 地址詳情',
                ip: 'IP 地址',
                location: '地理位置',
                isp: '網絡服務商',
                asn: '自治系統號',
                countryCode: '國家代碼',
                timezone: '時區',
                coordinates: '坐標',
                translation: '翻譯',
                enabled: '已啟用',
                disabled: '已禁用',
                close: '關閉',
                refresh: '刷新',
                mapView: '地圖視圖',
                viewOnGoogleMaps: '在Google地圖中查看',
                latitude: '緯度',
                longitude: '經度'
            },
            'ko': {
                title: 'IP 주소 세부정보',
                ip: 'IP 주소',
                location: '위치',
                isp: '인터넷 서비스 제공업체',
                asn: 'ASN',
                countryCode: '국가 코드',
                timezone: '시간대',
                coordinates: '좌표',
                translation: '번역',
                enabled: '사용',
                disabled: '사용 안 함',
                close: '닫기',
                refresh: '새로고침',
                mapView: '지도 보기',
                viewOnGoogleMaps: 'Google 지도에서 보기',
                latitude: '위도',
                longitude: '경도'
            },
            'ru': {
                title: 'Детали IP-адреса',
                ip: 'IP-адрес',
                location: 'Местоположение',
                isp: 'Интернет-провайдер',
                asn: 'ASN',
                countryCode: 'Код страны',
                timezone: 'Часовой пояс',
                coordinates: 'Координаты',
                translation: 'Перевод',
                enabled: 'Включено',
                disabled: 'Отключено',
                close: 'Закрыть',
                refresh: 'Обновить',
                mapView: 'Просмотр карты',
                viewOnGoogleMaps: 'Посмотреть в Google Картах',
                latitude: 'Широта',
                longitude: 'Долгота'
            },
            'ar': {
                title: 'تفاصيل عنوان IP',
                ip: 'عنوان IP',
                location: 'الموقع',
                isp: 'مزود خدمة الإنترنت',
                asn: 'ASN',
                countryCode: 'رمز الدولة',
                timezone: 'المنطقة الزمنية',
                coordinates: 'الإحداثيات',
                translation: 'ترجمة',
                enabled: 'مُمكّن',
                disabled: 'معطّل',
                close: 'إغلاق',
                refresh: 'تحديث',
                mapView: 'عرض الخريطة',
                viewOnGoogleMaps: 'عرض على خرائط Google',
                latitude: 'خط العرض',
                longitude: 'خط الطول'
            },
            'es': {
                title: 'Detalles de la dirección IP',
                ip: 'Dirección IP',
                location: 'Ubicación',
                isp: 'Proveedor de Internet',
                asn: 'ASN',
                countryCode: 'Código de país',
                timezone: 'Zona horaria',
                coordinates: 'Coordenadas',
                translation: 'Traducción',
                enabled: 'Habilitado',
                disabled: 'Deshabilitado',
                close: 'Cerrar',
                refresh: 'Actualizar',
                mapView: 'Vista de mapa',
                viewOnGoogleMaps: 'Ver en Google Maps',
                latitude: 'Latitud',
                longitude: 'Longitud'
            },
            'de': {
                title: 'IP-Adressdetails',
                ip: 'IP-Adresse',
                location: 'Standort',
                isp: 'ISP',
                asn: 'ASN',
                countryCode: 'Ländercode',
                timezone: 'Zeitzone',
                coordinates: 'Koordinaten',
                translation: 'Übersetzung',
                enabled: 'Aktiviert',
                disabled: 'Deaktiviert',
                close: 'Schließen',
                refresh: 'Aktualisieren',
                mapView: 'Kartenansicht',
                viewOnGoogleMaps: 'In Google Maps ansehen',
                latitude: 'Breitengrad',
                longitude: 'Längengrad'
            },
            'fr': {
                title: 'Détails de l’adresse IP',
                ip: 'Adresse IP',
                location: 'Emplacement',
                isp: 'FAI',
                asn: 'ASN',
                countryCode: 'Code du pays',
                timezone: 'Fuseau horaire',
                coordinates: 'Coordonnées',
                translation: 'Traduction',
                enabled: 'Activé',
                disabled: 'Désactivé',
                close: 'Fermer',
                refresh: 'Rafraîchir',
                mapView: 'Vue carte',
                viewOnGoogleMaps: 'Voir sur Google Maps',
                latitude: 'Latitude',
                longitude: 'Longitude'
            },
            'vi': {
                title: 'Chi tiết địa chỉ IP',
                ip: 'Địa chỉ IP',
                location: 'Vị trí',
                isp: 'Nhà cung cấp dịch vụ Internet',
                asn: 'ASN',
                countryCode: 'Mã quốc gia',
                timezone: 'Múi giờ',
                coordinates: 'Tọa độ',
                translation: 'Dịch',
                enabled: 'Bật',
                disabled: 'Tắt',
                close: 'Đóng',
                refresh: 'Làm mới',
                mapView: 'Xem bản đồ',
                viewOnGoogleMaps: 'Xem trên Google Maps',
                latitude: 'Vĩ độ',
                longitude: 'Kinh độ'
            },
            'bn': {
                title: 'IP ঠিকানার বিবরণ',
                ip: 'IP ঠিকানা',
                location: 'অবস্থান',
                isp: 'আইএসপি',
                asn: 'ASN',
                countryCode: 'দেশ কোড',
                timezone: 'সময় অঞ্চল',
                coordinates: 'অবস্থানাঙ্ক',
                translation: 'অনুবাদ',
                enabled: 'সক্রিয়',
                disabled: 'নিষ্ক্রিয়',
                close: 'বন্ধ করুন',
                refresh: 'রিফ্রেশ',
                mapView: 'মানচিত্র ভিউ',
                viewOnGoogleMaps: 'Google মানচিত্রে দেখুন',
                latitude: 'অক্ষাংশ',
                longitude: 'দ্রাঘিমাংশ'
            },
            'th': {
                title: 'รายละเอียดที่อยู่ IP',
                ip: 'ที่อยู่ IP',
                location: 'ตำแหน่งที่ตั้ง',
                isp: 'ผู้ให้บริการอินเทอร์เน็ต',
                asn: 'ASN',
                countryCode: 'รหัสประเทศ',
                timezone: 'เขตเวลา',
                coordinates: 'พิกัด',
                translation: 'การแปล',
                enabled: 'เปิดใช้งาน',
                disabled: 'ปิดใช้งาน',
                close: 'ปิด',
                refresh: 'รีเฟรช',
                mapView: 'มุมมองแผนที่',
                viewOnGoogleMaps: 'ดูบน Google Maps',
                latitude: 'ละติจูด',
                longitude: 'ลองจิจูด'
            }
        };

        const getLanguageText = async () => {
            try {
                const currentLang = await getRealTimeLanguage();
                return translations[currentLang] || translations['zh-CN'];
            } catch (error) {
                console.error('Failed to get language:', error);
                return translations['zh-CN'];
            }
        };

        const modalOverlay = document.createElement('div');
        modalOverlay.className = 'ip-modal-overlay';
        modalOverlay.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 10000;
            opacity: 0;
            transition: opacity 0.3s ease;
        `;
    
        const modalContent = document.createElement('div');
        modalContent.className = 'ip-modal-content';
        modalContent.style.cssText = `
            background: var(--bg-container);
            border-radius: 12px;
            width: 90%;
            max-width: 800px;
            max-height: 80vh;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            transform: scale(0.9);
            transition: transform 0.3s ease;
        `;

        modalContent.innerHTML = `
            <div class="ip-modal-header" style="
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
                color: white;
                padding: 20px;
                display: flex;
                justify-content: space-between;
                align-items: center;
            ">
                <h3 style="margin: 0; font-size: 1.2em;">
                    <span style="margin-right: 10px;">📋</span>
                    Loading...
                </h3>
                <button class="ip-modal-close" style="
                    background: none;
                    border: none;
                    color: white;
                    font-size: 1.5em;
                    cursor: pointer;
                    padding: 0;
                    width: 30px;
                    height: 30px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                ">×</button>
            </div>
        
            <div class="ip-modal-body" style="padding: 20px; max-height: 60vh; overflow-y: auto;">
                <div style="text-align: center; padding: 40px;">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        `;
    
        modalOverlay.appendChild(modalContent);
        document.body.appendChild(modalOverlay);
    
        setTimeout(() => {
            modalOverlay.style.opacity = '1';
            modalContent.style.transform = 'scale(1)';
        }, 10);

        getLanguageText().then(langText => {
            modalContent.innerHTML = `
                <div class="ip-modal-header" style="
                    background: var(--header-bg); 
                    color: white;
                    padding: 20px;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                ">
                    <h3 style="margin: 0; font-size: 1.3em;  font-weight:600; background:none; border:none; color:var(--accent-color);">
                        <span style="margin-right: 10px;"><i class="bi bi-clipboard"></i></span>
                        ${langText.title}
                    </h3>
                    <button class="ip-modal-close" style="
                        background:white;
                        border:none;
                        color:var(--accent-color);
                        font-size:1.1em;
                        cursor:pointer;
                        width:25px;
                        height:25px;
                        border-radius:6px;
                        display:flex;
                        align-items:center;
                        justify-content:center;
                        transition: transform 0.2s, color 0.2s;
                    "
                    onmouseover="this.firstElementChild.style.color='#1a73e8'; this.style.transform='scale(1.1)';"
                    onmouseout="this.firstElementChild.style.color='#4a6cf7'; this.style.transform='scale(1)';">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            
                <div class="ip-modal-body" style="padding: 20px; max-height: 60vh; overflow-y: auto;">
                    <div style="display: flex; margin-bottom: 20px;">
                        <div style="flex: 1; text-align: center;">
                            <img src="${_IMG}flags/${(data.country_code || 'unknown').toLowerCase()}.svg" 
                                style="max-height: 80px;" 
                                onerror="this.src='/luci-static/ipip/flags/unknown.svg'">
                            <div style="margin-top: 10px; font-weight: bold; color: var(--text-primary);">${country}</div>
                        </div>
                        <div style="flex: 2; margin-left: 20px;">
                            <div class="detail-item" style="margin-bottom: 10px; display: flex; gap: 20px;">
                                <span style="width: 180px; font-weight: 500;"><i class="bi bi-globe2"></i>  ${langText.ip}:</span>
                                <span style="flex: 1; word-break: break-word;">${cachedIP}</span>
                            </div>
                            <div class="detail-item" style="margin-bottom: 10px; display: flex; gap: 20px;">
                                <span style="width: 180px; font-weight: 500;"><i class="bi bi-geo-alt"></i>  ${langText.location}:</span>
                                <span style="flex: 1;">${locationDisplay}</span>
                            </div>
                            <div class="detail-item" style="margin-bottom: 10px; display: flex; gap: 20px;">
                                <span style="width: 180px; font-weight: 500;"><i class="bi bi-building"></i>  ${langText.isp}:</span>
                                <span style="flex: 1;">${isp}</span>
                            </div>
                            <div class="detail-item" style="margin-bottom: 10px; display: flex; gap: 20px;">
                                <span style="width: 180px; font-weight: 500;"><i class="bi bi-link-45deg"></i>  ${langText.asn}:</span>
                                <span style="flex: 1;">${data.asn || ''} ${asnOrg}</span>
                            </div>
                            <div class="detail-item" style="margin-bottom: 10px; display: flex; gap: 20px;">
                                <span style="width: 180px; font-weight: 500;"><i class="bi bi-flag"></i>  ${langText.countryCode}:</span>
                                <span style="flex: 1;">${countryCode}</span>
                            </div>
                            <div class="detail-item" style="margin-bottom: 10px; display: flex; gap: 20px;">
                                <span style="width: 180px; font-weight: 500;"><i class="bi bi-clock"></i>  ${langText.timezone}:</span>
                                <span style="flex: 1;">${timezone}</span>
                            </div>
                            <div class="detail-item" style="margin-bottom: 10px; display: flex; gap: 20px;">
                                <span style="width: 180px; font-weight: 500;"><i class="bi bi-geo"></i>  ${langText.coordinates}:</span>
                                <span style="flex: 1;">
                                    ${latitude !== 'N/A' && longitude !== 'N/A' ? 
                                        `${latitude}, ${longitude}` : 
                                        'N/A'}
                                </span>
                            </div>
                            <div class="detail-item" style="margin-bottom: 10px; display: flex; gap: 20px;">
                                <span style="width: 180px; font-weight: 500; color: #6c757d;"><i class="bi bi-translate"></i>  ${langText.translation}:</span>
                                <span style="flex: 1;">
                                    <span style="
                                        padding: 4px 8px;
                                        border-radius: 4px;
                                        font-size: 0.9em;
                                        background: ${translationEnabled ? '#28a745' : '#6c757d'};
                                        color: white !important;
                                    ">
                                        ${translationEnabled ? langText.enabled : langText.disabled}
                                    </span>
                                </span>
                            </div>
                        </div>
                    </div>

                    ${latitude !== 'N/A' && longitude !== 'N/A' ? `
                    <div style="margin-top: 20px; padding: 15px; background: var(--card-bg); border-radius: 8px;">
                        <div style="display: flex; align-items: center; margin-bottom: 10px;">
                            <span style="font-weight: 500; color: var(--text-primary); margin-right: 10px;"><i class="bi bi-map"></i>  ${langText.mapView}:</span>
                            <a href="https://maps.google.com/?q=${latitude},${longitude}" 
                               target="_blank" 
                               style="color: var(--accent-color); text-decoration: none;"
                               onmouseover="this.style.textDecoration='underline'" 
                               onmouseout="this.style.textDecoration='none'">
                                ${langText.viewOnGoogleMaps}
                            </a>
                        </div>
                        <div style="font-size: 0.9em; color: var(--text-primary);">
                            ${langText.latitude}: ${latitude}, ${langText.longitude}: ${longitude}
                        </div>
                    </div>
                    ` : ''}
                </div>
            
                <div class="ip-modal-footer" style="
                    padding: 15px 20px;
                    background: var(--header-bg);
                    display: flex;
                    justify-content: flex-end;
                    border-top: 1px solid var(--header-bg);
                ">

                    <button class="cbi-button cbi-button-remove">${langText.close}</button>
                    <button class="cbi-button cbi-button-apply">${langText.refresh}</button>
                </div>
            `;

            setupModalEvents(modalOverlay, modalContent);
        }).catch(error => {
            console.error('Failed to load language:', error);
        });

        const setupModalEvents = (overlay, content) => {
            const controlPanel = document.getElementById('control-panel-modal');
            if (controlPanel) {
                try {
                    UIkit.modal(controlPanel).hide();
                } catch (e) {}
            }

            const closeBtn = content.querySelector('.ip-modal-close');
            const closeModalBtn = content.querySelector('.cbi-button-remove');
        
            const closeModal = () => {
                overlay.style.opacity = '0';
                content.style.transform = 'scale(0.9)';
                setTimeout(() => {
                    if (overlay.parentNode) {
                        overlay.parentNode.removeChild(overlay);
                    }
                }, 300);
            };
        
            closeBtn.addEventListener('click', closeModal);
            closeModalBtn?.addEventListener('click', closeModal);
        
            overlay.addEventListener('click', (e) => {
                if (e.target === overlay) {
                    closeModal();
                }
            });

            const refreshBtn = content.querySelector('.cbi-button-apply');
            refreshBtn?.addEventListener('click', () => {
                closeModal();
                setTimeout(() => {
                    this.getIpipnetIP();
                }, 300);
            });
        };

        setupModalEvents(modalOverlay, modalContent);
    },
};

document.addEventListener('DOMContentLoaded', function() {
    setupLanguageChangeListener();
    
    const flagElement = document.getElementById('flag');
    if (flagElement) {
        flagElement.addEventListener('click', () => myAppIP.getIpipnetIP());
    }
    
    myAppIP.getIpipnetIP();
    setInterval(() => myAppIP.getIpipnetIP(), 180000);
});

function pingHost(id, name, url) {    
    const translations = languageTranslations[currentLang] || languageTranslations['zh'];
    const normal = document.getElementById(`${id}-normal`);
    const gray = document.getElementById(`${id}-gray`);
    const overlay = document.getElementById('result-overlay');
    const start = performance.now();

    fetch(url, { mode: 'no-cors' })
        .then(() => {
            const end = performance.now();
            const ping = Math.round(end - start);
            normal.style.display = 'inline';
            gray.style.display = 'none';

            let color = '#00c800';
            if (ping > 300) {
                color = 'red';
            } else if (ping > 150) {
                color = 'orange';
            }

            overlay.innerHTML = `${name} ${translations.ping}: ${ping} ms`;
            overlay.style.color = color;
            overlay.style.display = 'block';
            clearTimeout(overlay.timer);
            overlay.timer = setTimeout(() => {
                overlay.style.display = 'none';
            }, 5000);
        })
        .catch(() => {
            normal.style.display = 'none';
            gray.style.display = 'inline';
            overlay.innerHTML = `${name} ${translations.timeout}`;
            overlay.style.color = 'red';
            overlay.style.display = 'block';
            clearTimeout(overlay.timer);
            overlay.timer = setTimeout(() => {
                overlay.style.display = 'none';
            }, 5000);
        });
}

function checkAllSites() {
    const sites = [
        {id: 'baidu', name: 'Baidu', url: 'https://www.baidu.com'},
        {id: 'taobao', name: 'Taobao', url: 'https://www.taobao.com'},
        {id: 'google', name: 'Google', url: 'https://www.google.com'},
        {id: 'youtube', name: 'YouTube', url: 'https://www.youtube.com'}
    ];
    sites.forEach(site => {
        const normal = document.getElementById(`${site.id}-normal`);
        const gray = document.getElementById(`${site.id}-gray`);
        fetch(site.url, { mode: 'no-cors' })
            .then(() => {
                normal.style.display = 'inline';
                gray.style.display = 'none';
            })
            .catch(() => {
                normal.style.display = 'none';
                gray.style.display = 'inline';
            });
    });
}

window.addEventListener('load', () => {
    checkAllSites();
    setInterval(checkAllSites, 180000);
    setTimeout(() => {
        const elements = document.querySelectorAll('[id*="_status"]');
        
        elements.forEach((element, index) => {
            setTimeout(() => {
                element.click();
            }, index * 1000);
        });
    }, 3000);
});