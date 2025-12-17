const countryMap = {
    '阿富汗': '🇦🇫', '阿尔巴尼亚': '🇦🇱', '阿尔及利亚': '🇩🇿', '阿根廷': '🇦🇷', '阿曼': '🇴🇲',
    '阿塞拜疆': '🇦🇿', '爱尔兰': '🇮🇪', '埃及': '🇪🇬', '埃塞俄比亚': '🇪🇹', '澳大利亚': '🇦🇺',
    '奥地利': '🇦🇹', '巴巴多斯': '🇧🇧', '巴哈马': '🇧🇸', '巴基斯坦': '🇵🇰', '巴拿马': '🇵🇦',
    '巴布亚新几内亚': '🇵🇬', '巴拉圭': '🇵🇾', '巴林': '🇧🇭', '巴西': '🇧🇷', '白俄罗斯': '🇧🇾',
    '保加利亚': '🇧🇬', '北马里亚纳群岛': '🇲🇵', '比利时': '🇧🇪', '冰岛': '🇮🇸', '波兰': '🇵🇱',
    '波多黎各': '🇵🇷', '玻利维亚': '🇧🇴', '博茨瓦纳': '🇧🇼', '丹麦': '🇩🇰', '德国': '🇩🇪',
    '东帝汶': '🇹🇱', '多哥': '🇹🇬', '多米尼加': '🇩🇴', '厄瓜多尔': '🇪🇨', '厄立特里亚': '🇪🇷',
    '法国': '🇫🇷', '法罗群岛': '🇫🇴', '法属圭亚那': '🇬🇫', '芬兰': '🇫🇮', '菲律宾': '🇵🇭',
    '福克兰群岛': '🇫🇰', '冈比亚': '🇬🇲', '刚果（布）': '🇨🇬', '刚果（金）': '🇨🇩', '哥伦比亚': '🇨🇴',
    '哥斯达黎加': '🇨🇷', '格鲁吉亚': '🇬🇪', '格林纳达': '🇬🇩', '关岛': '🇬🇺', '瓜德罗普': '🇬🇵',
    '哈萨克斯坦': '🇰🇿', '海地': '🇭🇹', '韩国': '🇰🇷', '荷兰': '🇳🇱', '荷兰加勒比': '🇧🇶',
    '黑山': '🇲🇪', '洪都拉斯': '🇭🇳', '基里巴斯': '🇰🇮', '吉布提': '🇩🇯', '几内亚': '🇬🇳',
    '几内亚比绍': '🇬🇼', '加拿大': '🇨🇦', '加蓬': '🇬🇦', '柬埔寨': '🇰🇭', '捷克': '🇨🇿',
    '津巴布韦': '🇿🇼', '卡塔尔': '🇶🇦', '喀麦隆': '🇨🇲', '科摩罗': '🇰🇲', '科索沃': '🇽🇰',
    '科威特': '🇰🇼', '肯尼亚': '🇰🇪', '拉脱维亚': '🇱🇻', '莱索托': '🇱🇸', '黎巴嫩': '🇱🇧',
    '利比里亚': '🇱🇷', '利比亚': '🇱🇾', '列支敦士登': '🇱🇮', '立陶宛': '🇱🇹', '卢森堡': '🇱🇺',
    '毛里塔尼亚': '🇲🇷', '马达加斯加': '🇲🇬', '马拉维': '🇲🇼', '马来西亚': '🇲🇾', '马尔代夫': '🇲🇻',
    '马里': '🇲🇱', '马耳他': '🇲🇹', '马绍尔群岛': '🇲🇭', '马提尼克': '🇲🇶', '毛里求斯': '🇲🇺',
    '蒙古': '🇲🇳', '美国': '🇺🇸', '美属维尔京群岛': '🇻🇮', '密克罗尼西亚': '🇫🇲', '缅甸': '🇲🇲',
    '南非': '🇿🇦', '南苏丹': '🇸🇸', '尼泊尔': '🇳🇵', '尼日尔': '🇳🇪', '尼日利亚': '🇳🇬',
    '挪威': '🇳🇴', '诺福克岛': '🇳🇫', '帕劳': '🇵🇼', '葡萄牙': '🇵🇹', '日本': '🇯🇵',
    '瑞典': '🇸🇪', '瑞士': '🇨🇭', '萨尔瓦多': '🇸🇻', '塞尔维亚': '🇷🇸', '塞拉利昂': '🇸🇱',
    '塞舌尔': '🇸🇨', '沙特阿拉伯': '🇸🇦', '圣基茨和尼维斯': '🇰🇳', '圣卢西亚': '🇱🇨', '圣马力诺': '🇸🇲',
    '圣多美和普林西比': '🇸🇹', '圣文森特和格林纳丁斯': '🇻🇨', '斯里兰卡': '🇱🇰', '斯洛伐克': '🇸🇰',
    '斯洛文尼亚': '🇸🇮', '斯威士兰': '🇸🇿', '所罗门群岛': '🇸🇧', '苏丹': '🇸🇩', '苏里南': '🇸🇷',
    '台湾': '🇹🇼', '坦桑尼亚': '🇹🇿', '泰国': '🇹🇭', '汤加': '🇹🇴', '土耳其': '🇹🇷',
    '土库曼斯坦': '🇹🇲', '突尼斯': '🇹🇳', '图瓦卢': '🇹🇻', '瓦努阿图': '🇻🇺', '危地马拉': '🇬🇹',
    '乌干达': '🇺🇬', '乌克兰': '🇺🇦', '乌拉圭': '🇺🇾', '乌兹别克斯坦': '🇺🇿', '西班牙': '🇪🇸',
    '希腊': '🇬🇷', '新加坡': '🇸🇬', '新喀里多尼亚': '🇳🇨', '新西兰': '🇳🇿', '匈牙利': '🇭🇺',
    '叙利亚': '🇸🇾', '牙买加': '🇯🇲', '伊朗': '🇮🇷', '伊拉克': '🇮🇶', '意大利': '🇮🇹',
    '以色列': '🇮🇱', '印度': '🇮🇳', '印度尼西亚': '🇮🇩', '英国': '🇬🇧', '约旦': '🇯🇴',
    '泽西': '🇯🇪', '赞比亚': '🇿🇲', '乍得': '🇹🇩', '智利': '🇨🇱', '中非共和国': '🇨🇫',
    '中国': '🇨🇳', '直布罗陀': '🇬🇮', '台湾': '🇹🇼', '朱诺': '🇯🇪', '香港': '🇭🇰', '澳门': '🇲🇴',
    '安道尔': '🇦🇩', '安哥拉': '🇦🇴', '安提瓜和巴布达': '🇦🇬', '亚美尼亚': '🇦🇲', '孟加拉国': '🇧🇩',
    '伯利兹': '🇧🇿', '贝宁': '🇧🇯', '不丹': '🇧🇹', '波斯尼亚和黑塞哥维那': '🇧🇦', '文莱': '🇧🇳',
    '布基纳法索': '🇧🇫', '布隆迪': '🇧🇮', '佛得角': '🇨🇻', '哥斯达黎加': '🇨🇷', '科特迪瓦': '🇨🇮',
    '克罗地亚': '🇭🇷', '古巴': '🇨🇺', '塞浦路斯': '🇨🇾', '多米尼克': '🇩🇲', '多明尼加共和国': '🇩🇴',
    '萨尔瓦多': '🇸🇻', '赤道几内亚': '🇬🇶', '厄立特里亚': '🇪🇷', '爱沙尼亚': '🇪🇪', '斐济': '🇫🇯',
    '加纳': '🇬🇭', '格林纳达': '🇬🇩', '圭亚那': '🇬🇾', '洪都拉斯': '🇭🇳', '冰岛': '🇮🇸',
    '约旦': '🇯🇴', '肯尼亚': '🇰🇪', '基里巴斯': '🇰🇮', '科威特': '🇰🇼', '吉尔吉斯斯坦': '🇰🇬',
    '老挝': '🇱🇦', '拉脱维亚': '🇱🇻', '黎巴嫩': '🇱🇧', '莱索托': '🇱🇸', '利比里亚': '🇱🇷',
    '利比亚': '🇱🇾', '列支敦士登': '🇱🇮', '立陶宛': '🇱🇹', '卢森堡': '🇱🇺', '马达加斯加': '🇲🇬',
    '马拉维': '🇲🇼', '马来西亚': '🇲🇾', '马尔代夫': '🇲🇻', '马里': '🇲🇱', '马耳他': '🇲🇹',
    '马绍尔群岛': '🇲🇭', '毛里塔尼亚': '🇲🇷', '毛里求斯': '🇲🇺', '墨西哥': '🇲🇽', '密克罗尼西亚': '🇫🇲',
    '摩尔多瓦': '🇲🇩', '摩纳哥': '🇲🇨', '蒙古': '🇲🇳', '黑山': '🇲🇪', '摩洛哥': '🇲🇦',
    '莫桑比克': '🇲🇿', '缅甸': '🇲🇲', '纳米比亚': '🇳🇦', '瑙鲁': '🇳🇷', '尼泊尔': '🇳🇵',
    '荷兰': '🇳🇱', '新西兰': '🇳🇿', '尼加拉瓜': '🇳🇮', '尼日尔': '🇳🇪', '尼日利亚': '🇳🇬',
    '北马其顿': '🇲🇰', '挪威': '🇳🇴', '阿曼': '🇴🇲', '巴基斯坦': '🇵🇰', '帕劳': '🇵🇼',
    '巴拿马': '🇵🇦', '巴布亚新几内亚': '🇵🇬', '巴拉圭': '🇵🇾', '秘鲁': '🇵🇪', '菲律宾': '🇵🇭',
    '波兰': '🇵🇱', '葡萄牙': '🇵🇹', '卡塔尔': '🇶🇦', '罗马尼亚': '🇷🇴', '俄罗斯联邦': '🇷🇺',
    '卢旺达': '🇷🇼', '圣基茨和尼维斯': '🇰🇳', '圣卢西亚': '🇱🇨', '圣文森特和格林纳丁斯': '🇻🇨', '萨摩亚': '🇼🇸',
    '圣马力诺': '🇸🇲', '圣多美和普林西比': '🇸🇹', '沙特阿拉伯': '🇸🇦', '塞内加尔': '🇸🇳', '塞尔维亚': '🇷🇸',
    '塞舌尔': '🇸🇨', '塞拉利昂': '🇸🇱', '新加坡': '🇸🇬', '斯洛伐克': '🇸🇰', '斯洛文尼亚': '🇸🇮',
    '所罗门群岛': '🇸🇧', '索马里': '🇸🇴', '南非': '🇿🇦', '韩国': '🇰🇷', '南苏丹': '🇸🇸',
    '西班牙': '🇪🇸', '斯里兰卡': '🇱🇰', '苏丹': '🇸🇩', '苏里南': '🇸🇷', '瑞典': '🇸🇪',
    '瑞士': '🇨🇭', '叙利亚': '🇸🇾', '塔吉克斯坦': '🇹🇯', '坦桑尼亚': '🇹🇿', '泰国': '🇹🇭',
    '东帝汶': '🇹🇱', '多哥': '🇹🇬', '汤加': '🇹🇴', '特立尼达和多巴哥': '🇹🇹', '突尼斯': '🇹🇳',
    '土耳其': '🇹🇷', '土库曼斯坦': '🇹🇲', '图瓦卢': '🇹🇻', '乌干达': '🇺🇬', '乌克兰': '🇺🇦',
    '阿拉伯联合酋长国': '🇦🇪', '英国': '🇬🇧', '美国': '🇺🇸', '乌拉圭': '🇺🇾', '乌兹别克斯坦': '🇺🇿',
    '瓦努阿图': '🇻🇺', '委内瑞拉': '🇻🇪', '越南': '🇻🇳', '也门': '🇾🇪', '赞比亚': '🇿🇲',
    '津巴布韦': '🇿🇼'
};

