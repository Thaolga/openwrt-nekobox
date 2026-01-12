const countryMap = {
    '阿富汗': { flag: '🇦🇫', en: 'Afghanistan', code: 'AF' },
    '阿尔巴尼亚': { flag: '🇦🇱', en: 'Albania', code: 'AL' },
    '阿尔及利亚': { flag: '🇩🇿', en: 'Algeria', code: 'DZ' },
    '安道尔': { flag: '🇦🇩', en: 'Andorra', code: 'AD' },
    '安哥拉': { flag: '🇦🇴', en: 'Angola', code: 'AO' },
    '安圭拉': { flag: '🇦🇮', en: 'Anguilla', code: 'AI' },
    '安提瓜和巴布达': { flag: '🇦🇬', en: 'Antigua and Barbuda', code: 'AG' },
    '阿根廷': { flag: '🇦🇷', en: 'Argentina', code: 'AR' },
    '亚美尼亚': { flag: '🇦🇲', en: 'Armenia', code: 'AM' },
    '阿鲁巴': { flag: '🇦🇼', en: 'Aruba', code: 'AW' },
    '澳大利亚': { flag: '🇦🇺', en: 'Australia', code: 'AU' },
    '奥地利': { flag: '🇦🇹', en: 'Austria', code: 'AT' },
    '阿塞拜疆': { flag: '🇦🇿', en: 'Azerbaijan', code: 'AZ' },
    '巴哈马': { flag: '🇧🇸', en: 'Bahamas', code: 'BS' },
    '巴林': { flag: '🇧🇭', en: 'Bahrain', code: 'BH' },
    '孟加拉国': { flag: '🇧🇩', en: 'Bangladesh', code: 'BD' },
    '巴巴多斯': { flag: '🇧🇧', en: 'Barbados', code: 'BB' },
    '白俄罗斯': { flag: '🇧🇾', en: 'Belarus', code: 'BY' },
    '比利时': { flag: '🇧🇪', en: 'Belgium', code: 'BE' },
    '伯利兹': { flag: '🇧🇿', en: 'Belize', code: 'BZ' },
    '贝宁': { flag: '🇧🇯', en: 'Benin', code: 'BJ' },
    '百慕大': { flag: '🇧🇲', en: 'Bermuda', code: 'BM' },
    '不丹': { flag: '🇧🇹', en: 'Bhutan', code: 'BT' },
    '玻利维亚': { flag: '🇧🇴', en: 'Bolivia', code: 'BO' },
    '波斯尼亚和黑塞哥维那': { flag: '🇧🇦', en: 'Bosnia and Herzegovina', code: 'BA' },
    '博茨瓦纳': { flag: '🇧🇼', en: 'Botswana', code: 'BW' },
    '巴西': { flag: '🇧🇷', en: 'Brazil', code: 'BR' },
    '文莱': { flag: '🇧🇳', en: 'Brunei', code: 'BN' },
    '保加利亚': { flag: '🇧🇬', en: 'Bulgaria', code: 'BG' },
    '布基纳法索': { flag: '🇧🇫', en: 'Burkina Faso', code: 'BF' },
    '布隆迪': { flag: '🇧🇮', en: 'Burundi', code: 'BI' },
    '柬埔寨': { flag: '🇰🇭', en: 'Cambodia', code: 'KH' },
    '喀麦隆': { flag: '🇨🇲', en: 'Cameroon', code: 'CM' },
    '加拿大': { flag: '🇨🇦', en: 'Canada', code: 'CA' },
    '佛得角': { flag: '🇨🇻', en: 'Cape Verde', code: 'CV' },
    '开曼群岛': { flag: '🇰🇾', en: 'Cayman Islands', code: 'KY' },
    '中非': { flag: '🇨🇫', en: 'Central African Republic', code: 'CF' },
    '乍得': { flag: '🇹🇩', en: 'Chad', code: 'TD' },
    '智利': { flag: '🇨🇱', en: 'Chile', code: 'CL' },
    '中国': { flag: '🇨🇳', en: 'China', code: 'CN' },
    '哥伦比亚': { flag: '🇨🇴', en: 'Colombia', code: 'CO' },
    '科摩罗': { flag: '🇰🇲', en: 'Comoros', code: 'KM' },
    '刚果（布）': { flag: '🇨🇬', en: 'Republic of the Congo', code: 'CG' },
    '刚果（金）': { flag: '🇨🇩', en: 'Democratic Republic of the Congo', code: 'CD' },
    '库克群岛': { flag: '🇨🇰', en: 'Cook Islands', code: 'CK' },
    '哥斯达黎加': { flag: '🇨🇷', en: 'Costa Rica', code: 'CR' },
    '克罗地亚': { flag: '🇭🇷', en: 'Croatia', code: 'HR' },
    '古巴': { flag: '🇨🇺', en: 'Cuba', code: 'CU' },
    '库拉索': { flag: '🇨🇼', en: 'Curacao', code: 'CW' },
    '塞浦路斯': { flag: '🇨🇾', en: 'Cyprus', code: 'CY' },
    '捷克': { flag: '🇨🇿', en: 'Czech Republic', code: 'CZ' },
    '丹麦': { flag: '🇩🇰', en: 'Denmark', code: 'DK' },
    '吉布提': { flag: '🇩🇯', en: 'Djibouti', code: 'DJ' },
    '多米尼加': { flag: '🇩🇴', en: 'Dominican Republic', code: 'DO' },
    '厄瓜多尔': { flag: '🇪🇨', en: 'Ecuador', code: 'EC' },
    '埃及': { flag: '🇪🇬', en: 'Egypt', code: 'EG' },
    '萨尔瓦多': { flag: '🇸🇻', en: 'El Salvador', code: 'SV' },
    '赤道几内亚': { flag: '🇬🇶', en: 'Equatorial Guinea', code: 'GQ' },
    '厄立特里亚': { flag: '🇪🇷', en: 'Eritrea', code: 'ER' },
    '爱沙尼亚': { flag: '🇪🇪', en: 'Estonia', code: 'EE' },
    '埃塞俄比亚': { flag: '🇪🇹', en: 'Ethiopia', code: 'ET' },
    '法罗群岛': { flag: '🇫🇴', en: 'Faroe Islands', code: 'FO' },
    '斐济': { flag: '🇫🇯', en: 'Fiji', code: 'FJ' },
    '芬兰': { flag: '🇫🇮', en: 'Finland', code: 'FI' },
    '法国': { flag: '🇫🇷', en: 'France', code: 'FR' },
    '法属圭亚那': { flag: '🇬🇫', en: 'French Guiana', code: 'GF' },
    '法属波利尼西亚': { flag: '🇵🇫', en: 'French Polynesia', code: 'PF' },
    '加蓬': { flag: '🇬🇦', en: 'Gabon', code: 'GA' },
    '冈比亚': { flag: '🇬🇲', en: 'Gambia', code: 'GM' },
    '格鲁吉亚': { flag: '🇬🇪', en: 'Georgia', code: 'GE' },
    '德国': { flag: '🇩🇪', en: 'Germany', code: 'DE' },
    '加纳': { flag: '🇬🇭', en: 'Ghana', code: 'GH' },
    '直布罗陀': { flag: '🇬🇮', en: 'Gibraltar', code: 'GI' },
    '希腊': { flag: '🇬🇷', en: 'Greece', code: 'GR' },
    '格陵兰': { flag: '🇬🇱', en: 'Greenland', code: 'GL' },
    '格林纳达': { flag: '🇬🇩', en: 'Grenada', code: 'GD' },
    '瓜德罗普': { flag: '🇬🇵', en: 'Guadeloupe', code: 'GP' },
    '关岛': { flag: '🇬🇺', en: 'Guam', code: 'GU' },
    '危地马拉': { flag: '🇬🇹', en: 'Guatemala', code: 'GT' },
    '根西': { flag: '🇬🇬', en: 'Guernsey', code: 'GG' },
    '几内亚': { flag: '🇬🇳', en: 'Guinea', code: 'GN' },
    '几内亚比绍': { flag: '🇬🇼', en: 'Guinea-Bissau', code: 'GW' },
    '圭亚那': { flag: '🇬🇾', en: 'Guyana', code: 'GY' },
    '海地': { flag: '🇭🇹', en: 'Haiti', code: 'HT' },
    '洪都拉斯': { flag: '🇭🇳', en: 'Honduras', code: 'HN' },
    '香港': { flag: '🇭🇰', en: 'Hong Kong', code: 'HK' },
    '匈牙利': { flag: '🇭🇺', en: 'Hungary', code: 'HU' },
    '冰岛': { flag: '🇮🇸', en: 'Iceland', code: 'IS' },
    '印度': { flag: '🇮🇳', en: 'India', code: 'IN' },
    '印度尼西亚': { flag: '🇮🇩', en: 'Indonesia', code: 'ID' },
    '伊朗': { flag: '🇮🇷', en: 'Iran', code: 'IR' },
    '伊拉克': { flag: '🇮🇶', en: 'Iraq', code: 'IQ' },
    '爱尔兰': { flag: '🇮🇪', en: 'Ireland', code: 'IE' },
    '马恩岛': { flag: '🇮🇲', en: 'Isle of Man', code: 'IM' },
    '以色列': { flag: '🇮🇱', en: 'Israel', code: 'IL' },
    '意大利': { flag: '🇮🇹', en: 'Italy', code: 'IT' },
    '牙买加': { flag: '🇯🇲', en: 'Jamaica', code: 'JM' },
    '日本': { flag: '🇯🇵', en: 'Japan', code: 'JP' },
    '泽西': { flag: '🇯🇪', en: 'Jersey', code: 'JE' },
    '约旦': { flag: '🇯🇴', en: 'Jordan', code: 'JO' },
    '哈萨克斯坦': { flag: '🇰🇿', en: 'Kazakhstan', code: 'KZ' },
    '肯尼亚': { flag: '🇰🇪', en: 'Kenya', code: 'KE' },
    '基里巴斯': { flag: '🇰🇮', en: 'Kiribati', code: 'KI' },
    '科索沃': { flag: '🇽🇰', en: 'Kosovo', code: 'XK' },
    '科威特': { flag: '🇰🇼', en: 'Kuwait', code: 'KW' },
    '吉尔吉斯斯坦': { flag: '🇰🇬', en: 'Kyrgyzstan', code: 'KG' },
    '老挝': { flag: '🇱🇦', en: 'Laos', code: 'LA' },
    '拉脱维亚': { flag: '🇱🇻', en: 'Latvia', code: 'LV' },
    '黎巴嫩': { flag: '🇱🇧', en: 'Lebanon', code: 'LB' },
    '莱索托': { flag: '🇱🇸', en: 'Lesotho', code: 'LS' },
    '利比里亚': { flag: '🇱🇷', en: 'Liberia', code: 'LR' },
    '利比亚': { flag: '🇱🇾', en: 'Libya', code: 'LY' },
    '列支敦士登': { flag: '🇱🇮', en: 'Liechtenstein', code: 'LI' },
    '立陶宛': { flag: '🇱🇹', en: 'Lithuania', code: 'LT' },
    '卢森堡': { flag: '🇱🇺', en: 'Luxembourg', code: 'LU' },
    '澳门': { flag: '🇲🇴', en: 'Macau', code: 'MO' },
    '马达加斯加': { flag: '🇲🇬', en: 'Madagascar', code: 'MG' },
    '马拉维': { flag: '🇲🇼', en: 'Malawi', code: 'MW' },
    '马来西亚': { flag: '🇲🇾', en: 'Malaysia', code: 'MY' },
    '马尔代夫': { flag: '🇲🇻', en: 'Maldives', code: 'MV' },
    '马里': { flag: '🇲🇱', en: 'Mali', code: 'ML' },
    '马耳他': { flag: '🇲🇹', en: 'Malta', code: 'MT' },
    '马绍尔群岛': { flag: '🇲🇭', en: 'Marshall Islands', code: 'MH' },
    '马提尼克': { flag: '🇲🇶', en: 'Martinique', code: 'MQ' },
    '毛里塔尼亚': { flag: '🇲🇷', en: 'Mauritania', code: 'MR' },
    '毛里求斯': { flag: '🇲🇺', en: 'Mauritius', code: 'MU' },
    '马约特': { flag: '🇾🇹', en: 'Mayotte', code: 'YT' },
    '墨西哥': { flag: '🇲🇽', en: 'Mexico', code: 'MX' },
    '密克罗尼西亚': { flag: '🇫🇲', en: 'Micronesia', code: 'FM' },
    '摩尔多瓦': { flag: '🇲🇩', en: 'Moldova', code: 'MD' },
    '摩纳哥': { flag: '🇲🇨', en: 'Monaco', code: 'MC' },
    '蒙古': { flag: '🇲🇳', en: 'Mongolia', code: 'MN' },
    '黑山': { flag: '🇲🇪', en: 'Montenegro', code: 'ME' },
    '蒙特塞拉特': { flag: '🇲🇸', en: 'Montserrat', code: 'MS' },
    '摩洛哥': { flag: '🇲🇦', en: 'Morocco', code: 'MA' },
    '莫桑比克': { flag: '🇲🇿', en: 'Mozambique', code: 'MZ' },
    '缅甸': { flag: '🇲🇲', en: 'Myanmar', code: 'MM' },
    '纳米比亚': { flag: '🇳🇦', en: 'Namibia', code: 'NA' },
    '瑙鲁': { flag: '🇳🇷', en: 'Nauru', code: 'NR' },
    '尼泊尔': { flag: '🇳🇵', en: 'Nepal', code: 'NP' },
    '荷兰': { flag: '🇳🇱', en: 'Netherlands', code: 'NL' },
    '新喀里多尼亚': { flag: '🇳🇨', en: 'New Caledonia', code: 'NC' },
    '新西兰': { flag: '🇳🇿', en: 'New Zealand', code: 'NZ' },
    '纽埃': { flag: '🇳🇺', en: 'Niue', code: 'NU' },
    '北马里亚纳群岛': { flag: '🇲🇵', en: 'Northern Mariana Islands', code: 'MP' },
    '挪威': { flag: '🇳🇴', en: 'Norway', code: 'NO' },
    '阿曼': { flag: '🇴🇲', en: 'Oman', code: 'OM' },
    '巴基斯坦': { flag: '🇵🇰', en: 'Pakistan', code: 'PK' },
    '帕劳': { flag: '🇵🇼', en: 'Palau', code: 'PW' },
    '巴拿马': { flag: '🇵🇦', en: 'Panama', code: 'PA' },
    '巴布亚新几内亚': { flag: '🇵🇬', en: 'Papua New Guinea', code: 'PG' },
    '巴拉圭': { flag: '🇵🇾', en: 'Paraguay', code: 'PY' },
    '秘鲁': { flag: '🇵🇪', en: 'Peru', code: 'PE' },
    '菲律宾': { flag: '🇵🇭', en: 'Philippines', code: 'PH' },
    '皮特凯恩群岛': { flag: '🇵🇳', en: 'Pitcairn', code: 'PN' },
    '波兰': { flag: '🇵🇱', en: 'Poland', code: 'PL' },
    '葡萄牙': { flag: '🇵🇹', en: 'Portugal', code: 'PT' },
    '波多黎各': { flag: '🇵🇷', en: 'Puerto Rico', code: 'PR' },
    '卡塔尔': { flag: '🇶🇦', en: 'Qatar', code: 'QA' },
    '留尼汪': { flag: '🇷🇪', en: 'Réunion', code: 'RE' },
    '罗马尼亚': { flag: '🇷🇴', en: 'Romania', code: 'RO' },
    '俄罗斯': { flag: '🇷🇺', en: 'Russia', code: 'RU' },
    '卢旺达': { flag: '🇷🇼', en: 'Rwanda', code: 'RW' },
    '圣巴泰勒米': { flag: '🇧🇱', en: 'Saint Barthélemy', code: 'BL' },
    '圣赫勒拿': { flag: '🇸🇭', en: 'Saint Helena', code: 'SH' },
    '圣基茨和尼维斯': { flag: '🇰🇳', en: 'Saint Kitts and Nevis', code: 'KN' },
    '圣卢西亚': { flag: '🇱🇨', en: 'Saint Lucia', code: 'LC' },
    '圣马丁（法属）': { flag: '🇲🇫', en: 'Saint Martin (French part)', code: 'MF' },
    '圣皮埃尔和密克隆': { flag: '🇵🇲', en: 'Saint Pierre and Miquelon', code: 'PM' },
    '圣文森特和格林纳丁斯': { flag: '🇻🇨', en: 'Saint Vincent and the Grenadines', code: 'VC' },
    '萨摩亚': { flag: '🇼🇸', en: 'Samoa', code: 'WS' },
    '圣马力诺': { flag: '🇸🇲', en: 'San Marino', code: 'SM' },
    '圣多美和普林西比': { flag: '🇸🇹', en: 'Sao Tome and Principe', code: 'ST' },
    '沙特阿拉伯': { flag: '🇸🇦', en: 'Saudi Arabia', code: 'SA' },
    '塞内加尔': { flag: '🇸🇳', en: 'Senegal', code: 'SN' },
    '塞尔维亚': { flag: '🇷🇸', en: 'Serbia', code: 'RS' },
    '塞舌尔': { flag: '🇸🇨', en: 'Seychelles', code: 'SC' },
    '塞拉利昂': { flag: '🇸🇱', en: 'Sierra Leone', code: 'SL' },
    '新加坡': { flag: '🇸🇬', en: 'Singapore', code: 'SG' },
    '荷属圣马丁': { flag: '🇸🇽', en: 'Sint Maarten (Dutch part)', code: 'SX' },
    '斯洛伐克': { flag: '🇸🇰', en: 'Slovakia', code: 'SK' },
    '斯洛文尼亚': { flag: '🇸🇮', en: 'Slovenia', code: 'SI' },
    '所罗门群岛': { flag: '🇸🇧', en: 'Solomon Islands', code: 'SB' },
    '索马里': { flag: '🇸🇴', en: 'Somalia', code: 'SO' },
    '南非': { flag: '🇿🇦', en: 'South Africa', code: 'ZA' },
    '南乔治亚和南桑威奇群岛': { flag: '🇬🇸', en: 'South Georgia and the South Sandwich Islands', code: 'GS' },
    '南苏丹': { flag: '🇸🇸', en: 'South Sudan', code: 'SS' },
    '西班牙': { flag: '🇪🇸', en: 'Spain', code: 'ES' },
    '斯里兰卡': { flag: '🇱🇰', en: 'Sri Lanka', code: 'LK' },
    '苏丹': { flag: '🇸🇩', en: 'Sudan', code: 'SD' },
    '苏里南': { flag: '🇸🇷', en: 'Suriname', code: 'SR' },
    '斯瓦尔巴和扬马延': { flag: '🇸🇯', en: 'Svalbard and Jan Mayen', code: 'SJ' },
    '斯威士兰': { flag: '🇸🇿', en: 'Swaziland', code: 'SZ' },
    '瑞典': { flag: '🇸🇪', en: 'Sweden', code: 'SE' },
    '瑞士': { flag: '🇨🇭', en: 'Switzerland', code: 'CH' },
    '叙利亚': { flag: '🇸🇾', en: 'Syria', code: 'SY' },
    '台湾': { flag: '🇹🇼', en: 'Taiwan', code: 'TW' },
    '塔吉克斯坦': { flag: '🇹🇯', en: 'Tajikistan', code: 'TJ' },
    '坦桑尼亚': { flag: '🇹🇿', en: 'Tanzania', code: 'TZ' },
    '泰国': { flag: '🇹🇭', en: 'Thailand', code: 'TH' },
    '东帝汶': { flag: '🇹🇱', en: 'Timor-Leste', code: 'TL' },
    '多哥': { flag: '🇹🇬', en: 'Togo', code: 'TG' },
    '托克劳': { flag: '🇹🇰', en: 'Tokelau', code: 'TK' },
    '汤加': { flag: '🇹🇴', en: 'Tonga', code: 'TO' },
    '特立尼达和多巴哥': { flag: '🇹🇹', en: 'Trinidad and Tobago', code: 'TT' },
    '突尼斯': { flag: '🇹🇳', en: 'Tunisia', code: 'TN' },
    '土耳其': { flag: '🇹🇷', en: 'Turkey', code: 'TR' },
    '土库曼斯坦': { flag: '🇹🇲', en: 'Turkmenistan', code: 'TM' },
    '特克斯和凯科斯群岛': { flag: '🇹🇨', en: 'Turks and Caicos Islands', code: 'TC' },
    '图瓦卢': { flag: '🇹🇻', en: 'Tuvalu', code: 'TV' },
    '乌干达': { flag: '🇺🇬', en: 'Uganda', code: 'UG' },
    '乌克兰': { flag: '🇺🇦', en: 'Ukraine', code: 'UA' },
    '阿拉伯联合酋长国': { flag: '🇦🇪', en: 'United Arab Emirates', code: 'AE' },
    '英国': { flag: '🇬🇧', en: 'United Kingdom', code: 'GB' },
    '美国': { flag: '🇺🇸', en: 'United States', code: 'US' },
    '美国本土外小岛屿': { flag: '🇺🇲', en: 'United States Minor Outlying Islands', code: 'UM' },
    '乌拉圭': { flag: '🇺🇾', en: 'Uruguay', code: 'UY' },
    '乌兹别克斯坦': { flag: '🇺🇿', en: 'Uzbekistan', code: 'UZ' },
    '瓦努阿图': { flag: '🇻🇺', en: 'Vanuatu', code: 'VU' },
    '梵蒂冈': { flag: '🇻🇦', en: 'Vatican City', code: 'VA' },
    '委内瑞拉': { flag: '🇻🇪', en: 'Venezuela', code: 'VE' },
    '越南': { flag: '🇻🇳', en: 'Vietnam', code: 'VN' },
    '维尔京群岛（英）': { flag: '🇻🇬', en: 'British Virgin Islands', code: 'VG' },
    '维尔京群岛（美）': { flag: '🇻🇮', en: 'U.S. Virgin Islands', code: 'VI' },
    '瓦利斯和富图纳': { flag: '🇼🇫', en: 'Wallis and Futuna', code: 'WF' },
    '西撒哈拉': { flag: '🇪🇭', en: 'Western Sahara', code: 'EH' },
    '也门': { flag: '🇾🇪', en: 'Yemen', code: 'YE' },
    '赞比亚': { flag: '🇿🇲', en: 'Zambia', code: 'ZM' },
    '津巴布韦': { flag: '🇿🇼', en: 'Zimbabwe', code: 'ZW' }
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

const codeToCountry = {};
const enToCountry = {};

for (const key in countryMap) {
    const country = countryMap[key];
    codeToCountry[country.code] = country;
    enToCountry[country.en] = { ...country, cnName: key };
}

function addFlagIfMissing(titleText) {
    if (/[\u{1F1E6}-\u{1F1FF}]{2}/u.test(titleText)) {
        return titleText;
    }
    
    const codeMatches = titleText.match(/\b([A-Z]{2})\b/g);
    
    if (codeMatches) {
        for (const code of codeMatches) {
            if (codeToCountry[code]) {
                return codeToCountry[code].flag + ' ' + titleText;
            }
        }
    }
    
    for (const enName in enToCountry) {
        if (titleText.includes(enName)) {
            return enToCountry[enName].flag + ' ' + titleText;
        }
    }
    
    for (const cnName in countryMap) {
        if (titleText.includes(cnName)) {
            return countryMap[cnName].flag + ' ' + titleText;
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
    const color = badgeColors[protocol] || '#17a2b8';
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
        let hasSortingOccurred = false;
        let saveTimeout = null;
        
        Sortable.create(container, {
            handle: ".node-card, .drag-handle",
            filter: ".metric-item, .metric-item *",
            animation: 150,
            ghostClass: "pw-sortable-ghost",
            chosenClass: "sortable-chosen",
            dragClass: "dragging-row",
            
            onStart: function(evt) {
                hasSortingOccurred = false;
                showSaveButtonAtCorner(group);
            },
            
            onSort: function(evt) {
                hasSortingOccurred = true;
            },
            
            onEnd: function(evt) {
                clearTimeout(saveTimeout);
                
                if (hasSortingOccurred) {
                    saveTimeout = setTimeout(() => {
                        saveCardOrder(group);
                        hasSortingOccurred = false;
                    }, 200);
                }
                
                setTimeout(() => {
                    hideSaveButtonFromCorner(group);
                }, 2500);
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
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
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
    background: var(--drag-over-bg);
    backdrop-filter: blur(6px);
    -webkit-backdrop-filter: blur(6px);
    box-shadow: var(--shadow-inset);
    transform: translateY(-2px);
}

.node-card._now_use_bg {
    background: var(--card-bg) !important;
    border: var(--border-strong) !important;
    backdrop-filter: blur(6px);
    -webkit-backdrop-filter: blur(6px);
}

input[type="button"].btn.cbi-button.cbi-button-edit[onclick^="row_top"] {
    display: none !important;
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
    top: 15px;
    left: 50%;
    transform: translateX(-50%);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 2px 6px;
    height: 18px;
    border-radius: 4px;
    font-size: 10px;
    font-weight: 600;
    color: white;
    text-transform: uppercase;
    letter-spacing: 0.2px;
    box-shadow: var(--shadow-inset);
    border: var(--border-strong);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    margin: 0;
    z-index: 1;
    box-sizing: border-box;
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
    justify-content: center;
    align-items: center;
    white-space: nowrap;
    flex: 1;
    min-width: 0;
    transition: box-shadow 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    gap: 6px;
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
    border-radius: 50% !important;
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
    top: 10px;
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

.pw-sortable-ghost {
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