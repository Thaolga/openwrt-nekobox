document.addEventListener("DOMContentLoaded", function () {
    let isEnabled = localStorage.getItem('backgroundEnabled') !== 'false';

    const isAnimationEnabled = localStorage.getItem('animationEnabled') !== 'false';
    toggleAnimation(isAnimationEnabled);

    const savedColor = localStorage.getItem('customBackgroundColor');
    if (savedColor) {
        const rgbColor = hexToRgb(savedColor);
        const popupBgColor = `rgba(${rgbColor.r}, ${rgbColor.g}, ${rgbColor.b}, 0.9)`;
        document.documentElement.style.setProperty('--popup-bg-color', popupBgColor);
    } else {
        document.documentElement.style.setProperty(
            '--popup-bg-color', 
            'rgba(27, 27, 47, 0.9)'
        );
    }

    const savedFont = localStorage.getItem('selectedFont') || 'default';
    applyFont(savedFont);
    let videoTag, bgImages, bgIndex, availableImages, switchInterval;
    const ipContainer = document.querySelector('.ip-container');
    const flag = document.getElementById('flag');
    const ipText = document.querySelector('.ip-text');

    const dIp = document.getElementById('d-ip');
    if (dIp) {
        dIp.style.cssText = `
            color: #09B63F !important;
            font-weight: bold;
            font-size: 15px;
            position: relative;
            left: 1em; 
            margin-bottom: 3px;
            text-indent: -0.7ch; 
        `;
    }

    const ipip = document.getElementById('ipip');
    if (ipip) {
        ipip.style.cssText = `
            color: #FF00FF !important;
            font-weight: bold;
            font-size: 15px;
            display: block;
            margin-top: 3px;
            margin-bottom: 3px;
        `;
    }

    if (ipContainer) {
        ipContainer.style.cssText = `
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            z-index: 1;
            padding: 15px 20px;
            min-width: 300px;
            transition: all 0.3s ease;
        `;
        
        const shouldHide = localStorage.getItem('hideIP') === 'true';
        ipContainer.style.display = shouldHide ? 'none' : 'flex';
    }

    if (flag) {
        flag.style.cssText = `
            width: 60px;
            height: 40px;
            margin-right: 25px;
            margin-left: 15px;
            flex-shrink: 0;
        `;
    }

    if (ipText) {
        ipText.style.cssText = `
            font-family: Arial, sans-serif;
            line-height: 1.4;
            min-width: 180px;
            transform: translateY(1px); 
        `;
    }

    const savedMode = localStorage.getItem('backgroundMode');
    
    if (savedMode === 'color' && savedColor) {
        document.body.style.background = savedColor;
        document.body.style.backgroundSize = 'auto';
    } else if (isEnabled) {
        initBackgroundSystem();
    } else {
        applyPHPBackground();
    }

    const languages = {
        'en': {
            'enabled': 'Enabled',
            'disabled': 'Disabled',
            'currentTheme': 'Current Theme: ',
            'darkMode': 'Dark Mode',
            'lightMode': 'Light Mode',
            'themeSettings': 'Theme Settings',
            'videoMode': 'Video Mode',
            'imageMode': 'Image Mode',
            'solidMode': 'Solid Mode',
            'autoMode': 'Auto Mode',
            'backgroundSound': 'Background Sound',
            'displayRatio': 'Display Ratio',
            'normalRatio': 'Normal Ratio',
            'stretchFill': 'Stretch Fill',
            'originalSize': 'Original Size',
            'smartFit': 'Smart Fit',
            'defaultCrop': 'Default Crop',
            'showIP': 'Show IP Information',
            'hideIP': 'Hide IP Information',
            'usageGuide': 'Usage Guide',
            'colorPanel': 'Color Panel',
            'apply': 'Apply',
            'reset': 'Reset',
            'enableAnimation': 'Enable Animation',
            'disableAnimation': 'Disable Animation',
            'fontToggle': 'Font Style',
            'fontDefault': 'Default',
            'fontFredoka': 'Fredoka',
            'fontDMSerif': 'DM Serif',
            'fontNotoSerif': 'Noto Serif',
            'fontComicNeue': 'Comic Neue',
            'fontSettings': 'Font Settings', 
            'fontSize': 'Font Size',
            'fontColor': 'Font Color',
            'black': 'Black',
            'white': 'White',
            'red': 'Red',
            'blue': 'Blue',
            'green': 'Green',
            'purple': 'Purple',
            'controlPanel': 'Control Panel',
            'customColor': 'Custom Color',
            'guide1': '1. Video Mode: Default name is "bg.mp4"',
            'guide2': '2. Image Mode: Default names are "bg1-20.jpg"',
            'guide3': '3. Solid Mode: Transparent background + spectrum animation',
            'guide4': '4. Light Mode: Switch in theme settings, will automatically turn off the control switch',
            'guide5': '5. Theme Settings: Supports custom backgrounds, mode switching requires clearing the background',
            'guide6': '6. Menu Settings: Press Ctrl + Alt + S to open the settings menu',
            'guide7': '7. Project Address: <a class="github-link" href="https://github.com/Thaolga/openwrt-nekobox" target="_blank">Click to visit</a>',
            'themeTitle': 'Spectra Theme Settings'
        },
        'zh': {
            'enabled': '已启用',
            'disabled': '已禁用',
            'currentTheme': '当前主题: ',
            'darkMode': '暗色模式',
            'lightMode': '亮色模式',
            'themeSettings': '主题设置',
            'videoMode': '视频模式',
            'imageMode': '图片模式',
            'solidMode': '暗黑模式',
            'autoMode': '自动模式',
            'backgroundSound': '背景音效',
            'displayRatio': '显示比例',
            'normalRatio': '正常比例',
            'stretchFill': '拉伸填充',
            'originalSize': '原始尺寸',
            'smartFit': '智能适应',
            'defaultCrop': '默认裁剪',
            'showIP': '显示IP信息',
            'hideIP': '隐藏IP信息',
            'colorPanel': '颜色面板',
            'apply': '应用',
            'reset': '重置',
            'enableAnimation': '开启动画',  
            'disableAnimation': '关闭动画',
            'fontToggle': '字体切换',
            'fontDefault': '默认字体',
            'fontFredoka': '圆润字体',
            'fontDMSerif': '衬线字体',
            'fontNotoSerif': '思源宋体',
            'fontComicNeue': '漫画字体',
            'fontSettings': '字体设置', 
            'fontSize': '字体大小',
            'fontColor': '字体颜色',
            'black': '黑色',
            'white': '白色',
            'red': '红色',
            'blue': '蓝色',
            'green': '绿色',
            'purple': '紫色',
            'customColor': '自定义颜色',
            'controlPanel': '控制面板',
            'usageGuide': '使用说明',
            'guide1': '1. 视频模式：默认名称为「bg.mp4」',
            'guide2': '2. 图片模式：默认名称为「bg1-20.jpg」',
            'guide3': '3. 暗黑模式：透明背景+光谱动画',
            'guide4': '4. 亮色模式：主题设置进行切换，会自动关闭控制开关',
            'guide5': '5. 主题设置：支持自定义背景，模式切换需清除背景',
            'guide6': '6. 菜单设置：Ctrl + Alt + S 打开设置菜单',
            'guide7': '7. 项目地址：<a class="github-link" href="https://github.com/Thaolga/openwrt-nekobox" target="_blank">点击访问</a>',
            'themeTitle': 'Spectra 主题设置'
        },
        'zh-tw': {
            'enabled': '已啟用',
            'disabled': '已停用',
            'currentTheme': '當前主題: ',
            'darkMode': '暗色模式',
            'lightMode': '亮色模式',
            'themeSettings': '主題設定',
            'videoMode': '影片模式',
            'imageMode': '圖片模式',
            'solidMode': '暗黑模式',
            'autoMode': '自動模式',
            'backgroundSound': '背景音效',
            'displayRatio': '顯示比例',
            'normalRatio': '正常比例',
            'stretchFill': '拉伸填充',
            'originalSize': '原始尺寸',
            'smartFit': '智能適應',
            'defaultCrop': '預設裁剪',
            'showIP': '顯示IP資訊',
            'hideIP': '隱藏IP資訊',
            'colorPanel': '顏色面板',
            'apply': '套用',
            'reset': '重置',
            'enableAnimation': '啟用動畫',  
            'disableAnimation': '關閉動畫',
            'fontToggle': '字體切換',
            'fontDefault': '預設字體',
            'fontFredoka': '圓潤字體',
            'fontDMSerif': '襯線字體',
            'fontNotoSerif': '思源宋體',
            'fontComicNeue': '漫畫字體',
            'fontSettings': '字型設定', 
            'fontSize': '字體大小',
            'fontColor': '字體顏色',
            'black': '黑色',
            'white': '白色',
            'red': '紅色',
            'blue': '藍色',
            'green': '綠色',
            'purple': '紫色',
            'customColor': '自定義顏色',
            'controlPanel': '控制面板',
            'usageGuide': '使用說明',
            'guide1': '1. 影片模式：預設名稱為「bg.mp4」',
            'guide2': '2. 圖片模式：預設名稱為「bg1-20.jpg」',
            'guide3': '3. 暗黑模式：透明背景+光譜動畫',
            'guide4': '4. 亮色模式：主題設定進行切換，會自動關閉控制開關',
            'guide5': '5. 主題設定：支援自訂背景，模式切換需清除背景',
            'guide6': '6. 功能選單設定：Ctrl + Alt + S 開啟設定選單',
            'guide7': '7. 專案地址：<a class="github-link" href="https://github.com/Thaolga/openwrt-nekobox" target="_blank">點擊訪問</a>',
            'themeTitle': 'Spectra 主題設定'
        },
        'ko': {
            'enabled': '활성화됨',
            'disabled': '비활성화됨',
            'currentTheme': '현재 테마: ',
            'darkMode': '다크 모드',
            'lightMode': '라이트 모드',
            'themeSettings': '테마 설정',
            'videoMode': '비디오 모드',
            'imageMode': '이미지 모드',
            'solidMode': '솔리드 모드',
            'autoMode': '자동 모드',
            'backgroundSound': '배경 소리',
            'displayRatio': '표시 비율',
            'normalRatio': '일반 비율',
            'stretchFill': '스트레치 채우기',
            'originalSize': '원본 크기',
            'smartFit': '스마트 맞춤',
            'defaultCrop': '기본 자르기',
            'showIP': 'IP 정보 표시',
            'hideIP': 'IP 정보 숨기기',
            'colorPanel': '색상 패널',
            'apply': '적용',
            'reset': '초기화',
            'enableAnimation': '애니메이션 활성화',
            'disableAnimation': '애니메이션 비활성화',
            'fontToggle': '폰트 스타일',
            'fontDefault': '기본',
            'fontFredoka': 'Fredoka',
            'fontDMSerif': 'DM Serif',
            'fontNotoSerif': 'Noto Serif',
            'fontComicNeue': 'Comic Neue',
            'fontSettings': '폰트 설정',
            'fontSize': '폰트 크기',
            'fontColor': '폰트 색상',
            'black': '검정',
            'white': '흰색',
            'red': '빨강',
            'blue': '파랑',
            'green': '초록',
            'purple': '보라',
            'customColor': '사용자 정의 색상',
            'controlPanel': '제어 패널',
            'usageGuide': '사용 가이드',
            'guide1': '1. 비디오 모드: 기본 이름은 "bg.mp4"입니다',
            'guide2': '2. 이미지 모드: 기본 이름은 "bg1-20.jpg"입니다',
            'guide3': '3. 솔리드 모드: 투명 배경 + 스펙트럼 애니메이션',
            'guide4': '4. 라이트 모드: 테마 설정에서 전환, 제어 스위치가 자동으로 꺼집니다',
            'guide5': '5. 테마 설정: 사용자 정의 배경 지원, 모드 전환 시 배경을 지워야 함',
            'guide6': '6. 메뉴 설정: Ctrl + Alt + S를 눌러 설정 메뉴 열기',
            'guide7': '7. 프로젝트 주소: <a class="github-link" href="https://github.com/Thaolga/openwrt-nekobox" target="_blank">방문하기</a>',
            'themeTitle': 'Spectra 테마 설정'
        },
        'ja': {
            'enabled': '有効',
            'disabled': '無効',
            'currentTheme': '現在のテーマ: ',
            'darkMode': 'ダークモード',
            'lightMode': 'ライトモード',
            'themeSettings': 'テーマ設定',
            'videoMode': '動画モード',
            'imageMode': '画像モード',
            'solidMode': 'ソリッドモード',
            'autoMode': '自動モード',
            'backgroundSound': '背景音',
            'displayRatio': '表示比率',
            'normalRatio': '通常比率',
            'stretchFill': '引き伸ばし',
            'originalSize': '元のサイズ',
            'smartFit': 'スマートフィット',
            'defaultCrop': 'デフォルトクロップ',
            'showIP': 'IP情報を表示',
            'hideIP': 'IP情報を非表示',
            'colorPanel': 'カラーパネル',
            'apply': '適用',
            'reset': 'リセット',
            'enableAnimation': 'アニメーションを有効化',
            'disableAnimation': 'アニメーションを無効化',
            'fontToggle': 'フォントスタイル',
            'fontDefault': 'デフォルト',
            'fontFredoka': 'Fredoka',
            'fontDMSerif': 'DM Serif',
            'fontNotoSerif': 'Noto Serif',
            'fontComicNeue': 'Comic Neue',
            'fontSettings': 'フォント設定',
            'fontSize': 'フォントサイズ',
            'fontColor': 'フォント色',
            'black': '黒',
            'white': '白',
            'red': '赤',
            'blue': '青',
            'green': '緑',
            'purple': '紫',
            'customColor': 'カスタムカラー',
            'controlPanel': 'コントロールパネル',
            'usageGuide': '使用ガイド',
            'guide1': '1. 動画モード: デフォルト名は「bg.mp4」です',
            'guide2': '2. 画像モード: デフォルト名は「bg1-20.jpg」です',
            'guide3': '3. ソリッドモード: 透明背景+スペクトルアニメーション',
            'guide4': '4. ライトモード: テーマ設定で切り替えると、コントロールスイッチが自動的にオフになります',
            'guide5': '5. テーマ設定: カスタム背景をサポートし、モード切り替えには背景のクリアが必要です',
            'guide6': '6. メニュー設定: Ctrl + Alt + Sで設定メニューを開きます',
            'guide7': '7. プロジェクトアドレス: <a class="github-link" href="https://github.com/Thaolga/openwrt-nekobox" target="_blank">クリックして訪問</a>',
            'themeTitle': 'Spectra テーマ設定'
        },
        'vi': {
            'enabled': 'Đã bật',
            'disabled': 'Đã tắt',
            'currentTheme': 'Chủ đề hiện tại: ',
            'darkMode': 'Chế độ tối',
            'lightMode': 'Chế độ sáng',
            'themeSettings': 'Cài đặt chủ đề',
            'videoMode': 'Chế độ video',
            'imageMode': 'Chế độ hình ảnh',
            'solidMode': 'Chế độ đặc',
            'autoMode': 'Chế độ tự động',
            'backgroundSound': 'Âm thanh nền',
            'displayRatio': 'Tỷ lệ hiển thị',
            'normalRatio': 'Tỷ lệ thường',
            'stretchFill': 'Kéo dãn',
            'originalSize': 'Kích thước gốc',
            'smartFit': 'Vừa khít thông minh',
            'defaultCrop': 'Cắt mặc định',
            'showIP': 'Hiển thị thông tin IP',
            'hideIP': 'Ẩn thông tin IP',
            'colorPanel': 'Bảng màu',
            'apply': 'Áp dụng',
            'reset': 'Đặt lại',
            'enableAnimation': 'Bật hoạt ảnh',
            'disableAnimation': 'Tắt hoạt ảnh',
            'fontToggle': 'Kiểu phông chữ',
            'fontDefault': 'Mặc định',
            'fontFredoka': 'Fredoka',
            'fontDMSerif': 'DM Serif',
            'fontNotoSerif': 'Noto Serif',
            'fontComicNeue': 'Comic Neue',
            'fontSettings': 'Cài đặt phông chữ',
            'fontSize': 'Cỡ chữ',
            'fontColor': 'Màu chữ',
            'black': 'Đen',
            'white': 'Trắng',
            'red': 'Đỏ',
            'blue': 'Xanh',
            'green': 'Xanh lá',
            'purple': 'Tím',
            'customColor': 'Màu tùy chỉnh',
            'controlPanel': 'Bảng điều khiển',
            'usageGuide': 'Hướng dẫn sử dụng',
            'guide1': '1. Chế độ video: Tên mặc định là "bg.mp4"',
            'guide2': '2. Chế độ hình ảnh: Tên mặc định là "bg1-20.jpg"',
            'guide3': '3. Chế độ đặc: Nền trong suốt + hoạt ảnh quang phổ',
            'guide4': '4. Chế độ sáng: Chuyển đổi trong cài đặt chủ đề sẽ tự động tắt công tắc điều khiển',
            'guide5': '5. Cài đặt chủ đề: Hỗ trợ nền tùy chỉnh, chuyển đổi chế độ yêu cầu xóa nền',
            'guide6': '6. Cài đặt menu: Nhấn Ctrl + Alt + S để mở menu cài đặt',
            'guide7': '7. Địa chỉ dự án: <a class="github-link" href="https://github.com/Thaolga/openwrt-nekobox" target="_blank">Nhấn để truy cập</a>',
            'themeTitle': 'Cài đặt chủ đề Spectra'
        },
        'th': {
            'enabled': 'เปิดใช้งาน',
            'disabled': 'ปิดใช้งาน',
            'currentTheme': 'ธีมปัจจุบัน: ',
            'darkMode': 'โหมดมืด',
            'lightMode': 'โหมดสว่าง',
            'themeSettings': 'การตั้งค่าธีม',
            'videoMode': 'โหมดวิดีโอ',
            'imageMode': 'โหมดรูปภาพ',
            'solidMode': 'โหมดทึบ',
            'autoMode': 'โหมดอัตโนมัติ',
            'backgroundSound': 'เสียงพื้นหลัง',
            'displayRatio': 'อัตราส่วนการแสดงผล',
            'normalRatio': 'อัตราส่วนปกติ',
            'stretchFill': 'ยืดเต็ม',
            'originalSize': 'ขนาดเดิม',
            'smartFit': 'ปรับสมาร์ท',
            'defaultCrop': 'ครอปเริ่มต้น',
            'showIP': 'แสดงข้อมูล IP',
            'hideIP': 'ซ่อนข้อมูล IP',
            'colorPanel': 'แผงสี',
            'apply': 'นำไปใช้',
            'reset': 'รีเซ็ต',
            'enableAnimation': 'เปิดใช้งานแอนิเมชัน',
            'disableAnimation': 'ปิดใช้งานแอนิเมชัน',
            'fontToggle': 'รูปแบบตัวอักษร',
            'fontDefault': 'ค่าเริ่มต้น',
            'fontFredoka': 'Fredoka',
            'fontDMSerif': 'DM Serif',
            'fontNotoSerif': 'Noto Serif',
            'fontComicNeue': 'Comic Neue',
            'fontSettings': 'การตั้งค่าตัวอักษร',
            'fontSize': 'ขนาดตัวอักษร',
            'fontColor': 'สีตัวอักษร',
            'black': 'ดำ',
            'white': 'ขาว',
            'red': 'แดง',
            'blue': 'น้ำเงิน',
            'green': 'เขียว',
            'purple': 'ม่วง',
            'customColor': 'สีที่กำหนดเอง',
            'controlPanel': 'แผงควบคุม',
            'usageGuide': 'คู่มือการใช้งาน',
            'guide1': '1. โหมดวิดีโอ: ชื่อเริ่มต้นคือ "bg.mp4"',
            'guide2': '2. โหมดรูปภาพ: ชื่อเริ่มต้นคือ "bg1-20.jpg"',
            'guide3': '3. โหมดทึบ: พื้นหลังโปร่งใส + แอนิเมชันสเปกตรัม',
            'guide4': '4. โหมดสว่าง: สลับในการตั้งค่าธีม จะปิดสวิตช์ควบคุมโดยอัตโนมัติ',
            'guide5': '5. การตั้งค่าธีม: รองรับพื้นหลังที่กำหนดเอง การเปลี่ยนโหมดต้องล้างพื้นหลัง',
            'guide6': '6. การตั้งค่าเมนู: กด Ctrl + Alt + S เพื่อเปิดเมนูการตั้งค่า',
            'guide7': '7. ที่อยู่โครงการ: <a class="github-link" href="https://github.com/Thaolga/openwrt-nekobox" target="_blank">คลิกเพื่อเยี่ยมชม</a>',
            'themeTitle': 'การตั้งค่าธีม Spectra'
        },
        'ru': {
            'enabled': 'Включено',
            'disabled': 'Отключено',
            'currentTheme': 'Текущая тема: ',
            'darkMode': 'Темный режим',
            'lightMode': 'Светлый режим',
            'themeSettings': 'Настройки темы',
            'videoMode': 'Видео режим',
            'imageMode': 'Режим изображения',
            'solidMode': 'Сплошной режим',
            'autoMode': 'Авто режим',
            'backgroundSound': 'Фоновый звук',
            'displayRatio': 'Соотношение',
            'normalRatio': 'Обычное соотношение',
            'stretchFill': 'Растянуть',
            'originalSize': 'Оригинальный размер',
            'smartFit': 'Умная подгонка',
            'defaultCrop': 'Обрезка по умолчанию',
            'showIP': 'Показать IP',
            'hideIP': 'Скрыть IP',
            'colorPanel': 'Цветовая панель',
            'apply': 'Применить',
            'reset': 'Сброс',
            'enableAnimation': 'Включить анимацию',
            'disableAnimation': 'Отключить анимацию',
            'fontToggle': 'Стиль шрифта',
            'fontDefault': 'По умолчанию',
            'fontFredoka': 'Fredoka',
            'fontDMSerif': 'DM Serif',
            'fontNotoSerif': 'Noto Serif',
            'fontComicNeue': 'Comic Neue',
            'fontSettings': 'Настройки шрифта',
            'fontSize': 'Размер шрифта',
            'fontColor': 'Цвет шрифта',
            'black': 'Черный',
            'white': 'Белый',
            'red': 'Красный',
            'blue': 'Синий',
            'green': 'Зеленый',
            'purple': 'Фиолетовый',
            'customColor': 'Пользовательский цвет',
            'controlPanel': 'Панель управления',
            'usageGuide': 'Руководство',
            'guide1': '1. Видео режим: Имя по умолчанию "bg.mp4"',
            'guide2': '2. Режим изображения: Имена по умолчанию "bg1-20.jpg"',
            'guide3': '3. Сплошной режим: Прозрачный фон + анимация спектра',
            'guide4': '4. Светлый режим: Переключение в настройках темы автоматически отключит переключатель',
            'guide5': '5. Настройки темы: Поддержка пользовательского фона, для смены режима нужно очистить фон',
            'guide6': '6. Настройки меню: Нажмите Ctrl + Alt + S, чтобы открыть меню настроек',
            'guide7': '7. Адрес проекта: <a class="github-link" href="https://github.com/Thaolga/openwrt-nekobox" target="_blank">Перейти</a>',
            'themeTitle': 'Настройки темы Spectra'
        },
        'de': {
            'enabled': 'Aktiviert',
            'disabled': 'Deaktiviert',
            'currentTheme': 'Aktuelles Thema: ',
            'darkMode': 'Dunkelmodus',
            'lightMode': 'Hellmodus',
            'themeSettings': 'Theme-Einstellungen',
            'videoMode': 'Video-Modus',
            'imageMode': 'Bild-Modus',
            'solidMode': 'Einheitlicher Modus',
            'autoMode': 'Automatik-Modus',
            'backgroundSound': 'Hintergrundsound',
            'displayRatio': 'Anzeigeverhältnis',
            'normalRatio': 'Normales Verhältnis',
            'stretchFill': 'Strecken',
            'originalSize': 'Originalgröße',
            'smartFit': 'Intelligente Anpassung',
            'defaultCrop': 'Standard-Zuschnitt',
            'showIP': 'IP-Info anzeigen',
            'hideIP': 'IP-Info verbergen',
            'colorPanel': 'Farbpanel',
            'apply': 'Übernehmen',
            'reset': 'Zurücksetzen',
            'enableAnimation': 'Animation aktivieren',
            'disableAnimation': 'Animation deaktivieren',
            'fontToggle': 'Schriftstil',
            'fontDefault': 'Standard',
            'fontFredoka': 'Fredoka',
            'fontDMSerif': 'DM Serif',
            'fontNotoSerif': 'Noto Serif',
            'fontComicNeue': 'Comic Neue',
            'fontSettings': 'Schrifteinstellungen',
            'fontSize': 'Schriftgröße',
            'fontColor': 'Schriftfarbe',
            'black': 'Schwarz',
            'white': 'Weiß',
            'red': 'Rot',
            'blue': 'Blau',
            'green': 'Grün',
            'purple': 'Lila',
            'customColor': 'Benutzerdefinierte Farbe',
            'controlPanel': 'Bedienfeld',
            'usageGuide': 'Bedienungsanleitung',
            'guide1': '1. Video-Modus: Standardname ist "bg.mp4"',
            'guide2': '2. Bild-Modus: Standardnamen sind "bg1-20.jpg"',
            'guide3': '3. Einheitlicher Modus: Transparenter Hintergrund + Spektralanimation',
            'guide4': '4. Hellmodus: Wechsel in den Theme-Einstellungen schaltet den Kontrollschalter automatisch aus',
            'guide5': '5. Theme-Einstellungen: Unterstützt benutzerdefinierte Hintergründe, Moduswechsel erfordert Löschen des Hintergrunds',
            'guide6': '6. Menü-Einstellungen: Drücken Sie Strg + Alt + S, um das Einstellungsmenü zu öffnen',
            'guide7': '7. Projektadresse: <a class="github-link" href="https://github.com/Thaolga/openwrt-nekobox" target="_blank">Besuchen</a>',
            'themeTitle': 'Spectra Theme-Einstellungen'
        },
        'fr': {
            'enabled': 'Activé',
            'disabled': 'Désactivé',
            'currentTheme': 'Thème actuel: ',
            'darkMode': 'Mode sombre',
            'lightMode': 'Mode clair',
            'themeSettings': 'Paramètres du thème',
            'videoMode': 'Mode vidéo',
            'imageMode': 'Mode image',
            'solidMode': 'Mode uni',
            'autoMode': 'Mode auto',
            'backgroundSound': 'Son de fond',
            'displayRatio': 'Ratio d\'affichage',
            'normalRatio': 'Ratio normal',
            'stretchFill': 'Étirer',
            'originalSize': 'Taille originale',
            'smartFit': 'Ajustement intelligent',
            'defaultCrop': 'Recadrage par défaut',
            'showIP': 'Afficher IP',
            'hideIP': 'Masquer IP',
            'colorPanel': 'Panneau de couleur',
            'apply': 'Appliquer',
            'reset': 'Réinitialiser',
            'enableAnimation': 'Activer animation',
            'disableAnimation': 'Désactiver animation',
            'fontToggle': 'Style de police',
            'fontDefault': 'Par défaut',
            'fontFredoka': 'Fredoka',
            'fontDMSerif': 'DM Serif',
            'fontNotoSerif': 'Noto Serif',
            'fontComicNeue': 'Comic Neue',
            'fontSettings': 'Paramètres police',
            'fontSize': 'Taille police',
            'fontColor': 'Couleur police',
            'black': 'Noir',
            'white': 'Blanc',
            'red': 'Rouge',
            'blue': 'Bleu',
            'green': 'Vert',
            'purple': 'Violet',
            'customColor': 'Couleur personnalisée',
            'controlPanel': 'Panneau de contrôle',
            'usageGuide': 'Guide d\'utilisation',
            'guide1': '1. Mode vidéo: Nom par défaut "bg.mp4"',
            'guide2': '2. Mode image: Noms par défaut "bg1-20.jpg"',
            'guide3': '3. Mode uni: Fond transparent + animation spectrale',
            'guide4': '4. Mode clair: Basculer dans les paramètres de thème désactive automatiquement le contrôle',
            'guide5': '5. Paramètres thème: Prise en charge des arrière-plans personnalisés, changement de mode nécessite d\'effacer l\'arrière-plan',
            'guide6': '6. Paramètres menu: Ctrl + Alt + S pour ouvrir le menu des paramètres',
            'guide7': '7. Adresse projet: <a class="github-link" href="https://github.com/Thaolga/openwrt-nekobox" target="_blank">Visiter</a>',
            'themeTitle': 'Paramètres du thème Spectra'
        },
        'ar': {
            'enabled': 'مفعل',
            'disabled': 'معطل',
            'currentTheme': 'السمة الحالية: ',
            'darkMode': 'الوضع المظلم',
            'lightMode': 'الوضع الفاتح',
            'themeSettings': 'إعدادات السمة',
            'videoMode': 'وضع الفيديو',
            'imageMode': 'وضع الصورة',
            'solidMode': 'الوضع الصلب',
            'autoMode': 'الوضع التلقائي',
            'backgroundSound': 'صوت الخلفية',
            'displayRatio': 'نسبة العرض',
            'normalRatio': 'النسبة العادية',
            'stretchFill': 'تمديد لملء',
            'originalSize': 'الحجم الأصلي',
            'smartFit': 'ملاءمة ذكية',
            'defaultCrop': 'قص افتراضي',
            'showIP': 'عرض معلومات IP',
            'hideIP': 'إخفاء معلومات IP',
            'colorPanel': 'لوحة الألوان',
            'apply': 'تطبيق',
            'reset': 'إعادة تعيين',
            'enableAnimation': 'تمكين الرسوم المتحركة',
            'disableAnimation': 'تعطيل الرسوم المتحركة',
            'fontToggle': 'نمط الخط',
            'fontDefault': 'افتراضي',
            'fontFredoka': 'Fredoka',
            'fontDMSerif': 'DM Serif',
            'fontNotoSerif': 'Noto Serif',
            'fontComicNeue': 'Comic Neue',
            'fontSettings': 'إعدادات الخط',
            'fontSize': 'حجم الخط',
            'fontColor': 'لون الخط',
            'black': 'أسود',
            'white': 'أبيض',
            'red': 'أحمر',
            'blue': 'أزرق',
            'green': 'أخضر',
            'purple': 'بنفسجي',
            'customColor': 'لون مخصص',
            'controlPanel': 'لوحة التحكم',
            'usageGuide': 'دليل الاستخدام',
            'guide1': '1. وضع الفيديو: الاسم الافتراضي "bg.mp4"',
            'guide2': '2. وضع الصورة: الأسماء الافتراضية "bg1-20.jpg"',
            'guide3': '3. الوضع الصلب: خلفية شفافة + رسوم متحركة طيفية',
            'guide4': '4. الوضع الفاتح: التبديل في إعدادات السمة سيوقف مفتاح التحكم تلقائيًا',
            'guide5': '5. إعدادات السمة: يدعم الخلفيات المخصصة، تغيير الوضع يتطلب مسح الخلفية',
            'guide6': '6. إعدادات القائمة: اضغط Ctrl + Alt + S لفتح قائمة الإعدادات',
            'guide7': '7. عنوان المشروع: <a class="github-link" href="https://github.com/Thaolga/openwrt-nekobox" target="_blank">زيارة</a>',
            'themeTitle': 'إعدادات سمة Spectra'
        },
        'es': {
            'enabled': 'Activado',
            'disabled': 'Desactivado',
            'currentTheme': 'Tema actual: ',
            'darkMode': 'Modo oscuro',
            'lightMode': 'Modo claro',
            'themeSettings': 'Configuración de tema',
            'videoMode': 'Modo de video',
            'imageMode': 'Modo de imagen',
            'solidMode': 'Modo sólido',
            'autoMode': 'Modo automático',
            'backgroundSound': 'Sonido de fondo',
            'displayRatio': 'Relación de visualización',
            'normalRatio': 'Relación normal',
            'stretchFill': 'Estirar para llenar',
            'originalSize': 'Tamaño original',
            'smartFit': 'Ajuste inteligente',
            'defaultCrop': 'Recorte por defecto',
            'showIP': 'Mostrar información de IP',
            'hideIP': 'Ocultar información de IP',
            'colorPanel': 'Panel de color',
            'apply': 'Aplicar',
            'reset': 'Restablecer',
            'enableAnimation': 'Habilitar animación',
            'disableAnimation': 'Deshabilitar animación',
            'fontToggle': 'Estilo de fuente',
            'fontDefault': 'Predeterminado',
            'fontFredoka': 'Fredoka',
            'fontDMSerif': 'DM Serif',
            'fontNotoSerif': 'Noto Serif',
            'fontComicNeue': 'Comic Neue',
            'fontSettings': 'Configuración de fuente',
            'fontSize': 'Tamaño de fuente',
            'fontColor': 'Color de fuente',
            'black': 'Negro',
            'white': 'Blanco',
            'red': 'Rojo',
            'blue': 'Azul',
            'green': 'Verde',
            'purple': 'Morado',
            'customColor': 'Color personalizado',
            'controlPanel': 'Panel de control',
            'usageGuide': 'Guía de uso',
            'guide1': '1. Modo de video: El nombre predeterminado es "bg.mp4"',
            'guide2': '2. Modo de imagen: Los nombres predeterminados son "bg1-20.jpg"',
            'guide3': '3. Modo sólido: Fondo transparente + animación de espectro',
            'guide4': '4. Modo claro: Cambiar en la configuración de tema, desactivará automáticamente el interruptor de control',
            'guide5': '5. Configuración de tema: Admite fondos personalizados, el cambio de modo requiere borrar el fondo',
            'guide6': '6. Configuración de menú: Presiona Ctrl + Alt + S para abrir el menú de configuración',
            'guide7': '7. Dirección del proyecto: <a class="github-link" href="https://github.com/Thaolga/openwrt-nekobox" target="_blank">Haz clic para visitar</a>',
            'themeTitle': 'Configuración de tema Spectra'
        },
        'bn': {
            'enabled': 'সক্রিয়',
            'disabled': 'নিষ্ক্রিয়',
            'currentTheme': 'বর্তমান থিম: ',
            'darkMode': 'ডার্ক মোড',
            'lightMode': 'লাইট মোড',
            'themeSettings': 'থিম সেটিংস',
            'videoMode': 'ভিডিও মোড',
            'imageMode': 'ইমেজ মোড',
            'solidMode': 'সলিড মোড',
            'autoMode': 'অটো মোড',
            'backgroundSound': 'পটভূমি শব্দ',
            'displayRatio': 'প্রদর্শন অনুপাত',
            'normalRatio': 'সাধারণ অনুপাত',
            'stretchFill': 'প্রসারিত করে পূর্ণ করুন',
            'originalSize': 'মূল আকার',
            'smartFit': 'স্মার্ট ফিট',
            'defaultCrop': 'ডিফল্ট ক্রপ',
            'showIP': 'আইপি তথ্য দেখান',
            'hideIP': 'আইপি তথ্য লুকান',
            'colorPanel': 'রঙ প্যানেল',
            'apply': 'প্রয়োগ করুন',
            'reset': 'রিসেট করুন',
            'enableAnimation': 'অ্যানিমেশন সক্রিয় করুন',
            'disableAnimation': 'অ্যানিমেশন নিষ্ক্রিয় করুন',
            'fontToggle': 'ফন্ট স্টাইল',
            'fontDefault': 'ডিফল্ট',
            'fontFredoka': 'Fredoka',
            'fontDMSerif': 'DM Serif',
            'fontNotoSerif': 'Noto Serif',
            'fontComicNeue': 'Comic Neue',
            'fontSettings': 'ফন্ট সেটিংস',
            'fontSize': 'ফন্টের আকার',
            'fontColor': 'ফন্টের রঙ',
            'black': 'কালো',
            'white': 'সাদা',
            'red': 'লাল',
            'blue': 'নীল',
            'green': 'সবুজ',
            'purple': 'বেগুনি',
            'customColor': 'কাস্টম রঙ',
            'controlPanel': 'নিয়ন্ত্রণ প্যানেল',
            'usageGuide': 'ব্যবহার নির্দেশিকা',
            'guide1': '1. ভিডিও মোড: ডিফল্ট নাম "bg.mp4"',
            'guide2': '2. ইমেজ মোড: ডিফল্ট নামগুলি "bg1-20.jpg"',
            'guide3': '3. সলিড মোড: স্বচ্ছ পটভূমি + স্পেকট্রাম অ্যানিমেশন',
            'guide4': '4. লাইট মোড: থিম সেটিংসে সুইচ করুন, নিয়ন্ত্রণ সুইচ স্বয়ংক্রিয়ভাবে বন্ধ হবে',
            'guide5': '5. থিম সেটিংস: কাস্টম পটভূমি সমর্থন করে, মোড সুইচিংয়ের জন্য পটভূমি মুছে ফেলা প্রয়োজন',
            'guide6': '6. মেনু সেটিংস: সেটিংস মেনু খুলতে Ctrl + Alt + S চাপুন',
            'guide7': '7. প্রকল্পের ঠিকানা: <a class="github-link" href="https://github.com/Thaolga/openwrt-nekobox" target="_blank">দেখার জন্য ক্লিক করুন</a>',
            'themeTitle': 'Spectra থিম সেটিংস'
        }
    };

    let currentLanguage = localStorage.getItem('currentLanguage') || 'zh';

    function getLanguageButtonText() {
        switch(currentLanguage) {
            case 'zh': return '繁體中文';
            case 'zh-tw': return 'English';
            case 'en': return '한국어';
            case 'ko': return '日本語';
            case 'ja': return 'Tiếng Việt';
            case 'vi': return 'ภาษาไทย';
            case 'th': return 'Русский';
            case 'ru': return 'Deutsch';
            case 'de': return 'Français';
            case 'fr': return 'العربية';
            case 'ar': return 'Español';
            case 'es': return 'বাংলা';
            case 'bn': return '简体中文';
            default: return '繁體中文';
        }
    }

    function getLanguageButtonColor() {
        switch(currentLanguage) {
            case 'zh': return '#f44336';
            case 'zh-tw': return '#2196F3';
            case 'en': return '#4CAF50';
            case 'ko': return '#FF9800';
            case 'ja': return '#9C27B0';
            case 'vi': return '#009688';
            case 'th': return '#FFEB3B';
            case 'ru': return '#E91E63';
            case 'de': return '#607D8B';
            case 'fr': return '#03A9F4';
            case 'ar': return '#8BC34A';
            case 'es': return '#FF5722';
            case 'bn': return '#795548';
            default: return '#f44336';
        }
    }

    function translateText(key) {
        return languages[currentLanguage][key] || key;
    }

    function getFitButtonText() {
        const savedFit = localStorage.getItem('videoObjectFit') || 'cover';
        const texts = {
            'contain': translateText('normalRatio'),
            'fill': translateText('stretchFill'), 
            'none': translateText('originalSize'),
            'scale-down': translateText('smartFit'),
            'cover': translateText('defaultCrop')
        };
        return texts[savedFit] || translateText('defaultCrop');
    }

    function updateThemeButton(mode) {
        const btn = document.getElementById('theme-toggle');
        if (!btn) return;

        if (mode === "dark") {
            btn.innerHTML = `
                ${translateText('darkMode')}
                <div><i class="bi bi-moon-fill"></i></div>
            `;
        } else {
            btn.innerHTML = `
                ${translateText('lightMode')}
                <div><i class="bi bi-sun-fill"></i></div>
            `;
        }
    }

    fetch("/luci-static/spectra/bgm/theme-switcher.php")
        .then(res => res.json())
        .then(data => {
            updateThemeButton(data.mode);

            const masterSwitch = document.getElementById('master-switch');

            if (data.mode === "light") {
                isEnabled = false;
                localStorage.setItem('backgroundEnabled', 'false');

                if (masterSwitch) {
                    masterSwitch.style.background = '#f44336';
                    masterSwitch.querySelector('.status-led').style.background = '#f44336';
                    masterSwitch.querySelector('span').textContent = translateText('disabled') + ' ❌';
                }
                applyPHPBackground();
            } else {
                const enabled = localStorage.getItem('backgroundEnabled') === 'true';
                isEnabled = enabled;

                if (masterSwitch) {
                    masterSwitch.style.background = enabled ? '#4CAF50' : '#f44336';
                    masterSwitch.querySelector('.status-led').style.background = enabled ? '#4CAF50' : '#f44336';
                    masterSwitch.querySelector('span').textContent = enabled ? translateText('enabled') + ' ✅' : translateText('disabled') + ' ❌';
                }
                if (enabled) {
                    initBackgroundSystem();
                } else {
                    applyPHPBackground();
                }
            }
            updateUIText();
        })
        .catch(error => {
            console.error("获取主题模式失败:", error);
    });

    function generateControlPanel() {
        const isAnimationEnabled = localStorage.getItem('animationEnabled') !== 'false';
        const animationText = isAnimationEnabled ? translateText('disableAnimation') : translateText('enableAnimation');
        const animationIcon = isAnimationEnabled ? '<i class="bi bi-toggle-on status-on" style="color: white"></i>' : '<i class="bi bi-toggle-off status-off" style="color: white"></i>';
        const ipHidden = localStorage.getItem('hideIP') === 'true';
        const ipIcon = ipHidden ? '<i class="bi bi-eye-slash status-off" style="color: white"></i>' : '<i class="bi bi-eye status-on" style="color: white"></i>';
        const soundMuted = localStorage.getItem('videoMuted') === 'true';
        const soundIcon = soundMuted ? '<i class="bi bi-volume-mute status-off" style="color: white"></i>' : '<i class="bi bi-volume-up status-on" style="color: white"></i>';
        const currentFont = localStorage.getItem('selectedFont') || 'default';
        let fontText;
    
        switch (currentFont) {
            case 'fredoka':
                fontText = translateText('fontFredoka');
                break;
            case 'dmserif':
                fontText = translateText('fontDMSerif');
                break;
            case 'notoserif':
                fontText = translateText('fontNotoSerif');
                break;
            case 'comicneue':
                fontText = translateText('fontComicNeue');
                break;
            default:
                fontText = translateText('fontDefault');
        }

        return `
            <div id="settings-icon" style="width: 40px; height: 40px; border-radius: 50%; background: rgba(0, 0, 0, 0.5); display: flex; align-items: center; justify-content: center; overflow: hidden;">
                <img src="/luci-static/spectra/navbar/interface.gif" width="35" height="35" alt="Settings" style="border-radius: 50%; object-fit: cover;">
            </div>
            <div id="mode-popup">
            <div class="modal-header">
                <h5 class="modal-title" id="panel-title">
                    <i class="bi bi-gear-fill"></i>${translateText('controlPanel')}
                </h5>
            </div>
            <div class="button-grid">
                <button id="theme-toggle" class="always-visible" style="background:#2196F3 !important">
                    ${document.body.classList.contains('dark') ? 
                        `${translateText('darkMode')}` : 
                        `${translateText('lightMode')}`}
                    <div><i class="bi ${document.body.classList.contains('dark') ? 'bi-moon-fill' : 'bi-sun-fill'}"></i></div>
                </button>
                <button class="theme-settings-btn">
                    <span>${translateText('themeSettings')}</span>
                    <div><i class="bi bi-brush"></i></div>
                </button>
                <button id="master-switch">
                    <span>${translateText(isEnabled ? 'enabled' : 'disabled')}</span>
                    <div>${isEnabled ? '<i class="bi bi-toggle-on" style="color: white"></i>' : '<i class="bi bi-toggle-off" style="color: white"></i>'}</div>
                </button>
                <button class="sound-toggle">
                    <span>${translateText('backgroundSound')}</span>
                    <div>${soundIcon}</div>
                </button>

                <button data-mode="video">
                    <span>${translateText('videoMode')}</span>
                    <div><i class="bi bi-camera-video"></i></div>
                </button>
                <button data-mode="image">
                    <span>${translateText('imageMode')}</span>
                    <div><i class="bi bi-image"></i></div>
                </button>
                <button data-mode="solid">
                    <span>${translateText('solidMode')}</span>
                    <div><i class="bi bi-moon"></i></div>
                </button>
                <button data-mode="auto">
                    <span>${translateText('autoMode')}</span>
                    <div><i class="bi bi-stars" style="color: white"></i></div>
                </button>


                <button class="object-fit-btn" style="opacity:1 !important;pointer-events:auto !important">
                    <span>${translateText('displayRatio')}</span>
                    <div>${getFitButtonText()}</div>
                </button>
                <button class="ip-toggle">
                    <span>${ipHidden ? translateText('showIP') : translateText('hideIP')}</span>
                    <div>${ipIcon}</div>
                </button>
                <button id="language-toggle">
                    <span>${getLanguageButtonText()}</span>
                    <div><i class="bi bi-translate" style="color:${getLanguageButtonColor()}"></i></div>
                </button>
                <button id="animation-toggle" class="always-visible">
                    <span>${animationText}</span>
                    <div>${animationIcon}</div>
                </button>

                <button id="font-toggle" class="object-fit-btn">
                    <span>${translateText('fontToggle')}</span>
                    <div>${getFontButtonText()}</div>
                </button>
                <button id="color-panel-btn">
                    <span>${translateText('colorPanel')}</span>
                    <div><i class="bi bi-palette"></i></div>
                </button>
                <button id="font-settings-btn" class="always-visible">
                    <span>${translateText('fontSettings')}</span>
                    <div><i class="bi bi-textarea-t"></i></div>
                </button>
                <button class="info-btn">
                    <span>${translateText('usageGuide')}</span>
                    <div><i class="bi bi-info-circle"></i></div>
                </button>
            </div>
        </div>
        `;
    }

    function bindControlPanelEvents() {
        document.querySelector('.theme-settings-btn')?.addEventListener('click', showThemeSettings);
        document.querySelectorAll('[data-mode]').forEach(btn => {
            btn.addEventListener('click', function() {
                setMode(btn.dataset.mode);
                document.querySelectorAll('[data-mode]').forEach(b => b.classList.remove('selected-mode'));
                this.classList.add('selected-mode');
                localStorage.setItem('selectedMode', btn.dataset.mode);
            });
        });

        document.querySelector('.sound-toggle').addEventListener('click', function(e) {
            e.stopPropagation();
            const newMuted = localStorage.getItem('videoMuted') !== 'true';
            localStorage.setItem('videoMuted', newMuted);
            
            this.querySelector('div').innerHTML = newMuted ? 
                '<i class="bi bi-volume-up status-on" style="color: white"></i>' : 
                '<i class="bi bi-volume-mute status-off" style="color: white"></i>';
                
            if (videoTag) {
                videoTag.muted = newMuted;
            }
        });

        document.getElementById('color-panel-btn')?.addEventListener('click', function() {
            openColorPicker();
        });

        document.getElementById('font-settings-btn')?.addEventListener('click', function(e) {
            e.stopPropagation();
            showFontSettings();
        });


        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.altKey && e.key.toLowerCase() === 's') {
                e.preventDefault();
                const popup = document.getElementById('mode-popup');
                popup.classList.toggle('show');
            }
        });

        document.getElementById('font-toggle')?.addEventListener('click', function(e) {
            e.stopPropagation();
    
            const currentFont = localStorage.getItem('selectedFont') || 'default';
            let nextFont, nextFontName;
    
            switch (currentFont) {
                case 'default': nextFont = 'fredoka'; break;
                case 'fredoka': nextFont = 'dmserif'; break;
                case 'dmserif': nextFont = 'notoserif'; break;
                case 'notoserif': nextFont = 'comicneue'; break;
                default: nextFont = 'default';
            }
    
            localStorage.setItem('selectedFont', nextFont);
            this.querySelector('div').textContent = getFontButtonText();
            applyFont(nextFont);
        });

        document.getElementById('animation-toggle')?.addEventListener('click', function(e) {
            e.stopPropagation();

            const isAnimationEnabled = localStorage.getItem('animationEnabled') !== 'false';
            const newState = !isAnimationEnabled;
            localStorage.setItem('animationEnabled', newState);

            this.querySelector('span').textContent = newState ? 
                translateText('disableAnimation') : 
                translateText('enableAnimation');
    
            this.querySelector('div').innerHTML = newState ? 
                '<i class="bi bi-toggle-on status-on"></i>' : 
                '<i class="bi bi-toggle-off status-off"></i>';

            toggleAnimation(newState);
        });

        document.getElementById('theme-toggle')?.addEventListener('click', function(e) {
            e.stopPropagation();

            const switchingToLight = document.body.classList.contains('dark');

            fetch("/luci-static/spectra/bgm/theme-switcher.php", { method: "POST" })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {

                        if (switchingToLight) {
                            localStorage.setItem('backgroundEnabled', 'false');
                            clearBackgroundSystem();
                        }

                        updateThemeButton(data.mode);
                        updateUIText(); 
                        setTimeout(() => location.reload(), 300);
                    }
                })
        });

        document.querySelector('.info-btn').addEventListener('click', () => {
            showCustomAlert(translateText('usageGuide'), [
                translateText('guide1'),
                translateText('guide2'),
                translateText('guide3'),
                translateText('guide4'),
                translateText('guide5'),
                translateText('guide6'),
                translateText('guide7')
            ]);
        });

        document.getElementById('language-toggle').addEventListener('click', function(e) {
            e.stopPropagation();
    
            switch(currentLanguage) {
                case 'zh': currentLanguage = 'zh-tw'; break;
                case 'zh-tw': currentLanguage = 'en'; break;
                case 'en': currentLanguage = 'ko'; break;
                case 'ko': currentLanguage = 'ja'; break;
                case 'ja': currentLanguage = 'vi'; break;
                case 'vi': currentLanguage = 'th'; break;
                case 'th': currentLanguage = 'ru'; break;
                case 'ru': currentLanguage = 'de'; break;
                case 'de': currentLanguage = 'fr'; break;
                case 'fr': currentLanguage = 'ar'; break;
                case 'ar': currentLanguage = 'es'; break;
                case 'es': currentLanguage = 'bn'; break;
                case 'bn': currentLanguage = 'zh'; break;
                default: currentLanguage = 'zh';

            }
            localStorage.setItem('currentLanguage', currentLanguage);
    
            this.querySelector('span').textContent = getLanguageButtonText();
            this.querySelector('div').innerHTML = `<i class="bi bi-translate" style="color:${getLanguageButtonColor()}"></i>`;
    
            updateUIText();
    
            const fontSettingsOverlay = document.getElementById('font-settings-overlay');
            if (fontSettingsOverlay) {
                updateFontSettingsText();
            }
        });

        document.getElementById('settings-icon').addEventListener('click', function(e) {
            e.stopPropagation();
            const popup = document.getElementById('mode-popup');
            popup.classList.toggle('show');
        });

        document.getElementById('master-switch').addEventListener('click', function(e) {
            e.stopPropagation();

            isEnabled = !isEnabled;
            localStorage.setItem('backgroundEnabled', isEnabled);
            this.style.background = isEnabled ? '#4CAF50' : '#f44336';
            this.querySelector('span').textContent = translateText(isEnabled ? 'enabled' : 'disabled');
            this.querySelector('div').innerHTML = isEnabled ? 
                '<i class="bi bi-toggle-on" style="color: white"></i>' : 
                '<i class="bi bi-toggle-off" style="color: white"></i>';
            document.querySelectorAll('#mode-popup button:not(.always-visible):not(#master-switch):not(.sound-toggle):not(#redirect-btn):not(.info-btn):not(#language-toggle)').forEach(btn => {
                btn.style.opacity = isEnabled ? 1 : 0.5;
                btn.style.pointerEvents = isEnabled ? 'auto' : 'none';
            });

            if (isEnabled) {
                initBackgroundSystem();
            } else {
                clearBackgroundSystem();
            }
        });

        document.querySelector('.object-fit-btn')?.addEventListener('click', function() {
            const videos = document.querySelectorAll('video#background-video');
            if (videos.length === 0) return;

            const currentFit = videos[0].style.objectFit || localStorage.getItem('videoObjectFit') || 'cover';
            const fitOrder = ['cover', 'contain', 'fill', 'none', 'scale-down'];
            const newIndex = (fitOrder.indexOf(currentFit) + 1) % fitOrder.length;
            const newFit = fitOrder[newIndex];

            videos.forEach(video => {
                video.style.objectFit = newFit;
                if (newFit === 'none') {
                    video.style.minWidth = 'auto';
                    video.style.minHeight = 'auto';
                    video.style.width = '100%';
                    video.style.height = '100%';
                } else {
                    video.style.minWidth = '100%';
                    video.style.minHeight = '100%';
                }
            });

            localStorage.setItem('videoObjectFit', newFit);
            this.querySelector('div').textContent = getFitButtonText();
        });

        document.addEventListener('click', function(e) {
            const toggleBtn = e.target.closest('.ip-toggle');
            if (toggleBtn && ipContainer) {
                e.stopPropagation();
                const currentState = localStorage.getItem('hideIP') === 'true';
                const newState = !currentState;
        
                ipContainer.style.display = newState ? 'none' : 'flex';
                localStorage.setItem('hideIP', newState);
        
                toggleBtn.querySelector('span').textContent = newState ? translateText('showIP') : translateText('hideIP');
                toggleBtn.querySelector('div').innerHTML = newState ? 
                    '<i class="bi bi-eye-slash status-off" style="color: white"></i>' : 
                    '<i class="bi bi-eye status-on" style="color: white"></i>';
            }
        });

    }

    const styles = `
        .font-default {
            font-family: -apple-system, BlinkMacSystemFont, sans-serif !important;
        }

        .font-fredoka {
            font-family: 'Fredoka One', cursive !important;
        }

        .font-dmserif {
            font-family: 'DM Serif Display', serif !important;
        }

        .font-notoserif {
            font-family: 'Noto Serif SC', serif !important;
        }

        .font-comicneue {
            font-family: 'Comic Neue', cursive !important;
        }

        #settings-icon {
            position: fixed;
            right: 2%;
            bottom: 20px;
            cursor: pointer;
            z-index: 1001;
            font-size: 24px;
            background: rgba(0,0,0,0.5);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            transition: transform 0.3s ease;
        }

        #mode-popup button.sound-toggle {
            opacity: 1 !important;
            pointer-events: auto !important;
        }

        #redirect-btn,
        .info-btn {
            opacity: 1 !important;
            pointer-events: auto !important;
        }

        #settings-icon:hover {
            transform: rotate(90deg);
        }

        #mode-popup {
            position: fixed;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            background: var(--popup-bg-color, rgba(27, 27, 47, 0.9));
            border-radius: 10px;
            padding: 15px;
            color: white;
            z-index: 1002;
            display: none;
            width: 95%;
            max-width: 800px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.5);
            opacity: 0;
            transition: opacity 0.3s ease, background 0.3s ease;
            max-height: 90vh;
            overflow-y: auto;
        }

        .button-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            justify-items: center;
        }

        #mode-popup.show {
            display: block;
            opacity: 1;
        }

        #mode-popup button {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 100%;
            min-width: 120px;
            padding: 12px 8px;
            min-height: 80px;
            border: none;
            font-size: 14px !important; 
            color: white;
            border-radius: 5px;
            cursor: pointer;
            background: #444;
            opacity: ${isEnabled ? 1 : 0.5};
            pointer-events: ${isEnabled ? 'auto' : 'none'};
            transition: all 0.3s ease;
            text-align: center;
        }

        #mode-popup button:hover {
            transform: scale(1.05);
            box-shadow: 0 2px 10px rgba(0,0,0,0.3);
        }

        #mode-popup button span {
            display: block;
            margin-bottom: 6px;
            font-size: 14px;
            line-height: 1.2;
        }

        #mode-popup button div {
            font-size: 20px;
            margin-top: 5px;
        }

        #master-switch {
            background: ${isEnabled ? '#4CAF50' : '#f44336'};
            pointer-events: auto !important;
            opacity: 1 !important;
        }

        .status-led {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            box-shadow: 0 0 5px ${isEnabled ? '#4CAF50' : '#f44336'};
        }

        .selected-mode {
            background: #007BFF !important;
        }

        #mode-popup button.object-fit-btn {
            opacity: 1 !important;
            pointer-events: auto !important;
            background: #007BFF !important;
        }

        #mode-popup button.object-fit-btn div {
            color: #FFEB3B;
            margin-left: 8px;
            font-weight: bold;
        }

        #mode-popup button.ip-toggle {
            opacity: 1 !important;      
            pointer-events: auto !important;  
            background: #00A497 !important;  
        }

        #mode-popup button.theme-settings-btn {
            background: #EB6EA5 !important;
            opacity: 1 !important;
            pointer-events: auto !important;
        }

        #mode-popup button#language-toggle {
            background: #FF9800 !important;
            opacity: 1 !important;
            pointer-events: auto !important;
        }

        #mode-popup button#color-panel-btn {
            background: #795548 !important;
            opacity: 1 !important;
            pointer-events: auto !important;
        }

        #mode-popup button.always-visible {
            opacity: 1 !important;
            pointer-events: auto !important;
            background: #673AB7 !important;
        }

        #mode-popup button#font-toggle {
            opacity: 1 !important;
            pointer-events: auto !important;
            background: #9C27B0 !important;
        }

        #mode-popup,
        #custom-alert,
        #color-picker-dialog,
        #font-settings-dialog,
        #theme-settings-dialog {
            font-family: inherit !important;
        }

        #mode-popup *,
        #custom-alert *,
        #color-picker-dialog *,
        #font-settings-dialog *,
        #theme-settings-dialog * {
            font-family: inherit !important;
        }

        .status-indicator {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 20px;
            height: 20px;
            margin-left: 5px;
            font-size: 14px;
        }
    
        #master-switch i {
            color: white !important;
        }

        .status-on, .status-off {
            color: white !important;
        }
    
        #theme-toggle div,
        .theme-settings-btn div,
        #color-panel-btn div,
        #font-settings-btn div,
        .info-btn div {
            color: white !important;
        }

        #mode-popup button#language-toggle {
            background: #FF9800 !important;
            opacity: 1 !important;
            pointer-events: auto !important;
        }

        #mode-popup button#language-toggle div i {
            color: white !important;
        }

        body.video-mode [data-mode="video"],
        body.video-mode .object-fit-btn,
        body.video-mode .sound-toggle {
            opacity: 1 !important;
            pointer-events: auto !important;
        }

        [data-mode="video"].selected-mode {
            background: #007BFF !important;
        }

        .modal-header {
            display: flex;
            justify-content: flex-start;
            align-items: center;
            padding: 15px 20px;
            border-bottom: 1px solid #444;
            background: rgba(0, 0, 0, 0.2);
            border-top-left-radius: 0.5rem;
            border-top-right-radius: 0.5rem;
        }
    
        .modal-title {
            margin: 0;
            font-weight: bold;
            color: #9C27B0;
            font-size: 1.3rem !important;
            display: flex;
            align-items: center;
            gap: 10px;
        }
    
        .modal-title i {
            font-size: 1.1rem;
        }

        @media (max-width: 768px) {
            #mode-popup {
                position: fixed !important;
                top: 8% !important;
                left: 50% !important;
                transform: translate(-50%, 0) !important;
                width: 90% !important;
                height: auto !important;
                max-height: none !important;
                overflow: visible !important;
                padding: 10px !important;
                box-sizing: border-box;
            }
       
            .button-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 8px;
            }
        
            #mode-popup button {
                min-height: 60px;
                padding: 8px 5px;
            }
        
            #mode-popup button span {
                font-size: 12px;
            }
        
            #mode-popup button div {
                font-size: 16px;
            }
        }

        @media (min-width: 769px) and (max-width: 1024px) {
            .button-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (min-width: 1025px) {
            .button-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }
    
        @media (max-width: 600px) {
            #settings-icon {
                right: 8%;
                bottom: 10px;
                width: 30px;
                height: 30px;
                font-size: 18px;
            }
            #mode-popup {
                right: 10px;
                top: 50px;
                min-width: 120px;
            }
            #mode-popup button {
                padding: 6px;
                font-size: 12px;
            }
        }
    `;

    const styleTag = document.createElement('style');
    styleTag.textContent = styles;
    document.head.appendChild(styleTag);

    document.body.insertAdjacentHTML('beforeend', generateControlPanel());
    bindControlPanelEvents();

    if (localStorage.getItem('hideIP') === null) {
        localStorage.setItem('hideIP', 'false');
    }

    function showCustomAlert(title, messages) {
        const existingAlert = document.getElementById('custom-alert');
        if (existingAlert) existingAlert.remove();

        const alertHTML = `
            <div id="custom-alert-overlay">
                <div id="custom-alert" class="font-${localStorage.getItem('selectedFont') || 'default'}">
                    <div class="alert-header">
                        <h3>${translateText(title)}</h3>
                        <button class="close-btn">&times;</button>
                    </div>
                    <div class="alert-content">
                        ${messages.map(msg => `<p>${translateText(msg)}</p>`).join('')}
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', alertHTML);

        const style = document.createElement('style');
        style.textContent = `
            #custom-alert-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.7);
                z-index: 9999;
                display: flex;
                justify-content: center;
                align-items: center;
                backdrop-filter: blur(3px);
            }

            #custom-alert {
                background: rgba(0,0,0,0.95);
                border: 1px solid #333;
                border-radius: 8px;
                width: 90%;
                max-width: 500px;
                padding: 20px;
                box-shadow: 0 0 20px rgba(0,0,0,0.5);
                color: #fff;
            }

            .alert-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                border-bottom: 1px solid #333;
                padding-bottom: 15px;
                margin-bottom: 15px;
            }

            .alert-header h3 {
                margin: 0;
                color: #4CAF50;
                font-size: 1.3em;
            }

            .close-btn {
                background: none;
                border: none;
                color: #fff;
                font-size: 24px;
                cursor: pointer;
                padding: 0 8px;
                transition: color 0.3s;
            }

            .close-btn:hover {
                color: #4CAF50;
            }

            .alert-content {
                max-height: 60vh;
                overflow-y: auto;
            }

            .alert-content p {
                line-height: 1.6;
                margin: 10px 0;
                color: #ddd;
                font-size: 14px;
            }

            @media (max-width: 480px) {
                #custom-alert {
                    width: 95%;
                    padding: 15px;
                }
                
                .alert-header h3 {
                    font-size: 1.1em;
                }
                
                .alert-content p {
                    font-size: 13px;
                }
            }
        `;
        document.head.appendChild(style);

        document.querySelector('.close-btn').addEventListener('click', () => {
            document.getElementById('custom-alert-overlay').remove();
            style.remove();
        });

        document.getElementById('custom-alert-overlay').addEventListener('click', (e) => {
            if (e.target.id === 'custom-alert-overlay') {
                document.getElementById('custom-alert-overlay').remove();
                style.remove();
            }
        });
    }

    function initBackgroundSystem() {
        bgImages = Array.from({length: 20}, (_, i) => `bg${i + 1}.jpg`);
        bgIndex = 0;
        availableImages = [];
    
        const savedMode = localStorage.getItem('backgroundMode') || 'auto';
    
        const phpBackgroundSrc = localStorage.getItem('phpBackgroundSrc');
        const phpBackgroundType = localStorage.getItem('phpBackgroundType');
    
        if (phpBackgroundSrc && phpBackgroundType) {
            if (phpBackgroundType === 'image') {
                setImageBackground(phpBackgroundSrc);
            } else if (phpBackgroundType === 'video') {
                setVideoBackground(phpBackgroundSrc);
            }
        } else {
            setMode(savedMode);
        }
    
        document.querySelectorAll('[data-mode]').forEach(btn => {
            btn.classList.toggle('selected-mode', btn.dataset.mode === savedMode);
        });
    }

    function clearCustomBackground() {
        localStorage.removeItem('phpBackgroundSrc');
        localStorage.removeItem('phpBackgroundType');
    
        const savedMode = localStorage.getItem('backgroundMode') || 'auto';
        setMode(savedMode);
    }
   
    function updateModeButtons() {
        const currentMode = localStorage.getItem('backgroundMode') || 'auto';
        document.querySelectorAll('[data-mode]').forEach(btn => {
            btn.classList.toggle('selected-mode', btn.dataset.mode === currentMode);
        });
    }

    function clearBackgroundSystem() {
        const currentMode = localStorage.getItem('backgroundMode') || 'auto';
    
        if (videoTag) {
            videoTag.pause();
            videoTag.remove();
            videoTag = null;
        }
    
        document.querySelectorAll('#dynamic-style, #video-style').forEach(e => e.remove());
        clearInterval(switchInterval);
        document.body.style.background = '';
        document.body.style.backgroundImage = '';
        document.body.style.display = 'none';
        document.body.offsetHeight;
        document.body.style.display = '';
    
        localStorage.setItem('backgroundMode', currentMode);
    
        document.querySelectorAll('[data-mode]').forEach(btn => {
            btn.classList.toggle('selected-mode', btn.dataset.mode === currentMode);
        });
    }

    function toggleAnimation(enabled) {
        const wrapper = document.querySelector('.wrapper');
        if (wrapper) {
            wrapper.style.display = enabled ? 'block' : 'none';
        }
    }

    function applyFont(font) {
        loadFont(font);
    
        const styleTag = document.querySelector("#font-style") || document.createElement("style");
        styleTag.id = "font-style";
    
        let fontFamily;
        switch (font) {
            case 'fredoka': 
                fontFamily = "'Fredoka One', cursive";
                break;
            case 'dmserif':
                fontFamily = "'DM Serif Display', serif";
                break;
            case 'notoserif':
                fontFamily = "'Noto Serif SC', serif";
                break;
            case 'comicneue':
                fontFamily = "'Comic Neue', cursive";
                break;
            default:
                fontFamily = "-apple-system, BlinkMacSystemFont, sans-serif";
        }

        styleTag.textContent = `
            body,
            #mode-popup,
            #custom-alert,
            #color-picker-dialog,
            #font-settings-dialog,
            #theme-settings-dialog,
            #mode-popup *,
            #custom-alert *,
            #color-picker-dialog *,
            #font-settings-dialog *,
            #theme-settings-dialog * {
                font-family: ${fontFamily} !important;
            }
        `;
    
        const oldStyle = document.getElementById('font-style');
        if (oldStyle) oldStyle.remove();
        document.head.appendChild(styleTag);
    
        refreshOpenPopups(font);
    }

    function updateUIText() {
        const popup = document.getElementById('mode-popup');
        if (!popup) return;
    
        const masterSwitch = popup.querySelector('#master-switch');
        if (masterSwitch) {
            masterSwitch.querySelector('span').textContent = translateText(isEnabled ? 'enabled' : 'disabled');
        }

        const panelTitle = document.getElementById('panel-title');
        if (panelTitle) {
            panelTitle.innerHTML = `<i class="bi bi-gear-fill"></i>${translateText('controlPanel')}`;
        }

        const soundMuted = localStorage.getItem('videoMuted') === 'true';
        const soundToggle = popup.querySelector('.sound-toggle');
        if (soundToggle) {
        soundToggle.querySelector('div').innerHTML = soundMuted ? 
            '<i class="bi bi-volume-mute status-off" style="color: white"></i>' : 
            '<i class="bi bi-volume-up status-on" style="color: white"></i>';
        }
    
        const themeToggle = popup.querySelector('#theme-toggle');
        if (themeToggle) {
            themeToggle.innerHTML = document.body.classList.contains('dark') ? 
                `${translateText('darkMode')}<div><i class="bi bi-moon-fill"></i></div>` : 
                `${translateText('lightMode')}<div><i class="bi bi-sun-fill"></i></div>`;
        }
    
        popup.querySelector('.theme-settings-btn span').textContent = translateText('themeSettings');
        popup.querySelector('[data-mode="video"] span').textContent = translateText('videoMode');
        popup.querySelector('[data-mode="image"] span').textContent = translateText('imageMode');
        popup.querySelector('[data-mode="solid"] span').textContent = translateText('solidMode');
        popup.querySelector('[data-mode="auto"] span').textContent = translateText('autoMode');
        popup.querySelector('.sound-toggle span').textContent = translateText('backgroundSound');
        popup.querySelector('.object-fit-btn span').textContent = translateText('displayRatio');
        popup.querySelector('.object-fit-btn div').textContent = getFitButtonText();
    
        const ipHidden = localStorage.getItem('hideIP') === 'true';
        popup.querySelector('.ip-toggle span').textContent = ipHidden ? translateText('showIP') : translateText('hideIP');
    
        popup.querySelector('.info-btn span').textContent = translateText('usageGuide');
        popup.querySelector('#font-settings-btn span').textContent = translateText('fontSettings');
        popup.querySelector('#color-panel-btn span').textContent = translateText('colorPanel');
    
        const isAnimationEnabled = localStorage.getItem('animationEnabled') !== 'false';
        popup.querySelector('#animation-toggle span').textContent = isAnimationEnabled ? 
            translateText('disableAnimation') : 
            translateText('enableAnimation');
    
        popup.querySelector('#font-toggle span').textContent = translateText('fontToggle');
        popup.querySelector('#font-toggle div').textContent = getFontButtonText();
    
        const langBtn = popup.querySelector('#language-toggle');
        if (langBtn) {
            langBtn.querySelector('span').textContent = getLanguageButtonText();
            langBtn.querySelector('div').innerHTML = `<i class="bi bi-translate" style="color:${getLanguageButtonColor()}"></i>`;
        }
    }

    function loadFont(font) {
        const fontMap = {
            'fredoka': 'Fredoka+One',
            'dmserif': 'DM+Serif+Display',
            'notoserif': 'Noto+Serif+SC',
            'comicneue': 'Comic+Neue'
        };
    
        if (font !== 'default' && fontMap[font]) {
            if (!document.querySelector(`link[href*="${fontMap[font]}"`)) {
                const link = document.createElement('link');
                link.href = `https://fonts.googleapis.com/css2?family=${fontMap[font]}&display=swap`;
                link.rel = 'stylesheet';
                document.head.appendChild(link);
            }
        }
    }

    function refreshOpenPopups(font) {
        const fontClass = `font-${font}`;
    
        const popups = [
            document.getElementById('mode-popup'),
            document.getElementById('custom-alert'),
            document.getElementById('color-picker-dialog'),
            document.getElementById('font-settings-dialog'),
            document.getElementById('theme-settings-dialog')
        ];
    
        popups.forEach(popup => {
            if (popup) {
                popup.classList.remove(
                    'font-default',
                    'font-fredoka',
                    'font-dmserif',
                    'font-notoserif',
                    'font-comicneue'
                );
                popup.classList.add(fontClass);
            }
        });
    }

    function getFontButtonText() {
        const currentFont = localStorage.getItem('selectedFont') || 'default';
        switch (currentFont) {
            case 'fredoka': return translateText('fontFredoka');
            case 'dmserif': return translateText('fontDMSerif');
            case 'notoserif': return translateText('fontNotoSerif');
            case 'comicneue': return translateText('fontComicNeue');
            default: return translateText('fontDefault');
        }
    }

    function setMode(mode) {
        if (!isEnabled && mode !== 'solid') return;
    
        localStorage.setItem('backgroundMode', mode);
        localStorage.setItem('selectedMode', mode);
    
        clearExistingBackground();

        switch (mode) {
            case 'video':
                checkFileExists('bg.mp4', exists => exists ? initVideoBackground() : fallbackToAuto());
                break;
            case 'image':
                checkImages(() => availableImages.length > 0 ? initImageBackground() : fallbackToAuto());
                break;
            case 'solid':
                applyCSS(null);
                break;
            case 'color':
                const savedColor = localStorage.getItem('customBackgroundColor') || '#333333';
                document.body.style.background = savedColor;
                document.body.style.backgroundSize = 'auto';
                break;
            case 'auto':
                initAutoBackground();
                break;
        }
    
        document.querySelectorAll('[data-mode]').forEach(btn => {
            btn.classList.toggle('selected-mode', btn.dataset.mode === mode);
        });
    }

    function clearExistingBackground() {
        if (videoTag) {
            videoTag.pause();
            videoTag.remove();
            videoTag = null;
        }
    
        document.querySelectorAll('#dynamic-style, #video-style').forEach(e => e.remove());
    
        clearInterval(switchInterval);
    
        const currentMode = localStorage.getItem('backgroundMode');
        if (currentMode !== 'color') {
            document.body.style.background = '';
            document.body.style.backgroundImage = '';
        }
    
        const existingVideoTag = document.getElementById("background-video");
        if (existingVideoTag && existingVideoTag !== videoTag) {
            existingVideoTag.remove();
        }
    }

    function initVideoBackground() {
        insertVideoBackground();
        updateSoundToggle();
    }

    function initImageBackground() {
        bgIndex = Math.floor(Math.random() * availableImages.length);
        applyCSS(availableImages[bgIndex]);
        switchInterval = setInterval(switchBackground, 120000);
    }

    function initAutoBackground() {
        checkFileExists('bg.mp4', exists => {
            if (exists) {
                initVideoBackground();
            } else {
                checkImages(() => {
                    availableImages.length > 0 ? initImageBackground() : applyCSS(null);
                });
            }
        });
    }

    function fallbackToAuto() {
        console.log('资源不存在，已切换至自动模式');
        localStorage.setItem('backgroundMode', 'auto');
        initAutoBackground();
    }

    function checkImages(callback) {
        availableImages = [];
        let checked = 0;
        bgImages.forEach(image => {
            checkFileExists(image, exists => {
                checked++;
                if (exists) availableImages.push(image);
                if (checked === bgImages.length) callback();
            });
        });
    }

    function checkFileExists(file, callback) {
        const xhr = new XMLHttpRequest();
        xhr.open('HEAD', `/luci-static/spectra/bgm/${file}`);
        xhr.onreadystatechange = () => xhr.readyState === 4 && callback(xhr.status === 200);
        xhr.send();
    }

    function openColorPicker() {
        const overlay = document.createElement('div');
        overlay.id = 'color-picker-overlay';
        overlay.innerHTML = `
            <div id="color-picker-dialog" class="font-${localStorage.getItem('selectedFont') || 'default'}">
                <div class="dialog-header">
                    <h3>${translateText('colorPanel')}</h3>
                    <button class="close-btn">&times;</button>
                </div>
                <div class="color-preview" id="color-preview"></div>
                <div class="color-controls">
                    <div class="color-input-group">
                        <input type="color" id="color-selector">
                        <input type="text" id="color-input" placeholder="#RRGGBB">
                    </div>
                    <div class="button-group">
                        <button id="apply-color">${translateText('apply')}</button>
                        <button id="reset-color">${translateText('reset')}</button>
                    </div>
                </div>
                <div class="preset-colors">
                    ${generateColorPresets()}
                </div>
            </div>
        `;
        
        document.body.appendChild(overlay);
        
        const style = document.createElement('style');
        style.textContent = `
            #color-picker-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.7);
                z-index: 9999;
                display: flex;
                justify-content: center;
                align-items: center;
                backdrop-filter: blur(3px);
            }
            
            #color-picker-dialog {
                background: rgba(0,0,0,0.9);
                width: 400px;
                padding: 25px;
                border-radius: 10px;
                box-shadow: 0 0 25px rgba(0,0,0,0.6);
                border: 1px solid #444;
            }
            
            .dialog-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 15px;
                padding-bottom: 10px;
                border-bottom: 1px solid #444;
            }
            
            .dialog-header h3 {
                margin: 0;
                color: #9C27B0;
                font-size: 1.2em;
            }
            
            .close-btn {
                background: none;
                border: none;
                color: #fff;
                font-size: 24px;
                cursor: pointer;
            }
            
            .color-preview {
                width: 100%;
                height: 100px;
                border-radius: 5px;
                margin-bottom: 15px;
                border: 1px solid #555;
                transition: background 0.3s;
            }
            
            .color-controls {
                display: flex;
                flex-direction: column;
                gap: 15px;
                margin-bottom: 20px;
                align-items: center; 
            }

            .color-input-group {
                display: flex;
                gap: 15px;
                width: 100%;
            }
               
            #color-selector {
                height: 50px;
                min-width: 80px;
                flex: none;
            }
            
            #color-input {
                flex: 1;
                padding: 12px;
                font-size: 16px;
                background: #222;
                border: 1px solid #444;
                color: #fff;
                border-radius: 4px;
                min-width: 0;
            }

            .button-group {
                display: flex;
                gap: 10px;
                width: 100%;
            }
            
            #apply-color, #reset-color {
                flex: 1;
                background: #4CAF50;
                border: none;
                color: white;
                border-radius: 4px;
                cursor: pointer;
                transition: background 0.3s;
                padding: 12px;
                font-size: 16px;
                white-space: nowrap; 
            }
            
            #reset-color {
                background: #f44336;
            }
            
            #apply-color:hover {
                background: #45a049;
            }
            
            #reset-color:hover {
                background: #d32f2f;
            }
            
            .preset-colors {
                display: grid;
                grid-template-columns: repeat(8, 1fr);
                gap: 8px;
            }
            
            .color-preset {
                height: 35px;
                border-radius: 4px;
                cursor: pointer;
                border: 2px solid transparent;
                transition: transform 0.2s, border-color 0.2s;
            }
            
            .color-preset:hover {
                transform: scale(1.1);
            }
            
            @media (max-width: 480px) {
                #color-picker-dialog {
                    width: 90%;
                    padding: 15px;
                }
            
                .preset-colors {
                    grid-template-columns: repeat(5, 1fr);
                }
            }
        `;
        document.head.appendChild(style);
        
        const savedColor = localStorage.getItem('customBackgroundColor') || '#333333';
        document.getElementById('color-preview').style.background = savedColor;
        document.getElementById('color-selector').value = savedColor;
        document.getElementById('color-input').value = savedColor;
        
        document.getElementById('color-selector').addEventListener('input', function(e) {
            const color = e.target.value;
            document.getElementById('color-preview').style.background = color;
            document.getElementById('color-input').value = color;
        });
        
        document.getElementById('color-input').addEventListener('input', function(e) {
            const color = e.target.value;
            if (isValidColor(color)) {
                document.getElementById('color-preview').style.background = color;
                document.getElementById('color-selector').value = color;
            }
        });
        
        document.getElementById('apply-color').addEventListener('click', function() {
            const color = document.getElementById('color-input').value;
            if (isValidColor(color)) {
                applyCustomBackgroundColor(color);
            }
        });
        
        document.getElementById('reset-color').addEventListener('click', function() {
            resetCustomBackgroundColor();
            setTimeout(() => location.reload(), 300); 
        });
        
        document.querySelector('.close-btn').addEventListener('click', function() {
            overlay.remove();
            style.remove();
        });
        
        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) {
                overlay.remove();
                style.remove();
            }
        });
        
        document.querySelectorAll('.color-preset').forEach(preset => {
            preset.addEventListener('click', function() {
                const color = this.getAttribute('data-color');
                document.getElementById('color-preview').style.background = color;
                document.getElementById('color-selector').value = color;
                document.getElementById('color-input').value = color;
                applyCustomBackgroundColor(color);
            });
        });
    }

    function generateColorPresets() {
        const presets = [
            '#0f3460', '#16213e', '#1a1a2e', '#1b1b2f', '#1e272e', '#1f4068', '#1e3799', '#222f3e', '#212529',
            '#343a40', '#485460', '#495057', '#4a69bd', '#4361ee', '#4895ef', '#90e0ef',
            '#00b4d8', '#00cec9', '#4cc9f0', '#60a3bc', '#6a89cc', '#82ccdd', '#81ecec',
            '#1b4332', '#2d6a4f', '#40916c', '#52b788', '#55efc4', '#74c69d', '#95d5b2', '#b7e4c7', '#d8f3dc',
            '#6c757d', '#808e9b', '#8d99ae', '#adb5bd', '#ced4da', '#ffffff',
            '#7209b7', '#560bad', '#3a0ca3', '#3f37c9', '#b5179e', '#f72585',
            '#e94560', '#f67280', '#ff7c7c', '#ff9a76', '#ffb997',
            '#e1b12c', '#fbc531', '#f9ca24', '#ff9f1a', '#ffd32a'
        ];
        
        return presets.map(color => `
            <div class="color-preset" data-color="${color}" style="background: ${color}"></div>
        `).join('');
    }

    function showFontSettings() {
        const existing = document.getElementById('font-settings-overlay');
        if (existing) return;

        const currentSize = localStorage.getItem('fontSize') || '16';
        const currentColor = localStorage.getItem('fontColor') || '#ffffff';

        const overlay = document.createElement('div');
        overlay.id = 'font-settings-overlay';
        overlay.innerHTML = `
            <div id="font-settings-dialog" class="font-${localStorage.getItem('selectedFont') || 'default'}">
                <div class="dialog-header">
                    <h3>${translateText('fontSettings')}</h3>
                    <button class="close-btn">&times;</button>
                </div>
                <div class="font-controls">
                    <div class="font-size-control">
                        <label style="color: white">${translateText('fontSize')}: <span id="font-size-value" style="color: inherit">${currentSize}px</span></label>
                        <input type="range" id="font-size-slider" min="10" max="30" value="${currentSize}" step="1">
                    </div>
                    <div class="font-color-control">
                        <label>${translateText('fontColor')}:</label>
                        <div class="color-presets">
                            <button data-color="#000000" class="color-preset black">${translateText('black')}</button>
                            <button data-color="#ffffff" class="color-preset white">${translateText('white')}</button>
                            <button data-color="#ff0000" class="color-preset red">${translateText('red')}</button>
                            <button data-color="#0000ff" class="color-preset blue">${translateText('blue')}</button>
                            <button data-color="#00ff00" class="color-preset green">${translateText('green')}</button>
                            <button data-color="#800080" class="color-preset purple">${translateText('purple')}</button>
                            <button id="custom-color-btn" class="color-preset">${translateText('customColor')}</button>
                        </div>
                    </div>
                    <div class="font-preview" style="font-size: ${currentSize}px; color: ${currentColor}">
                        ${currentSize}px | ${currentColor}
                    </div>
                    <div class="button-group">
                        <button id="apply-font">${translateText('apply')}</button>
                        <button id="reset-font">${translateText('reset')}</button>
                    </div>
                </div>
            </div>
        `;

        const style = document.createElement('style');
        style.textContent = `
            #font-settings-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.7);
                z-index: 9999;
                display: flex;
                justify-content: center;
                align-items: center;
                backdrop-filter: blur(3px);
            }
            
            #font-settings-dialog {
                background: rgba(0,0,0,0.9);
                width: 400px;
                padding: 25px;
                border-radius: 10px;
                box-shadow: 0 0 25px rgba(0,0,0,0.6);
                border: 1px solid #444;
            }
            
            .dialog-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 15px;
                padding-bottom: 10px;
                border-bottom: 1px solid #444;
            }
            
            .dialog-header h3 {
                margin: 0;
                color: #9C27B0;
                font-size: 1.2em;
            }
            
            .close-btn {
                background: none;
                border: none;
                color: #fff;
                font-size: 24px;
                cursor: pointer;
            }
            
            .font-controls {
                display: flex;
                flex-direction: column;
                gap: 20px;
            }
            
            .font-size-control {
                display: flex;
                flex-direction: column;
                gap: 10px;
            }
            
            #font-size-slider {
                width: 100%;
                height: 8px;
                -webkit-appearance: none;
                background: #444;
                border-radius: 4px;
                outline: none;
            }
            
            #font-size-slider::-webkit-slider-thumb {
                -webkit-appearance: none;
                width: 20px;
                height: 20px;
                background: #9C27B0;
                border-radius: 50%;
                cursor: pointer;
            }
            
            .font-color-control {
                display: flex;
                flex-direction: column;
                gap: 10px;
            }
            
            .color-presets {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 8px;
            }
            
            .color-preset {
                padding: 8px;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                transition: transform 0.2s;
                color: white;
                text-align: center;
            }
            
            .color-preset:hover {
                transform: scale(1.05);
            }
            
            .black { background: #000000; }
            .white { background: #ffffff; color: #000 !important; }
            .red { background: #ff0000; }
            .blue { background: #0000ff; }
            .green { background: #00ff00; color: #000 !important; }
            .purple { background: #800080; }
            
            #custom-color-btn {
                background: #555;
                grid-column: span 3;
            }
            
            .font-preview {
                padding: 15px;
                border: 1px solid #444;
                border-radius: 4px;
                text-align: center;
                margin-top: 10px;
            }
            
            .button-group {
                display: flex;
                gap: 10px;
                margin-top: 15px;
            }
            
            #apply-font, #reset-font {
                flex: 1;
                padding: 10px;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                font-weight: bold;
            }
            
            #apply-font {
                background: #4CAF50;
                color: white;
            }
            
            #reset-font {
                background: #f44336;
                color: white;
            }

            .font-size-control label, 
            .font-color-control label {
                color: white !important;
            }
           
            @media (max-width: 480px) {
                #font-settings-dialog {
                    width: 90%;
                    padding: 15px;
                }
                
                .color-presets {
                    grid-template-columns: repeat(2, 1fr);
                }
                
                #custom-color-btn {
                    grid-column: span 2;
                }
            }
        `;

        document.body.appendChild(overlay);
        document.head.appendChild(style);

        const fontSizeSlider = document.getElementById('font-size-slider');
        const fontSizeValue = document.getElementById('font-size-value');
        const fontPreview = document.querySelector('.font-preview');

        fontSizeSlider.addEventListener('input', function() {
            const size = this.value;
            fontSizeValue.textContent = `${size}px`;
            fontPreview.style.fontSize = `${size}px`;
            const currentColor = fontPreview.style.color || '#ffffff';
            fontPreview.textContent = `${size}px | ${currentColor}`;
        });

        document.querySelectorAll('.color-preset:not(#custom-color-btn)').forEach(btn => {
            btn.addEventListener('click', function() {
                const color = this.getAttribute('data-color');
                fontPreview.style.color = color;
                const currentSize = fontSizeSlider.value;
                fontPreview.textContent = `${currentSize}px | ${color}`;
            });
        });

        document.getElementById('custom-color-btn').addEventListener('click', function() {
            const colorInput = document.createElement('input');
            colorInput.type = 'color';
            colorInput.value = fontPreview.style.color || '#ffffff';
            colorInput.click();
            
            colorInput.addEventListener('input', function() {
                fontPreview.style.color = this.value;
                const currentSize = fontSizeSlider.value;
                fontPreview.textContent = `${currentSize}px | ${this.value}`;
            });
        });

        document.getElementById('apply-font').addEventListener('click', function() {
            const fontSize = fontSizeSlider.value;
            const fontColor = fontPreview.style.color || currentColor;
            
            localStorage.setItem('fontSize', fontSize);
            localStorage.setItem('fontColor', fontColor);
            
            applyFontSettings(fontSize, fontColor);
            
            overlay.remove();
            style.remove();
        });

        document.getElementById('reset-font').addEventListener('click', function() {
            localStorage.removeItem('fontSize');
            localStorage.removeItem('fontColor');
            
            applyFontSettings('16', '#ffffff');
            
            overlay.remove();
            style.remove();
            location.reload();
        });

        overlay.querySelector('.close-btn').addEventListener('click', function() {
            overlay.remove();
            style.remove();
        });

        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) {
                overlay.remove();
                style.remove();
            }
        });
    }

    function applyFontSettings(size, color) {
        let styleTag = document.querySelector("#font-size-color-style") || document.createElement("style");
        styleTag.id = "font-size-color-style";
        styleTag.textContent = `
            body {
                font-size: ${size}px !important;
                color: ${color} !important;
            }
        `;
        document.head.appendChild(styleTag);
    }

    const savedFontSize = localStorage.getItem('fontSize');
    const savedFontColor = localStorage.getItem('fontColor');
    if (savedFontSize || savedFontColor) {
        applyFontSettings(savedFontSize || '16', savedFontColor || '#ffffff');
    }

    function updateFontSettingsText() {
        const overlay = document.getElementById('font-settings-dialog');
        if (!overlay) return;

        overlay.querySelector('h3').textContent = translateText('fontSettings');
        overlay.querySelector('.font-size-control label').innerHTML = `${translateText('fontSize')}: <span id="font-size-value">${localStorage.getItem('fontSize') || '16'}px</span>`;
        overlay.querySelector('.font-color-control label').textContent = translateText('fontColor');
        overlay.querySelector('.color-preset.black').textContent = translateText('black');
        overlay.querySelector('.color-preset.white').textContent = translateText('white');
        overlay.querySelector('.color-preset.red').textContent = translateText('red');
        overlay.querySelector('.color-preset.blue').textContent = translateText('blue');
        overlay.querySelector('.color-preset.green').textContent = translateText('green');
        overlay.querySelector('.color-preset.purple').textContent = translateText('purple');
        overlay.querySelector('#custom-color-btn').textContent = translateText('customColor');
        overlay.querySelector('#apply-font').textContent = translateText('apply');
        overlay.querySelector('#reset-font').textContent = translateText('reset');
    }

    function applyCustomBackgroundColor(color) {
        const rgbColor = hexToRgb(color);
        const popupBgColor = `rgba(${rgbColor.r}, ${rgbColor.g}, ${rgbColor.b}, 0.9)`;    
        document.documentElement.style.setProperty('--popup-bg-color', popupBgColor);

        localStorage.setItem('customBackgroundColor', color);
        localStorage.setItem('backgroundMode', 'color');

        clearExistingBackground();
        document.body.style.background = color;
        document.body.style.backgroundSize = 'auto';

        const overlay = document.getElementById('color-picker-overlay');
        if (overlay) overlay.remove();
    }

    function hexToRgb(hex) {
        hex = hex.replace('#', '');
    
        if (hex.length === 3) {
            hex = hex.split('').map(c => c + c).join('');
        }
    
        const r = parseInt(hex.substring(0, 2), 16);
        const g = parseInt(hex.substring(2, 4), 16);
        const b = parseInt(hex.substring(4, 6), 16);
    
        return { r, g, b };
    }

    function resetCustomBackgroundColor() {
        localStorage.removeItem('customBackgroundColor');
    
        document.documentElement.style.setProperty(
            '--popup-bg-color', 
            'rgba(27, 27, 47, 0.9)'
        );

        localStorage.setItem('backgroundMode', 'auto');
    
        const overlay = document.getElementById('color-picker-overlay');
        if (overlay) overlay.remove();
    
        setMode('auto');
    }

    function isValidColor(strColor) {
        const s = new Option().style;
        s.color = strColor;
        return s.color !== '';
    }

    function applyCSS(image) {
        let styleTag = document.querySelector("#dynamic-style");
        if (!styleTag) {
            styleTag = document.createElement("style");
            styleTag.id = "dynamic-style";
            document.head.appendChild(styleTag);
        }

        if (image) {
            styleTag.innerHTML = `
                body {
                    background: url('/luci-static/spectra/bgm/${image}') no-repeat center center fixed !important;
                    background-size: cover !important;
                    transition: background 1s ease-in-out;
                }
                .wrapper span {
                    display: none !important;
                }
            `;
        } else {
            styleTag.innerHTML = `
                body {
                    background: #111 !important;
                    background-image: none !important;
                    background-size: auto !important;
                }
                .wrapper span {
                    display: block !important;
                }
            `;
        }
    }

    function insertVideoBackground(src = 'bg.mp4') {
        document.querySelectorAll('video#background-video').forEach(v => v.remove());
        videoTag = document.createElement("video");
        videoTag.className = "video-background";
        videoTag.id = "background-video";
        videoTag.autoplay = true;
        videoTag.loop = true;
        videoTag.muted = localStorage.getItem('videoMuted') === 'true';
        videoTag.playsInline = true;
        videoTag.innerHTML = `
            <source src="/luci-static/spectra/bgm/bg.mp4" type="video/mp4">
            Your browser does not support the video tag.
        `;
        const savedFit = localStorage.getItem('videoObjectFit') || 'cover';
        videoTag.style.objectFit = savedFit;
        document.body.prepend(videoTag);
        videoTag.muted = localStorage.getItem('videoMuted') === 'true'; 

        let styleTag = document.querySelector("#video-style");
        if (!styleTag) {
            styleTag = document.createElement("style");
            styleTag.id = "video-style";
            document.head.appendChild(styleTag);
        }
        styleTag.innerHTML = `
            body {
                background: transparent !important;
                margin: 0;
                padding: 0;
                height: 100vh;
                overflow: hidden;
            }
            .video-background {
                position: fixed;
                top: 50%;
                left: 50%;
                width: auto;
                height: auto;
                min-width: 100%;
                min-height: 100%;
                transform: translate(-50%, -50%);
                z-index: -1;
            }
            .video-background + .wrapper span {
                display: none !important;
            }
        `;
    }

    function applyPHPBackground() {
        const phpBackgroundSrc = localStorage.getItem('phpBackgroundSrc');
        const phpBackgroundType = localStorage.getItem('phpBackgroundType');
    
        if (phpBackgroundSrc && phpBackgroundType) {
            if (phpBackgroundType === 'image') {
                setImageBackground(phpBackgroundSrc);
            } else if (phpBackgroundType === 'video') {
                setVideoBackground(phpBackgroundSrc);
            }
        } else {
            const savedMode = localStorage.getItem('backgroundMode') || 'auto';
            setMode(savedMode);
        }
    }

    if (isEnabled) {
        initBackgroundSystem();
    } else {
        applyPHPBackground();
    }

    document.addEventListener('click', (e) => {
        const popup = document.getElementById('mode-popup');
        if (!popup.contains(e.target) && e.target.id !== 'settings-icon') {
            popup.classList.remove('show');
        }
    });

    if (typeof phpBackgroundSrc !== 'undefined' && typeof phpBackgroundType !== 'undefined') {
        if (phpBackgroundType === 'image') {
            setImageBackground(phpBackgroundSrc);
        } else if (phpBackgroundType === 'video') {
            setVideoBackground(phpBackgroundSrc, true);
        }
    }

    currentLanguage = localStorage.getItem('currentLanguage') || 'zh';
    updateUIText();

    function setImageBackground(src) {
        clearExistingBackground();
        document.body.style.background = `url('/luci-static/spectra/bgm/${src}') no-repeat center center fixed`;
        document.body.style.backgroundSize = 'cover';
        localStorage.setItem('phpBackgroundSrc', src);
        localStorage.setItem('phpBackgroundType', 'image');
        localStorage.setItem('backgroundMode', 'image');
    }

    function updateSoundToggle() {
        const soundMuted = localStorage.getItem('videoMuted') === 'true';
        const soundToggle = document.querySelector('.sound-toggle');
    
        if (soundToggle) {
            soundToggle.querySelector('div').innerHTML = soundMuted ? 
                '<i class="bi bi-volume-mute status-off" style="color: white"></i>' : 
                '<i class="bi bi-volume-up status-on" style="color: white"></i>';
        }
    
        if (videoTag) {
            videoTag.muted = soundMuted;
        }
    }

    function setVideoBackground(src, isPHP = false) {
        const wasEnabled = isEnabled;
    
        clearExistingBackground();
    
        isEnabled = wasEnabled;
    
        let existingVideoTag = document.getElementById("background-video");
        const savedFit = localStorage.getItem('videoObjectFit') || 'cover'; 

        if (existingVideoTag) {
            existingVideoTag.src = `/luci-static/spectra/bgm/${src}`;
            existingVideoTag.muted = localStorage.getItem('videoMuted') === 'true'; 
            existingVideoTag.style.objectFit = savedFit; 
        } else {
            videoTag = document.createElement("video");
            videoTag.className = "video-background";
            videoTag.id = "background-video";
            videoTag.autoplay = true;
            videoTag.loop = true;
            videoTag.muted = localStorage.getItem('videoMuted') === 'true'; 
            videoTag.playsInline = true;
            videoTag.style.objectFit = savedFit; 
            videoTag.innerHTML = `
                <source src="/luci-static/spectra/bgm/${src}" type="video/mp4">
                Your browser does not support the video tag.
            `;
            document.body.prepend(videoTag);

            videoTag.addEventListener('loadedmetadata', () => {
                videoTag.play().catch(error => {
                    console.log('视频自动播放被阻止:', error);
                });
            });
        }

        let styleTag = document.querySelector("#video-style");
        if (!styleTag) {
            styleTag = document.createElement("style");
            styleTag.id = "video-style";
            document.head.appendChild(styleTag);
        }
        styleTag.innerHTML = `
            body {
                background: transparent !important;
                margin: 0;
                padding: 0;
                height: 100vh;
                overflow: hidden;
            }

            .video-background {
                position: fixed;
                top: 50%;
                left: 50%;
                width: auto;
                height: auto;
                min-width: 100%;
                min-height: 100%;
                transform: translate(-50%, -50%);
                z-index: -1; 
            }

            .video-background + .wrapper span {
                display: none !important;
            }
        `;

        if (savedFit === 'none') {
            videoTag.style.minWidth = 'auto';
            videoTag.style.minHeight = 'auto';
            videoTag.style.width = '100%';
            videoTag.style.height = '100%';
        } else {
            videoTag.style.minWidth = '100%';
            videoTag.style.minHeight = '100%';
        }

        localStorage.setItem('phpBackgroundSrc', src);
        localStorage.setItem('phpBackgroundType', 'video');
        localStorage.setItem('backgroundMode', 'video');
    
        updateVideoModeUI(true);
    
        updateSoundToggle();
    }

    function updateVideoModeUI(isVideoMode = false) {
        const masterSwitch = document.getElementById('master-switch');
        if (masterSwitch) {
            masterSwitch.style.background = isEnabled ? '#4CAF50' : '#f44336';
            masterSwitch.querySelector('.status-led').style.background = isEnabled ? '#4CAF50' : '#f44336';
            masterSwitch.querySelector('span').textContent = translateText(isEnabled ? 'enabled' : 'disabled');
            masterSwitch.querySelector('div').innerHTML = isEnabled ? 
                '<i class="bi bi-toggle-on" style="color: white"></i>' : 
                '<i class="bi bi-toggle-off" style="color: white"></i>';
        }
    
        const videoRelatedButtons = [
            '[data-mode="video"]',
            '.object-fit-btn',
            '.sound-toggle'
        ].join(',');
    
        document.querySelectorAll(videoRelatedButtons).forEach(btn => {
            btn.style.opacity = 1;
            btn.style.pointerEvents = 'auto';
        
            if (isVideoMode && btn.dataset.mode === 'video') {
                btn.classList.add('selected-mode');
            }
        });
    
        const currentMode = localStorage.getItem('backgroundMode') || 'video';
        document.querySelectorAll('[data-mode]').forEach(btn => {
            btn.classList.toggle('selected-mode', btn.dataset.mode === currentMode);
        });
    
        const fitBtn = document.querySelector('.object-fit-btn');
        if (fitBtn) {
            fitBtn.querySelector('div').textContent = getFitButtonText();
        }
    
        updateUIText();
    }

    function showThemeSettings() {
        const existing = document.getElementById('theme-settings-overlay');
        if (existing) return;

        const overlay = document.createElement('div');
        overlay.id = 'theme-settings-overlay';
        overlay.innerHTML = `
            <div id="theme-settings-dialog" class="font-${localStorage.getItem('selectedFont') || 'default'}">
                <div class="dialog-header">
                    <h3>${translateText('themeTitle')}</h3>
                    <button class="close-btn">&times;</button>
                </div>
                <iframe id="theme-iframe" 
                    src="${window.location.protocol}//${window.location.host}/luci-static/spectra/bgm/index.php"
                    style="width: 100%; height: calc(100% - 40px); border: none; border-radius: 0 0 5px 5px;">
                </iframe>
            </div>
        `;

        const style = document.createElement('style');
        style.textContent = `
            #theme-settings-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.7);
                z-index: 9999;
                display: flex;
                justify-content: center;
                align-items: flex-start;
                padding-top: 5vh;  
                backdrop-filter: blur(3px);
            }
            #theme-settings-dialog {
                background: rgba(0,0,0,0.9);
                width: 70%;
                height: 80vh;
                margin-top: 0;
                border-radius: 8px;
                box-shadow: 0 0 20px rgba(0,0,0,0.5);
                transform: translateY(0);
            }
            .dialog-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 15px 20px;
                background: linear-gradient(135deg, #6f42c1, #9C27B0);
                border-radius: 8px 8px 0 0;
            }
            .dialog-header h3 {
                margin: 0;
                color: #9C27B0;
                font-size: 1.2em;
            }
            .close-btn {
                background: none;
                border: none;
                color: white;
                font-size: 24px;
                cursor: pointer;
                padding: 0 8px;
            }

            @media (max-width: 768px) {
                #theme-settings-dialog {
                    width: 90%;
                    height: 90vh;
                }
            }
        `;

        document.body.appendChild(overlay);
        document.head.appendChild(style);

        overlay.querySelector('.close-btn').addEventListener('click', () => {
            overlay.remove();
            style.remove();
        });

        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) {
                overlay.remove();
                style.remove();
            }
        });
    }

    function switchBackground() {
        if (availableImages.length > 0) {
            bgIndex = (bgIndex + 1) % availableImages.length;
            applyCSS(availableImages[bgIndex]);
        }
    }
});