function updateCardLayout() {
    const cardsContainer = document.querySelector('.cards-container');
    if (!cardsContainer) return;
    
    const width = window.innerWidth;
    let columns;
    
    if (width >= 2300) {
        columns = 5;
    } else if (width >= 1800) {
        columns = 4;
    } else if (width >= 1400) {
        columns = 3;
    } else if (width >= 992) {
        columns = 2;
    } else if (width >= 768) {
        columns = 2;
    } else {
        columns = 1;
    }
    
    cardsContainer.style.gridTemplateColumns = `repeat(${columns}, 1fr)`;
}

function addFlagIfMissing(titleText) {
    if (/[\u{1F1E6}-\u{1F1FF}]{2}/u.test(titleText)) {
        return titleText;
    }
    for (const name in countryMap) {
        if (titleText.includes(name)) {
            return countryMap[name] + ' ' + titleText;
        }
    }
    return titleText;
}

function wrapEmojiWithSpan(text) {
    return text.replace(/([\u{1F1E6}-\u{1F1FF}]{2})/gu, '<span class="flag">$1</span>');
}

function extractProtocolFromTitle(titleText) {
    const match = titleText.match(/^([^\s：]+)\s*([^\s：]+)：\s*([\s\S]*)$/);
    if (match) {
        return {
            protocol: match[2],
            actualTitle: match[3] || ''
        };
    }
    return {
        protocol: '',
        actualTitle: titleText
    };
}

