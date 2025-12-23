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

        let isHighlighted = false;
        
        if (row.classList.contains('_now_use_bg')) {
            isHighlighted = true;
        }

        if (isHighlighted) {
            card.classList.add('_now_use_bg');
        }

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
        const titleText = wrapEmojiWithSpan(titleWithFlag);
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = titleText;
        const plainText = tempDiv.textContent || tempDiv.innerText || '';
        const titleAttr = `title="${plainText}"`;

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
            <div class="card-header ${remarksClass}" ${titleAttr}>
                ${titleText}
                ${badgeHtml}
            </div>
            <div class="card-metrics">
                <div class="metric-item">Ping ${ping}</div>
                <div class="metric-item">TCPing ${tcping}</div>
                <div class="metric-item">URL ${urlTest}</div>
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

(function() {
    const originalXHR = window.XHR;
    if (originalXHR && originalXHR.get) {
        const originalGet = originalXHR.get;
        window.XHR.get = function(url, data, callback) {
            return originalGet.call(this, url, data, function(xhr, result) {
                if (callback) callback(xhr, result);
                
                if (url.includes('get_now_use_node')) {
                    setTimeout(() => {
                        if (!document.querySelector('.cards-container')) {
                            initAllTabs();
                        } else {
                            const activeContainer = document.querySelector('.cbi-tabcontainer[style*="display: block"], .cbi-tabcontainer[style*="display:block"]');
                            if (activeContainer) {
                                activeContainer.removeAttribute('data-cards-converted');
                                const oldCards = activeContainer.querySelector('.cards-container');
                                if (oldCards) oldCards.remove();
                                setTimeout(convertTablesToCards, 100);
                            }
                        }
                    }, 300);
                }
            });
        };
    }
})();

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

.cards-container {
    display: grid;
    gap: 16px;
    padding: 2px 14px 16px 0;
    margin-left: 0;
    width: calc(100% - 14px);
    box-sizing: border-box;
    overflow: visible !important;
}

@media screen and (min-width: 2300px) {
    .cards-container {
        grid-template-columns: repeat(5, 1fr);
        gap: 20px;
        padding: 2px 17px 20px 0;
        width: calc(100% - 17px);
    }
}

@media screen and (min-width: 1800px) and (max-width: 2299px) {
    .cards-container {
        grid-template-columns: repeat(4, 1fr);
        gap: 18px;
        padding: 2px 16px 18px 0;
        width: calc(100% - 16px);
    }
}

@media screen and (min-width: 1400px) and (max-width: 1799px) {
    .cards-container {
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
        padding: 2px 14px 16px 0;
        width: calc(100% - 14px);
    }
}

@media screen and (min-width: 992px) and (max-width: 1399px) {
    .cards-container {
        grid-template-columns: repeat(2, 1fr);
        gap: 14px;
        padding: 2px 16px 14px 0;
        width: calc(100% - 16px);
    }
}

@media screen and (min-width: 768px) and (max-width: 991px) {
    .cards-container {
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
        padding: 2px 10px 12px 0;
        width: calc(100% - 10px);
    }
}

@media screen and (max-width: 767px) {
    .cards-container {
        grid-template-columns: 1fr;
        gap: 10px;
        padding: 2px 16px 10px 0;
        width: calc(100% - 16px);
    }
}

.node-card {
    background: var(--bg-container);
    border: var(--border-strong);
    border-radius: 8px;
    padding: 16px;
    transition: box-shadow 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: visible;
    min-height: 160px;
    display: flex;
    flex-direction: column;
    max-width: 100%;
    overflow: hidden;
    animation: fadeIn 0.2s ease-out;
}

.node-card:hover {
    background: rgba(173, 216, 230, 0.2);
    backdrop-filter: blur(6px);
    -webkit-backdrop-filter: blur(6px);
    box-shadow: var(--shadow-inset);
}