function getNodeTypeFromTitle(titleText) {
    if (titleText.includes('Sing-Box')) return 'Sing-Box';
    if (titleText.includes('Xray')) return 'Xray';
    return '';
}

function getProtocolBadge(protocol) {
    const badgeColors = {
        'HY2': '#ff6b6b',
        'HY': '#ffa726',
        'VMess': '#42a5f5',
        'VLESS': '#5c6bc0',
        'SS': '#66bb6a',
        'SSR': '#4caf50',
        'WG': '#ab47bc',
        'Trojan': '#ff69b4',
        'URLTest': '#7e57c2',
        'Balancing': '#ef5350',
        'Shunt': '#26a69a'
    };
    const badgeTexts = {
        'HY2': 'HY2',
        'HY': 'HY',
        'VMess': 'VMess',
        'VLESS': 'VLESS',
        'SS': 'SS',
        'SSR': 'SSR',
        'WG': 'WG',
        'Trojan': 'Trojan',
        'URLTest': 'Test',
        'Balancing': 'Balance',
        'Shunt': 'Shunt'
    };
    const color = badgeColors[protocol] || '#000';
    const text = badgeTexts[protocol] || protocol;
    return `<span class="card-badge" style="background-color: ${color}">${text}</span>`;
}

function rebindURLEvents(container) {
    container.querySelectorAll('.metric-item:nth-child(3) a').forEach(link => {
        const originalOnClick = link.getAttribute('onclick');
        if (originalOnClick && originalOnClick.includes('urltest_node')) {
            link.removeAttribute('onclick');
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const match = originalOnClick.match(/urltest_node\('([^']+)', this\)/);
                if (match) {
                    const cbiId = match[1];
                    urltest_node(cbiId, this);
                }
            });
        }
    });
}

function convertTablesToCards() {
    const activeContainer = document.querySelector('.cbi-tabcontainer[style*="display: block"], .cbi-tabcontainer[style*="display:block"]');
    if (!activeContainer) return;
    if (activeContainer.getAttribute('data-cards-converted') === 'true') return;

    const table = activeContainer.querySelector('.cbi-section-table');
    if (!table) return;

    const rows = table.querySelectorAll('tr.cbi-section-table-row');
    if (!rows.length) return;

    const cardsContainer = document.createElement('div');
    cardsContainer.className = 'cards-container';
    
    const group = table.id.replace('cbi-passwall-nodes-', '').replace('-table', '');
    cardsContainer.setAttribute('data-group', group);

    rows.forEach(row => {
        const card = document.createElement('div');
        card.className = 'node-card';
        card.setAttribute('data-id', row.id.replace('cbi-passwall-', ''));
        if (row.classList.contains('_now_use_bg')) card.classList.add('_now_use_bg');

        const checkboxCell = row.querySelector('td.pw-checkbox');
        const originalCheckbox = checkboxCell ? checkboxCell.querySelector('.nodes_select') : null;
        
        const tds = row.querySelectorAll('td');
        const originalTitle = tds[1]?.textContent || '';
        const ping = tds[2]?.innerHTML || '';
        const tcping = tds[3]?.innerHTML || '';
        const urlTest = tds[4]?.innerHTML || '';
        const actions = tds[5]?.innerHTML || '';

        const remarksElement = row.querySelector('#cbi-passwall-' + row.id.replace('cbi-passwall-', '') + '-remarks');
        let remarksClass = '';
        if (remarksElement && remarksElement.classList.contains('_now_use')) {
            remarksClass = '_now_use';
        }

        const { protocol, actualTitle } = extractProtocolFromTitle(originalTitle);
        const nodeType = getNodeTypeFromTitle(originalTitle);

        const titleWithFlag = addFlagIfMissing(actualTitle);

        let badgeHtml = protocol ? getProtocolBadge(protocol) : '';
        if (nodeType && badgeHtml) {
            badgeHtml = badgeHtml.replace('<span class="card-badge"', `<span class="card-badge" title="${nodeType}"`);
        }

        let checkboxHtml = '';
        if (originalCheckbox) {
            checkboxHtml = checkboxCell.innerHTML;
        }

        card.innerHTML = `
            ${checkboxHtml}
            <div class="card-header ${remarksClass}">
                ${wrapEmojiWithSpan(titleWithFlag)}
                ${badgeHtml}
            </div>
            <div class="card-metrics">
                <div class="metric-item">Ping: ${ping}</div>
                <div class="metric-item">TCPing: ${tcping}</div>
                <div class="metric-item">URL: ${urlTest}</div>
            </div>
            <div class="card-actions">${actions}</div>
        `;

        cardsContainer.appendChild(card);
    });

    table.style.display = 'none';
    table.parentNode.insertBefore(cardsContainer, table);
    activeContainer.setAttribute('data-cards-converted', 'true');
    
    updateCardLayout();
    
    initCardSortable(cardsContainer, group);
    rebindURLEvents(cardsContainer);
}