[data-theme="dark"] .node-card {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

[data-theme="dark"] .node-card:hover {
    transform: translateY(-2px);
}

.node-card._now_use_bg {
    background: rgba(173, 216, 230, 0.2) !important;
    border: var(--border-strong) !important;
    backdrop-filter: blur(6px);
    -webkit-backdrop-filter: blur(6px);
}

.card-header {
    font-size: 14px;
    font-weight: 600;
    color: var(--text-primary);
    margin-top: 16px;
    margin-bottom: 12px;
    line-height: 1.4;
    padding-bottom: 8px;
    text-align: center;
    border-bottom: 1px solid var(--header-bg);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 100%;
    min-height: 24px;
    display: block;
    box-sizing: border-box;
}

.card-header .flag {
    font-family: "NotoColorEmojiFlags";
    margin-right: 6px;
    font-size: 20px;
    line-height: 1;
}

.card-badge {
    position: absolute;
    top: 18px;
    left: 50%;
    transform: translateX(-50%);
    display: inline-block;
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 10px;
    font-weight: 600;
    color: white;
    vertical-align: middle;
    line-height: 1;
    text-transform: uppercase;
    letter-spacing: 0.2px;
    box-shadow: var(--shadow-inset);
    border: var(--border-strong);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    margin: 0;
    z-index: 1;
}

.card-badge:hover {
    transform: translateX(-50%) translateY(-1px);
}

.card-metrics {
    flex: 1;
    margin: 12px 0;
    display: flex;
    gap: 8px;
    justify-content: center;
    align-items: stretch;
    flex-wrap: nowrap;
}

.metric-item a {
    display: inline-block;
    background: rgba(94, 114, 228, 0.1);
    color: #5e72e4 !important;
    text-decoration: none !important;
    padding: 2px 8px;
    border-radius: 4px;
    border: 1px solid rgba(94, 114, 228, 0.2);
    font-size: 11px;
    font-weight: 500;
    margin-left: 4px;
    transition: all 0.2s ease;
    line-height: 1.2;
    min-width: 40px;
    text-align: center;
}

.metric-item a:hover {
    background: rgba(94, 114, 228, 0.2);
    color: #4a5bd8 !important;
    border-color: rgba(94, 114, 228, 0.3);
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(94, 114, 228, 0.1);
}

.metric-item:nth-child(1) a {
    background: rgba(66, 153, 225, 0.1);
    color: #4299e1 !important;
    border-color: rgba(66, 153, 225, 0.2);
}

.metric-item:nth-child(1) a:hover {
    background: rgba(66, 153, 225, 0.2);
    color: #3182ce !important;
    border-color: rgba(66, 153, 225, 0.3);
}

.metric-item:nth-child(2) a {
    background: rgba(72, 187, 120, 0.1);
    color: #48bb78 !important;
    border-color: rgba(72, 187, 120, 0.2);
}

.metric-item:nth-child(2) a:hover {
    background: rgba(72, 187, 120, 0.2);
    color: #38a169 !important;
    border-color: rgba(72, 187, 120, 0.3);
}

.metric-item:nth-child(3) a {
    background: rgba(237, 100, 104, 0.1);
    color: #ed6468 !important;
    border-color: rgba(237, 100, 104, 0.2);
}

.metric-item:nth-child(3) a:hover {
    background: rgba(237, 100, 104, 0.2);
    color: #e53e3e !important;
    border-color: rgba(237, 100, 104, 0.3);
}

.metric-item {
    font-size: 12px;
    color: var(--text-primary);
    line-height: 1.3;
    background: color-mix(in oklch, var(--card-bg), transparent 40%);
    border: var(--border-strong);
    border-radius: 6px;
    padding: 6px 10px;
    text-align: center;
    transition: all 0.2s ease;
    display: flex;
    justify-content: space-between;
    align-items: center;
    white-space: nowrap;
    flex: 1;
    min-width: 0;
    transition: box-shadow 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.metric-item:hover {
    background:  color-mix(in oklch, var(--header-bg), transparent 20%);
    border: var(--border-strong);
    box-shadow: var(--shadow-inset);
}

.metric-item > span:first-child:not(a) {
    font-weight: 500;
    color: var(--text-secondary);
}

@media screen and (max-width: 767px) {
    .metric-item a {
        padding: 1px 6px;
        font-size: 10px;
        min-width: 35px;
    }

    .card-actions .btn + .btn {
        margin-left: 8px !important;
    }
}

[data-theme="dark"] .metric-item {
    box-shadow: var(--border-glow);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

[data-theme="dark"] .metric-item:hover {
    transform: translateY(-2px);
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

.card-actions {
    display: flex;
    gap: 8px !important;
    justify-content: center;
    padding-top: 12px;
    border-top: 1px solid var(--header-bg);
    margin-top: 8px;
}

.card-actions .btn {
    font-size: 12px !important;
    padding: 6px 12px !important;
    height: auto !important;
    line-height: 1.3 !important;
    min-width: unset !important;
    width: auto !important;
    display: inline-flex !important;
    color: white !important;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease !important;
    border-radius: 4px !important;
    font-weight: 500 !important;
}

.card-actions .btn:hover {
    transform: translateY(-2px) !important;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1) !important;
    opacity: 0.95 !important;
}

.card-actions .cbi-button-edit {
    background: #4A84B4 !important;
}

.card-actions .cbi-button-apply {
    background: #28a745 !important;
}

.card-actions .cbi-button-add {
    background: #17a2b8 !important;
}

.card-actions .cbi-button-remove {
    background: #dc3545 !important;
}

.drag-handle {
    position: absolute;
    top: 12px;
    right: 12px;
    cursor: grab;
    opacity: 0.6;
    transition: opacity 0.2s, transform 0.2s;
    z-index: 2;
    font-size: 14px;
    transform: translateZ(0);
    will-change: transform;
    backface-visibility: hidden;
}

.drag-handle:hover {
    opacity: 1;
    transform: translateZ(0) scale(1.1);
}

.sortable-ghost {
    opacity: 0.4;
    transform: translateZ(0);
    overflow: visible !important;
}

.sortable-chosen {
    box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
    transform: translateZ(0);
    overflow: visible !important;
    z-index: 1000 !important;
}

.dragging-row {
    cursor: grabbing;
    transform: rotate(1deg) translateZ(0);
    will-change: transform;
    overflow: visible !important;
    z-index: 1000 !important;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(5px) translateZ(0);
    }
    to {
        opacity: 1;
        transform: translateY(0) translateZ(0);
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