window.addEventListener('resize', updateCardLayout);

function initCardSortable(container, group) {
    if (typeof Sortable === 'undefined') return;
    
    const isSmallScreen = window.innerWidth < 768;
    
    if (isSmallScreen) {
        const dragHandles = container.querySelectorAll('.drag-handle');
        dragHandles.forEach(handle => {
            handle.style.display = 'none';
        });
        return;
    }
    
    try {
        Sortable.create(container, {
            handle: ".node-card, .drag-handle",
            filter: ".metric-item, .metric-item *",
            animation: 150,
            ghostClass: "sortable-ghost",
            chosenClass: "sortable-chosen",
            dragClass: "dragging-row",
            
            onStart: function(evt) {
                showSaveButtonAtCorner(group);
            },
            
            onEnd: function(evt) {
                setTimeout(() => {
                    hideSaveButtonFromCorner(group);
                }, 2500);
                
                saveCardOrder(group);
            }
        });
    } catch (err) {
        console.error('Sortable init failed for cards:', err);
    }
}

function showSaveButtonAtCorner(group) {
    const saveBtn = document.getElementById("save_order_btn_" + group);
    if (!saveBtn) return;
    
    if (saveBtn.classList.contains('corner-showing')) return;
    
    if (!saveBtn.getAttribute('data-original-style')) {
        saveBtn.setAttribute('data-original-style', saveBtn.style.cssText);
    }
    
    saveBtn.classList.add('corner-showing');
    saveBtn.style.cssText = `
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 9999;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 25px;
        cursor: pointer;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        font-weight: bold;
        font-size: 14px;
        display: block !important;
        transition: all 0.3s ease;
        min-width: 120px;
        text-align: center;
        animation: slideInUp 0.3s ease;
    `;
}

function hideSaveButtonFromCorner(group) {
    const saveBtn = document.getElementById("save_order_btn_" + group);
    if (!saveBtn) return;
    
    saveBtn.classList.remove('corner-showing');
    
    const originalStyle = saveBtn.getAttribute('data-original-style');
    if (originalStyle) {
        saveBtn.style.cssText = originalStyle;
    } else {
        saveBtn.style.cssText = '';
    }
    
    saveBtn.style.display = 'none';
}

if (!document.querySelector('#save-button-animation-style')) {
    const style = document.createElement('style');
    style.id = 'save-button-animation-style';
    style.textContent = `
        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    `;
    document.head.appendChild(style);
}

function initAllTabs() {
    document.querySelectorAll('.cbi-tabcontainer').forEach(container => {
        container.removeAttribute('data-cards-converted');
        const oldCards = container.querySelector('.cards-container');
        if (oldCards) oldCards.remove();
    });
    convertTablesToCards();
}

document.addEventListener('DOMContentLoaded', () => setTimeout(initAllTabs, 100));
document.addEventListener('click', e => {
    const tabLink = e.target.closest('.cbi-tab');
    if (tabLink && tabLink.querySelector('a')) setTimeout(initAllTabs, 200);
});

const observer = new MutationObserver(mutations => {
    mutations.forEach(mutation => {
        if (mutation.type === 'attributes' && mutation.attributeName === 'style') {
            const target = mutation.target;
            if (target.classList.contains('cbi-tabcontainer') && target.style.display.includes('block')) {
                setTimeout(initAllTabs, 100);
            }
        }
    });
});

observer.observe(document.body, {
    attributes: true,
    subtree: true,
    attributeFilter: ['style']
});

const cardLayoutCSS = `
.cbi-tabcontainer[style*="display: block"] .cbi-section-table,
.cbi-tabcontainer[style*="display:block"] .cbi-section-table {
    display: none;
}

.cbi-tabcontainer[style*="display: block"] .cards-container,
.cbi-tabcontainer[style*="display:block"] .cards-container {
    background: var(--card-bg);
    display: grid;
    gap: 15px;
    padding: 20px;
}

.cbi-tabcontainer:not([style*="display: block"]):not([style*="display:block"]) .cards-container {
    display: none;
}

.cbi-tabcontainer:not([style*="display: block"]):not([style*="display:block"]) .cbi-section-table {
    display: table;
}

#cbi-passwall-nodes .node-card,
.cbi-section-node-tabbed .node-card {
    flex: 0 0 calc(20% - 15px); 
    background: var(--bg-container);
    border: var(--glow-border);
    box-shadow: 0 1.5px 4.5px -2px color-mix(in oklch, var(--bg-container), black 40%);
    border-radius: 8px;
    padding: 15px;
    transition: all 0.3s ease;
    margin: 0;
    position: relative;
    box-sizing: border-box;
}

[data-theme="dark"] #cbi-passwall-nodes .node-card,
[data-theme="dark"] .cbi-section-node-tabbed .node-card {
    box-shadow: var(--border-glow);
}

#cbi-passwall-nodes .node-card:hover,
.cbi-section-node-tabbed .node-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--glow-shadow-default);
}

#cbi-passwall-nodes .card-header,
.cbi-section-node-tabbed .card-header {
    font-weight: bold;
    font-size: 14px;
    margin-bottom: 10px;
    color: var(--text-primary);
    line-height: 1.4;
    min-height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    background: transparent;
    border: none;
    padding: 0;
}

.card-header .flag {
    font-family: "NotoColorEmojiFlags";
    margin-right: 6px;
    font-size: 20px;
    line-height: 1;
}

.card-header .card-badge {
    position: absolute;
    top: 15px;
    right: 15px;
    color: #fff;
    font-size: 10px;
    padding: 0 10px;
    border-radius: 12px;
    cursor: default;
    min-width: 20px;
    text-align: center;
    height: 18px;
    line-height: 18px;
    display: inline-block;
    box-sizing: border-box;
    vertical-align: middle;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.card-header .card-badge:hover {
    transform: scale(1.05);
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
}

.metric-item,
.metric-item * {
    cursor: default !important;
    user-select: text !important;
    pointer-events: auto !important;
}

#cbi-passwall-nodes .card-metrics,
.cbi-section-node-tabbed .card-metrics {
    display: flex;
    justify-content: space-between;
    margin-bottom: 12px;
    gap: 5px;
    flex-wrap: wrap;
    animation: var(--breath-animation);
}

#cbi-passwall-nodes .metric-item,
.cbi-section-node-tabbed .metric-item {
    flex: 1;
    text-align: center;
    padding: 5px;
    background: var(--card-bg);
    border-radius: 4px;
    font-size: 12px;
    border: var(--border-strong);
    min-width: 0;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

#cbi-passwall-nodes .metric-item:hover,
.cbi-section-node-tabbed .metric-item:hover {
    transform: scale(1.05);
    box-shadow: var(--glow-shadow-default);
}

#cbi-passwall-nodes .card-actions,
.cbi-section-node-tabbed .card-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
    justify-content: center;
    background: transparent;
    border: none;
    padding: 0;
}

.node-card {
    cursor: grab;
    transition: all 0.3s ease;
}

.node-card:active {
    cursor: grabbing;
}

.node-card:hover {
    background: var(--header-bg) !important;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.node-wrapper .drag-handle {
    color: var(--text-primary) !important;
    margin-left: 5px;
}

#nodes_link {
    background: var(--card-bg);
    border: var(--border-strong);
    border-radius: 10px;
    color: var(--text-primary);
}

[data-theme="dark"] #nodes_link {
    box-shadow: var(--border-glow);
}

#addlink_group_custom .selected-display {
    background: var(--card-bg);
}

#addlink_group_custom .dropdown-list {
    background: var(--card-bg);
}

#addlink_group_custom .dropdown-item:hover {
    background: var(--accent-color);
    color: var(--color-white);
}

.config-select option {
    background: var(--card-bg);
}

.node-card .card-actions input[onclick*="row_top"] {
    display: none !important;
}

.cbi-button-add[onclick="to_add_node()"] {
    position: fixed;
    right: 25px;
    bottom: 20px;
    width: 40px !important;
    height: 40px !important;
    padding: 0;
    border-radius: 50%;
    background-color: var(--btn-primary-bg) !important;
    font-size: 0 !important;
    color: transparent !important;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='white' viewBox='0 0 16 16'%3E%3Cpath d='M8 1a.5.5 0 0 1 .5.5V7.5H14.5a.5.5 0 0 1 0 1H8.5V14.5a.5.5 0 0 1-1 0V8.5H1.5a.5.5 0 0 1 0-1H7.5V1.5A.5.5 0 0 1 8 1z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: center;
    background-size: 20px 20px;
    transition: all var(--transition-speed) ease;
}

.cbi-button-add[onclick="to_add_node()"]:hover {
    background-color: var(--btn-primary-hover) !important;
    transform: scale(1.1);
}

@media (max-width: 786px) {
    .cbi-tabcontainer[style*="display: block"] .cards-container,
    .cbi-tabcontainer[style*="display:block"] .cards-container {
        background: transparent;
        border: none;
        display: block;
        gap: 12px;
        padding: 10px 8px;
        margin: 0;
        width: 100%;
        box-sizing: border-box;
        overflow-x: hidden;
    }
    
    #cbi-passwall-nodes .node-card,
    .cbi-section-node-tabbed .node-card {
        flex: 0 0 100%;
        width: 100%;
        margin: 0 0 12px 0;
        padding: 12px;
        box-sizing: border-box;
        min-height: auto;
    }
    
    #cbi-passwall-nodes .card-header,
    .cbi-section-node-tabbed .card-header {
        min-height: auto;
        font-size: 14px;
        padding: 0 4px;
        text-align: left;
        justify-content: flex-start;
        word-break: break-word;
        line-height: 1.3;
        margin-bottom: 8px;
    }
    
    #cbi-passwall-nodes .card-metrics,
    .cbi-section-node-tabbed .card-metrics {
        flex-direction: column;
        gap: 6px;
        margin-bottom: 10px;
        padding: 0;
    }
    
    #cbi-passwall-nodes .metric-item,
    .cbi-section-node-tabbed .metric-item {
        text-align: left;
        padding: 6px 8px;
        font-size: 12px;
        margin: 0;
        min-width: auto;
        white-space: normal;
        line-height: 1.2;
    }
    
    #cbi-passwall-nodes .card-actions,
    .cbi-section-node-tabbed .card-actions {
        justify-content: space-around;
        gap: 8px;
        padding: 0;
        min-height: 36px;
    }
    
    .node-wrapper .cbi-button {
        min-width: 36px;
        min-height: 36px;
        font-size: 12px;
    }
    
    .drag-handle {
        display: none;
    }
    
    .card-header .card-badge {
        top: 8px;
        right: 8px;
        font-size: 9px;
        padding: 1px 4px;
    }
}
`;

function injectCardLayoutCSS() {
    if (!document.getElementById('card-layout-css')) {
        const style = document.createElement('style');
        style.id = 'card-layout-css';
        style.textContent = cardLayoutCSS;
        document.head.appendChild(style);
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', injectCardLayoutCSS);
} else {
    injectCardLayoutCSS();
}