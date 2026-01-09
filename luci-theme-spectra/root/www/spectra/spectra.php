<?php
$langFilePath = __DIR__ . '/lib/language.txt';
$defaultLang = 'en';

$langData = [
    'zh' => [
        'select_language'        => '选择语言',
        'simplified_chinese'     => '简体中文',
        'traditional_chinese'    => '繁體中文',
        'english'                => '英文',
        'korean'                 => '韩语',
        'vietnamese'             => '越南语',
        'thailand'               => '泰语',
        'japanese'               => '日语',
        'russian'                => '俄语',
        'germany'                => '德语',
        'france'                 => '法语',
        'arabic'                 => '阿拉伯语',
        'spanish'                => '西班牙语',
        'bangladesh'             => '孟加拉语',
        'close'                  => '关闭',
        'save'                   => '保存',
        'oklch_values'     => 'OKLCH 值：',
        'contrast_ratio'   => '對比度：',
        'reset'            => '重設',
        'theme_download'         => '主题下载',
        'select_all'             => '全选',
        'batch_delete'           => '批量删除选中文件',
        'batch_delete_success'   => '✅ 批量删除成功',
        'batch_delete_failed'    => '❌ 批量删除失败',
        'confirm_delete'         => '确定删除？',
        'total'                  => '总共：',
        'free'                   => '剩余：',
        'hover_to_preview'       => '点击激活悬停播放',
        'spectra_config'         => 'Spectra 配置管理',
        'current_mode'           => '当前模式: 加载中...',
        'toggle_mode'            => '切换模式',
        'check_update'           => '检查更新',
        'batch_upload'           => '选择文件进行批量上传',
        'add_to_playlist'        => '勾选添加到播放列表',
        'clear_background'       => '清除背景',
        'clear_background_label' => '清除背景',
        'file_list'              => '文件列表',
        'component_bg_color'     => '选择组件背景色',
        'page_bg_color'          => '选择页面背景色',
        'toggle_font'            => '切换字体',
        'filename'               => '名称：',
        'filesize'               => '大小：',
        'duration'               => '时长：',
        'resolution'             => '分辨率：',
        'bitrate'                => '比特率：',
        'type'                   => '类型：',
        'image'                  => '图片',
        'video'                  => '视频',
        'audio'                  => '音频',
        'document'               => '文档',
        'delete'                 => '删除',
        'rename'                 => '重命名',
        'download'               => '下载',
        'set_background'         => '设置背景',
        'preview'                => '预览',
        'toggle_fullscreen'      => '切换全屏',
        'supported_formats'      => '支持格式：[ jpg, jpeg, png, gif, webp, mp4, webm, mkv, mp3, wav, flac ]',
        'drop_files_here'        => '拖放文件到这里',
        'or'                     => '或',
        'select_files'           => '选择文件',
        'unlock_php_upload_limit'=> '解锁 PHP 上传限制',
        'upload'                 => '上传',
        'cancel'                 => '取消',
        'rename_file'            => '重命名',
        'new_filename'           => '新文件名',
        'invalid_filename_chars' => '文件名不能包含以下字符：\\/：*?"<>|',
        'confirm'                => '确认',
        'media_player'           => '媒体播放器',
        'playlist'               => '播放列表',
        'clear_list'             => '清除列表',
        'toggle_list'            => '隐藏列表',
        'picture_in_picture'     => '画中画',
        'fullscreen'             => '全屏',
        'fetching_version'       => '正在获取版本信息...',
        'download_local'         => '下载到本地',
        'change_language'        => '更改语言',
        'hour_announcement'      => '整点报时，现在是北京时间',  
        'hour_exact'             => '点整',
        'weekDays' => ['日', '一', '二', '三', '四', '五', '六'],
        'labels' => [
            'year' => '年',
            'month' => '月',
            'day' => '号',
            'week' => '星期'
        ],
        'zodiacs' => ['猴','鸡','狗','猪','鼠','牛','虎','兔','龙','蛇','马','羊'],
        'heavenlyStems' => ['甲','乙','丙','丁','戊','己','庚','辛','壬','癸'],
        'earthlyBranches' => ['子','丑','寅','卯','辰','巳','午','未','申','酉','戌','亥'],
        'months' => ['正','二','三','四','五','六','七','八','九','十','冬','腊'],
        'days' => ['初一','初二','初三','初四','初五','初六','初七','初八','初九','初十',
                   '十一','十二','十三','十四','十五','十六','十七','十八','十九','二十',
                   '廿一','廿二','廿三','廿四','廿五','廿六','廿七','廿八','廿九','三十'],
        'leap_prefix' => '闰',
        'year_suffix' => '年',
        'month_suffix' => '月',
        'day_suffix' => '',
        'periods' => ['子', '丑', '寅', '卯', '辰', '巳', '午', '未', '申', '酉', '戌', '亥'],
        'default_period' => '時',
        'initial' => '初',  
        'middle' => '正',   
        'final' =>'末',  
        'clear_confirm' =>'确定要清除目前配置恢复预设配置吗？', 
        'back_to_first' => '已返回播放列表第一首歌曲',
        'font_default' => '已切换为圆润字体',
        'font_fredoka' => '已切换为默认字体',
        'font_mono'   => '已切换为趣味手写字体',
        'font_noto'     => '已切换为中文衬线字体',
        'font_dm_serif'     => '已切换为 DM Serif Display 字体',
        'error_loading_time' => '时间显示异常',
        'switch_to_light_mode' => '切换到亮色模式',
        'switch_to_dark_mode' => '切换到暗色模式',
        'current_mode_dark' => '当前模式: 暗色模式',
        'current_mode_light' => '当前模式: 亮色模式',
        'fetching_version' => '正在获取版本信息...',
        'latest_version' => '最新版本',
        'unable_to_fetch_version' => '无法获取最新版本信息',
        'request_failed' => '请求失败，请稍后再试',
        'pip_not_supported' => '当前媒体不支持画中画',
        'pip_operation_failed' => '画中画操作失败',
        'exit_picture_in_picture' => '退出画中画',
        'picture_in_picture' => '画中画',
        'hide_playlist' => '隐藏列表',
        'show_playlist' => '显示列表',
        'enter_fullscreen' => '进入全屏',
        'exit_fullscreen' => '退出全屏',
        'confirm_update_php' => '您确定要更新 PHP 配置吗？',
        'select_files_to_delete' => '请先选择要删除的文件！',
        'confirm_batch_delete' => '确定要删除选中的 %d 个文件吗？',
        'unable_to_fetch_current_version' => '正在获取当前版本...',
        'current_version' => '当前版本',
        'copy_command'     => '复制命令',
        'command_copied'   => '命令已复制到剪贴板！',
        "updateModalLabel" => "更新状态",
        "updateDescription" => "更新过程即将开始。",
        "waitingMessage" => "等待操作开始...",
        "update_plugin" => "更新插件",
        "installation_complete" => "安装完成！",
        'confirm_title'             => '确认操作',
        'confirm_delete_file'   => '确定要删除文件 %s 吗？',
        'delete_success'      => '删除成功：%s',
        'delete_failure'      => '删除失败：%s',
        'upload_error_type_not_supported' => '不支持的文件类型：%s',
        'upload_error_move_failed'        => '文件上传失败：%s',
        'confirm_clear_background' => '确定要清除背景吗？',
        'background_cleared'      => '背景已清除！',
        'createShareLink' => '创建分享链接',
        'closeButton' => '关闭',
        'expireTimeLabel' => '过期时间',
        'expire1Hour' => '1 小时',
        'expire1Day' => '1 天',
        'expire7Days' => '7 天',
        'expire30Days' => '30 天',
        'maxDownloadsLabel' => '最大下载次数',
        'max1Download' => '1 次',
        'max5Downloads' => '5 次',
        'max10Downloads' => '10 次',
        'maxUnlimited' => '不限',
        'shareLinkLabel' => '分享链接',
        'copyLinkButton' => '复制链接',
        'closeButtonFooter' => '关闭',
        'generateLinkButton' => '生成链接',
        'fileNotSelected' => '未选择文件',
        'httpError' => 'HTTP 错误',
        'linkGenerated' => '✅ 分享链接已生成',
        'operationFailed' => '❌ 操作失败',
        'generateLinkFirst' => '请先生成分享链接',
        'linkCopied' => '📋 链接已复制',
        'copyFailed' => '❌ 复制失败',
        'cleanExpiredButton' => '清理过期',
        'deleteAllButton' => '删除全部',
        'cleanSuccess' => '✅ 清理完成，%s 项已删除',
        'deleteSuccess' => '✅ 所有分享记录已删除，%s 个文件已移除',
        'confirmDeleteAll' => '⚠️ 确定要删除所有分享记录吗？',
        'operationFailed' => '❌ 操作失败',
        'ip_info' => 'IP详细信息',
        'ip_support' => 'IP支持',
        'ip_address' => 'IP地址',
        'location' => '地区',
        'isp' => '运营商',
        'asn' => 'ASN',
        'timezone' => '时区',
        'latitude_longitude' => '经纬度',
        'latency_info' => '延迟信息',
        'fit_contain'    => '正常比例',
        'fit_fill'       => '拉伸填充',
        'fit_none'       => '原始尺寸',
        'fit_scale-down' => '智能适应',
        'fit_cover'      => '默认裁剪',
        'current_fit_mode'    => '当前显示模式',
        'advanced_color_settings' => '高级颜色设置',
        'advanced_color_control' => '高级颜色控制',
        'color_control' => '颜色控制',
        'primary_hue' => '主色调',
        'chroma' => '饱和度',
        'lightness' => '亮度',
        'or_use_palette' => '或使用调色板',
        'reset_to_default' => '重置为默认',
        'preview_and_contrast' => '预览与对比度',
        'color_preview' => '颜色预览',
        'readability_check' => '可读性检查',
        'contrast_between_text_and_bg' => '文本与背景的对比度：',
        'hue_adjustment' => '色相调整',
        'recent_colors' => '最近使用的颜色',
        'apply' => '应用',
        'excellent_aaa' => '优秀 (AAA)',
        'good_aa' => '良好 (AA)',
        'poor_needs_improvement' => '不足 (需要改进)',
        'mount_point' => '挂载点：',
        'used_space' => '已用空间：',
        'pageTitle' => '文件助手',
        'uploadBtn' => '上传文件',
        'rootDirectory' => '根目录',
        'permissions' => '权限',
        'actions' => '操作',
        'directory' => '目录',
        'file' => '文件',
        'confirmDelete' => '确定要删除 {0} 吗？这个操作不可撤销。',
        'newName' => '新名称:',
        'setPermissions' => '🔒 设置权限',
        'modifiedTime' => '修改时间',
        'owner' => '拥有者',
        'create' => '创建',
        'newFolder' => '新建文件夹',
        'newFile' => '新建文件',
        'folderName' => '文件夹名称:',
        'searchFiles' => '搜索文件',
        'noMatchingFiles' => '没有找到匹配的文件。',
        'moveTo' => '移至',
        'confirm' => '确认',
        'goBack' => '返回上一级',
        'refreshDirectory' => '刷新目录内容',
        'filePreview' => '文件预览',
        'unableToLoadImage' => '无法加载图片:',
        'unableToLoadSVG' => '无法加载SVG文件:',
        'unableToLoadAudio' => '无法加载音频:',
        'unableToLoadVideo' => '无法加载视频:',
        'fileAssistant' => '文件助手',
        'errorSavingFile' => '错误: 无法保存文件。',
        'uploadFailed' => '上传失败',
        'fileNotExistOrNotReadable' => '文件不存在或不可读。',
        'inputFileName' => '输入文件名',
        'permissionValue' => '权限值（例如：0644）',
        'inputThreeOrFourDigits' => '输入三位或四位数字，例如：0644 或 0755',
        'fontSizeL' => '字体大小',
        'newNameCannotBeEmpty' => '新名称不能为空',
        'fileNameCannotContainChars' => '文件名不能包含以下字符: < > : " / \\ | ? *',
        'folderNameCannotBeEmpty' => '文件夹名称不能为空',
        'fileNameCannotBeEmpty' => '文件名称不能为空',
        'searchError' => '搜索时出错: ',
        'encodingChanged' => '编码已更改为 {0}。实际转换将在保存时在服务器端进行。',
        'errorLoadingFileContent' => '加载文件内容时出错: ',
        'permissionHelp' => '请输入有效的权限值（三位或四位八进制数字，例如：644 或 0755）',
        'permissionValueCannotExceed' => '权限值不能超过 0777',
        'goBackTitle' => '返回上一级',
        'rootDirectoryTitle' => '返回根目录',
        'homeDirectoryTitle' => '返回主目录',
        'refreshDirectoryTitle' => '刷新目录内容',
        'selectAll' => '全选',
        'invertSelection' => '反选',
        'deleteSelected' => '删除所选',
        'searchTitle' => '搜索',
        'createTitle' => '新建',
        'uploadTitle' => '上传',
        'dragHint' => '请将文件拖拽至此处或点击选择文件批量上传',
        'searchInputPlaceholder' => '输入文件名',
        'search_placeholder' => '输入要搜索的文件名...',
        'advancedEdit' => '高级编辑',
        'search' => '搜索',
        'format' => '格式化',
        'goToParentDirectoryTitle' => '返回上一级目录',
        'alreadyAtRootDirectory' => '已经在根目录，无法返回上一级。',
        'fullscreen' => '全屏',
        'exitFullscreen' => '退出全屏',
        'search_title' => '搜索文件内容',
        'json_format_success' => 'JSON格式化成功',
        'js_format_success' => 'JavaScript格式化成功',
        'format_not_supported' => '当前模式不支持格式化',
        'format_error' => '格式化错误: ',
        'json_syntax_valid' => 'JSON语法正确',
        'json_syntax_error' => 'JSON语法错误: ',
        'yaml_syntax_valid' => 'YAML语法正确',
        'yaml_syntax_error' => 'YAML语法错误: ',
        'yaml_format_success' => 'YAML格式化成功',
        'yaml_format_error' => 'YAML格式化错误: ',
        'search_placeholder' => '搜索...',
        'replace_placeholder' => '替换为...',
        'find_all' => '全部',
        'replace' => '替换',
        'replace_all' => '全部替换',
        'toggle_replace_mode' => '切换替换模式',
        'toggle_regexp_mode' => '正则表达式搜索',
        'toggle_case_sensitive' => '区分大小写搜索',
        'toggle_whole_words' => '全词匹配搜索',
        'search_in_selection' => '在选中范围内搜索',
        'search_counter_of' => '共',
        'select_all' => '全选',
        'selected_info' => '已选择 {count} 个文件，合计 {size}',
        'selected_info_none' => '已选择 0 项',
        'batch_delete' => '批量删除',
        'batch_delete_confirm' => '确定要删除 {count} 个选中的文件/文件夹吗？此操作无法撤销！',
        'batch_delete_no_selection' => '请先选择要删除的文件！',
        'chmod_invalid_input' => '请输入有效的权限值（3或4位八进制数字，例如：644 或 0755）。',
        'delete_confirm' => '⚠️ 确定要删除 "{name}" 吗？此操作无法撤销！',
        'json_format_success' => 'JSON 格式化成功',
        'js_format_success' => 'JavaScript 格式化成功',
        'unsupported_format' => '当前模式不支持格式化',
        'format_error' => '格式化错误：{message}',
        'json_syntax_valid' => 'JSON 语法正确',
        'json_syntax_error' => 'JSON 语法错误：{message}',
        'yaml_syntax_valid' => 'YAML 语法正确',
        'yaml_syntax_error' => 'YAML 语法错误：{message}',
        'yaml_format_success' => 'YAML 格式化成功',
        'yaml_format_error' => 'YAML 格式化错误：{message}',
        'search_empty_input' => '请输入搜索关键词',
        'search_no_results' => '没有找到匹配的文件',
        'search_error' => '搜索出错：{message}',
        'search_filename' => '文件名',
        'search_path' => '路径',
        'search_action' => '操作',
        'search_move_to' => '移至',
        'edit_file_title' => '编辑文件：{filename}',
        'fetch_content_error' => '无法获取文件内容：{message}',
        'save_file_success' => '文件保存成功',
        'search.noResults' => '无结果',
        'search.previousMatch' => '上一个匹配项 (Shift+Enter)',
        'search.nextMatch' => '下一个匹配项 (Enter)',
        'search.matchCase' => '匹配大小写 (Alt+C)',
        'search.matchWholeWord' => '匹配整个单词 (Alt+W)',
        'search.useRegex' => '使用正则表达式 (Alt+R)',
        'search.findInSelection' => '在选区内查找 (Alt+L)',
        'search.close' => '关闭 (Escape)',
        'search.toggleReplace' => '切换替换',
        'search.preserveCase' => '保留大小写 (Alt+P)',
        'search.replaceAll' => '全部替换 (Ctrl+Alt+Enter)',
        'search.replace' => '替换 (Enter)',
        'search.find' => '查找',
        'search.replace' => '替换',
        'format_success' => '格式化成功',
        'format_unsupported' => '暂不支持格式化',
        'format_error' => '格式化错误：{message}',
        'unsupported_format' => '当前模式不支持格式化',
        'toggleComment' => '切换注释',
        'compare' => '比较',
        'enterModifiedContent' => '请输入用于比较的修改内容：',
        'closeDiff' => '关闭差异视图',
        "cancelButton" => "取消",
        "saveButton" => "保存",
        'toggleFullscreen' => '全屏',
        "lineColumnDisplay" => "行: {line}, 列: {column}",
        "charCountDisplay" => "字符数: {charCount}",
        "fileName" => "文件名",
        "fileSize" => "大小",
        "fileType" => "文件类型",
        'validateJson' => '验证 JSON 语法',
        'formatYaml' => '格式化 YAML',
        'validateJson' => '验证 JSON 语法',
        'validateYaml' => '验证 YAML 语法',
        'total_items'  => '共',
        'items'        => '个项目',
        'current_path' => '当前路径',
        'disk'         => '磁盘',
        'root'         => '根目录', 
        'file_summary' => '已选择 %d 个文件，合计 %s MB'
    ],

    'hk' => [
        'select_language'        => '選擇語言',
        'simplified_chinese'     => '簡體中文',
        'traditional_chinese'    => '繁體中文',
        'english'                => '英文',
        'korean'                 => '韓語',
        'vietnamese'             => '越南語',
        'thailand'               => '泰語',
        'japanese'               => '日語',
        'russian'                => '俄語',
        'germany'                => '德語',
        'france'                 => '法語',
        'arabic'                 => '阿拉伯語',
        'spanish'                => '西班牙語',
       'bangladesh'              => '孟加拉語',
        'close'                  => '關閉',
        'save'                   => '保存',
        'theme_download'         => '主題下載',
        'oklch_values'     => 'OKLCH 值：',
        'contrast_ratio'   => '對比度：',
        'reset'            => '重設',
        'select_all'             => '全選',
        'batch_delete'           => '批量刪除選中文件',
        'total'                  => '總共：',
        'free'                   => '剩餘：',
        'hover_to_preview'       => '點擊激活懸停播放',
        'mount_info'             => '掛載點：{{mount}}｜已用空間：{{used}}',
        'spectra_config'         => 'Spectra 配置管理',
        'current_mode'           => '當前模式: 加載中...',
        'toggle_mode'            => '切換模式',
        'check_update'           => '檢查更新',
        'batch_upload'           => '選擇文件進行批量上傳',
        'add_to_playlist'        => '勾選添加到播放列表',
        'clear_background'       => '清除背景',
        'clear_background_label' => '清除背景',
        'file_list'              => '文件列表',
        'component_bg_color'     => '選擇組件背景色',
        'page_bg_color'          => '選擇頁面背景色',
        'toggle_font'            => '切換字體',
        'filename'               => '名稱：',
        'filesize'               => '大小：',
        'duration'               => '時長：',
        'resolution'             => '分辨率：',
        'bitrate'                => '比特率：',
        'type'                   => '類型：',
        'image'                  => '圖片',
        'video'                  => '視頻',
        'audio'                  => '音頻',
        'document'               => '文檔',
        'delete'                 => '刪除',
        'rename'                 => '重命名',
        'download'               => '下載',
        'set_background'         => '設置背景',
        'preview'                => '預覽',
        'toggle_fullscreen'      => '切換全屏',
        'supported_formats'      => '支持格式：[ jpg, jpeg, png, gif, webp, mp4, webm, mkv, mp3, wav, flac ]',
        'drop_files_here'        => '拖放文件到這裡',
        'or'                     => '或',
        'select_files'           => '選擇文件',
        'unlock_php_upload_limit'=> '解鎖 PHP 上傳限制',
        'upload'                 => '上傳',
        'cancel'                 => '取消',
        'rename_file'            => '重命名',
        'new_filename'           => '新文件名',
        'invalid_filename_chars' => '文件名不能包含以下字符：\\/：*?"<>|',
        'confirm'                => '確認',
        'media_player'           => '媒體播放器',
        'playlist'               => '播放列表',
        'clear_list'             => '清除列表',
        'toggle_list'            => '隱藏列表',
        'picture_in_picture'     => '畫中畫',
        'fullscreen'             => '全屏',
        'fetching_version'       => '正在獲取版本信息...',
        'download_local'         => '下載到本地',
        'change_language'        => '更改語言',
        'hour_announcement'      => '整點報時，現在是北京時間',  
        'hour_exact'             => '點整',
        'weekDays' => ['日', '一', '二', '三', '四', '五', '六'],
        'labels' => [
            'year' => '年',
            'month' => '月',
            'day' => '號',
            'week' => '星期'
        ],
        'zodiacs' => ['猴','雞','狗','豬','鼠','牛','虎','兔','龍','蛇','馬','羊'],
        'heavenlyStems' => ['甲','乙','丙','丁','戊','己','庚','辛','壬','癸'],
        'earthlyBranches' => ['子','丑','寅','卯','辰','巳','午','未','申','酉','戌','亥'],
        'months' => ['正','二','三','四','五','六','七','八','九','十','冬','臘'],
        'days' => ['初一','初二','初三','初四','初五','初六','初七','初八','初九','初十',
                   '十一','十二','十三','十四','十五','十六','十七','十八','十九','二十',
                   '廿一','廿二','廿三','廿四','廿五','廿六','廿七','廿八','廿九','三十'],
        'leap_prefix' => '閏',
        'year_suffix' => '年',
        'month_suffix' => '月',
        'day_suffix' => '',
        'periods' => ['子', '丑', '寅', '卯', '辰', '巳', '午', '未', '申', '酉', '戌', '亥'],
        'default_period' => '時',
        'initial' => '初',  
        'middle' => '正',   
        'final' =>'末',   
        'clear_confirm' => '確定要清除目前配置恢復預設配置嗎？',
        'back_to_first' => '已返回播放列表第一首歌曲', 
        'error_loading_time' => '時間顯示異常',
        'switch_to_light_mode' => '切換到亮色模式',
        'switch_to_dark_mode' => '切換到暗色模式',
        'current_mode_dark' => '當前模式: 暗色模式',
        'current_mode_light' => '當前模式: 亮色模式',
        'fetching_version' => '正在獲取版本信息...',
        'latest_version' => '最新版本',
        'unable_to_fetch_version' => '無法獲取最新版本信息',
        'request_failed' => '請求失敗，請稍後再試',
        'pip_not_supported' => '當前媒體不支持畫中畫',
        'pip_operation_failed' => '畫中畫操作失敗',
        'exit_picture_in_picture' => '退出畫中畫',
        'picture_in_picture' => '畫中畫',
        'hide_playlist' => '隱藏列表',
        'show_playlist' => '顯示列表',
        'enter_fullscreen' => '進入全屏',
        'exit_fullscreen' => '退出全屏',
        'confirm_update_php' => '您確定要更新 PHP 配置嗎？',
        'select_files_to_delete' => '請先選擇要刪除的文件！',
        'confirm_batch_delete' => '確定要刪除選中的 %d 個文件嗎？',
        'font_default' => '已切換為圓潤字體',
        'font_fredoka' => '已切換為預設字體',
        'font_mono'    => '已切換為趣味手寫字體',
        'font_noto'    => '已切換為中文襯線字體',
        'font_dm_serif'     => '已切換為 DM Serif Display 字體',
        'batch_delete_success' => '✅ 批量刪除成功',
        'batch_delete_failed' => '❌ 批量刪除失敗',
        'confirm_delete' => '確定刪除？',
        'unable_to_fetch_current_version' => '正在獲取當前版本...',
        'current_version' => '當前版本',
        'copy_command'     => '複製命令',
        'command_copied'   => '命令已複製到剪貼簿！',
        "updateModalLabel" => "更新狀態",
        "updateDescription" => "更新過程即將開始。",
        "waitingMessage" => "等待操作開始...",
        "update_plugin" => "更新插件",
        "installation_complete" => "安裝完成！",
        'confirm_title'         => '確認操作',
        'confirm_delete_file'   => '確定要刪除文件 %s 嗎？',
        'delete_success'      => '刪除成功：%s',
        'delete_failure'      => '刪除失敗：%s',
        'upload_error_type_not_supported' => '不支持的文件類型：%s',
        'upload_error_move_failed'        => '文件上傳失敗：%s',
        'confirm_clear_background' => '確定要清除背景嗎？',
        'background_cleared'      => '背景已清除！',
        'createShareLink' => '創建分享鏈接',
        'closeButton' => '關閉',
        'expireTimeLabel' => '過期時間',
        'expire1Hour' => '1 小時',
        'expire1Day' => '1 天',
        'expire7Days' => '7 天',
        'expire30Days' => '30 天',
        'maxDownloadsLabel' => '最大下載次數',
        'max1Download' => '1 次',
        'max5Downloads' => '5 次',
        'max10Downloads' => '10 次',
        'maxUnlimited' => '不限',
        'shareLinkLabel' => '分享鏈接',
        'copyLinkButton' => '複製鏈接',
        'closeButtonFooter' => '關閉',
        'generateLinkButton' => '生成鏈接',
        'fileNotSelected' => '未選擇文件',
        'httpError' => 'HTTP 錯誤',
        'linkGenerated' => '✅ 分享鏈接已生成',
        'operationFailed' => '❌ 操作失敗',
        'generateLinkFirst' => '請先生成分享鏈接',
        'linkCopied' => '📋 鏈接已複製',
        'copyFailed' => '❌ 複製失敗',
        'cleanExpiredButton' => '清理過期',
        'deleteAllButton' => '刪除全部',
        'cleanSuccess' => '✅ 清理完成，%s 項已刪除',
        'deleteSuccess' => '✅ 所有分享記錄已刪除，%s 個文件已移除',
        'confirmDeleteAll' => '⚠️ 確定要刪除所有分享記錄嗎？',
        'operationFailed' => '❌ 操作失敗',
        'ip_info' => 'IP詳細資料',
        'ip_support' => 'IP支援',
        'ip_address' => 'IP地址',
        'location' => '地區',
        'isp' => '運營商',
        'asn' => 'ASN',
        'timezone' => '時區',
        'latitude_longitude' => '經緯度',
        'latency_info' => '延遲資訊',
        'fit_contain'    => '正常比例',
        'fit_fill'       => '拉伸填充',
        'fit_none'       => '原始尺寸',
        'fit_scale-down' => '智能適應',
        'fit_cover'      => '預設裁剪',
        'current_fit_mode'    => '當前顯示模式',
        'advanced_color_settings' => '高級顏色設定',
        'advanced_color_control' => '高級顏色控制',
        'color_control' => '顏色控制',
        'primary_hue' => '主色調',
        'chroma' => '飽和度',
        'lightness' => '亮度',
        'or_use_palette' => '或使用調色盤',
        'reset_to_default' => '重設為預設',
        'preview_and_contrast' => '預覽與對比度',
        'color_preview' => '顏色預覽',
        'readability_check' => '可讀性檢查',
        'contrast_between_text_and_bg' => '文字與背景的對比度：',
        'hue_adjustment' => '色相調整',
        'recent_colors' => '最近使用的顏色',
        'apply' => '套用',
        'excellent_aaa' => '優秀 (AAA)',
        'good_aa' => '良好 (AA)',
        'poor_needs_improvement' => '不足 (需要改進)',
        'mount_point' => '掛載點：',
        'used_space'  => '已用空間：',
        'file_summary' => '已選擇 %d 個文件，合計 %s MB',
        'pageTitle' => '檔案助手',
        'uploadBtn' => '上傳檔案',
        'rootDirectory' => '根目錄',
        'permissions' => '權限',
        'actions' => '操作',
        'directory' => '目錄',
        'file' => '檔案',
        'confirmDelete' => '確定要刪除 {0} 嗎？此操作無法還原。',
        'newName' => '新名稱:',
        'setPermissions' => '🔒 設定權限',
        'modifiedTime' => '修改時間',
        'owner' => '擁有者',
        'create' => '建立',
        'newFolder' => '新增資料夾',
        'newFile' => '新增檔案',
        'folderName' => '資料夾名稱:',
        'searchFiles' => '搜尋檔案',
        'noMatchingFiles' => '找不到符合的檔案。',
        'moveTo' => '移至',
        'confirm' => '確認',
        'goBack' => '返回上一級',
        'refreshDirectory' => '重新整理目錄內容',
        'filePreview' => '檔案預覽',
        'unableToLoadImage' => '無法載入圖片:',
        'unableToLoadSVG' => '無法載入SVG檔案:',
        'unableToLoadAudio' => '無法載入音訊:',
        'unableToLoadVideo' => '無法載入影片:',
        'fileAssistant' => '檔案助手',
        'errorSavingFile' => '錯誤: 無法儲存檔案。',
        'uploadFailed' => '上傳失敗',
        'fileNotExistOrNotReadable' => '檔案不存在或無法讀取。',
        'inputFileName' => '輸入檔案名稱',
        'permissionValue' => '權限值（例如：0644）',
        'inputThreeOrFourDigits' => '輸入三位或四位數字，例如：0644 或 0755',
        'fontSizeL' => '字型大小',
        'newNameCannotBeEmpty' => '新名稱不能為空',
        'fileNameCannotContainChars' => '檔案名稱不能包含以下字元: < > : " / \\ | ? *',
        'folderNameCannotBeEmpty' => '資料夾名稱不能為空',
        'fileNameCannotBeEmpty' => '檔案名稱不能為空',
        'searchError' => '搜尋時出錯: ',
        'encodingChanged' => '編碼已更改為 {0}。實際轉換將在儲存時於伺服器端進行。',
        'errorLoadingFileContent' => '載入檔案內容時出錯: ',
        'permissionHelp' => '請輸入有效的權限值（三位或四位八進位數字，例如：644 或 0755）',
        'permissionValueCannotExceed' => '權限值不能超過 0777',
        'goBackTitle' => '返回上一級',
        'rootDirectoryTitle' => '返回根目錄',
        'homeDirectoryTitle' => '返回主目錄',
        'refreshDirectoryTitle' => '重新整理目錄內容',
        'selectAll' => '全選',
        'invertSelection' => '反選',
        'deleteSelected' => '刪除所選',
        'searchTitle' => '搜尋',
        'createTitle' => '新增',
        'uploadTitle' => '上傳',
        'dragHint' => '請將檔案拖放至此處或點選選擇檔案批量上傳',
        'searchInputPlaceholder' => '輸入檔案名稱',
        'search_placeholder' => '輸入要搜尋的檔案名稱...',
        'advancedEdit' => '進階編輯',
        'search' => '搜尋',
        'format' => '格式化',
        'goToParentDirectoryTitle' => '返回上一級目錄',
        'alreadyAtRootDirectory' => '已經在根目錄，無法返回上一級。',
        'fullscreen' => '全螢幕',
        'exitFullscreen' => '結束全螢幕',
        'search_title' => '搜尋檔案內容',
        'json_format_success' => 'JSON格式化成功',
        'js_format_success' => 'JavaScript格式化成功',
        'format_not_supported' => '目前模式不支援格式化',
        'format_error' => '格式化錯誤: ',
        'json_syntax_valid' => 'JSON語法正確',
        'json_syntax_error' => 'JSON語法錯誤: ',
        'yaml_syntax_valid' => 'YAML語法正確',
        'yaml_syntax_error' => 'YAML語法錯誤: ',
        'yaml_format_success' => 'YAML格式化成功',
        'yaml_format_error' => 'YAML格式化錯誤: ',
        'search_placeholder' => '搜尋...',
        'replace_placeholder' => '替換為...',
        'find_all' => '全部',
        'replace' => '替換',
        'replace_all' => '全部替換',
        'toggle_replace_mode' => '切換替換模式',
        'toggle_regexp_mode' => '正則表達式搜尋',
        'toggle_case_sensitive' => '區分大小寫搜尋',
        'toggle_whole_words' => '全詞匹配搜尋',
        'search_in_selection' => '在選取範圍內搜尋',
        'search_counter_of' => '共',
        'select_all' => '全選',
        'selected_info' => '已選擇 {count} 個檔案，合計 {size}',
        'selected_info_none' => '已選擇 0 項',
        'batch_delete' => '批量刪除',
        'batch_delete_confirm' => '確定要刪除 {count} 個選取的檔案/資料夾嗎？此操作無法還原！',
        'batch_delete_no_selection' => '請先選擇要刪除的檔案！',
        'chmod_invalid_input' => '請輸入有效的權限值（3或4位八進位數字，例如：644 或 0755）。',
        'delete_confirm' => '⚠️ 確定要刪除 "{name}" 嗎？此操作無法還原！',
        'json_format_success' => 'JSON 格式化成功',
        'js_format_success' => 'JavaScript 格式化成功',
        'unsupported_format' => '目前模式不支援格式化',
        'format_error' => '格式化錯誤：{message}',
        'json_syntax_valid' => 'JSON 語法正確',
        'json_syntax_error' => 'JSON 語法錯誤：{message}',
        'yaml_syntax_valid' => 'YAML 語法正確',
        'yaml_syntax_error' => 'YAML 語法錯誤：{message}',
        'yaml_format_success' => 'YAML 格式化成功',
        'yaml_format_error' => 'YAML 格式化錯誤：{message}',
        'search_empty_input' => '請輸入搜尋關鍵字',
        'search_no_results' => '找不到符合的檔案',
        'search_error' => '搜尋出錯：{message}',
        'search_filename' => '檔案名稱',
        'search_path' => '路徑',
        'search_action' => '操作',
        'search_move_to' => '移至',
        'edit_file_title' => '編輯檔案：{filename}',
        'fetch_content_error' => '無法取得檔案內容：{message}',
        'save_file_success' => '檔案儲存成功',
        'search.noResults' => '無結果',
        'search.previousMatch' => '上一個符合項目 (Shift+Enter)',
        'search.nextMatch' => '下一個符合項目 (Enter)',
        'search.matchCase' => '匹配大小寫 (Alt+C)',
        'search.matchWholeWord' => '匹配整個詞語 (Alt+W)',
        'search.useRegex' => '使用正則表達式 (Alt+R)',
        'search.findInSelection' => '在選取區內查找 (Alt+L)',
        'search.close' => '關閉 (Escape)',
        'search.toggleReplace' => '切換替換',
        'search.preserveCase' => '保留大小寫 (Alt+P)',
        'search.replaceAll' => '全部替換 (Ctrl+Alt+Enter)',
        'search.replace' => '替換 (Enter)',
        'search.find' => '查找',
        'search.replace' => '替換',
        'format_success' => '格式化成功',
        'format_unsupported' => '暫不支援格式化',
        'format_error' => '格式化錯誤：{message}',
        'unsupported_format' => '目前模式不支援格式化',
        'toggleComment' => '切換註釋',
        'compare' => '比較',
        'enterModifiedContent' => '請輸入用於比較的修改內容：',
        'closeDiff' => '關閉差異檢視',
        "cancelButton" => "取消",
        "saveButton" => "儲存",
        'toggleFullscreen' => '全螢幕',
        "lineColumnDisplay" => "行: {line}, 列: {column}",
        "charCountDisplay" => "字元數: {charCount}",
        "fileName" => "檔案名稱",
        "fileSize" => "大小",
        "fileType" => "檔案類型",
        'formatYaml' => '格式化 YAML',
        'validateJson' => '驗證 JSON 語法',
        'total_items'  => '共',
        'items'        => '個項目',
        'current_path' => '當前路徑',
        'disk'         => '磁碟',
        'root'         => '根目錄',
        'validateYaml' => '驗證 YAML 語法'
    ],

    'ko' => [
        'select_language'        => '언어 선택',
        'simplified_chinese'     => '중국어 간체',
        'traditional_chinese'    => '중국어 번체',
        'english'                => '영어',
        'korean'                 => '한국어',
        'vietnamese'             => '베트남어',
        'thailand'               => '태국어',
        'japanese'               => '일본어',
        'russian'                => '러시아어',
        'germany'                => '독일어',
        'france'                 => '프랑스어',
        'arabic'                 => '아랍어',
        'spanish'                => '스페인어',
        'bangladesh'             => '벵골어',
        'oklch_values'     => 'OKLCH 값:',
        'contrast_ratio'   => '명암비:',
        'reset'            => '초기화',
        'close'                  => '닫기',
        'save'                   => '저장',
        'theme_download'         => '테마 다운로드',
        'select_all'             => '전체 선택',
        'batch_delete'           => '선택한 파일 일괄 삭제',
        'total'                  => '총합:',
        'free'                   => '남은 공간:',
        'hover_to_preview'       => '클릭하여 미리보기 활성화',
        'mount_info'             => '마운트 포인트: {{mount}}｜사용 공간: {{used}}',
        'spectra_config'         => 'Spectra 설정 관리',
        'current_mode'           => '현재 모드: 로드 중...',
        'toggle_mode'            => '모드 전환',
        'check_update'           => '업데이트 확인',
        'batch_upload'           => '파일 선택하여 일괄 업로드',
        'add_to_playlist'        => '체크한 항목을 재생 목록에 추가',
        'clear_background'       => '배경 지우기',
        'clear_background_label' => '배경 지우기',
        'file_list'              => '파일 목록',
        'component_bg_color'     => '구성 요소 배경색 선택',
        'page_bg_color'          => '페이지 배경색 선택',
        'toggle_font'            => '글꼴 전환',
        'filename'               => '이름:',
        'filesize'               => '크기:',
        'duration'               => '재생 시간:',
        'resolution'             => '해상도:',
        'bitrate'                => '비트레이트:',
        'type'                   => '유형:',
        'image'                  => '이미지',
        'video'                  => '비디오',
        'audio'                  => '오디오',
        'document'               => '문서',
        'delete'                 => '삭제',
        'rename'                 => '이름 변경',
        'download'               => '다운로드',
        'set_background'         => '배경 설정',
        'preview'                => '미리보기',
        'toggle_fullscreen'      => '전체 화면 전환',
        'supported_formats'      => '지원 포맷: [ jpg, jpeg, png, gif, webp, mp4, webm, mkv, mp3, wav, flac ]',
        'drop_files_here'        => '파일을 여기에 드롭하세요',
        'or'                     => '또는',
        'select_files'           => '파일 선택',
        'unlock_php_upload_limit'=> 'PHP 업로드 제한 해제',
        'upload'                 => '업로드',
        'cancel'                 => '취소',
        'rename_file'            => '파일 이름 변경',
        'new_filename'           => '새 파일 이름',
        'invalid_filename_chars' => '파일 이름에 다음 문자를 포함할 수 없습니다: \\/:*?"<>|',
        'confirm'                => '확인',
        'media_player'           => '미디어 플레이어',
        'playlist'               => '재생 목록',
        'clear_list'             => '목록 지우기',
        'toggle_list'            => '목록 숨기기',
        'picture_in_picture'     => '화면 속 화면',
        'fullscreen'             => '전체 화면',
        'fetching_version'       => '버전 정보를 가져오는 중...',
        'download_local'         => '로컬에 다운로드',
        'change_language'        => '언어 변경',
        'hour_announcement'      => '정각 알림, 현재 시간은',
        'hour_exact'             => '시 정각',
        'weekDays' =>  ['일요일', '월요일', '화요일', '수요일', '목요일', '금요일', '토요일'],
        'labels' => [
            'year' => '년',
            'month' => '월',
            'day' => '일',
            'week' => ''
        ],
        'zodiacs' => ['원숭이','닭','개','돼지','쥐','소','호랑이','토끼','용','뱀','말','양'],
        'heavenlyStems' => ['갑','을','병','정','무','기','경','신','임','계'],
        'earthlyBranches' => ['자','축','인','묘','진','사','오','미','신','유','술','해'],
        'months' => ['1','2','3','4','5','6','7','8','9','10','11','12'],
        'days' => ['1일','2일','3일','4일','5일','6일','7일','8일','9일','10일',
                   '11일','12일','13일','14일','15일','16일','17일','18일','19일','20일',
                   '21일','22일','23일','24일','25일','26일','27일','28일','29일','30일'],
        'leap_prefix' => '윤',
        'year_suffix' => '년',
        'month_suffix' => '월',
        'day_suffix' => '',
        'initial' => '초',  
        'middle' => '정',   
        'final' =>'말',  
        'clear_confirm' => '구성을 지우시겠습니까?',
        'back_to_first' => '플레이리스트 첫 번째 곡으로 돌아갔습니다',
        'periods' => ['자', '축', '인', '묘', '진', '사', '오', '미', '신', '유', '술', '해'],
        'default_period' => '시',
        'error_loading_time' => '시간 표시 오류',
        'switch_to_light_mode' => '밝은 모드로 전환',
        'switch_to_dark_mode' => '어두운 모드로 전환',
        'current_mode_dark' => '현재 모드: 어두운 모드',
        'current_mode_light' => '현재 모드: 밝은 모드',
        'fetching_version' => '버전 정보를 가져오는 중...',
        'latest_version' => '최신 버전',
        'unable_to_fetch_version' => '최신 버전 정보를 가져올 수 없습니다',
        'request_failed' => '요청 실패, 나중에 다시 시도하세요',
        'pip_not_supported' => '현재 미디어는 화면 속 화면을 지원하지 않습니다',
        'pip_operation_failed' => '화면 속 화면 작업 실패',
        'exit_picture_in_picture' => '화면 속 화면 종료',
        'picture_in_picture' => '화면 속 화면',
        'hide_playlist' => '목록 숨기기',
        'show_playlist' => '목록 표시',
        'enter_fullscreen' => '전체 화면으로 전환',
        'exit_fullscreen' => '전체 화면 종료',
        'confirm_update_php' => 'PHP 설정을 업데이트하시겠습니까?',
        'select_files_to_delete' => '삭제할 파일을 선택하세요!',
        'confirm_batch_delete' => '선택된 %d개의 파일을 삭제하시겠습니까?',
        'font_default' => '둥근 글꼴로 전환되었습니다',
        'font_fredoka' => '기본 글꼴로 전환되었습니다',
        'font_mono'    => '재미있는 손글씨 글꼴로 전환되었습니다',
        'font_noto'    => '중국어 명조체 글꼴로 전환되었습니다',
        'font_dm_serif'     => 'DM Serif Display 글꼴로 변경됨',
        'batch_delete_success' => '✅ 배치 삭제 성공',
        'batch_delete_failed' => '❌ 배치 삭제 실패',
        'confirm_delete' => '삭제하시겠습니까?',
        'unable_to_fetch_current_version' => '현재 버전 정보를 가져오는 중...',
        'current_version' => '현재 버전',
        'copy_command'     => '명령 복사',
        'command_copied'   => '명령이 클립보드에 복사되었습니다!',
        "updateModalLabel" => "업데이트 상태",
        "updateDescription" => "업데이트 과정이 곧 시작됩니다.",
        "waitingMessage" => "작업이 시작될 때까지 기다리는 중...",
        "update_plugin" => "플러그인 업데이트",
        "installation_complete" => "설치 완료!",
        'confirm_title'         => '작업 확인',
        'confirm_delete_file'   => '파일 %s을(를) 삭제하시겠습니까?',
        'delete_success'      => '삭제 성공: %s',
        'delete_failure'      => '삭제 실패: %s',
        'upload_error_type_not_supported' => '지원되지 않는 파일 형식: %s',
        'upload_error_move_failed'        => '파일 업로드 실패: %s',
        'confirm_clear_background' => '배경을 지우시겠습니까?',
        'background_cleared'      => '배경이 지워졌습니다!',
        'createShareLink' => '공유 링크 생성',
        'closeButton' => '닫기',
        'expireTimeLabel' => '만료 시간',
        'expire1Hour' => '1 시간',
        'expire1Day' => '1 일',
        'expire7Days' => '7 일',
        'expire30Days' => '30 일',
        'maxDownloadsLabel' => '최대 다운로드 횟수',
        'max1Download' => '1 회',
        'max5Downloads' => '5 회',
        'max10Downloads' => '10 회',
        'maxUnlimited' => '무제한',
        'shareLinkLabel' => '공유 링크',
        'copyLinkButton' => '링크 복사',
        'closeButtonFooter' => '닫기',
        'generateLinkButton' => '링크 생성',
        'fileNotSelected' => '파일을 선택하지 않았습니다',
        'httpError' => 'HTTP 오류',
        'linkGenerated' => '✅ 공유 링크 생성됨',
        'operationFailed' => '❌ 작업 실패',
        'generateLinkFirst' => '먼저 공유 링크를 생성하십시오',
        'linkCopied' => '📋 링크 복사됨',
        'copyFailed' => '❌ 복사 실패',
        'cleanExpiredButton' => '만료 정리',
        'deleteAllButton' => '모두 삭제',
        'cleanSuccess' => '✅ 정리 완료, %s 항목이 삭제되었습니다',
        'deleteSuccess' => '✅ 모든 공유 기록이 삭제되었습니다, %s 개의 파일이 제거되었습니다',
        'confirmDeleteAll' => '⚠️ 모든 공유 기록을 삭제하시겠습니까?',
        'operationFailed' => '❌ 작업 실패',
        'ip_info' => 'IP 상세 정보',
        'ip_support' => 'IP 지원',
        'ip_address' => 'IP 주소',
        'location' => '지역',
        'isp' => '통신사',
        'asn' => 'ASN',
        'timezone' => '시간대',
        'latitude_longitude' => '좌표',
        'latency_info' => '지연 정보',
        'current_fit_mode'    => '현재 모드',
        'fit_contain'    => '정상 비율',
        'fit_fill'       => '채우기',
        'fit_none'       => '원본 크기',
        'fit_scale-down' => '스케일 다운',
        'fit_cover'      => '자르기',
        'advanced_color_settings' => '고급 색상 설정',
        'advanced_color_control' => '고급 색상 조절',
        'color_control' => '색상 조절',
        'primary_hue' => '기본 색조',
        'chroma' => '채도',
        'lightness' => '명도',
        'or_use_palette' => '또는 팔레트 사용',
        'reset_to_default' => '기본값으로 재설정',
        'preview_and_contrast' => '미리보기 및 대비',
        'color_preview' => '색상 미리보기',
        'readability_check' => '가독성 확인',
        'contrast_between_text_and_bg' => '텍스트와 배경의 대비:',
        'hue_adjustment' => '색조 조정',
        'recent_colors' => '최근 사용한 색상',
        'apply' => '적용',
        'excellent_aaa' => '우수 (AAA)',
        'good_aa' => '양호 (AA)',
        'poor_needs_improvement' => '미흡 (개선 필요)',
        'mount_point' => '마운트 지점:',
        'used_space'  => '사용된 공간:',
        'file_summary' => '선택된 파일: %d개, 총합: %s MB',
        'pageTitle' => '파일 도우미',
        'uploadBtn' => '파일 업로드',
        'rootDirectory' => '루트 디렉토리',
        'permissions' => '권한',
        'actions' => '작업',
        'directory' => '디렉토리',
        'file' => '파일',
        'confirmDelete' => '{0}을(를) 삭제하시겠습니까? 이 작업은 취소할 수 없습니다.',
        'newName' => '새 이름:',
        'setPermissions' => '🔒 권한 설정',
        'modifiedTime' => '수정 시간',
        'owner' => '소유자',
        'create' => '생성',
        'newFolder' => '새 폴더',
        'newFile' => '새 파일',
        'folderName' => '폴더 이름:',
        'searchFiles' => '파일 검색',
        'noMatchingFiles' => '일치하는 파일을 찾을 수 없습니다.',
        'moveTo' => '이동',
        'confirm' => '확인',
        'goBack' => '상위로 이동',
        'refreshDirectory' => '디렉토리 새로고침',
        'filePreview' => '파일 미리보기',
        'unableToLoadImage' => '이미지를 로드할 수 없습니다:',
        'unableToLoadSVG' => 'SVG 파일을 로드할 수 없습니다:',
        'unableToLoadAudio' => '오디오를 로드할 수 없습니다:',
        'unableToLoadVideo' => '비디오를 로드할 수 없습니다:',
        'fileAssistant' => '파일 도우미',
        'errorSavingFile' => '오류: 파일을 저장할 수 없습니다.',
        'uploadFailed' => '업로드 실패',
        'fileNotExistOrNotReadable' => '파일이 존재하지 않거나 읽을 수 없습니다.',
        'inputFileName' => '파일 이름 입력',
        'permissionValue' => '권한 값 (예: 0644)',
        'inputThreeOrFourDigits' => '3자리 또는 4자리 숫자를 입력하세요 (예: 0644 또는 0755)',
        'fontSizeL' => '글꼴 크기',
        'newNameCannotBeEmpty' => '새 이름은 비워둘 수 없습니다',
        'fileNameCannotContainChars' => '파일 이름에 다음 문자를 포함할 수 없습니다: < > : " / \\ | ? *',
        'folderNameCannotBeEmpty' => '폴더 이름은 비워둘 수 없습니다',
        'fileNameCannotBeEmpty' => '파일 이름은 비워둘 수 없습니다',
        'searchError' => '검색 중 오류: ',
        'encodingChanged' => '인코딩이 {0}(으)로 변경되었습니다. 실제 변환은 저장 시 서버 측에서 수행됩니다.',
        'errorLoadingFileContent' => '파일 내용 로드 중 오류: ',
        'permissionHelp' => '유효한 권한 값을 입력하세요 (3자리 또는 4자리 8진수, 예: 644 또는 0755)',
        'permissionValueCannotExceed' => '권한 값은 0777을 초과할 수 없습니다',
        'goBackTitle' => '상위로 이동',
        'rootDirectoryTitle' => '루트 디렉토리로 이동',
        'homeDirectoryTitle' => '홈 디렉토리로 이동',
        'refreshDirectoryTitle' => '디렉토리 새로고침',
        'selectAll' => '전체 선택',
        'invertSelection' => '선택 반전',
        'deleteSelected' => '선택 항목 삭제',
        'searchTitle' => '검색',
        'createTitle' => '새로 만들기',
        'uploadTitle' => '업로드',
        'dragHint' => '파일을 여기로 끌어다 놓거나 클릭하여 여러 파일을 업로드하세요',
        'searchInputPlaceholder' => '파일 이름 입력',
        'search_placeholder' => '검색할 파일 이름 입력...',
        'advancedEdit' => '고급 편집',
        'search' => '검색',
        'format' => '포맷',
        'goToParentDirectoryTitle' => '상위 디렉토리로 이동',
        'alreadyAtRootDirectory' => '이미 루트 디렉토리에 있습니다. 상위로 이동할 수 없습니다.',
        'fullscreen' => '전체 화면',
        'exitFullscreen' => '전체 화면 종료',
        'search_title' => '파일 내용 검색',
        'json_format_success' => 'JSON 포맷 성공',
        'js_format_success' => 'JavaScript 포맷 성공',
        'format_not_supported' => '현재 모드에서는 포맷을 지원하지 않습니다',
        'format_error' => '포맷 오류: ',
        'json_syntax_valid' => 'JSON 구문이 올바릅니다',
        'json_syntax_error' => 'JSON 구문 오류: ',
        'yaml_syntax_valid' => 'YAML 구문이 올바릅니다',
        'yaml_syntax_error' => 'YAML 구문 오류: ',
        'yaml_format_success' => 'YAML 포맷 성공',
        'yaml_format_error' => 'YAML 포맷 오류: ',
        'search_placeholder' => '검색...',
        'replace_placeholder' => '바꿀 내용...',
        'find_all' => '전체',
        'replace' => '바꾸기',
        'replace_all' => '모두 바꾸기',
        'toggle_replace_mode' => '바꾸기 모드 전환',
        'toggle_regexp_mode' => '정규식 검색',
        'toggle_case_sensitive' => '대소문자 구분 검색',
        'toggle_whole_words' => '전체 단어 일치 검색',
        'search_in_selection' => '선택 영역 내 검색',
        'search_counter_of' => '총',
        'select_all' => '전체 선택',
        'selected_info' => '{count}개 파일 선택, 총 {size}',
        'selected_info_none' => '0개 항목 선택',
        'batch_delete' => '일괄 삭제',
        'batch_delete_confirm' => '선택한 {count}개의 파일/폴더를 삭제하시겠습니까? 이 작업은 취소할 수 없습니다!',
        'batch_delete_no_selection' => '삭제할 파일을 먼저 선택하세요!',
        'chmod_invalid_input' => '유효한 권한 값을 입력하세요 (3자리 또는 4자리 8진수, 예: 644 또는 0755).',
        'delete_confirm' => '⚠️ "{name}"을(를) 삭제하시겠습니까? 이 작업은 취소할 수 없습니다!',
        'json_format_success' => 'JSON 포맷 성공',
        'js_format_success' => 'JavaScript 포맷 성공',
        'unsupported_format' => '현재 모드에서는 포맷을 지원하지 않습니다',
        'format_error' => '포맷 오류: {message}',
        'json_syntax_valid' => 'JSON 구문이 올바릅니다',
        'json_syntax_error' => 'JSON 구문 오류: {message}',
        'yaml_syntax_valid' => 'YAML 구문이 올바릅니다',
        'yaml_syntax_error' => 'YAML 구문 오류: {message}',
        'yaml_format_success' => 'YAML 포맷 성공',
        'yaml_format_error' => 'YAML 포맷 오류: {message}',
        'search_empty_input' => '검색어를 입력하세요',
        'search_no_results' => '일치하는 파일을 찾을 수 없습니다',
        'search_error' => '검색 오류: {message}',
        'search_filename' => '파일 이름',
        'search_path' => '경로',
        'search_action' => '작업',
        'search_move_to' => '이동',
        'edit_file_title' => '파일 편집: {filename}',
        'fetch_content_error' => '파일 내용을 가져올 수 없습니다: {message}',
        'save_file_success' => '파일 저장 성공',
        'search.noResults' => '결과 없음',
        'search.previousMatch' => '이전 일치 항목 (Shift+Enter)',
        'search.nextMatch' => '다음 일치 항목 (Enter)',
        'search.matchCase' => '대소문자 구분 (Alt+C)',
        'search.matchWholeWord' => '전체 단어 일치 (Alt+W)',
        'search.useRegex' => '정규식 사용 (Alt+R)',
        'search.findInSelection' => '선택 영역 내 검색 (Alt+L)',
        'search.close' => '닫기 (Escape)',
        'search.toggleReplace' => '바꾸기 전환',
        'search.preserveCase' => '대소문자 유지 (Alt+P)',
        'search.replaceAll' => '모두 바꾸기 (Ctrl+Alt+Enter)',
        'search.replace' => '바꾸기 (Enter)',
        'search.find' => '찾기',
        'search.replace' => '바꾸기',
        'format_success' => '포맷 성공',
        'format_unsupported' => '포맷을 지원하지 않습니다',
        'format_error' => '포맷 오류: {message}',
        'unsupported_format' => '현재 모드에서는 포맷을 지원하지 않습니다',
        'toggleComment' => '주석 전환',
        'compare' => '비교',
        'enterModifiedContent' => '비교에 사용할 수정 내용을 입력하세요:',
        'closeDiff' => '차이 보기 닫기',
        "cancelButton" => "취소",
        "saveButton" => "저장",
        'toggleFullscreen' => '전체 화면',
        "lineColumnDisplay" => "행: {line}, 열: {column}",
        "charCountDisplay" => "문자 수: {charCount}",
        "fileName" => "파일 이름",
        "fileSize" => "크기",
        "fileType" => "파일 유형",
        'formatYaml' => 'YAML 포맷',
        'validateJson' => 'JSON 구문 검증',
        'total_items'  => '총',
        'items'        => '개 항목',
        'current_path' => '현재 경로',
        'disk'         => '디스크',
        'root'         => '루트 디렉토리',
        'validateYaml' => 'YAML 구문 검증'
    ],

    'ja' => [
        'select_language'        => '言語を選択',
        'simplified_chinese'     => '簡体字中国語',
        'traditional_chinese'    => '繁体字中国語',
        'english'                => '英語',
        'korean'                 => '韓国語',
        'vietnamese'             => 'ベトナム語',
        'thailand'               => 'タイ語',
        'japanese'               => '日本語',
        'russian'                => 'ロシア語',
        'germany'                => 'ドイツ語',
        'france'                 => 'フランス語',
        'arabic'                 => 'アラビア語',
        'spanish'                => 'スペイン語',
        'bangladesh'             => 'ベンガル語',
        'oklch_values'     => 'OKLCH 値：',
        'contrast_ratio'   => 'コントラスト比：',
        'reset'            => 'リセット',
        'close'                  => '閉じる',
        'save'                   => '保存',
        'theme_download'         => 'テーマのダウンロード',
        'select_all'             => 'すべて選択',
        'batch_delete'           => '一括削除',
        'batch_delete_success'   => '✅ 一括削除成功',
        'batch_delete_failed'    => '❌ 一括削除失敗',
        'confirm_delete'         => '削除しますか？',
        'total'                  => '合計：',
        'free'                   => '空き容量：',
        'hover_to_preview'       => 'ホバーでプレビュー（クリックで有効化）',
        'spectra_config'         => 'Spectra設定管理',
        'current_mode'           => '現在のモード：読み込み中...',
        'toggle_mode'            => 'モード切替',
        'check_update'           => 'アップデート確認',
        'batch_upload'           => '一括アップロード',
        'add_to_playlist'        => 'プレイリストに追加',
        'clear_background'       => '背景をクリア',
        'clear_background_label' => '背景クリア',
        'file_list'              => 'ファイル一覧',
        'component_bg_color'     => 'コンポーネント背景色',
        'page_bg_color'          => 'ページ背景色',
        'toggle_font'            => 'フォント切替',
        'filename'               => 'ファイル名：',
        'filesize'               => 'サイズ：',
        'duration'               => '再生時間：',
        'resolution'             => '解像度：',
        'bitrate'                => 'ビットレート：',
        'type'                   => 'タイプ：',
        'image'                  => '画像',
        'video'                  => '動画',
        'audio'                  => '音声',
        'document'               => 'ドキュメント',
        'delete'                 => '削除',
        'rename'                 => '名前変更',
        'download'               => 'ダウンロード',
        'set_background'         => '背景設定',
        'preview'                => 'プレビュー',
        'toggle_fullscreen'      => '全画面切替',
        'supported_formats'      => '対応フォーマット：[ jpg, jpeg, png, gif, webp, mp4, webm, mkv, mp3, wav, flac ]',
        'drop_files_here'        => 'ファイルをドラッグ＆ドロップ',
        'or'                     => 'または',
        'select_files'           => 'ファイルを選択',
        'unlock_php_upload_limit'=> 'PHPアップロード制限解除',
        'upload'                 => 'アップロード',
        'cancel'                 => 'キャンセル',
        'rename_file'            => 'ファイル名変更',
        'new_filename'           => '新しいファイル名',
        'invalid_filename_chars' => '使用不可文字：\\/:*?"<>|',
        'confirm'                => '確認',
        'media_player'           => 'メディアプレイヤー',
        'playlist'               => 'プレイリスト',
        'clear_list'             => 'リストクリア',
        'toggle_list'            => 'リスト非表示',
        'picture_in_picture'     => 'ピクチャーインピクチャー',
        'fullscreen'             => '全画面表示',
        'fetching_version'       => 'バージョン確認中...',
        'download_local'         => 'ローカルに保存',
        'change_language'        => '言語変更',
        'hour_announcement'      => '時報、現在の時間は',
        'hour_exact'             => '時ちょうど',
        'weekDays' =>  ['日曜日', '月曜日', '火曜日', '水曜日', '木曜日', '金曜日', '土曜日'],
        'labels' => [
            'year' => '年',
            'month' => '月',
            'day' => '日',
            'week' => ''
        ],
        'zodiacs' => ['申','酉','戌','亥','子','丑','寅','卯','辰','巳','午','未'],
        'heavenlyStems' => ['甲','乙','丙','丁','戊','己','庚','辛','壬','癸'],
        'earthlyBranches' => ['子','丑','寅','卯','辰','巳','午','未','申','酉','戌','亥'],
        'months' => ['1','2','3','4','5','6','7','8','9','10','11','12'],
        'days' => ['1日','2日','3日','4日','5日','6日','7日','8日','9日','10日',
                   '11日','12日','13日','14日','15日','16日','17日','18日','19日','20日',
                   '21日','22日','23日','24日','25日','26日','27日','28日','29日','30日'],
        'leap_prefix' => '閏',
        'year_suffix' => '年',
        'month_suffix' => '月',
        'day_suffix' => '',
        'periods' => ['子', '丑', '寅', '卯', '辰', '巳', '午', '未', '申', '酉', '戌', '亥'],
        'default_period' => '時',
        'initial' => '初',  
        'middle' => '正',   
        'final' =>'末',  
        'clear_confirm' =>'設定をリセットしますか？', 
        'back_to_first' => 'プレイリストの先頭に戻りました',
        'font_default' => '丸ゴシック体に変更',
        'font_fredoka' => 'デフォルトフォントに戻す',
        'font_mono'   => '手書き風フォントに変更',
        'font_noto'     => '漢字書体に変更',
        'font_dm_serif'     => 'DM Serif Display フォントに切り替えました',
        'error_loading_time' => '時刻表示エラー',
        'switch_to_light_mode' => 'ライトモードへ',
        'switch_to_dark_mode' => 'ダークモードへ',
        'current_mode_dark' => '現在のモード：ダーク',
        'current_mode_light' => '現在のモード：ライト',
        'fetching_version' => 'バージョン確認中...',
        'latest_version' => '最新バージョン',
        'unable_to_fetch_version' => '最新バージョン取得失敗',
        'request_failed' => 'リクエスト失敗、後ほど再試行してください',
        'pip_not_supported' => 'ピクチャーインピクチャー非対応',
        'pip_operation_failed' => 'ピクチャーインピクチャー操作失敗',
        'exit_picture_in_picture' => 'ピクチャーインピクチャー終了',
        'picture_in_picture' => 'ピクチャーインピクチャー',
        'hide_playlist' => 'リスト非表示',
        'show_playlist' => 'リスト表示',
        'enter_fullscreen' => '全画面開始',
        'exit_fullscreen' => '全画面終了',
        'confirm_update_php' => 'PHP設定を更新しますか？',
        'select_files_to_delete' => '削除するファイルを選択してください！',
        'confirm_batch_delete' => '%d個のファイルを削除しますか？',
        'unable_to_fetch_current_version' => '現在のバージョン確認中...',
        'current_version' => '現在のバージョン',
        'copy_command'     => 'コマンドをコピー',
        'command_copied'   => 'コマンドをコピーしました！',
        "updateModalLabel" => "更新ステータス",
        "updateDescription" => "更新プロセスを開始します",
        "waitingMessage" => "処理開始待機中...",
        "update_plugin" => "プラグイン更新",
        "installation_complete" => "インストール完了！",
        'confirm_title'             => '操作確認',
        'confirm_delete_file'   => 'ファイル「%s」を削除しますか？',
        'delete_success'      => '削除成功：%s',
        'delete_failure'      => '削除失敗：%s',
        'upload_error_type_not_supported' => '非対応フォーマット：%s',
        'upload_error_move_failed'        => 'アップロード失敗：%s',
        'confirm_clear_background' => '背景をクリアしますか？',
        'background_cleared'      => '背景をクリアしました！',
        'createShareLink' => 'シェアリンクを作成',
        'closeButton' => '閉じる',
        'expireTimeLabel' => '有効期限',
        'expire1Hour' => '1 時間',
        'expire1Day' => '1 日',
        'expire7Days' => '7 日',
        'expire30Days' => '30 日',
        'maxDownloadsLabel' => '最大ダウンロード回数',
        'max1Download' => '1 回',
        'max5Downloads' => '5 回',
        'max10Downloads' => '10 回',
        'maxUnlimited' => '無制限',
        'shareLinkLabel' => 'シェアリンク',
        'copyLinkButton' => 'リンクをコピー',
        'closeButtonFooter' => '閉じる',
        'generateLinkButton' => 'リンクを生成',
        'fileNotSelected' => 'ファイルが選択されていません',
        'httpError' => 'HTTP エラー',
        'linkGenerated' => '✅ シェアリンクが生成されました',
        'operationFailed' => '❌ 操作失敗',
        'generateLinkFirst' => '先にシェアリンクを生成してください',
        'linkCopied' => '📋 リンクがコピーされました',
        'copyFailed' => '❌ コピー失敗',
        'cleanExpiredButton' => '期限切れを削除',
        'deleteAllButton' => 'すべて削除',
        'cleanSuccess' => '✅ クリーン完了, %s 件が削除されました',
        'deleteSuccess' => '✅ すべての共有記録を削除しました, %s 個のファイルが削除されました',
        'confirmDeleteAll' => '⚠️ すべての共有記録を削除してもよろしいですか？',
        'operationFailed' => '❌ 操作に失敗しました',
        'ip_info' => 'IP詳細情報',
        'ip_support' => 'IPサポート',
        'ip_address' => 'IPアドレス',
        'location' => '地域',
        'isp' => 'プロバイダ',
        'asn' => 'ASN',
        'timezone' => 'タイムゾーン',
        'latitude_longitude' => '座標',
        'latency_info' => 'レイテンシ情報',
        'fit_contain'    => '標準比率',
        'fit_fill'       => '引き伸ばし',
        'fit_none'       => '元のサイズ',
        'fit_scale-down' => '自動調整',
        'fit_cover'      => 'トリミング',
        'current_fit_mode'    => '現在のモード',
        'advanced_color_settings' => '高度なカラー設定',
        'advanced_color_control' => '高度なカラーコントロール',
        'color_control' => 'カラーコントロール',
        'primary_hue' => '基本色相',
        'chroma' => '彩度',
        'lightness' => '明度',
        'or_use_palette' => 'またはパレットを使用',
        'reset_to_default' => 'デフォルトにリセット',
        'preview_and_contrast' => 'プレビューとコントラスト',
        'color_preview' => 'カラー プレビュー',
        'readability_check' => '可読性チェック',
        'contrast_between_text_and_bg' => 'テキストと背景のコントラスト：',
        'hue_adjustment' => '色相調整',
        'recent_colors' => '最近使用した色',
        'apply' => '適用',
        'excellent_aaa' => '優秀 (AAA)',
        'good_aa' => '良好 (AA)',
        'poor_needs_improvement' => '不十分 (改善が必要)',
        'mount_point' => 'マウントポイント：',
        'used_space'  => '使用済み容量：',
        'file_summary' => '%dファイル選択（%s MB）',
        'pageTitle' => 'ファイルアシスタント',
        'uploadBtn' => 'ファイルをアップロード',
        'rootDirectory' => 'ルートディレクトリ',
        'permissions' => '権限',
        'actions' => '操作',
        'directory' => 'ディレクトリ',
        'file' => 'ファイル',
        'confirmDelete' => '{0}を削除してもよろしいですか？この操作は元に戻せません。',
        'newName' => '新しい名前:',
        'setPermissions' => '🔒 権限を設定',
        'modifiedTime' => '更新日時',
        'owner' => '所有者',
        'create' => '作成',
        'newFolder' => '新規フォルダ',
        'newFile' => '新規ファイル',
        'folderName' => 'フォルダ名:',
        'searchFiles' => 'ファイルを検索',
        'noMatchingFiles' => '一致するファイルが見つかりません。',
        'moveTo' => '移動',
        'confirm' => '確認',
        'goBack' => '一つ上へ',
        'refreshDirectory' => 'ディレクトリを更新',
        'filePreview' => 'ファイルプレビュー',
        'unableToLoadImage' => '画像を読み込めません:',
        'unableToLoadSVG' => 'SVGファイルを読み込めません:',
        'unableToLoadAudio' => '音声を読み込めません:',
        'unableToLoadVideo' => '動画を読み込めません:',
        'fileAssistant' => 'ファイルアシスタント',
        'errorSavingFile' => 'エラー: ファイルを保存できません。',
        'uploadFailed' => 'アップロード失敗',
        'fileNotExistOrNotReadable' => 'ファイルが存在しないか、読み取りできません。',
        'inputFileName' => 'ファイル名を入力',
        'permissionValue' => '権限値（例: 0644）',
        'inputThreeOrFourDigits' => '3桁または4桁の数字を入力（例: 0644 または 0755）',
        'fontSizeL' => 'フォントサイズ',
        'newNameCannotBeEmpty' => '新しい名前を空にすることはできません',
        'fileNameCannotContainChars' => 'ファイル名に次の文字を含めることはできません: < > : " / \\ | ? *',
        'folderNameCannotBeEmpty' => 'フォルダ名を空にすることはできません',
        'fileNameCannotBeEmpty' => 'ファイル名を空にすることはできません',
        'searchError' => '検索中にエラー: ',
        'encodingChanged' => 'エンコーディングが {0} に変更されました。実際の変換は保存時にサーバー側で行われます。',
        'errorLoadingFileContent' => 'ファイル内容の読み込み中にエラー: ',
        'permissionHelp' => '有効な権限値を入力してください（3桁または4桁の8進数、例: 644 または 0755）',
        'permissionValueCannotExceed' => '権限値は 0777 を超えることはできません',
        'goBackTitle' => '一つ上へ',
        'rootDirectoryTitle' => 'ルートディレクトリへ',
        'homeDirectoryTitle' => 'ホームディレクトリへ',
        'refreshDirectoryTitle' => 'ディレクトリを更新',
        'selectAll' => 'すべて選択',
        'invertSelection' => '選択を反転',
        'deleteSelected' => '選択した項目を削除',
        'searchTitle' => '検索',
        'createTitle' => '新規作成',
        'uploadTitle' => 'アップロード',
        'dragHint' => 'ファイルをここにドラッグ＆ドロップするか、クリックして複数ファイルをアップロード',
        'searchInputPlaceholder' => 'ファイル名を入力',
        'search_placeholder' => '検索するファイル名を入力...',
        'advancedEdit' => '高度な編集',
        'search' => '検索',
        'format' => 'フォーマット',
        'goToParentDirectoryTitle' => '親ディレクトリへ移動',
        'alreadyAtRootDirectory' => '既にルートディレクトリです。これ以上上へ移動できません。',
        'fullscreen' => '全画面',
        'exitFullscreen' => '全画面を終了',
        'search_title' => 'ファイル内容を検索',
        'json_format_success' => 'JSONフォーマット成功',
        'js_format_success' => 'JavaScriptフォーマット成功',
        'format_not_supported' => '現在のモードではフォーマットをサポートしていません',
        'format_error' => 'フォーマットエラー: ',
        'json_syntax_valid' => 'JSON構文は正しいです',
        'json_syntax_error' => 'JSON構文エラー: ',
        'yaml_syntax_valid' => 'YAML構文は正しいです',
        'yaml_syntax_error' => 'YAML構文エラー: ',
        'yaml_format_success' => 'YAMLフォーマット成功',
        'yaml_format_error' => 'YAMLフォーマットエラー: ',
        'search_placeholder' => '検索...',
        'replace_placeholder' => '置換...',
        'find_all' => 'すべて',
        'replace' => '置換',
        'replace_all' => 'すべて置換',
        'toggle_replace_mode' => '置換モードを切り替え',
        'toggle_regexp_mode' => '正規表現検索',
        'toggle_case_sensitive' => '大文字小文字を区別',
        'toggle_whole_words' => '単語単位で一致',
        'search_in_selection' => '選択範囲内を検索',
        'search_counter_of' => '合計',
        'select_all' => 'すべて選択',
        'selected_info' => '{count}個のファイルを選択、合計 {size}',
        'selected_info_none' => '0項目を選択',
        'batch_delete' => '一括削除',
        'batch_delete_confirm' => '選択した{count}個のファイル/フォルダを削除してもよろしいですか？この操作は元に戻せません！',
        'batch_delete_no_selection' => '削除するファイルを選択してください！',
        'chmod_invalid_input' => '有効な権限値を入力してください（3桁または4桁の8進数、例: 644 または 0755）。',
        'delete_confirm' => '⚠️ "{name}"を削除してもよろしいですか？この操作は元に戻せません！',
        'json_format_success' => 'JSON フォーマット成功',
        'js_format_success' => 'JavaScript フォーマット成功',
        'unsupported_format' => '現在のモードではフォーマットをサポートしていません',
        'format_error' => 'フォーマットエラー：{message}',
        'json_syntax_valid' => 'JSON 構文は正しいです',
        'json_syntax_error' => 'JSON 構文エラー：{message}',
        'yaml_syntax_valid' => 'YAML 構文は正しいです',
        'yaml_syntax_error' => 'YAML 構文エラー：{message}',
        'yaml_format_success' => 'YAML フォーマット成功',
        'yaml_format_error' => 'YAML フォーマットエラー：{message}',
        'search_empty_input' => '検索キーワードを入力してください',
        'search_no_results' => '一致するファイルが見つかりません',
        'search_error' => '検索エラー：{message}',
        'search_filename' => 'ファイル名',
        'search_path' => 'パス',
        'search_action' => '操作',
        'search_move_to' => '移動',
        'edit_file_title' => 'ファイル編集：{filename}',
        'fetch_content_error' => 'ファイル内容を取得できません：{message}',
        'save_file_success' => 'ファイルの保存に成功しました',
        'search.noResults' => '結果なし',
        'search.previousMatch' => '前の一致 (Shift+Enter)',
        'search.nextMatch' => '次の一致 (Enter)',
        'search.matchCase' => '大文字小文字を区別 (Alt+C)',
        'search.matchWholeWord' => '単語単位で一致 (Alt+W)',
        'search.useRegex' => '正規表現を使用 (Alt+R)',
        'search.findInSelection' => '選択範囲内を検索 (Alt+L)',
        'search.close' => '閉じる (Escape)',
        'search.toggleReplace' => '置換を切り替え',
        'search.preserveCase' => '大文字小文字を保持 (Alt+P)',
        'search.replaceAll' => 'すべて置換 (Ctrl+Alt+Enter)',
        'search.replace' => '置換 (Enter)',
        'search.find' => '検索',
        'search.replace' => '置換',
        'format_success' => 'フォーマット成功',
        'format_unsupported' => 'フォーマットをサポートしていません',
        'format_error' => 'フォーマットエラー：{message}',
        'unsupported_format' => '現在のモードではフォーマットをサポートしていません',
        'toggleComment' => 'コメントを切り替え',
        'compare' => '比較',
        'enterModifiedContent' => '比較用の修正内容を入力してください：',
        'closeDiff' => '差分ビューを閉じる',
        "cancelButton" => "キャンセル",
        "saveButton" => "保存",
        'toggleFullscreen' => '全画面',
        "lineColumnDisplay" => "行: {line}, 列: {column}",
        "charCountDisplay" => "文字数: {charCount}",
        "fileName" => "ファイル名",
        "fileSize" => "サイズ",
        "fileType" => "ファイルタイプ",
        'formatYaml' => 'YAMLをフォーマット',
        'validateJson' => 'JSON構文を検証',
        'total_items'  => '合計',
        'items'        => '項目',
        'current_path' => '現在のパス',
        'disk'         => 'ディスク',
        'root'         => 'ルートディレクトリ',
        'validateYaml' => 'YAML構文を検証'
    ],

    'vi' => [
        'select_language'        => 'Chọn ngôn ngữ',
        'simplified_chinese'     => 'Tiếng Trung Giản thể',
        'traditional_chinese'    => 'Tiếng Trung Phồn thể',
        'english'                => 'Tiếng Anh',
        'korean'                 => 'Tiếng Hàn',
        'vietnamese'             => 'Tiếng Việt',
        'thailand'               => 'Tiếng Thái',
        'japanese'               => 'Tiếng Nhật',
        'russian'                => 'Tiếng Nga',
        'germany'                => 'Tiếng Đức',
        'france'                 => 'Tiếng Pháp',
        'arabic'                 => 'Tiếng Ả Rập',
        'spanish'                => 'Tiếng Tây Ban Nha',
        'bangladesh'             => 'Tiếng Bangladesh',
        'oklch_values'     => 'Giá trị OKLCH:',
        'contrast_ratio'   => 'Tỷ lệ tương phản:',
        'reset'            => 'Đặt lại',
        'close'                  => 'Đóng',
        'save'                   => 'Lưu',
        'theme_download'         => 'Tải chủ đề',
        'select_all'             => 'Chọn tất cả',
        'batch_delete'           => 'Xóa hàng loạt',
        'batch_delete_success'   => '✅ Xóa hàng loạt thành công',
        'batch_delete_failed'    => '❌ Xóa hàng loạt thất bại',
        'confirm_delete'         => 'Xác nhận xóa?',
        'total'                  => 'Tổng:',
        'free'                   => 'Còn lại:',
        'hover_to_preview'       => 'Nhấp để kích hoạt xem trước khi di chuột',
        'spectra_config'         => 'Quản lý cấu hình Spectra',
        'current_mode'           => 'Chế độ hiện tại: Đang tải...',
        'toggle_mode'            => 'Chuyển chế độ',
        'check_update'           => 'Kiểm tra cập nhật',
        'batch_upload'           => 'Chọn tệp để tải lên hàng loạt',
        'add_to_playlist'        => 'Chọn để thêm vào danh sách phát',
        'clear_background'       => 'Xóa nền',
        'clear_background_label' => 'Xóa nền',
        'file_list'              => 'Danh sách tệp',
        'component_bg_color'     => 'Chọn màu nền thành phần',
        'page_bg_color'          => 'Chọn màu nền trang',
        'toggle_font'            => 'Thay đổi phông chữ',
        'filename'               => 'Tên:',
        'filesize'               => 'Kích thước:',
        'duration'               => 'Thời lượng:',
        'resolution'             => 'Độ phân giải:',
        'bitrate'                => 'Tốc độ bit:',
        'type'                   => 'Loại:',
        'image'                  => 'Hình ảnh',
        'video'                  => 'Video',
        'audio'                  => 'Âm thanh',
        'document'               => 'Tài liệu',
        'delete'                 => 'Xóa',
        'rename'                 => 'Đổi tên',
        'download'               => 'Tải xuống',
        'set_background'         => 'Đặt nền',
        'preview'                => 'Xem trước',
        'toggle_fullscreen'      => 'Chuyển toàn màn hình',
        'supported_formats'      => 'Định dạng hỗ trợ: [ jpg, jpeg, png, gif, webp, mp4, webm, mkv, mp3, wav, flac ]',
        'drop_files_here'        => 'Kéo thả tệp vào đây',
        'or'                     => 'hoặc',
        'select_files'           => 'Chọn tệp',
        'unlock_php_upload_limit'=> 'Mở khóa giới hạn tải lên PHP',
        'upload'                 => 'Tải lên',
        'cancel'                 => 'Hủy',
        'rename_file'            => 'Đổi tên tệp',
        'new_filename'           => 'Tên mới',
        'invalid_filename_chars' => 'Tên tệp không được chứa: \\/:*?"<>|',
        'confirm'                => 'Xác nhận',
        'media_player'           => 'Trình phát đa phương tiện',
        'playlist'               => 'Danh sách phát',
        'clear_list'             => 'Xóa danh sách',
        'toggle_list'            => 'Ẩn danh sách',
        'picture_in_picture'     => 'Hình trong hình',
        'fullscreen'             => 'Toàn màn hình',
        'fetching_version'       => 'Đang kiểm tra phiên bản...',
        'download_local'         => 'Tải về máy',
        'change_language'        => 'Thay đổi ngôn ngữ',
        'hour_announcement'      => 'Báo giờ, hiện tại là',  
        'hour_exact'             => 'giờ đúng',
        'weekDays' => ['Chủ Nhật', 'Thứ Hai', 'Thứ Ba', 'Thứ Tư', 'Thứ Năm', 'Thứ Sáu', 'Thứ Bảy'],
        'labels' => [
            'year' => 'Năm',
            'month' => 'Tháng',
            'day' => 'Ngày',
            'week' => ''
        ],
        'zodiacs' => ['Khỉ','Gà','Chó','Lợn','Chuột','Trâu','Hổ','Thỏ','Rồng','Rắn','Ngựa','Dê'],
        'heavenlyStems' => ['Giáp','Ất','Bính','Đinh','Mậu','Kỷ','Canh','Tân','Nhâm','Quý'],
        'earthlyBranches' => ['Tí','Sửu','Dần','Mão','Thìn','Tỵ','Ngọ','Mùi','Thân','Dậu','Tuất','Hợi'],
        'months' => ['Tháng 1','Tháng 2','Tháng 3','Tháng 4','Tháng 5','Tháng 6','Tháng 7','Tháng 8','Tháng 9','Tháng 10','Tháng 11','Tháng 12'],
        'days' => ['Ngày 1','Ngày 2','Ngày 3','Ngày 4','Ngày 5','Ngày 6','Ngày 7','Ngày 8','Ngày 9','Ngày 10',
                   'Ngày 11','Ngày 12','Ngày 13','Ngày 14','Ngày 15','Ngày 16','Ngày 17','Ngày 18','Ngày 19','Ngày 20',
                   'Ngày 21','Ngày 22','Ngày 23','Ngày 24','Ngày 25','Ngày 26','Ngày 27','Ngày 28','Ngày 29','Ngày 30'],
        'leap_prefix' => 'Nhuận',
        'year_suffix' => ' Năm',
        'month_suffix' => '',
        'day_suffix' => '',
        'periods' => ['Tý', 'Sửu', 'Dần', 'Mão', 'Thìn', 'Tỵ', 'Ngọ', 'Mùi', 'Thân', 'Dậu', 'Tuất', 'Hợi'],
        'default_period' => ' Giờ',
        'initial' => 'đầu',  
        'middle' => 'giữa',   
        'final' =>'cuối',  
        'clear_confirm' =>'Xác nhận xóa cấu hình hiện tại?', 
        'back_to_first' => 'Đã quay về bài đầu tiên',
        'font_default' => 'Đã chuyển sang font tròn',
        'font_fredoka' => 'Đã chuyển về font mặc định',
        'font_mono'   => 'Đã chuyển sang font viết tay',
        'font_noto'     => 'Đã chuyển sang font chữ Hán',
        'font_dm_serif'     => 'Đã chuyển sang font DM Serif Display',
        'error_loading_time' => 'Lỗi hiển thị thời gian',
        'switch_to_light_mode' => 'Chuyển sang chế độ sáng',
        'switch_to_dark_mode' => 'Chuyển sang chế độ tối',
        'current_mode_dark' => 'Chế độ hiện tại: Tối',
        'current_mode_light' => 'Chế độ hiện tại: Sáng',
        'fetching_version' => 'Đang kiểm tra phiên bản...',
        'latest_version' => 'Phiên bản mới nhất',
        'unable_to_fetch_version' => 'Không thể kiểm tra phiên bản mới',
        'request_failed' => 'Yêu cầu thất bại, vui lòng thử lại',
        'pip_not_supported' => 'Không hỗ trợ hình trong hình',
        'pip_operation_failed' => 'Thao tác hình trong hình thất bại',
        'exit_picture_in_picture' => 'Thoát hình trong hình',
        'picture_in_picture' => 'Hình trong hình',
        'hide_playlist' => 'Ẩn danh sách',
        'show_playlist' => 'Hiện danh sách',
        'enter_fullscreen' => 'Vào toàn màn hình',
        'exit_fullscreen' => 'Thoát toàn màn hình',
        'confirm_update_php' => 'Xác nhận cập nhật cấu hình PHP?',
        'select_files_to_delete' => 'Vui lòng chọn tệp cần xóa!',
        'confirm_batch_delete' => 'Xác nhận xóa %d tệp?',
        'unable_to_fetch_current_version' => 'Đang kiểm tra phiên bản hiện tại...',
        'current_version' => 'Phiên bản hiện tại',
        'copy_command'     => 'Sao chép lệnh',
        'command_copied'   => 'Đã sao chép lệnh!',
        "updateModalLabel" => "Trạng thái cập nhật",
        "updateDescription" => "Quá trình cập nhật đang bắt đầu.",
        "waitingMessage" => "Đang chờ bắt đầu...",
        "update_plugin" => "Cập nhật plugin",
        "installation_complete" => "Cài đặt hoàn tất!",
        'confirm_title'             => 'Xác nhận thao tác',
        'confirm_delete_file'   => 'Xác nhận xóa tệp %s?',
        'delete_success'      => 'Xóa thành công: %s',
        'delete_failure'      => 'Xóa thất bại: %s',
        'upload_error_type_not_supported' => 'Không hỗ trợ định dạng: %s',
        'upload_error_move_failed'        => 'Tải lên thất bại: %s',
        'confirm_clear_background' => 'Xác nhận xóa nền?',
        'background_cleared'      => 'Đã xóa nền!',
        'createShareLink' => 'Tạo liên kết chia sẻ',
        'closeButton' => 'Đóng',
        'expireTimeLabel' => 'Thời gian hết hạn',
        'expire1Hour' => '1 giờ',
        'expire1Day' => '1 ngày',
        'expire7Days' => '7 ngày',
        'expire30Days' => '30 ngày',
        'maxDownloadsLabel' => 'Số lượt tải tối đa',
        'max1Download' => '1 lần',
        'max5Downloads' => '5 lần',
        'max10Downloads' => '10 lần',
        'maxUnlimited' => 'Không giới hạn',
        'shareLinkLabel' => 'Liên kết chia sẻ',
        'copyLinkButton' => 'Sao chép liên kết',
        'closeButtonFooter' => 'Đóng',
        'generateLinkButton' => 'Tạo liên kết',
        'fileNotSelected' => 'Chưa chọn tệp',
        'httpError' => 'Lỗi HTTP',
        'linkGenerated' => '✅ Đã tạo liên kết chia sẻ',
        'operationFailed' => '❌ Thao tác thất bại',
        'generateLinkFirst' => 'Vui lòng tạo liên kết chia sẻ trước',
        'linkCopied' => '📋 Liên kết đã được sao chép',
        'copyFailed' => '❌ Sao chép thất bại',
        'cleanExpiredButton' => 'Dọn hết hạn',
        'deleteAllButton' => 'Xóa tất cả',
        'cleanSuccess' => '✅ Dọn dẹp hoàn tất, %s mục đã bị xóa',
        'deleteSuccess' => '✅ Tất cả liên kết đã bị xóa, %s tệp đã bị xóa',
        'confirmDeleteAll' => '⚠️ Bạn có chắc muốn xóa TẤT CẢ các liên kết chia sẻ không?',
        'operationFailed' => '❌ Thao tác thất bại',
        'ip_info' => 'Thông tin IP',
        'ip_support' => 'Hỗ trợ IP',
        'ip_address' => 'Địa chỉ IP',
        'location' => 'Khu vực',
        'isp' => 'Nhà cung cấp',
        'asn' => 'ASN',
        'timezone' => 'Múi giờ',
        'latitude_longitude' => 'Tọa độ',
        'latency_info' => 'Thông tin độ trễ',
        'current_fit_mode'    => 'Chế độ hiện tại',
        'fit_contain'    => 'Giữ tỷ lệ',
        'fit_fill'       => 'Kéo giãn',
        'fit_none'       => 'Kích thước gốc',
        'fit_scale-down' => 'Tự động thu nhỏ',
        'fit_cover'      => 'Cắt vừa',
        'advanced_color_settings' => 'Cài đặt màu nâng cao',
        'advanced_color_control' => 'Điều khiển màu nâng cao',
        'color_control' => 'Điều khiển màu',
        'primary_hue' => 'Tông màu chính',
        'chroma' => 'Độ bão hòa',
        'lightness' => 'Độ sáng',
        'or_use_palette' => 'hoặc sử dụng bảng màu',
        'reset_to_default' => 'Đặt lại về mặc định',
        'preview_and_contrast' => 'Xem trước và độ tương phản',
        'color_preview' => 'Xem trước màu',
        'readability_check' => 'Kiểm tra khả năng đọc',
        'contrast_between_text_and_bg' => 'Độ tương phản giữa chữ và nền:',
        'hue_adjustment' => 'Điều chỉnh tông màu',
        'recent_colors' => 'Màu đã dùng gần đây',
        'apply' => 'Áp dụng',
        'excellent_aaa' => 'Xuất sắc (AAA)',
        'good_aa' => 'Tốt (AA)',
        'poor_needs_improvement' => 'Kém (Cần cải thiện)',
        'mount_point' => 'Điểm gắn:',
        'used_space'  => 'Dung lượng đã sử dụng:',
        'file_summary' => 'Đã chọn %d tệp (%s MB)',
        'pageTitle' => 'Trợ lý Tập tin',
        'uploadBtn' => 'Tải lên Tập tin',
        'rootDirectory' => 'Thư mục Gốc',
        'permissions' => 'Quyền',
        'actions' => 'Thao tác',
        'directory' => 'Thư mục',
        'file' => 'Tập tin',
        'confirmDelete' => 'Bạn có chắc chắn muốn xóa {0} không? Thao tác này không thể hoàn tác.',
        'newName' => 'Tên mới:',
        'setPermissions' => '🔒 Thiết lập Quyền',
        'modifiedTime' => 'Thời gian Sửa đổi',
        'owner' => 'Chủ sở hữu',
        'create' => 'Tạo',
        'newFolder' => 'Thư mục Mới',
        'newFile' => 'Tập tin Mới',
        'folderName' => 'Tên thư mục:',
        'searchFiles' => 'Tìm kiếm Tập tin',
        'noMatchingFiles' => 'Không tìm thấy tập tin phù hợp.',
        'moveTo' => 'Di chuyển đến',
        'confirm' => 'Xác nhận',
        'goBack' => 'Quay lại',
        'refreshDirectory' => 'Làm mới Thư mục',
        'filePreview' => 'Xem trước Tập tin',
        'unableToLoadImage' => 'Không thể tải hình ảnh:',
        'unableToLoadSVG' => 'Không thể tải tập tin SVG:',
        'unableToLoadAudio' => 'Không thể tải âm thanh:',
        'unableToLoadVideo' => 'Không thể tải video:',
        'fileAssistant' => 'Trợ lý Tập tin',
        'errorSavingFile' => 'Lỗi: Không thể lưu tập tin.',
        'uploadFailed' => 'Tải lên Thất bại',
        'fileNotExistOrNotReadable' => 'Tập tin không tồn tại hoặc không thể đọc.',
        'inputFileName' => 'Nhập tên tập tin',
        'permissionValue' => 'Giá trị quyền (ví dụ: 0644)',
        'inputThreeOrFourDigits' => 'Nhập số có 3 hoặc 4 chữ số, ví dụ: 0644 hoặc 0755',
        'fontSizeL' => 'Cỡ chữ',
        'newNameCannotBeEmpty' => 'Tên mới không được để trống',
        'fileNameCannotContainChars' => 'Tên tập tin không được chứa các ký tự: < > : " / \\ | ? *',
        'folderNameCannotBeEmpty' => 'Tên thư mục không được để trống',
        'fileNameCannotBeEmpty' => 'Tên tập tin không được để trống',
        'searchError' => 'Lỗi khi tìm kiếm: ',
        'encodingChanged' => 'Mã hóa đã được đổi thành {0}. Việc chuyển đổi thực tế sẽ được thực hiện ở phía máy chủ khi lưu.',
        'errorLoadingFileContent' => 'Lỗi khi tải nội dung tập tin: ',
        'permissionHelp' => 'Vui lòng nhập giá trị quyền hợp lệ (số bát phân 3 hoặc 4 chữ số, ví dụ: 644 hoặc 0755)',
        'permissionValueCannotExceed' => 'Giá trị quyền không được vượt quá 0777',
        'goBackTitle' => 'Quay lại',
        'rootDirectoryTitle' => 'Về Thư mục Gốc',
        'homeDirectoryTitle' => 'Về Thư mục Chính',
        'refreshDirectoryTitle' => 'Làm mới Thư mục',
        'selectAll' => 'Chọn Tất cả',
        'invertSelection' => 'Đảo ngược Lựa chọn',
        'deleteSelected' => 'Xóa Đã chọn',
        'searchTitle' => 'Tìm kiếm',
        'createTitle' => 'Tạo mới',
        'uploadTitle' => 'Tải lên',
        'dragHint' => 'Kéo và thả tập tin vào đây hoặc nhấp để chọn nhiều tập tin',
        'searchInputPlaceholder' => 'Nhập tên tập tin',
        'search_placeholder' => 'Nhập tên tập tin cần tìm...',
        'advancedEdit' => 'Chỉnh sửa Nâng cao',
        'search' => 'Tìm kiếm',
        'format' => 'Định dạng',
        'goToParentDirectoryTitle' => 'Về Thư mục Cha',
        'alreadyAtRootDirectory' => 'Đã ở thư mục gốc, không thể quay lại.',
        'fullscreen' => 'Toàn màn hình',
        'exitFullscreen' => 'Thoát Toàn màn hình',
        'search_title' => 'Tìm kiếm Nội dung Tập tin',
        'json_format_success' => 'Định dạng JSON thành công',
        'js_format_success' => 'Định dạng JavaScript thành công',
        'format_not_supported' => 'Chế độ hiện tại không hỗ trợ định dạng',
        'format_error' => 'Lỗi định dạng: ',
        'json_syntax_valid' => 'Cú pháp JSON chính xác',
        'json_syntax_error' => 'Lỗi cú pháp JSON: ',
        'yaml_syntax_valid' => 'Cú pháp YAML chính xác',
        'yaml_syntax_error' => 'Lỗi cú pháp YAML: ',
        'yaml_format_success' => 'Định dạng YAML thành công',
        'yaml_format_error' => 'Lỗi định dạng YAML: ',
        'search_placeholder' => 'Tìm kiếm...',
        'replace_placeholder' => 'Thay thế bằng...',
        'find_all' => 'Tất cả',
        'replace' => 'Thay thế',
        'replace_all' => 'Thay thế Tất cả',
        'toggle_replace_mode' => 'Chuyển chế độ Thay thế',
        'toggle_regexp_mode' => 'Tìm kiếm Biểu thức Chính quy',
        'toggle_case_sensitive' => 'Phân biệt Chữ hoa chữ thường',
        'toggle_whole_words' => 'Khớp Toàn bộ Từ',
        'search_in_selection' => 'Tìm trong Vùng chọn',
        'search_counter_of' => 'Tổng',
        'select_all' => 'Chọn Tất cả',
        'selected_info' => 'Đã chọn {count} tập tin, tổng cộng {size}',
        'selected_info_none' => 'Đã chọn 0 mục',
        'batch_delete' => 'Xóa Hàng loạt',
        'batch_delete_confirm' => 'Bạn có chắc chắn muốn xóa {count} tập tin/thư mục đã chọn không? Thao tác này không thể hoàn tác!',
        'batch_delete_no_selection' => 'Vui lòng chọn tập tin để xóa trước!',
        'chmod_invalid_input' => 'Vui lòng nhập giá trị quyền hợp lệ (số bát phân 3 hoặc 4 chữ số, ví dụ: 644 hoặc 0755).',
        'delete_confirm' => '⚠️ Bạn có chắc chắn muốn xóa "{name}" không? Thao tác này không thể hoàn tác!',
        'json_format_success' => 'Định dạng JSON thành công',
        'js_format_success' => 'Định dạng JavaScript thành công',
        'unsupported_format' => 'Chế độ hiện tại không hỗ trợ định dạng',
        'format_error' => 'Lỗi định dạng: {message}',
        'json_syntax_valid' => 'Cú pháp JSON chính xác',
        'json_syntax_error' => 'Lỗi cú pháp JSON: {message}',
        'yaml_syntax_valid' => 'Cú pháp YAML chính xác',
        'yaml_syntax_error' => 'Lỗi cú pháp YAML: {message}',
        'yaml_format_success' => 'Định dạng YAML thành công',
        'yaml_format_error' => 'Lỗi định dạng YAML: {message}',
        'search_empty_input' => 'Vui lòng nhập từ khóa tìm kiếm',
        'search_no_results' => 'Không tìm thấy tập tin phù hợp',
        'search_error' => 'Lỗi tìm kiếm: {message}',
        'search_filename' => 'Tên tập tin',
        'search_path' => 'Đường dẫn',
        'search_action' => 'Thao tác',
        'search_move_to' => 'Di chuyển đến',
        'edit_file_title' => 'Chỉnh sửa Tập tin: {filename}',
        'fetch_content_error' => 'Không thể lấy nội dung tập tin: {message}',
        'save_file_success' => 'Lưu tập tin thành công',
        'search.noResults' => 'Không có kết quả',
        'search.previousMatch' => 'Kết quả trước (Shift+Enter)',
        'search.nextMatch' => 'Kết quả tiếp theo (Enter)',
        'search.matchCase' => 'Phân biệt chữ hoa/thường (Alt+C)',
        'search.matchWholeWord' => 'Khớp toàn bộ từ (Alt+W)',
        'search.useRegex' => 'Sử dụng biểu thức chính quy (Alt+R)',
        'search.findInSelection' => 'Tìm trong vùng chọn (Alt+L)',
        'search.close' => 'Đóng (Escape)',
        'search.toggleReplace' => 'Chuyển đổi Thay thế',
        'search.preserveCase' => 'Giữ nguyên chữ hoa/thường (Alt+P)',
        'search.replaceAll' => 'Thay thế Tất cả (Ctrl+Alt+Enter)',
        'search.replace' => 'Thay thế (Enter)',
        'search.find' => 'Tìm',
        'search.replace' => 'Thay thế',
        'format_success' => 'Định dạng thành công',
        'format_unsupported' => 'Không hỗ trợ định dạng',
        'format_error' => 'Lỗi định dạng: {message}',
        'unsupported_format' => 'Chế độ hiện tại không hỗ trợ định dạng',
        'toggleComment' => 'Chuyển đổi Chú thích',
        'compare' => 'So sánh',
        'enterModifiedContent' => 'Vui lòng nhập nội dung đã sửa để so sánh:',
        'closeDiff' => 'Đóng Chế độ Xem Khác biệt',
        "cancelButton" => "Hủy",
        "saveButton" => "Lưu",
        'toggleFullscreen' => 'Toàn màn hình',
        "lineColumnDisplay" => "Dòng: {line}, Cột: {column}",
        "charCountDisplay" => "Số ký tự: {charCount}",
        "fileName" => "Tên tập tin",
        "fileSize" => "Kích thước",
        "fileType" => "Loại tập tin",
        'formatYaml' => 'Định dạng YAML',
        'validateJson' => 'Kiểm tra Cú pháp JSON',
         'total_items'  => 'Tổng',
        'items'        => 'mục',
        'current_path' => 'Đường dẫn hiện tại',
        'disk'         => 'Đĩa',
        'root'         => 'Thư mục gốc',
        'validateYaml' => 'Kiểm tra Cú pháp YAML'
    ],

    'th' => [
        'select_language'        => 'เลือกภาษา',
        'simplified_chinese'     => 'ภาษาจีนตัวย่อ',
        'traditional_chinese'    => 'ภาษาจีนตัวเต็ม',
        'english'                => 'ภาษาอังกฤษ',
        'korean'                 => 'ภาษาเกาหลี',
        'vietnamese'             => 'ภาษาเวียดนาม',
        'japanese'               => 'ภาษาญี่ปุ่น',
        'russian'                => 'ภาษารัสเซีย',
        'germany'                => 'ภาษาเยอรมัน',
        'france'                 => 'ภาษาฝรั่งเศส',
        'arabic'                 => 'ภาษาอาหรับ',
        'spanish'                => 'ภาษาสเปน',
        'bangladesh'             => 'เบงกาลี',
        'oklch_values'     => 'ค่า OKLCH:',
        'contrast_ratio'   => 'อัตราความเปรียบต่าง:',
        'reset'            => 'รีเซ็ต',
        'close'                  => 'ปิด',
        'save'                   => 'บันทึก',
        'theme_download'         => 'ดาวน์โหลดธีม',
        'select_all'             => 'เลือกทั้งหมด',
        'batch_delete'           => 'ลบไฟล์ที่เลือกทั้งหมด',
        'total'                  => 'รวมทั้งหมด:',
        'free'                   => 'ที่เหลือ:',
        'hover_to_preview'       => 'คลิกเพื่อเปิดการแสดงตัวอย่าง',
        'mount_info'             => 'จุดเชื่อมต่อ: {{mount}}｜พื้นที่ที่ใช้ไป: {{used}}',
        'spectra_config'         => 'การจัดการการตั้งค่า Spectra',
        'current_mode'           => 'โหมดปัจจุบัน: กำลังโหลด...',
        'toggle_mode'            => 'สลับโหมด',
        'check_update'           => 'ตรวจสอบการอัปเดต',
        'batch_upload'           => 'เลือกไฟล์เพื่ออัปโหลดครั้งละหลายไฟล์',
        'add_to_playlist'        => 'เลือกเพื่อเพิ่มลงในเพลย์ลิสต์',
        'clear_background'       => 'ล้างพื้นหลัง',
        'clear_background_label' => 'ล้างพื้นหลัง',
        'file_list'              => 'รายการไฟล์',
        'component_bg_color'     => 'เลือกสีพื้นหลังของคอมโพเนนต์',
        'page_bg_color'          => 'เลือกสีพื้นหลังของหน้า',
        'toggle_font'            => 'สลับแบบอักษร',
        'filename'               => 'ชื่อไฟล์:',
        'filesize'               => 'ขนาดไฟล์:',
        'duration'               => 'ระยะเวลา:',
        'resolution'             => 'ความละเอียด:',
        'bitrate'                => 'บิตเรต:',
        'type'                   => 'ประเภท:',
        'image'                  => 'ภาพ',
        'video'                  => 'วิดีโอ',
        'audio'                  => 'เสียง',
        'document'               => 'เอกสาร',
        'delete'                 => 'ลบ',
        'rename'                 => 'เปลี่ยนชื่อ',
        'download'               => 'ดาวน์โหลด',
        'set_background'         => 'ตั้งค่าพื้นหลัง',
        'preview'                => 'ดูตัวอย่าง',
        'toggle_fullscreen'      => 'สลับเป็นเต็มจอ',
        'supported_formats'      => 'รูปแบบที่รองรับ: [ jpg, jpeg, png, gif, webp, mp4, webm, mkv, mp3, wav, flac ]',
        'drop_files_here'        => 'ลากไฟล์มาที่นี่',
        'or'                     => 'หรือ',
        'select_files'           => 'เลือกไฟล์',
        'unlock_php_upload_limit'=> 'ปลดล็อกข้อจำกัดการอัปโหลดของ PHP',
        'upload'                 => 'อัปโหลด',
        'cancel'                 => 'ยกเลิก',
        'rename_file'            => 'เปลี่ยนชื่อไฟล์',
        'new_filename'           => 'ชื่อไฟล์ใหม่',
        'invalid_filename_chars' => 'ชื่อไฟล์ต้องไม่มีอักขระต่อไปนี้: \\/:*?"<>|',
        'confirm'                => 'ยืนยัน',
        'media_player'           => 'เครื่องเล่นสื่อ',
        'playlist'               => 'เพลย์ลิสต์',
        'clear_list'             => 'ล้างรายการ',
        'toggle_list'            => 'ซ่อนรายการ',
        'picture_in_picture'     => 'ภาพในภาพ',
        'fullscreen'             => 'เต็มจอ',
        'fetching_version'       => 'กำลังดึงข้อมูลเวอร์ชัน...',
        'download_local'         => 'ดาวน์โหลดไปยังเครื่อง',
        'change_language'        => 'เปลี่ยนภาษา',
        'hour_announcement'      => 'การประกาศเวลา, เวลาขณะนี้คือ',
        'hour_exact'             => 'โมงตรง',
        'weekDays' => ['อาทิตย์', 'จันทร์', 'อังคาร', 'พุธ', 'พฤหัสบดี', 'ศุกร์', 'เสาร์'],
        'labels' => [
            'year' => 'ปี',
            'month' => 'เดือน',
            'day' => 'วัน',
            'week' => 'สัปดาห์'
        ],
        'error_loading_time' => 'แสดงเวลาไม่ถูกต้อง',
        'switch_to_light_mode' => 'เปลี่ยนเป็นโหมดสว่าง',
        'switch_to_dark_mode' => 'เปลี่ยนเป็นโหมดมืด',
        'current_mode_dark' => 'โหมดปัจจุบัน: โหมดมืด',
        'current_mode_light' => 'โหมดปัจจุบัน: โหมดสว่าง',
        'fetching_version' => 'กำลังดึงข้อมูลเวอร์ชัน...',
        'latest_version' => 'เวอร์ชันล่าสุด',
        'unable_to_fetch_version' => 'ไม่สามารถดึงข้อมูลเวอร์ชันล่าสุด',
        'request_failed' => 'การร้องขอล้มเหลว กรุณาลองใหม่ภายหลัง',
        'pip_not_supported' => 'สื่อปัจจุบันไม่รองรับภาพในภาพ',
        'pip_operation_failed' => 'การดำเนินการภาพในภาพล้มเหลว',
        'exit_picture_in_picture' => 'ออกจากภาพในภาพ',
        'picture_in_picture' => 'ภาพในภาพ',
        'hide_playlist' => 'ซ่อนรายการ',
        'show_playlist' => 'แสดงรายการ',
        'enter_fullscreen' => 'เปลี่ยนเป็นเต็มจอ',
        'exit_fullscreen' => 'ออกจากเต็มจอ',
        'confirm_update_php' => 'คุณแน่ใจหรือไม่ว่าต้องการอัปเดตการตั้งค่า PHP?',
        'select_files_to_delete' => 'กรุณาเลือกไฟล์ที่จะลบ!',
        'confirm_batch_delete' => 'คุณแน่ใจหรือไม่ว่าต้องการลบไฟล์ที่เลือก %d ไฟล์?',
        'clear_confirm' => 'คุณแน่ใจหรือว่าต้องการล้างการตั้งค่า?',
        'back_to_first' => 'กลับไปที่เพลงแรกในรายการเพลง',
        'font_default' => 'เปลี่ยนเป็นแบบอักษรโค้งมนแล้ว',
        'font_fredoka' => 'เปลี่ยนเป็นแบบอักษรเริ่มต้นแล้ว',
        'font_mono'    => 'เปลี่ยนเป็นแบบอักษรลายมือสนุก ๆ แล้ว',
        'font_noto'    => 'เปลี่ยนเป็นแบบอักษรมีเชิงภาษาจีนแล้ว',
        'font_dm_serif'     => 'เปลี่ยนเป็นฟอนต์ DM Serif Display',
        'batch_delete_success' => '✅ การลบเป็นกลุ่มสำเร็จ',
        'batch_delete_failed' => '❌ การลบเป็นกลุ่มล้มเหลว',
        'confirm_delete' => 'คุณแน่ใจหรือไม่ว่าต้องการลบ?',
        'unable_to_fetch_current_version' => 'กำลังดึงข้อมูลเวอร์ชันปัจจุบัน...',
        'current_version' => 'เวอร์ชันปัจจุบัน',
        'copy_command'     => 'คัดลอกคำสั่ง',
        'command_copied'   => 'คัดลอกคำสั่งไปยังคลิปบอร์ดแล้ว!',
        "updateModalLabel" => "สถานะการอัปเดต",
        "updateDescription" => "กระบวนการอัปเดตกำลังจะเริ่มต้น...",
        "waitingMessage" => "รอให้การดำเนินการเริ่มต้น...",
        "update_plugin" => "อัปเดตปลั๊กอิน",
        "installation_complete" => "การติดตั้งเสร็จสิ้น!",
        'confirm_title'         => 'ยืนยันการดำเนินการ',
        'confirm_delete_file'   => 'คุณแน่ใจหรือไม่ที่จะลบไฟล์ %s?',
        'delete_success'      => 'ลบสำเร็จ: %s',
        'delete_failure'      => 'ลบไม่สำเร็จ: %s',
        'upload_error_type_not_supported' => 'ประเภทไฟล์ที่ไม่รองรับ: %s',
        'upload_error_move_failed'        => 'การอัปโหลดไฟล์ล้มเหลว: %s',
        'confirm_clear_background' => 'แน่ใจหรือไม่ว่าต้องการลบพื้นหลัง?',
        'background_cleared'      => 'ลบพื้นหลังแล้ว!',
        'createShareLink' => 'สร้างลิงค์การแชร์',
        'closeButton' => 'ปิด',
        'expireTimeLabel' => 'เวลาหมดอายุ',
        'expire1Hour' => '1 ชั่วโมง',
        'expire1Day' => '1 วัน',
        'expire7Days' => '7 วัน',
        'expire30Days' => '30 วัน',
        'maxDownloadsLabel' => 'จำนวนการดาวน์โหลดสูงสุด',
        'max1Download' => '1 ครั้ง',
        'max5Downloads' => '5 ครั้ง',
        'max10Downloads' => '10 ครั้ง',
        'maxUnlimited' => 'ไม่จำกัด',
        'shareLinkLabel' => 'ลิงค์การแชร์',
        'copyLinkButton' => 'คัดลอกลิงค์',
        'closeButtonFooter' => 'ปิด',
        'generateLinkButton' => 'สร้างลิงค์',
        'fileNotSelected' => 'ไม่เลือกไฟล์',
        'httpError' => 'ข้อผิดพลาด HTTP',
        'linkGenerated' => '✅ สร้างลิงค์การแชร์แล้ว',
        'operationFailed' => '❌ การดำเนินการล้มเหลว',
        'generateLinkFirst' => 'โปรดสร้างลิงค์การแชร์ก่อน',
        'linkCopied' => '📋 ลิงค์ถูกคัดลอก',
        'copyFailed' => '❌ การคัดลอกล้มเหลว',
        'cleanExpiredButton' => 'ล้างที่หมดอายุ',
        'deleteAllButton' => 'ลบทั้งหมด',
        'cleanSuccess' => '✅ ล้างสำเร็จ, %s รายการถูกลบ',
        'deleteSuccess' => '✅ ลบประวัติการแชร์ทั้งหมดแล้ว, %s ไฟล์ถูกลบ',
        'confirmDeleteAll' => '⚠️ คุณแน่ใจหรือไม่ว่าต้องการลบประวัติการแชร์ทั้งหมด?',
        'operationFailed' => '❌ ล้มเหลวในการดำเนินการ',
        'ip_info' => 'รายละเอียด IP',
        'ip_support' => 'การสนับสนุน IP',
        'ip_address' => 'ที่อยู่ IP',
        'location' => 'ที่ตั้ง',
        'isp' => 'ผู้ให้บริการ',
        'asn' => 'ASN',
        'timezone' => 'เขตเวลา',
        'latitude_longitude' => 'พิกัด',
        'latency_info' => 'ข้อมูลความหน่วง',
        'current_fit_mode'    => 'โหมดปัจจุบัน',
        'fit_contain'    => 'อัตราส่วนปกติ',
        'fit_fill'       => 'เติมเต็ม',
        'fit_none'       => 'ขนาดดั้งเดิม',
        'fit_scale-down' => 'ปรับอัตโนมัติ',
        'fit_cover'      => 'ครอบตัด',
        'advanced_color_settings' => 'การตั้งค่าสีขั้นสูง',
        'advanced_color_control' => 'การควบคุมสีขั้นสูง',
        'color_control' => 'การควบคุมสี',
        'primary_hue' => 'สีหลัก',
        'chroma' => 'ความอิ่มตัว',
        'lightness' => 'ความสว่าง',
        'or_use_palette' => 'หรือใช้จานสี',
        'reset_to_default' => 'รีเซ็ตเป็นค่าเริ่มต้น',
        'preview_and_contrast' => 'ดูตัวอย่างและความเปรียบต่าง',
        'color_preview' => 'ดูตัวอย่างสี',
        'readability_check' => 'ตรวจสอบความสามารถในการอ่าน',
        'contrast_between_text_and_bg' => 'ความเปรียบต่างระหว่างข้อความกับพื้นหลัง:',
        'hue_adjustment' => 'การปรับสี',
        'recent_colors' => 'สีที่ใช้ล่าสุด',
        'apply' => 'นำไปใช้',
        'excellent_aaa' => 'ยอดเยี่ยม (AAA)',
        'good_aa' => 'ดี (AA)',
        'poor_needs_improvement' => 'ต่ำ (ต้องปรับปรุง)',
        'mount_point' => 'จุดเมานท์:',
        'used_space'  => 'พื้นที่ที่ใช้:',
        'file_summary' => 'เลือกไฟล์แล้ว %d ไฟล์ รวมทั้งหมด %s MB',
        'pageTitle' => 'ผู้ช่วยจัดการไฟล์',
        'uploadBtn' => 'อัพโหลดไฟล์',
        'rootDirectory' => 'ไดเรกทอรีหลัก',
        'permissions' => 'สิทธิ์',
        'actions' => 'การดำเนินการ',
        'directory' => 'ไดเรกทอรี',
        'file' => 'ไฟล์',
        'confirmDelete' => 'คุณแน่ใจหรือไม่ที่จะลบ {0}? การดำเนินการนี้ไม่สามารถย้อนกลับได้',
        'newName' => 'ชื่อใหม่:',
        'setPermissions' => '🔒 ตั้งค่าสิทธิ์',
        'modifiedTime' => 'เวลาแก้ไข',
        'owner' => 'เจ้าของ',
        'create' => 'สร้าง',
        'newFolder' => 'โฟลเดอร์ใหม่',
        'newFile' => 'ไฟล์ใหม่',
        'folderName' => 'ชื่อโฟลเดอร์:',
        'searchFiles' => 'ค้นหาไฟล์',
        'noMatchingFiles' => 'ไม่พบไฟล์ที่ตรงกัน',
        'moveTo' => 'ย้ายไปยัง',
        'confirm' => 'ยืนยัน',
        'goBack' => 'กลับไปยังระดับก่อนหน้า',
        'refreshDirectory' => 'รีเฟรชไดเรกทอรี',
        'filePreview' => 'แสดงตัวอย่างไฟล์',
        'unableToLoadImage' => 'ไม่สามารถโหลดภาพ:',
        'unableToLoadSVG' => 'ไม่สามารถโหลดไฟล์ SVG:',
        'unableToLoadAudio' => 'ไม่สามารถโหลดเสียง:',
        'unableToLoadVideo' => 'ไม่สามารถโหลดวิดีโอ:',
        'fileAssistant' => 'ผู้ช่วยจัดการไฟล์',
        'errorSavingFile' => 'ข้อผิดพลาด: ไม่สามารถบันทึกไฟล์ได้',
        'uploadFailed' => 'อัพโหลดล้มเหลว',
        'fileNotExistOrNotReadable' => 'ไฟล์ไม่มีอยู่หรือไม่สามารถอ่านได้',
        'inputFileName' => 'ป้อนชื่อไฟล์',
        'permissionValue' => 'ค่าสิทธิ์ (เช่น: 0644)',
        'inputThreeOrFourDigits' => 'ป้อนตัวเลข 3 หรือ 4 หลัก เช่น 0644 หรือ 0755',
        'fontSizeL' => 'ขนาดฟอนต์',
        'newNameCannotBeEmpty' => 'ชื่อใหม่ไม่สามารถเว้นว่างได้',
        'fileNameCannotContainChars' => 'ชื่อไฟล์ไม่สามารถมีอักขระต่อไปนี้: < > : " / \\ | ? *',
        'folderNameCannotBeEmpty' => 'ชื่อโฟลเดอร์ไม่สามารถเว้นว่างได้',
        'fileNameCannotBeEmpty' => 'ชื่อไฟล์ไม่สามารถเว้นว่างได้',
        'searchError' => 'เกิดข้อผิดพลาดในการค้นหา: ',
        'encodingChanged' => 'เปลี่ยนการเข้ารหัสเป็น {0} แล้ว การแปลงจริงจะดำเนินการบนเซิร์ฟเวอร์เมื่อบันทึก',
        'errorLoadingFileContent' => 'เกิดข้อผิดพลาดในการโหลดเนื้อหาไฟล์: ',
        'permissionHelp' => 'กรุณาป้อนค่าสิทธิ์ที่ถูกต้อง (ตัวเลขฐานแปด 3 หรือ 4 หลัก เช่น 644 หรือ 0755)',
        'permissionValueCannotExceed' => 'ค่าสิทธิ์ต้องไม่เกิน 0777',
        'goBackTitle' => 'กลับไปยังระดับก่อนหน้า',
        'rootDirectoryTitle' => 'กลับไปยังไดเรกทอรีหลัก',
        'homeDirectoryTitle' => 'กลับไปยังไดเรกทอรีหลัก',
        'refreshDirectoryTitle' => 'รีเฟรชไดเรกทอรี',
        'selectAll' => 'เลือกทั้งหมด',
        'invertSelection' => 'กลับด้านการเลือก',
        'deleteSelected' => 'ลบที่เลือก',
        'searchTitle' => 'ค้นหา',
        'createTitle' => 'สร้าง',
        'uploadTitle' => 'อัพโหลด',
        'dragHint' => 'ลากและวางไฟล์ที่นี่หรือคลิกเพื่อเลือกไฟล์หลายไฟล์',
        'searchInputPlaceholder' => 'ป้อนชื่อไฟล์',
        'search_placeholder' => 'ป้อนชื่อไฟล์ที่ต้องการค้นหา...',
        'advancedEdit' => 'แก้ไขขั้นสูง',
        'search' => 'ค้นหา',
        'format' => 'จัดรูปแบบ',
        'goToParentDirectoryTitle' => 'กลับไปยังไดเรกทอรีระดับบน',
        'alreadyAtRootDirectory' => 'อยู่ในไดเรกทอรีหลักแล้ว ไม่สามารถกลับไปยังระดับก่อนหน้าได้',
        'fullscreen' => 'เต็มหน้าจอ',
        'exitFullscreen' => 'ออกจากเต็มหน้าจอ',
        'search_title' => 'ค้นหาเนื้อหาไฟล์',
        'json_format_success' => 'จัดรูปแบบ JSON สำเร็จ',
        'js_format_success' => 'จัดรูปแบบ JavaScript สำเร็จ',
        'format_not_supported' => 'โหมดปัจจุบันไม่รองรับการจัดรูปแบบ',
        'format_error' => 'ข้อผิดพลาดในการจัดรูปแบบ: ',
        'json_syntax_valid' => 'ไวยากรณ์ JSON ถูกต้อง',
        'json_syntax_error' => 'ข้อผิดพลาดไวยากรณ์ JSON: ',
        'yaml_syntax_valid' => 'ไวยากรณ์ YAML ถูกต้อง',
        'yaml_syntax_error' => 'ข้อผิดพลาดไวยากรณ์ YAML: ',
        'yaml_format_success' => 'จัดรูปแบบ YAML สำเร็จ',
        'yaml_format_error' => 'ข้อผิดพลาดในการจัดรูปแบบ YAML: ',
        'search_placeholder' => 'ค้นหา...',
        'replace_placeholder' => 'แทนที่ด้วย...',
        'find_all' => 'ทั้งหมด',
        'replace' => 'แทนที่',
        'replace_all' => 'แทนที่ทั้งหมด',
        'toggle_replace_mode' => 'สลับโหมดแทนที่',
        'toggle_regexp_mode' => 'ค้นหาด้วย Regular Expression',
        'toggle_case_sensitive' => 'ค้นหาโดยคำนึงถึงตัวพิมพ์',
        'toggle_whole_words' => 'ค้นหาคำทั้งหมด',
        'search_in_selection' => 'ค้นหาในส่วนที่เลือก',
        'search_counter_of' => 'ทั้งหมด',
        'select_all' => 'เลือกทั้งหมด',
        'selected_info' => 'เลือก {count} ไฟล์ รวม {size}',
        'selected_info_none' => 'เลือก 0 รายการ',
        'batch_delete' => 'ลบเป็นชุด',
        'batch_delete_confirm' => 'คุณแน่ใจหรือไม่ที่จะลบ {count} ไฟล์/โฟลเดอร์ที่เลือก? การดำเนินการนี้ไม่สามารถย้อนกลับได้!',
        'batch_delete_no_selection' => 'กรุณาเลือกไฟล์ที่จะลบก่อน!',
        'chmod_invalid_input' => 'กรุณาป้อนค่าสิทธิ์ที่ถูกต้อง (ตัวเลขฐานแปด 3 หรือ 4 หลัก เช่น 644 หรือ 0755)',
        'delete_confirm' => '⚠️ คุณแน่ใจหรือไม่ที่จะลบ "{name}"? การดำเนินการนี้ไม่สามารถย้อนกลับได้!',
        'json_format_success' => 'จัดรูปแบบ JSON สำเร็จ',
        'js_format_success' => 'จัดรูปแบบ JavaScript สำเร็จ',
        'unsupported_format' => 'โหมดปัจจุบันไม่รองรับการจัดรูปแบบ',
        'format_error' => 'ข้อผิดพลาดในการจัดรูปแบบ: {message}',
        'json_syntax_valid' => 'ไวยากรณ์ JSON ถูกต้อง',
        'json_syntax_error' => 'ข้อผิดพลาดไวยากรณ์ JSON: {message}',
        'yaml_syntax_valid' => 'ไวยากรณ์ YAML ถูกต้อง',
        'yaml_syntax_error' => 'ข้อผิดพลาดไวยากรณ์ YAML: {message}',
        'yaml_format_success' => 'จัดรูปแบบ YAML สำเร็จ',
        'yaml_format_error' => 'ข้อผิดพลาดในการจัดรูปแบบ YAML: {message}',
        'search_empty_input' => 'กรุณาป้อนคำค้นหา',
        'search_no_results' => 'ไม่พบไฟล์ที่ตรงกัน',
        'search_error' => 'ข้อผิดพลาดในการค้นหา: {message}',
        'search_filename' => 'ชื่อไฟล์',
        'search_path' => 'เส้นทาง',
        'search_action' => 'การดำเนินการ',
        'search_move_to' => 'ย้ายไปยัง',
        'edit_file_title' => 'แก้ไขไฟล์: {filename}',
        'fetch_content_error' => 'ไม่สามารถดึงเนื้อหาไฟล์: {message}',
        'save_file_success' => 'บันทึกไฟล์สำเร็จ',
        'search.noResults' => 'ไม่มีผลลัพธ์',
        'search.previousMatch' => 'รายการที่ตรงกันก่อนหน้า (Shift+Enter)',
        'search.nextMatch' => 'รายการที่ตรงกันถัดไป (Enter)',
        'search.matchCase' => 'คำนึงถึงตัวพิมพ์ (Alt+C)',
        'search.matchWholeWord' => 'ค้นหาคำทั้งหมด (Alt+W)',
        'search.useRegex' => 'ใช้ Regular Expression (Alt+R)',
        'search.findInSelection' => 'ค้นหาในส่วนที่เลือก (Alt+L)',
        'search.close' => 'ปิด (Escape)',
        'search.toggleReplace' => 'สลับการแทนที่',
        'search.preserveCase' => 'รักษาตัวพิมพ์ (Alt+P)',
        'search.replaceAll' => 'แทนที่ทั้งหมด (Ctrl+Alt+Enter)',
        'search.replace' => 'แทนที่ (Enter)',
        'search.find' => 'ค้นหา',
        'search.replace' => 'แทนที่',
        'format_success' => 'จัดรูปแบบสำเร็จ',
        'format_unsupported' => 'ไม่รองรับการจัดรูปแบบ',
        'format_error' => 'ข้อผิดพลาดในการจัดรูปแบบ: {message}',
        'unsupported_format' => 'โหมดปัจจุบันไม่รองรับการจัดรูปแบบ',
        'toggleComment' => 'สลับความคิดเห็น',
        'compare' => 'เปรียบเทียบ',
        'enterModifiedContent' => 'กรุณาป้อนเนื้อหาที่แก้ไขสำหรับการเปรียบเทียบ:',
        'closeDiff' => 'ปิดมุมมองความแตกต่าง',
        "cancelButton" => "ยกเลิก",
        "saveButton" => "บันทึก",
        'toggleFullscreen' => 'เต็มหน้าจอ',
        "lineColumnDisplay" => "บรรทัด: {line}, คอลัมน์: {column}",
        "charCountDisplay" => "จำนวนอักขระ: {charCount}",
        "fileName" => "ชื่อไฟล์",
        "fileSize" => "ขนาด",
        "fileType" => "ประเภทไฟล์",
        'formatYaml' => 'จัดรูปแบบ YAML',
        'validateJson' => 'ตรวจสอบไวยากรณ์ JSON',
        'total_items'  => 'ทั้งหมด',
        'items'        => 'รายการ',
        'current_path' => 'เส้นทางปัจจุบัน',
        'disk'         => 'ดิสก์',
        'root'         => 'ไดเรกทอรีราก',
        'validateYaml' => 'ตรวจสอบไวยากรณ์ YAML'
    ],
    'ru' => [
        'select_language'        => 'Выберите язык',
        'simplified_chinese'     => 'Упрощенный китайский',
        'traditional_chinese'    => 'Традиционный китайский',
        'english'                => 'Английский',
        'korean'                 => 'Корейский',
        'vietnamese'             => 'Вьетнамский',
        'thailand'               => 'Тайский',
        'japanese'               => 'Японский',
        'russian'                => 'Русский',
        'germany'                => 'Немецкий',
        'france'                 => 'Французский',
        'arabic'                 => 'Арабский',
        'spanish'                => 'Испанский',
        'bangladesh'             => 'Бенгальский',
        'oklch_values'     => 'Значения OKLCH:',
        'contrast_ratio'   => 'Контрастность:',
        'reset'            => 'Сброс',
        'close'                  => 'Закрыть',
        'save'                   => 'Сохранить',
        'theme_download'         => 'Скачать тему',
        'select_all'             => 'Выбрать все',
        'batch_delete'           => 'Удалить выбранные файлы',
        'total'                  => 'Всего:',
        'free'                   => 'Свободно:',
        'hover_to_preview'       => 'Нажмите, чтобы включить предварительный просмотр',
        'mount_info'             => 'Точка монтирования: {{mount}}｜Используемое место: {{used}}',
        'spectra_config'         => 'Управление конфигурацией Spectra',
        'current_mode'           => 'Текущий режим: загрузка...',
        'toggle_mode'            => 'Переключить режим',
        'check_update'           => 'Проверить обновление',
        'batch_upload'           => 'Выберите файлы для массовой загрузки',
        'add_to_playlist'        => 'Добавить в плейлист',
        'clear_background'       => 'Очистить фон',
        'clear_background_label' => 'Очистить фон',
        'file_list'              => 'Список файлов',
        'component_bg_color'     => 'Выберите цвет фона компонента',
        'page_bg_color'          => 'Выберите цвет фона страницы',
        'toggle_font'            => 'Переключить шрифт',
        'filename'               => 'Имя файла:',
        'filesize'               => 'Размер:',
        'duration'               => 'Длительность:',
        'resolution'             => 'Разрешение:',
        'bitrate'                => 'Битрейт:',
        'type'                   => 'Тип:',
        'image'                  => 'Изображение',
        'video'                  => 'Видео',
        'audio'                  => 'Аудио',
        'document'               => 'Документ',
        'delete'                 => 'Удалить',
        'rename'                 => 'Переименовать',
        'download'               => 'Скачать',
        'set_background'         => 'Установить фон',
        'preview'                => 'Предварительный просмотр',
        'toggle_fullscreen'      => 'Переключить полноэкранный режим',
        'supported_formats'      => 'Поддерживаемые форматы: [ jpg, jpeg, png, gif, webp, mp4, webm, mkv, mp3, wav, flac ]',
        'drop_files_here'        => 'Перетащите файлы сюда',
        'or'                     => 'или',
        'select_files'           => 'Выбрать файлы',
        'unlock_php_upload_limit'=> 'Снять ограничение PHP загрузки',
        'upload'                 => 'Загрузить',
        'cancel'                 => 'Отменить',
        'rename_file'            => 'Переименовать файл',
        'new_filename'           => 'Новое имя файла',
        'invalid_filename_chars' => 'Имя файла не может содержать следующие символы: \\/:*?"<>|',
        'confirm'                => 'Подтвердить',
        'media_player'           => 'Медиа-плеер',
        'playlist'               => 'Плейлист',
        'clear_list'             => 'Очистить список',
        'toggle_list'            => 'Скрыть список',
        'picture_in_picture'     => 'Картинка в картинке',
        'fullscreen'             => 'Полноэкранный режим',
        'fetching_version'       => 'Получение информации о версии...',
        'download_local'         => 'Скачать локально',
        'change_language'        => 'Изменить язык',
        'hour_announcement'      => 'Объявление времени, сейчас',
        'hour_exact'             => 'час ровно',
        'weekDays' => ['Воскресенье', 'Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота'],
        'labels' => [
            'year' => 'Год',
            'month' => 'Месяц',
            'day' => 'День',
            'week' => 'Неделя'
        ],
        'error_loading_time' => 'Ошибка отображения времени',
        'switch_to_light_mode' => 'Переключиться на светлый режим',
        'switch_to_dark_mode' => 'Переключиться на темный режим',
        'current_mode_dark' => 'Текущий режим: темный',
        'current_mode_light' => 'Текущий режим: светлый',
        'fetching_version' => 'Получение информации о версии...',
        'latest_version' => 'Последняя версия',
        'unable_to_fetch_version' => 'Не удалось получить последнюю версию',
        'request_failed' => 'Запрос не удался, попробуйте позже',
        'pip_not_supported' => 'Текущее медиа не поддерживает картинку в картинке',
        'pip_operation_failed' => 'Не удалось выполнить операцию картинка в картинке',
        'exit_picture_in_picture' => 'Выйти из картинки в картинке',
        'picture_in_picture' => 'Картинка в картинке',
        'hide_playlist' => 'Скрыть список',
        'show_playlist' => 'Показать список',
        'enter_fullscreen' => 'Включить полноэкранный режим',
        'exit_fullscreen' => 'Выйти из полноэкранного режима',
        'confirm_update_php' => 'Вы уверены, что хотите обновить конфигурацию PHP?',
        'select_files_to_delete' => 'Выберите файлы для удаления!',
        'confirm_batch_delete' => 'Вы уверены, что хотите удалить выбранные %d файлов?',
        'clear_confirm' => 'Вы уверены, что хотите очистить конфигурацию?',
        'back_to_first' => 'Вернулся к первой песне в плейлисте',
        'font_default' => 'Переключено на округлый шрифт',
        'font_fredoka' => 'Переключено на шрифт по умолчанию',
        'font_mono'    => 'Переключено на забавный рукописный шрифт',
        'font_noto'    => 'Переключено на китайский рубленый шрифт',
        'font_dm_serif'     => 'Переключено на шрифт DM Serif Display',
        'batch_delete_success' => '✅ Успешное массовое удаление',
        'batch_delete_failed' => '❌ Ошибка массового удаления',
        'confirm_delete' => 'Вы уверены, что хотите удалить?',
        'unable_to_fetch_current_version' => 'Получение информации о текущей версии...',
        'current_version' => 'Текущая версия',
        'copy_command'     => 'Скопировать команду',
        'command_copied'   => 'Команда скопирована в буфер обмена!',
        "updateModalLabel" => "Статус обновления",
        "updateDescription" => "Процесс обновления вот-вот начнется.",
        "waitingMessage" => "Ожидание начала операции...",
        "update_plugin" => "Обновить плагин",
        "installation_complete" => "Установка завершена!",
        'confirm_title'         => 'Подтвердите действие',
        'confirm_delete_file'   => 'Вы уверены, что хотите удалить файл %s?',
        'confirm_delete_file' => 'Вы уверены, что хотите удалить файл %s?',
        'delete_success'      => 'Успешно удалено: %s',
        'delete_failure'      => 'Не удалось удалить: %s',
        'upload_error_type_not_supported' => 'Неподдерживаемый тип файла: %s',
        'upload_error_move_failed'        => 'Ошибка загрузки файла: %s',
        'confirm_clear_background' => 'Вы уверены, что хотите очистить фон?',
        'background_cleared'      => 'Фон очищен!',
        'createShareLink' => 'Создать ссылку для обмена',
        'closeButton' => 'Закрыть',
        'expireTimeLabel' => 'Время истечения',
        'expire1Hour' => '1 час',
        'expire1Day' => '1 день',
        'expire7Days' => '7 дней',
        'expire30Days' => '30 дней',
        'maxDownloadsLabel' => 'Максимальное количество загрузок',
        'max1Download' => '1 раз',
        'max5Downloads' => '5 раз',
        'max10Downloads' => '10 раз',
        'maxUnlimited' => 'Неограничено',
        'shareLinkLabel' => 'Ссылка для обмена',
        'copyLinkButton' => 'Копировать ссылку',
        'closeButtonFooter' => 'Закрыть',
        'generateLinkButton' => 'Создать ссылку',
        'fileNotSelected' => 'Файл не выбран',
        'httpError' => 'Ошибка HTTP',
        'linkGenerated' => '✅ Ссылка для обмена создана',
        'operationFailed' => '❌ Операция не удалась',
        'generateLinkFirst' => 'Сначала создайте ссылку для обмена',
        'linkCopied' => '📋 Ссылка скопирована',
        'copyFailed' => '❌ Ошибка копирования',
        'cleanExpiredButton' => 'Очистить просроченное',
        'deleteAllButton' => 'Удалить всё',
        'cleanSuccess' => '✅ Очистка завершена, %s предмет(ов) удалено',
        'deleteSuccess' => '✅ Все записи о совместном доступе удалены, %s файл(ов) удалено',
        'confirmDeleteAll' => '⚠️ Вы уверены, что хотите удалить ВСЕ записи о совместном доступе?',
        'operationFailed' => '❌ Не удалось выполнить операцию',
        'ip_info' => 'IP информация',
        'ip_support' => 'IP поддержка',
        'ip_address' => 'IP адрес',
        'location' => 'Локация',
        'isp' => 'Провайдер',
        'asn' => 'ASN',
        'timezone' => 'Часовой пояс',
        'latitude_longitude' => 'Координаты',
        'latency_info' => 'Задержка',
        'current_fit_mode'    => 'Текущий режим',
        'fit_contain'    => 'Обычное соотношение',
        'fit_fill'       => 'Растянуть',
        'fit_none'       => 'Оригинальный размер',
        'fit_scale-down' => 'Уменьшить при необходимости',
        'fit_cover'      => 'Обрезать по размеру',
        'advanced_color_settings' => 'Расширенные настройки цвета',
        'advanced_color_control' => 'Расширенное управление цветом',
        'color_control' => 'Управление цветом',
        'primary_hue' => 'Основной оттенок',
        'chroma' => 'Насыщенность',
        'lightness' => 'Яркость',
        'or_use_palette' => 'или используйте палитру',
        'reset_to_default' => 'Сбросить по умолчанию',
        'preview_and_contrast' => 'Предпросмотр и контраст',
        'color_preview' => 'Предпросмотр цвета',
        'readability_check' => 'Проверка читаемости',
        'contrast_between_text_and_bg' => 'Контраст текста и фона:',
        'hue_adjustment' => 'Настройка оттенка',
        'recent_colors' => 'Недавние цвета',
        'apply' => 'Применить',
        'excellent_aaa' => 'Отлично (AAA)',
        'good_aa' => 'Хорошо (AA)',
        'poor_needs_improvement' => 'Низкий (нуждается в улучшении)',
        'mount_point' => 'Точка монтирования:',
        'used_space'  => 'Используемое место:',
        'file_summary' => 'Выбрано %d файлов, всего %s MB',
        'pageTitle' => 'Файловый помощник',
        'uploadBtn' => 'Загрузить файл',
        'rootDirectory' => 'Корневая директория',
        'permissions' => 'Права доступа',
        'actions' => 'Действия',
        'directory' => 'Директория',
        'file' => 'Файл',
        'confirmDelete' => 'Вы уверены, что хотите удалить {0}? Это действие нельзя отменить.',
        'newName' => 'Новое имя:',
        'setPermissions' => '🔒 Установить права',
        'modifiedTime' => 'Время изменения',
        'owner' => 'Владелец',
        'create' => 'Создать',
        'newFolder' => 'Новая папка',
        'newFile' => 'Новый файл',
        'folderName' => 'Имя папки:',
        'searchFiles' => 'Поиск файлов',
        'noMatchingFiles' => 'Совпадений не найдено.',
        'moveTo' => 'Переместить в',
        'confirm' => 'Подтвердить',
        'goBack' => 'Назад',
        'refreshDirectory' => 'Обновить директорию',
        'filePreview' => 'Предпросмотр файла',
        'unableToLoadImage' => 'Не удалось загрузить изображение:',
        'unableToLoadSVG' => 'Не удалось загрузить SVG файл:',
        'unableToLoadAudio' => 'Не удалось загрузить аудио:',
        'unableToLoadVideo' => 'Не удалось загрузить видео:',
        'fileAssistant' => 'Файловый помощник',
        'errorSavingFile' => 'Ошибка: Не удалось сохранить файл.',
        'uploadFailed' => 'Ошибка загрузки',
        'fileNotExistOrNotReadable' => 'Файл не существует или недоступен для чтения.',
        'inputFileName' => 'Введите имя файла',
        'permissionValue' => 'Права доступа (например: 0644)',
        'inputThreeOrFourDigits' => 'Введите 3 или 4 цифры, например: 0644 или 0755',
        'fontSizeL' => 'Размер шрифта',
        'newNameCannotBeEmpty' => 'Новое имя не может быть пустым',
        'fileNameCannotContainChars' => 'Имя файла не может содержать следующие символы: < > : " / \\ | ? *',
        'folderNameCannotBeEmpty' => 'Имя папки не может быть пустым',
        'fileNameCannotBeEmpty' => 'Имя файла не может быть пустым',
        'searchError' => 'Ошибка при поиске: ',
        'encodingChanged' => 'Кодировка изменена на {0}. Фактическое преобразование будет выполнено на сервере при сохранении.',
        'errorLoadingFileContent' => 'Ошибка при загрузке содержимого файла: ',
        'permissionHelp' => 'Пожалуйста, введите корректное значение прав доступа (3 или 4 восьмеричных цифры, например: 644 или 0755)',
        'permissionValueCannotExceed' => 'Значение прав доступа не может превышать 0777',
        'goBackTitle' => 'Назад',
        'rootDirectoryTitle' => 'В корневую директорию',
        'homeDirectoryTitle' => 'В домашнюю директорию',
        'refreshDirectoryTitle' => 'Обновить директорию',
        'selectAll' => 'Выбрать все',
        'invertSelection' => 'Инвертировать выбор',
        'deleteSelected' => 'Удалить выбранное',
        'searchTitle' => 'Поиск',
        'createTitle' => 'Создать',
        'uploadTitle' => 'Загрузить',
        'dragHint' => 'Перетащите файлы сюда или нажмите для выбора нескольких файлов',
        'searchInputPlaceholder' => 'Введите имя файла',
        'search_placeholder' => 'Введите имя файла для поиска...',
        'advancedEdit' => 'Расширенное редактирование',
        'search' => 'Поиск',
        'format' => 'Форматировать',
        'goToParentDirectoryTitle' => 'В родительскую директорию',
        'alreadyAtRootDirectory' => 'Уже в корневой директории, нельзя подняться выше.',
        'fullscreen' => 'Полный экран',
        'exitFullscreen' => 'Выйти из полноэкранного режима',
        'search_title' => 'Поиск по содержимому файлов',
        'json_format_success' => 'JSON отформатирован успешно',
        'js_format_success' => 'JavaScript отформатирован успешно',
        'format_not_supported' => 'Текущий режим не поддерживает форматирование',
        'format_error' => 'Ошибка форматирования: ',
        'json_syntax_valid' => 'Синтаксис JSON корректен',
        'json_syntax_error' => 'Ошибка синтаксиса JSON: ',
        'yaml_syntax_valid' => 'Синтаксис YAML корректен',
        'yaml_syntax_error' => 'Ошибка синтаксиса YAML: ',
        'yaml_format_success' => 'YAML отформатирован успешно',
        'yaml_format_error' => 'Ошибка форматирования YAML: ',
        'search_placeholder' => 'Поиск...',
        'replace_placeholder' => 'Заменить на...',
        'find_all' => 'Все',
        'replace' => 'Заменить',
        'replace_all' => 'Заменить все',
        'toggle_replace_mode' => 'Переключить режим замены',
        'toggle_regexp_mode' => 'Поиск по регулярному выражению',
        'toggle_case_sensitive' => 'Учитывать регистр',
        'toggle_whole_words' => 'Поиск целых слов',
        'search_in_selection' => 'Искать в выделенном',
        'search_counter_of' => 'Всего',
        'select_all' => 'Выбрать все',
        'selected_info' => 'Выбрано {count} файлов, всего {size}',
        'selected_info_none' => 'Выбрано 0 элементов',
        'batch_delete' => 'Массовое удаление',
        'batch_delete_confirm' => 'Вы уверены, что хотите удалить {count} выбранных файлов/папок? Это действие нельзя отменить!',
        'batch_delete_no_selection' => 'Пожалуйста, сначала выберите файлы для удаления!',
        'chmod_invalid_input' => 'Пожалуйста, введите корректное значение прав доступа (3 или 4 восьмеричных цифры, например: 644 или 0755).',
        'delete_confirm' => '⚠️ Вы уверены, что хотите удалить "{name}"? Это действие нельзя отменить!',
        'json_format_success' => 'JSON отформатирован успешно',
        'js_format_success' => 'JavaScript отформатирован успешно',
        'unsupported_format' => 'Текущий режим не поддерживает форматирование',
        'format_error' => 'Ошибка форматирования: {message}',
        'json_syntax_valid' => 'Синтаксис JSON корректен',
        'json_syntax_error' => 'Ошибка синтаксиса JSON: {message}',
        'yaml_syntax_valid' => 'Синтаксис YAML корректен',
        'yaml_syntax_error' => 'Ошибка синтаксиса YAML: {message}',
        'yaml_format_success' => 'YAML отформатирован успешно',
        'yaml_format_error' => 'Ошибка форматирования YAML: {message}',
        'search_empty_input' => 'Пожалуйста, введите поисковый запрос',
        'search_no_results' => 'Совпадений не найдено',
        'search_error' => 'Ошибка поиска: {message}',
        'search_filename' => 'Имя файла',
        'search_path' => 'Путь',
        'search_action' => 'Действие',
        'search_move_to' => 'Переместить в',
        'edit_file_title' => 'Редактирование файла: {filename}',
        'fetch_content_error' => 'Не удалось получить содержимое файла: {message}',
        'save_file_success' => 'Файл успешно сохранен',
        'search.noResults' => 'Нет результатов',
        'search.previousMatch' => 'Предыдущее совпадение (Shift+Enter)',
        'search.nextMatch' => 'Следующее совпадение (Enter)',
        'search.matchCase' => 'Учитывать регистр (Alt+C)',
        'search.matchWholeWord' => 'Целые слова (Alt+W)',
        'search.useRegex' => 'Регулярные выражения (Alt+R)',
        'search.findInSelection' => 'Искать в выделенном (Alt+L)',
        'search.close' => 'Закрыть (Escape)',
        'search.toggleReplace' => 'Переключить замену',
        'search.preserveCase' => 'Сохранить регистр (Alt+P)',
        'search.replaceAll' => 'Заменить все (Ctrl+Alt+Enter)',
        'search.replace' => 'Заменить (Enter)',
        'search.find' => 'Найти',
        'search.replace' => 'Заменить',
        'format_success' => 'Форматирование успешно',
        'format_unsupported' => 'Форматирование не поддерживается',
        'format_error' => 'Ошибка форматирования: {message}',
        'unsupported_format' => 'Текущий режим не поддерживает форматирование',
        'toggleComment' => 'Переключить комментарий',
        'compare' => 'Сравнить',
        'enterModifiedContent' => 'Введите измененное содержимое для сравнения:',
        'closeDiff' => 'Закрыть просмотр различий',
        "cancelButton" => "Отмена",
        "saveButton" => "Сохранить",
        'toggleFullscreen' => 'Полный экран',
        "lineColumnDisplay" => "Строка: {line}, Колонка: {column}",
        "charCountDisplay" => "Символов: {charCount}",
        "fileName" => "Имя файла",
        "fileSize" => "Размер",
        "fileType" => "Тип файла",
        'formatYaml' => 'Форматировать YAML',
        'validateJson' => 'Проверить синтаксис JSON',
        'total_items'  => 'Всего',
        'items'        => 'элементов',
        'current_path' => 'Текущий путь',
        'disk'         => 'Диск',
        'root'         => 'Корневая папка',
        'validateYaml' => 'Проверить синтаксис YAML'
    ],
    'ar' => [
        'select_language'        => 'اختر اللغة',
        'simplified_chinese'     => 'الصينية المبسطة',
        'traditional_chinese'    => 'الصينية التقليدية',
        'english'                => 'الإنجليزية',
        'korean'                 => 'الكورية',
        'vietnamese'             => 'الفيتنامية',
        'thailand'               => 'التايلاندية',
        'japanese'               => 'اليابانية',
        'russian'                => 'الروسية',
        'germany'                => 'الألمانية',
        'france'                 => 'الفرنسية',
        'arabic'                 => 'العربية',
        'spanish'                => 'الإسبانية',
        'bangladesh'             => 'البنغالية',
        'oklch_values'     => 'قِيَم OKLCH:',
        'contrast_ratio'   => 'نسبة التباين:',
        'reset'            => 'إعادة تعيين',
        'close'                  => 'إغلاق',
        'save'                   => 'حفظ',
        'theme_download'         => 'تنزيل السمة',
        'select_all'             => 'تحديد الكل',
        'batch_delete'           => 'حذف جماعي للملفات المحددة',
        'batch_delete_success'   => '✅ الحذف الجماعي ناجح',
        'batch_delete_failed'    => '❌ فشل الحذف الجماعي',
        'confirm_delete'         => 'تأكيد الحذف؟',
        'total'                  => 'الإجمالي:',
        'free'                   => 'المتبقي:',
        'hover_to_preview'       => 'انقر لتفعيل معاينة التحويم',
        'spectra_config'         => 'إدارة إعدادات Spectra',
        'current_mode'           => 'الوضع الحالي: جاري التحميل...',
        'toggle_mode'            => 'تبديل الوضع',
        'check_update'           => 'التحقق من التحديثات',
        'batch_upload'           => 'اختر ملفات للرفع الجماعي',
        'add_to_playlist'        => 'حدد لإضافة إلى قائمة التشغيل',
        'clear_background'       => 'مسح الخلفية',
        'clear_background_label' => 'مسح الخلفية',
        'file_list'              => 'قائمة الملفات',
        'component_bg_color'     => 'اختر لون خلفية المكون',
        'page_bg_color'          => 'اختر لون خلفية الصفحة',
        'toggle_font'            => 'تبديل الخط',
        'filename'               => 'الاسم:',
        'filesize'               => 'الحجم:',
        'duration'               => 'المدة:',
        'resolution'             => 'الدقة:',
        'bitrate'                => 'معدل البت:',
        'type'                   => 'النوع:',
        'image'                  => 'صورة',
        'video'                  => 'فيديو',
        'audio'                  => 'صوت',
        'document'               => 'مستند',
        'delete'                 => 'حذف',
        'rename'                 => 'إعادة تسمية',
        'download'               => 'تنزيل',
        'set_background'         => 'تعيين خلفية',
        'preview'                => 'معاينة',
        'toggle_fullscreen'      => 'تبديل ملء الشاشة',
        'supported_formats'      => 'التنسيقات المدعومة: [ jpg, jpeg, png, gif, webp, mp4, webm, mkv, mp3, wav, flac ]',
        'drop_files_here'        => 'أسقط الملفات هنا',
        'or'                     => 'أو',
        'select_files'           => 'اختر ملفات',
        'unlock_php_upload_limit'=> 'رفع قيود الرفع في PHP',
        'upload'                 => 'رفع',
        'cancel'                 => 'إلغاء',
        'rename_file'            => 'إعادة تسمية الملف',
        'new_filename'           => 'اسم الملف الجديد',
        'invalid_filename_chars' => 'لا يمكن أن يحتوي اسم الملف على: \\/:*?"<>|',
        'confirm'                => 'تأكيد',
        'media_player'           => 'مشغل الوسائط',
        'playlist'               => 'قائمة التشغيل',
        'clear_list'             => 'مسح القائمة',
        'toggle_list'            => 'إخفاء القائمة',
        'picture_in_picture'     => 'صورة داخل صورة',
        'fullscreen'             => 'ملء الشاشة',
        'fetching_version'       => 'جاري التحقق من الإصدار...',
        'download_local'         => 'تنزيل محلي',
        'change_language'        => 'تغيير اللغة',
        'hour_announcement'      => 'النشرة الزمنية، التوقيت المحلي هو',  
        'hour_exact'             => 'الساعة بالضبط',
        'weekDays' => ['الأحد', 'الاثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت'],
        'labels' => [
            'year' => 'سنة',
            'month' => 'شهر',
            'day' => 'يوم',
            'week' => 'أسبوع'
        ],
        'zodiacs' => ['القرد','الديك','الكلب','الخنزير','الفأر','الثور','النمر','الأرنب','التنين','الأفعى','الحصان','الخروف'],
        'heavenlyStems' => ['جيا','يي','بينغ','دينغ','وو','جي','قينغ','شين','رين','غوي'],
        'earthlyBranches' => ['زي','تشو','يين','ماو','تشين','سي','وو','وي','شين','يو','شو','هاي'],
        'months' => ['يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'],
        'days' => ['الأول','الثاني','الثالث','الرابع','الخامس','السادس','السابع','الثامن','التاسع','العاشر',
                   'الحادي عشر','الثاني عشر','الثالث عشر','الرابع عشر','الخامس عشر','السادس عشر','السابع عشر','الثامن عشر','التاسع عشر','العشرون',
                   'الحادي والعشرون','الثاني والعشرون','الثالث والعشرون','الرابع والعشرون','الخامس والعشرون','السادس والعشرون','السابع والعشرون','الثامن والعشرون','التاسع والعشرون','الثلاثون'],
        'clear_confirm' =>'هل تريد مسح الإعدادات الحالية؟', 
        'back_to_first' => 'العودة إلى أول مقطع في القائمة',
        'font_default' => 'تم التبديل إلى الخط المدور',
        'font_fredoka' => 'تم التبديل إلى الخط الافتراضي',
        'font_mono'   => 'تم التبديل إلى الخط اليدوي',
        'font_noto'     => 'تم التبديل إلى الخط الصيني',
        'font_dm_serif'     => 'تم التبديل إلى خط DM Serif Display',
        'error_loading_time' => 'خطأ في عرض الوقت',
        'switch_to_light_mode' => 'الوضع الفاتح',
        'switch_to_dark_mode' => 'الوضع الداكن',
        'current_mode_dark' => 'الوضع الحالي: داكن',
        'current_mode_light' => 'الوضع الحالي: فاتح',
        'fetching_version' => 'جاري التحقق من الإصدار...',
        'latest_version' => 'آخر إصدار',
        'unable_to_fetch_version' => 'تعذر الحصول على آخر إصدار',
        'request_failed' => 'فشل الطلب، حاول لاحقًا',
        'pip_not_supported' => 'لا يدعم التشغيل بصورة داخل صورة',
        'pip_operation_failed' => 'فشل عملية الصورة داخل الصورة',
        'exit_picture_in_picture' => 'خروج من صورة داخل صورة',
        'picture_in_picture' => 'صورة داخل صورة',
        'hide_playlist' => 'إخفاء القائمة',
        'show_playlist' => 'إظهار القائمة',
        'enter_fullscreen' => 'ملء الشاشة',
        'exit_fullscreen' => 'خروج من ملء الشاشة',
        'confirm_update_php' => 'هل تريد تحديث إعدادات PHP؟',
        'select_files_to_delete' => 'الرجاء اختيار الملفات للحذف أولاً!',
        'confirm_batch_delete' => 'هل تريد حذف %d ملفات؟',
        'unable_to_fetch_current_version' => 'جاري التحقق من الإصدار الحالي...',
        'current_version' => 'الإصدار الحالي',
        'copy_command'     => 'نسخ الأمر',
        'command_copied'   => 'تم نسخ الأمر!',
        "updateModalLabel" => "حالة التحديث",
        "updateDescription" => "جاري بدء عملية التحديث.",
        "waitingMessage" => "بانتظار بدء العملية...",
        "update_plugin" => "تحديث الملحق",
        "installation_complete" => "اكتمل التثبيت!",
        'confirm_title'             => 'تأكيد العملية',
        'confirm_delete_file'   => 'هل تريد حذف الملف %s؟',
        'delete_success'      => 'تم الحذف: %s',
        'delete_failure'      => 'فشل الحذف: %s',
        'upload_error_type_not_supported' => 'نوع ملف غير مدعوم: %s',
        'upload_error_move_failed'        => 'فشل الرفع: %s',
        'confirm_clear_background' => 'هل تريد مسح الخلفية؟',
        'background_cleared'      => 'تم مسح الخلفية!',
        'createShareLink' => 'إنشاء رابط المشاركة',
        'closeButton' => 'إغلاق',
        'expireTimeLabel' => 'وقت الانتهاء',
        'expire1Hour' => '1 ساعة',
        'expire1Day' => '1 يوم',
        'expire7Days' => '7 أيام',
        'expire30Days' => '30 يوم',
        'maxDownloadsLabel' => 'الحد الأقصى للتنزيلات',
        'max1Download' => '1 مرة',
        'max5Downloads' => '5 مرات',
        'max10Downloads' => '10 مرات',
        'maxUnlimited' => 'غير محدود',
        'shareLinkLabel' => 'رابط المشاركة',
        'copyLinkButton' => 'نسخ الرابط',
        'closeButtonFooter' => 'إغلاق',
        'generateLinkButton' => 'إنشاء الرابط',
        'fileNotSelected' => 'لم يتم اختيار الملف',
        'httpError' => 'خطأ HTTP',
        'linkGenerated' => '✅ تم إنشاء رابط المشاركة',
        'operationFailed' => '❌ فشل العملية',
        'generateLinkFirst' => 'يرجى إنشاء رابط المشاركة أولاً',
        'linkCopied' => '📋 تم نسخ الرابط',
        'copyFailed' => '❌ فشل النسخ',
        'cleanExpiredButton' => 'تنظيف المنتهية',
        'deleteAllButton' => 'حذف الكل',
        'cleanSuccess' => '✅ تم التنظيف بنجاح، تم حذف %s عنصرًا منتهي الصلاحية',
        'deleteSuccess' => '✅ تم حذف جميع سجلات المشاركة، تم حذف %s ملفًا',
        'confirmDeleteAll' => '⚠️ هل أنت متأكد أنك تريد حذف جميع سجلات المشاركة؟',
        'operationFailed' => '❌ فشل في العملية',
        'ip_info' => 'تفاصيل IP',
        'ip_support' => 'دعم IP',
        'ip_address' => 'عنوان IP',
        'location' => 'الموقع',
        'isp' => 'مزود الخدمة',
        'asn' => 'ASN',
        'timezone' => 'المنطقة الزمنية',
        'latitude_longitude' => 'إحداثيات',
        'latency_info' => 'معلومات التأخر',  
        'current_fit_mode'    => 'الوضع الحالي',
        'fit_contain'    => 'نسبة عادية',
        'fit_fill'       => 'تمديد لملء',
        'fit_none'       => 'الحجم الأصلي',
        'fit_scale-down' => 'تكييف ذكي',
        'fit_cover'      => 'اقتصاص افتراضي',
        'advanced_color_settings' => 'إعدادات الألوان المتقدمة',
        'advanced_color_control' => 'تحكم الألوان المتقدم',
        'color_control' => 'تحكم الألوان',
        'primary_hue' => 'درجة اللون الأساسية',
        'chroma' => 'التشبع',
        'lightness' => 'السطوع',
        'or_use_palette' => 'أو استخدام لوحة الألوان',
        'reset_to_default' => 'إعادة التعيين للوضع الافتراضي',
        'preview_and_contrast' => 'المعاينة والتباين',
        'color_preview' => 'معاينة اللون',
        'readability_check' => 'فحص قابلية القراءة',
        'contrast_between_text_and_bg' => 'التباين بين النص والخلفية:',
        'hue_adjustment' => 'ضبط درجة اللون',
        'recent_colors' => 'الألوان المستخدمة مؤخراً',
        'apply' => 'تطبيق',
        'excellent_aaa' => 'ممتاز (AAA)',
        'good_aa' => 'جيد (AA)',
        'poor_needs_improvement' => 'ضعيف (بحاجة إلى تحسين)',
        'mount_point' => 'نقطة التركيب:',
        'used_space'  => 'المساحة المستخدمة:',
        'file_summary' => 'تم اختيار %d ملفات (%s ميجابايت)',
        'pageTitle' => 'مساعد الملفات',
        'uploadBtn' => 'رفع ملف',
        'rootDirectory' => 'المجلد الرئيسي',
        'permissions' => 'الصلاحيات',
        'actions' => 'الإجراءات',
        'directory' => 'مجلد',
        'file' => 'ملف',
        'confirmDelete' => 'هل أنت متأكد من حذف {0}؟ لا يمكن التراجع عن هذا الإجراء.',
        'newName' => 'الاسم الجديد:',
        'setPermissions' => '🔒 تعيين الصلاحيات',
        'modifiedTime' => 'وقت التعديل',
        'owner' => 'المالك',
        'create' => 'إنشاء',
        'newFolder' => 'مجلد جديد',
        'newFile' => 'ملف جديد',
        'folderName' => 'اسم المجلد:',
        'searchFiles' => 'بحث في الملفات',
        'noMatchingFiles' => 'لم يتم العثور على ملفات مطابقة.',
        'moveTo' => 'نقل إلى',
        'confirm' => 'تأكيد',
        'goBack' => 'العودة للخلف',
        'refreshDirectory' => 'تحديث المجلد',
        'filePreview' => 'معاينة الملف',
        'unableToLoadImage' => 'تعذر تحميل الصورة:',
        'unableToLoadSVG' => 'تعذر تحميل ملف SVG:',
        'unableToLoadAudio' => 'تعذر تحميل الصوت:',
        'unableToLoadVideo' => 'تعذر تحميل الفيديو:',
        'fileAssistant' => 'مساعد الملفات',
        'errorSavingFile' => 'خطأ: تعذر حفظ الملف.',
        'uploadFailed' => 'فشل الرفع',
        'fileNotExistOrNotReadable' => 'الملف غير موجود أو غير قابل للقراءة.',
        'inputFileName' => 'أدخل اسم الملف',
        'permissionValue' => 'قيمة الصلاحية (مثال: 0644)',
        'inputThreeOrFourDigits' => 'أدخل 3 أو 4 أرقام، مثال: 0644 أو 0755',
        'fontSizeL' => 'حجم الخط',
        'newNameCannotBeEmpty' => 'الاسم الجديد لا يمكن أن يكون فارغًا',
        'fileNameCannotContainChars' => 'اسم الملف لا يمكن أن يحتوي على الأحرف التالية: < > : " / \\ | ? *',
        'folderNameCannotBeEmpty' => 'اسم المجلد لا يمكن أن يكون فارغًا',
        'fileNameCannotBeEmpty' => 'اسم الملف لا يمكن أن يكون فارغًا',
        'searchError' => 'خطأ في البحث: ',
        'encodingChanged' => 'تم تغيير الترميز إلى {0}. سيتم التحويل الفعلي على الخادم عند الحفظ.',
        'errorLoadingFileContent' => 'خطأ في تحميل محتوى الملف: ',
        'permissionHelp' => 'الرجاء إدخال قيمة صلاحية صالحة (3 أو 4 أرقام ثمانية، مثال: 644 أو 0755)',
        'permissionValueCannotExceed' => 'قيمة الصلاحية لا يمكن أن تتجاوز 0777',
        'goBackTitle' => 'العودة للخلف',
        'rootDirectoryTitle' => 'العودة للمجلد الرئيسي',
        'homeDirectoryTitle' => 'العودة للمجلد الرئيسي',
        'refreshDirectoryTitle' => 'تحديث المجلد',
        'selectAll' => 'تحديد الكل',
        'invertSelection' => 'عكس التحديد',
        'deleteSelected' => 'حذف المحدد',
        'searchTitle' => 'بحث',
        'createTitle' => 'إنشاء',
        'uploadTitle' => 'رفع',
        'dragHint' => 'اسحب وأسقط الملفات هنا أو انقر لاختيار ملفات متعددة',
        'searchInputPlaceholder' => 'أدخل اسم الملف',
        'search_placeholder' => 'أدخل اسم الملف للبحث...',
        'advancedEdit' => 'تحرير متقدم',
        'search' => 'بحث',
        'format' => 'تنسيق',
        'goToParentDirectoryTitle' => 'العودة للمجلد الأب',
        'alreadyAtRootDirectory' => 'أنت بالفعل في المجلد الرئيسي، لا يمكن العودة للخلف.',
        'fullscreen' => 'ملء الشاشة',
        'exitFullscreen' => 'خروج من ملء الشاشة',
        'search_title' => 'بحث في محتوى الملفات',
        'json_format_success' => 'تم تنسيق JSON بنجاح',
        'js_format_success' => 'تم تنسيق JavaScript بنجاح',
        'format_not_supported' => 'الوضع الحالي لا يدعم التنسيق',
        'format_error' => 'خطأ في التنسيق: ',
        'json_syntax_valid' => 'بناء جملة JSON صحيح',
        'json_syntax_error' => 'خطأ في بناء جملة JSON: ',
        'yaml_syntax_valid' => 'بناء جملة YAML صحيح',
        'yaml_syntax_error' => 'خطأ في بناء جملة YAML: ',
        'yaml_format_success' => 'تم تنسيق YAML بنجاح',
        'yaml_format_error' => 'خطأ في تنسيق YAML: ',
        'search_placeholder' => 'بحث...',
        'replace_placeholder' => 'استبدال بـ...',
        'find_all' => 'الكل',
        'replace' => 'استبدال',
        'replace_all' => 'استبدال الكل',
        'toggle_replace_mode' => 'تبديل وضع الاستبدال',
        'toggle_regexp_mode' => 'بحث بالتعبير النمطي',
        'toggle_case_sensitive' => 'بحث حساس لحالة الأحرف',
        'toggle_whole_words' => 'بحث بمطابقة الكلمات كاملة',
        'search_in_selection' => 'بحث في التحديد',
        'search_counter_of' => 'من',
        'select_all' => 'تحديد الكل',
        'selected_info' => 'تم تحديد {count} ملف، إجمالي {size}',
        'selected_info_none' => 'تم تحديد 0 عنصر',
        'batch_delete' => 'حذف جماعي',
        'batch_delete_confirm' => 'هل أنت متأكد من حذف {count} من الملفات/المجلدات المحددة؟ لا يمكن التراجع عن هذا الإجراء!',
        'batch_delete_no_selection' => 'الرجاء تحديد الملفات للحذف أولاً!',
        'chmod_invalid_input' => 'الرجاء إدخال قيمة صلاحية صالحة (3 أو 4 أرقام ثمانية، مثال: 644 أو 0755).',
        'delete_confirm' => '⚠️ هل أنت متأكد من حذف "{name}"؟ لا يمكن التراجع عن هذا الإجراء!',
        'json_format_success' => 'تم تنسيق JSON بنجاح',
        'js_format_success' => 'تم تنسيق JavaScript بنجاح',
        'unsupported_format' => 'الوضع الحالي لا يدعم التنسيق',
        'format_error' => 'خطأ في التنسيق: {message}',
        'json_syntax_valid' => 'بناء جملة JSON صحيح',
        'json_syntax_error' => 'خطأ في بناء جملة JSON: {message}',
        'yaml_syntax_valid' => 'بناء جملة YAML صحيح',
        'yaml_syntax_error' => 'خطأ في بناء جملة YAML: {message}',
        'yaml_format_success' => 'تم تنسيق YAML بنجاح',
        'yaml_format_error' => 'خطأ في تنسيق YAML: {message}',
        'search_empty_input' => 'الرجاء إدخال كلمة البحث',
        'search_no_results' => 'لم يتم العثور على ملفات مطابقة',
        'search_error' => 'خطأ في البحث: {message}',
        'search_filename' => 'اسم الملف',
        'search_path' => 'المسار',
        'search_action' => 'الإجراء',
        'search_move_to' => 'نقل إلى',
        'edit_file_title' => 'تحرير الملف: {filename}',
        'fetch_content_error' => 'تعذر جلب محتوى الملف: {message}',
        'save_file_success' => 'تم حفظ الملف بنجاح',
        'search.noResults' => 'لا توجد نتائج',
        'search.previousMatch' => 'المطابقة السابقة (Shift+Enter)',
        'search.nextMatch' => 'المطابقة التالية (Enter)',
        'search.matchCase' => 'مطابقة حالة الأحرف (Alt+C)',
        'search.matchWholeWord' => 'مطابقة الكلمات كاملة (Alt+W)',
        'search.useRegex' => 'استخدام التعبير النمطي (Alt+R)',
        'search.findInSelection' => 'البحث في التحديد (Alt+L)',
        'search.close' => 'إغلاق (Escape)',
        'search.toggleReplace' => 'تبديل الاستبدال',
        'search.preserveCase' => 'الحفاظ على حالة الأحرف (Alt+P)',
        'search.replaceAll' => 'استبدال الكل (Ctrl+Alt+Enter)',
        'search.replace' => 'استبدال (Enter)',
        'search.find' => 'بحث',
        'search.replace' => 'استبدال',
        'format_success' => 'تم التنسيق بنجاح',
        'format_unsupported' => 'التنسيق غير مدعوم',
        'format_error' => 'خطأ في التنسيق: {message}',
        'unsupported_format' => 'الوضع الحالي لا يدعم التنسيق',
        'toggleComment' => 'تبديل التعليق',
        'compare' => 'مقارنة',
        'enterModifiedContent' => 'الرجاء إدخال المحتوى المعدل للمقارنة:',
        'closeDiff' => 'إغلاق عرض الاختلافات',
        "cancelButton" => "إلغاء",
        "saveButton" => "حفظ",
        'toggleFullscreen' => 'ملء الشاشة',
        "lineColumnDisplay" => "السطر: {line}, العمود: {column}",
        "charCountDisplay" => "عدد الأحرف: {charCount}",
        "fileName" => "اسم الملف",
        "fileSize" => "الحجم",
        "fileType" => "نوع الملف",
        'formatYaml' => 'تنسيق YAML',
        'validateJson' => 'التحقق من صحة بناء جملة JSON',
        'total_items'  => 'إجمالي',
        'items'        => 'عنصر',
        'current_path' => 'المسار الحالي',
        'disk'         => 'القرص',
        'root'         => 'المجلد الجذر',
        'validateYaml' => 'التحقق من صحة بناء جملة YAML'
    ],
    'es' => [
        'select_language'        => 'Seleccionar idioma',
        'simplified_chinese'     => 'Chino simplificado',
        'traditional_chinese'    => 'Chino tradicional',
        'english'                => 'Inglés',
        'korean'                 => 'Coreano',
        'vietnamese'             => 'Vietnamita',
        'thailand'               => 'Tailandés',
        'japanese'               => 'Japonés',
        'russian'                => 'Ruso',
        'germany'                => 'Alemán',
        'france'                 => 'Francés',
        'arabic'                 => 'Árabe',
        'spanish'                => 'Español',
        'bangladesh'             => 'Bengalí',
        'oklch_values'     => 'Valores OKLCH:',
        'contrast_ratio'   => 'Relación de contraste:',
        'reset'            => 'Restablecer',
        'close'                  => 'Cerrar',
        'save'                   => 'Guardar',
        'theme_download'         => 'Descargar tema',
        'select_all'             => 'Seleccionar todo',
        'batch_delete'           => 'Eliminar archivos seleccionados en lote',
        'total'                  => 'Total:',
        'free'                   => 'Libre:',
        'hover_to_preview'       => 'Haga clic para activar la vista previa',
        'mount_info'             => 'Punto de montaje: {{mount}}｜Espacio utilizado: {{used}}',
        'spectra_config'         => 'Gestión de configuración de Spectra',
        'current_mode'           => 'Modo actual: cargando...',
        'toggle_mode'            => 'Cambiar modo',
        'check_update'           => 'Buscar actualizaciones',
        'batch_upload'           => 'Seleccionar archivos para carga masiva',
        'add_to_playlist'        => 'Seleccionar para añadir a la lista de reproducción',
        'clear_background'       => 'Borrar fondo',
        'clear_background_label' => 'Borrar fondo',
        'file_list'              => 'Lista de archivos',
        'component_bg_color'     => 'Seleccionar color de fondo del componente',
        'page_bg_color'          => 'Seleccionar color de fondo de la página',
        'toggle_font'            => 'Cambiar fuente',
        'filename'               => 'Nombre:',
        'filesize'               => 'Tamaño:',
        'duration'               => 'Duración:',
        'resolution'             => 'Resolución:',
        'bitrate'                => 'Tasa de bits:',
        'type'                   => 'Tipo:',
        'image'                  => 'Imagen',
        'video'                  => 'Vídeo',
        'audio'                  => 'Audio',
        'document'               => 'Documento',
        'delete'                 => 'Eliminar',
        'rename'                 => 'Renombrar',
        'download'               => 'Descargar',
        'set_background'         => 'Establecer fondo',
        'preview'                => 'Vista previa',
        'toggle_fullscreen'      => 'Cambiar a pantalla completa',
        'supported_formats'      => 'Formatos compatibles: [ jpg, jpeg, png, gif, webp, mp4, webm, mkv, mp3, wav, flac ]',
        'drop_files_here'        => 'Arrastra los archivos aquí',
        'or'                     => 'o',
        'select_files'           => 'Seleccionar archivos',
        'unlock_php_upload_limit'=> 'Desbloquear límite de carga PHP',
        'upload'                 => 'Subir',
        'cancel'                 => 'Cancelar',
        'rename_file'            => 'Renombrar archivo',
        'new_filename'           => 'Nuevo nombre de archivo',
        'invalid_filename_chars' => 'El nombre del archivo no puede contener los siguientes caracteres: \\/:*?"<>|',
        'confirm'                => 'Confirmar',
        'media_player'           => 'Reproductor multimedia',
        'playlist'               => 'Lista de reproducción',
        'clear_list'             => 'Borrar lista',
        'toggle_list'            => 'Ocultar lista',
        'picture_in_picture'     => 'Imagen en imagen',
        'fullscreen'             => 'Pantalla completa',
        'fetching_version'       => 'Obteniendo información de la versión...',
        'download_local'         => 'Descargar localmente',
        'change_language'        => 'Cambiar idioma',
        'hour_announcement'      => 'Anuncio de hora, ahora son las',
        'hour_exact'             => 'en punto',
        'weekDays' => ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
        'labels' => [
            'year' => 'Año',
            'month' => 'Mes',
            'day' => 'Día',
            'week' => 'Semana'
        ],
        'error_loading_time' => 'Error al mostrar la hora',
        'switch_to_light_mode' => 'Cambiar al modo claro',
        'switch_to_dark_mode' => 'Cambiar al modo oscuro',
        'current_mode_dark' => 'Modo actual: Modo oscuro',
        'current_mode_light' => 'Modo actual: Modo claro',
        'fetching_version' => 'Obteniendo información de la versión...',
        'latest_version' => 'Última versión',
        'unable_to_fetch_version' => 'No se pudo obtener la última versión',
        'request_failed' => 'Solicitud fallida, inténtelo de nuevo más tarde',
        'pip_not_supported' => 'El medio actual no admite Imagen en Imagen',
        'pip_operation_failed' => 'Error en la operación Imagen en Imagen',
        'exit_picture_in_picture' => 'Salir de Imagen en Imagen',
        'picture_in_picture' => 'Imagen en Imagen',
        'hide_playlist' => 'Ocultar lista de reproducción',
        'show_playlist' => 'Mostrar lista de reproducción',
        'enter_fullscreen' => 'Cambiar a pantalla completa',
        'exit_fullscreen' => 'Salir de pantalla completa',
        'confirm_update_php' => '¿Está seguro de que desea actualizar la configuración de PHP?',
        'select_files_to_delete' => '¡Seleccione primero los archivos a eliminar!',
        'confirm_batch_delete' => '¿Está seguro de que desea eliminar los %d archivos seleccionados?',
        'clear_confirm' => '¿Estás seguro de que deseas borrar la configuración?',
        'back_to_first' => 'Regresado a la primera canción en la lista de reproducción',
        'font_default' => 'Cambiado a fuente redondeada',
        'font_fredoka' => 'Cambiado a fuente predeterminada',
        'font_mono'    => 'Cambiado a fuente manuscrita divertida',
        'font_noto'    => 'Cambiado a fuente serif en chino',
        'font_dm_serif'     => 'Cambiado a la fuente DM Serif Display',
        'batch_delete_success' => '✅ Eliminación masiva exitosa',
        'batch_delete_failed' => '❌ Fallo en la eliminación masiva',
        'confirm_delete' => '¿Estás seguro de que deseas eliminar?',
        'unable_to_fetch_current_version' => 'Obteniendo la versión actual...',
        'current_version' => 'Versión actual',
        'copy_command'     => 'Copiar comando',
        'command_copied'   => '¡Comando copiado al portapapeles!',
        "updateModalLabel" => "Estado de actualización",
        "updateDescription" => "El proceso de actualización está a punto de comenzar.",
        "waitingMessage" => "Esperando que comience la operación...",
        "update_plugin" => "Actualizar complemento",
        "installation_complete" => "¡Instalación completa!",
        'confirm_title'         => 'Confirmar acción',
        'confirm_delete_file'   => '¿Estás seguro de que deseas eliminar el archivo %s?',
        'delete_success'      => 'Eliminado con éxito: %s',
        'delete_failure'      => 'Error al eliminar: %s',
        'upload_error_type_not_supported' => 'Tipo de archivo no soportado: %s',
        'upload_error_move_failed'        => 'Error de carga: %s',
        'confirm_clear_background' => '¿Estás seguro de que quieres borrar el fondo?',
        'background_cleared'      => '¡Fondo borrado!',
        'createShareLink' => 'Crear enlace de compartición',
        'closeButton' => 'Cerrar',
        'expireTimeLabel' => 'Tiempo de expiración',
        'expire1Hour' => '1 Hora',
        'expire1Day' => '1 Día',
        'expire7Days' => '7 Días',
        'expire30Days' => '30 Días',
        'maxDownloadsLabel' => 'Descargas máximas',
        'max1Download' => '1 vez',
        'max5Downloads' => '5 veces',
        'max10Downloads' => '10 veces',
        'maxUnlimited' => 'Ilimitado',
        'shareLinkLabel' => 'Enlace para compartir',
        'copyLinkButton' => 'Copiar enlace',
        'closeButtonFooter' => 'Cerrar',
        'generateLinkButton' => 'Generar enlace',
        'fileNotSelected' => 'Archivo no seleccionado',
        'httpError' => 'Error HTTP',
        'linkGenerated' => '✅ Enlace de compartición generado',
        'operationFailed' => '❌ Operación fallida',
        'generateLinkFirst' => 'Por favor, genera el enlace de compartición primero',
        'linkCopied' => '📋 Enlace copiado',
        'copyFailed' => '❌ Error al copiar',
        'cleanExpiredButton' => 'Limpiar caducados',
        'deleteAllButton' => 'Eliminar todo',
        'cleanSuccess' => '✅ Limpieza completada, %s elemento(s) caducado(s) eliminado(s)',
        'deleteSuccess' => '✅ Todos los registros compartidos han sido eliminados, %s archivo(s) eliminado(s)',
        'confirmDeleteAll' => '⚠️ ¿Está seguro de que desea eliminar TODOS los registros compartidos?',
        'operationFailed' => '❌ Operación fallida',
        'ip_info' => 'Detalles de IP',
        'ip_support' => 'Soporte IP',
        'ip_address' => 'Dirección IP',
        'location' => 'Ubicación',
        'isp' => 'Proveedor',
        'asn' => 'ASN',
        'timezone' => 'Zona horaria',
        'latitude_longitude' => 'Coordenadas',
        'latency_info' => 'Informe de latencia',
        'current_fit_mode'    => 'Modo actual',
        'fit_contain'    => 'Proporción normal',
        'fit_fill'       => 'Estirar',
        'fit_none'       => 'Tamaño original',
        'fit_scale-down' => 'Escalado inteligente',
        'fit_cover'      => 'Recorte predeterminado',
        'advanced_color_settings' => 'Configuración avanzada de color',
        'advanced_color_control' => 'Control avanzado de color',
        'color_control' => 'Control de color',
        'primary_hue' => 'Tono principal',
        'chroma' => 'Croma',
        'lightness' => 'Luminosidad',
        'or_use_palette' => 'o usar paleta',
        'reset_to_default' => 'Restablecer por defecto',
        'preview_and_contrast' => 'Vista previa y contraste',
        'color_preview' => 'Vista previa del color',
        'readability_check' => 'Comprobación de legibilidad',
        'contrast_between_text_and_bg' => 'Contraste entre texto y fondo:',
        'hue_adjustment' => 'Ajuste de tono',
        'recent_colors' => 'Colores recientes',
        'apply' => 'Aplicar',
        'excellent_aaa' => 'Excelente (AAA)',
        'good_aa' => 'Bueno (AA)',
        'poor_needs_improvement' => 'Pobre (Necesita mejora)',
        'mount_point' => 'Punto de montaje:',
        'used_space'  => 'Espacio usado:',
        'file_summary' => 'Seleccionados %d archivos, en total %s MB',
        'pageTitle' => 'Asistente de Archivos',
        'uploadBtn' => 'Subir Archivo',
        'rootDirectory' => 'Directorio Raíz',
        'permissions' => 'Permisos',
        'actions' => 'Acciones',
        'directory' => 'Directorio',
        'file' => 'Archivo',
        'confirmDelete' => '¿Estás seguro de que quieres eliminar {0}? Esta acción no se puede deshacer.',
        'newName' => 'Nuevo nombre:',
        'setPermissions' => '🔒 Establecer Permisos',
        'modifiedTime' => 'Hora de Modificación',
        'owner' => 'Propietario',
        'create' => 'Crear',
        'newFolder' => 'Nueva Carpeta',
        'newFile' => 'Nuevo Archivo',
        'folderName' => 'Nombre de carpeta:',
        'searchFiles' => 'Buscar Archivos',
        'noMatchingFiles' => 'No se encontraron archivos coincidentes.',
        'moveTo' => 'Mover a',
        'confirm' => 'Confirmar',
        'goBack' => 'Volver Atrás',
        'refreshDirectory' => 'Actualizar Directorio',
        'filePreview' => 'Vista Previa de Archivo',
        'unableToLoadImage' => 'No se puede cargar la imagen:',
        'unableToLoadSVG' => 'No se puede cargar el archivo SVG:',
        'unableToLoadAudio' => 'No se puede cargar el audio:',
        'unableToLoadVideo' => 'No se puede cargar el video:',
        'fileAssistant' => 'Asistente de Archivos',
        'errorSavingFile' => 'Error: No se puede guardar el archivo.',
        'uploadFailed' => 'Error al Subir',
        'fileNotExistOrNotReadable' => 'El archivo no existe o no se puede leer.',
        'inputFileName' => 'Ingresar nombre de archivo',
        'permissionValue' => 'Valor de permiso (ej: 0644)',
        'inputThreeOrFourDigits' => 'Ingrese 3 o 4 dígitos, ej: 0644 o 0755',
        'fontSizeL' => 'Tamaño de Fuente',
        'newNameCannotBeEmpty' => 'El nuevo nombre no puede estar vacío',
        'fileNameCannotContainChars' => 'El nombre de archivo no puede contener los siguientes caracteres: < > : " / \\ | ? *',
        'folderNameCannotBeEmpty' => 'El nombre de la carpeta no puede estar vacío',
        'fileNameCannotBeEmpty' => 'El nombre del archivo no puede estar vacío',
        'searchError' => 'Error al buscar: ',
        'encodingChanged' => 'Codificación cambiada a {0}. La conversión real se realizará en el servidor al guardar.',
        'errorLoadingFileContent' => 'Error al cargar el contenido del archivo: ',
        'permissionHelp' => 'Por favor ingrese un valor de permiso válido (3 o 4 dígitos octales, ej: 644 o 0755)',
        'permissionValueCannotExceed' => 'El valor de permiso no puede exceder 0777',
        'goBackTitle' => 'Volver Atrás',
        'rootDirectoryTitle' => 'Volver al Directorio Raíz',
        'homeDirectoryTitle' => 'Volver al Directorio Principal',
        'refreshDirectoryTitle' => 'Actualizar Directorio',
        'selectAll' => 'Seleccionar Todo',
        'invertSelection' => 'Invertir Selección',
        'deleteSelected' => 'Eliminar Seleccionados',
        'searchTitle' => 'Buscar',
        'createTitle' => 'Crear',
        'uploadTitle' => 'Subir',
        'dragHint' => 'Arrastra y suelta archivos aquí o haz clic para seleccionar múltiples archivos',
        'searchInputPlaceholder' => 'Ingresar nombre de archivo',
        'search_placeholder' => 'Ingrese el nombre del archivo a buscar...',
        'advancedEdit' => 'Edición Avanzada',
        'search' => 'Buscar',
        'format' => 'Formatear',
        'goToParentDirectoryTitle' => 'Volver al Directorio Padre',
        'alreadyAtRootDirectory' => 'Ya estás en el directorio raíz, no se puede volver atrás.',
        'fullscreen' => 'Pantalla Completa',
        'exitFullscreen' => 'Salir de Pantalla Completa',
        'search_title' => 'Buscar en Contenido de Archivos',
        'json_format_success' => 'JSON formateado exitosamente',
        'js_format_success' => 'JavaScript formateado exitosamente',
        'format_not_supported' => 'El modo actual no admite formateo',
        'format_error' => 'Error de formateo: ',
        'json_syntax_valid' => 'Sintaxis JSON correcta',
        'json_syntax_error' => 'Error de sintaxis JSON: ',
        'yaml_syntax_valid' => 'Sintaxis YAML correcta',
        'yaml_syntax_error' => 'Error de sintaxis YAML: ',
        'yaml_format_success' => 'YAML formateado exitosamente',
        'yaml_format_error' => 'Error de formateo YAML: ',
        'search_placeholder' => 'Buscar...',
        'replace_placeholder' => 'Reemplazar con...',
        'find_all' => 'Todo',
        'replace' => 'Reemplazar',
        'replace_all' => 'Reemplazar Todo',
        'toggle_replace_mode' => 'Alternar modo de reemplazo',
        'toggle_regexp_mode' => 'Búsqueda con expresión regular',
        'toggle_case_sensitive' => 'Búsqueda sensible a mayúsculas',
        'toggle_whole_words' => 'Búsqueda de palabras completas',
        'search_in_selection' => 'Buscar en selección',
        'search_counter_of' => 'de',
        'select_all' => 'Seleccionar Todo',
        'selected_info' => '{count} archivos seleccionados, total {size}',
        'selected_info_none' => '0 elementos seleccionados',
        'batch_delete' => 'Eliminación por Lotes',
        'batch_delete_confirm' => '¿Estás seguro de que quieres eliminar {count} archivos/carpetas seleccionados? ¡Esta acción no se puede deshacer!',
        'batch_delete_no_selection' => '¡Por favor selecciona los archivos a eliminar primero!',
        'chmod_invalid_input' => 'Por favor ingrese un valor de permiso válido (3 o 4 dígitos octales, ej: 644 o 0755).',
        'delete_confirm' => '⚠️ ¿Estás seguro de que quieres eliminar "{name}"? ¡Esta acción no se puede deshacer!',
        'json_format_success' => 'JSON formateado exitosamente',
        'js_format_success' => 'JavaScript formateado exitosamente',
        'unsupported_format' => 'El modo actual no admite formateo',
        'format_error' => 'Error de formateo: {message}',
        'json_syntax_valid' => 'Sintaxis JSON correcta',
        'json_syntax_error' => 'Error de sintaxis JSON: {message}',
        'yaml_syntax_valid' => 'Sintaxis YAML correcta',
        'yaml_syntax_error' => 'Error de sintaxis YAML: {message}',
        'yaml_format_success' => 'YAML formateado exitosamente',
        'yaml_format_error' => 'Error de formateo YAML: {message}',
        'search_empty_input' => 'Por favor ingrese una palabra clave de búsqueda',
        'search_no_results' => 'No se encontraron archivos coincidentes',
        'search_error' => 'Error de búsqueda: {message}',
        'search_filename' => 'Nombre de archivo',
        'search_path' => 'Ruta',
        'search_action' => 'Acción',
        'search_move_to' => 'Mover a',
        'edit_file_title' => 'Editar Archivo: {filename}',
        'fetch_content_error' => 'No se puede obtener el contenido del archivo: {message}',
        'save_file_success' => 'Archivo guardado exitosamente',
        'search.noResults' => 'Sin resultados',
        'search.previousMatch' => 'Coincidencia anterior (Shift+Enter)',
        'search.nextMatch' => 'Siguiente coincidencia (Enter)',
        'search.matchCase' => 'Coincidir mayúsculas y minúsculas (Alt+C)',
        'search.matchWholeWord' => 'Coincidir palabra completa (Alt+W)',
        'search.useRegex' => 'Usar expresión regular (Alt+R)',
        'search.findInSelection' => 'Buscar en selección (Alt+L)',
        'search.close' => 'Cerrar (Escape)',
        'search.toggleReplace' => 'Alternar reemplazo',
        'search.preserveCase' => 'Preservar mayúsculas/minúsculas (Alt+P)',
        'search.replaceAll' => 'Reemplazar todo (Ctrl+Alt+Enter)',
        'search.replace' => 'Reemplazar (Enter)',
        'search.find' => 'Buscar',
        'search.replace' => 'Reemplazar',
        'format_success' => 'Formateo exitoso',
        'format_unsupported' => 'Formateo no admitido',
        'format_error' => 'Error de formateo: {message}',
        'unsupported_format' => 'El modo actual no admite formateo',
        'toggleComment' => 'Alternar comentario',
        'compare' => 'Comparar',
        'enterModifiedContent' => 'Por favor ingrese el contenido modificado para comparar:',
        'closeDiff' => 'Cerrar vista de diferencias',
        "cancelButton" => "Cancelar",
        "saveButton" => "Guardar",
        'toggleFullscreen' => 'Pantalla Completa',
        "lineColumnDisplay" => "Línea: {line}, Columna: {column}",
        "charCountDisplay" => "Caracteres: {charCount}",
        "fileName" => "Nombre de archivo",
        "fileSize" => "Tamaño",
        "fileType" => "Tipo de archivo",
        'formatYaml' => 'Formatear YAML',
        'validateJson' => 'Validar sintaxis JSON',
        'total_items'  => 'Total',
        'items'        => 'elementos',
        'current_path' => 'Ruta actual',
        'disk'         => 'Disco',
        'root'         => 'Directorio raíz',
        'validateYaml' => 'Validar sintaxis YAML'
    ],
    'de' => [
        'select_language'        => 'Sprache auswählen',
        'simplified_chinese'     => 'Vereinfachtes Chinesisch',
        'traditional_chinese'    => 'Traditionelles Chinesisch',
        'english'                => 'Englisch',
        'korean'                 => 'Koreanisch',
        'vietnamese'             => 'Vietnamesisch',
        'thailand'               => 'Thailändisch',
        'japanese'               => 'Japanisch',
        'russian'                => 'Russisch',
        'germany'                => 'Deutsch',
        'france'                 => 'Französisch',
        'arabic'                 => 'Arabisch',
        'spanish'                => 'Spanisch',
        'bangladesh'             => 'Bengalisch',
        'oklch_values'     => 'OKLCH-Werte:',
        'contrast_ratio'   => 'Kontrastverhältnis:',
        'reset'            => 'Zurücksetzen',
        'close'                  => 'Schließen',
        'save'                   => 'Speichern',
        'theme_download'         => 'Theme herunterladen',
        'select_all'             => 'Alle auswählen',
        'batch_delete'           => 'Ausgewählte Dateien stapelweise löschen',
        'total'                  => 'Gesamt:',
        'free'                   => 'Frei:',
        'hover_to_preview'       => 'Klicken Sie, um die Vorschau zu aktivieren',
        'mount_info'             => 'Einhängepunkt: {{mount}}｜Verwendeter Speicherplatz: {{used}}',
        'spectra_config'         => 'Spectra-Konfigurationsverwaltung',
        'current_mode'           => 'Aktueller Modus: Laden...',
        'toggle_mode'            => 'Modus wechseln',
        'check_update'           => 'Nach Updates suchen',
        'batch_upload'           => 'Wählen Sie Dateien zum Stapel-Upload aus',
        'add_to_playlist'        => 'Zur Wiedergabeliste hinzufügen',
        'clear_background'       => 'Hintergrund löschen',
        'clear_background_label' => 'Hintergrund löschen',
        'file_list'              => 'Dateiliste',
        'component_bg_color'     => 'Hintergrundfarbe der Komponente auswählen',
        'page_bg_color'          => 'Hintergrundfarbe der Seite auswählen',
        'toggle_font'            => 'Schriftart wechseln',
        'filename'               => 'Dateiname:',
        'filesize'               => 'Dateigröße:',
        'duration'               => 'Dauer:',
        'resolution'             => 'Auflösung:',
        'bitrate'                => 'Bitrate:',
        'type'                   => 'Typ:',
        'image'                  => 'Bild',
        'video'                  => 'Video',
        'audio'                  => 'Audio',
        'document'               => 'Dokument',
        'delete'                 => 'Löschen',
        'rename'                 => 'Umbenennen',
        'download'               => 'Herunterladen',
        'set_background'         => 'Hintergrund festlegen',
        'preview'                => 'Vorschau',
        'toggle_fullscreen'      => 'Vollbildmodus umschalten',
        'supported_formats'      => 'Unterstützte Formate: [ jpg, jpeg, png, gif, webp, mp4, webm, mkv, mp3, wav, flac ]',
        'drop_files_here'        => 'Dateien hier ablegen',
        'or'                     => 'oder',
        'select_files'           => 'Dateien auswählen',
        'unlock_php_upload_limit'=> 'PHP-Upload-Limit aufheben',
        'upload'                 => 'Hochladen',
        'cancel'                 => 'Abbrechen',
        'rename_file'            => 'Datei umbenennen',
        'new_filename'           => 'Neuer Dateiname',
        'invalid_filename_chars' => 'Dateiname darf folgende Zeichen nicht enthalten: \\/:*?"<>|',
        'confirm'                => 'Bestätigen',
        'media_player'           => 'Mediaplayer',
        'playlist'               => 'Wiedergabeliste',
        'clear_list'             => 'Liste löschen',
        'toggle_list'            => 'Liste ausblenden',
        'picture_in_picture'     => 'Bild-in-Bild',
        'fullscreen'             => 'Vollbild',
        'fetching_version'       => 'Version wird abgerufen...',
        'download_local'         => 'Lokal herunterladen',
        'change_language'        => 'Sprache ändern',
        'hour_announcement'      => 'Stundenansage, es ist jetzt',
        'hour_exact'             => 'Uhr',
        'weekDays' => ['Sonntag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag'],
        'labels' => [
            'year' => 'Jahr',
            'month' => 'Monat',
            'day' => 'Tag',
            'week' => 'Woche'
        ],
        'error_loading_time' => 'Fehler beim Anzeigen der Zeit',
        'switch_to_light_mode' => 'Zum hellen Modus wechseln',
        'switch_to_dark_mode' => 'Zum dunklen Modus wechseln',
        'current_mode_dark' => 'Aktueller Modus: Dunkelmodus',
        'current_mode_light' => 'Aktueller Modus: Hellmodus',
        'fetching_version' => 'Version wird abgerufen...',
        'latest_version' => 'Neueste Version',
        'unable_to_fetch_version' => 'Neueste Version konnte nicht abgerufen werden',
        'request_failed' => 'Anfrage fehlgeschlagen, bitte später erneut versuchen',
        'pip_not_supported' => 'Das aktuelle Medium unterstützt Bild-in-Bild nicht',
        'pip_operation_failed' => 'Bild-in-Bild-Operation fehlgeschlagen',
        'exit_picture_in_picture' => 'Bild-in-Bild beenden',
        'picture_in_picture' => 'Bild-in-Bild',
        'hide_playlist' => 'Wiedergabeliste ausblenden',
        'show_playlist' => 'Wiedergabeliste anzeigen',
        'enter_fullscreen' => 'Vollbildmodus aktivieren',
        'exit_fullscreen' => 'Vollbildmodus beenden',
        'confirm_update_php' => 'Möchten Sie die PHP-Konfiguration wirklich aktualisieren?',
        'select_files_to_delete' => 'Bitte wählen Sie Dateien zum Löschen aus!',
        'confirm_batch_delete' => 'Möchten Sie die ausgewählten %d Dateien wirklich löschen?',
        'clear_confirm' => 'Sind Sie sicher, dass Sie die Konfiguration löschen möchten?', 
        'back_to_first' => 'Zur ersten Wiedergabeliste zurückgekehrt',
        'font_default' => 'Auf runde Schriftart umgestellt',
        'font_fredoka' => 'Auf Standardschriftart umgestellt',
        'font_mono'    => 'Auf lustige Handschrift umgestellt',
        'font_noto'    => 'Auf chinesische Serifenschrift umgestellt',
        'font_dm_serif'     => 'Auf DM Serif Display-Schriftart umgeschaltet',
        'batch_delete_success' => '✅ Stapel-Löschung erfolgreich',
        'batch_delete_failed' => '❌ Stapel-Löschung fehlgeschlagen',
        'confirm_delete' => 'Bist du sicher, dass du löschen möchtest?',
        'unable_to_fetch_current_version' => 'Aktuelle Version wird abgerufen...',
        'current_version' => 'Aktuelle Version',
        'copy_command'     => 'Befehl kopieren',
        'command_copied'   => 'Befehl wurde in die Zwischenablage kopiert!',
        "updateModalLabel" => "Aktualisierungsstatus",
        "updateDescription" => "Der Aktualisierungsprozess wird gleich beginnen.",
        "waitingMessage" => "Warten auf den Beginn der Operation...",
        "update_plugin" => "Plugin aktualisieren",
        "installation_complete" => "Installation abgeschlossen!",
        'confirm_title'         => 'Bestätigen Sie die Aktion',
        'confirm_delete_file'   => 'Möchten Sie die Datei %s wirklich löschen?',
        'delete_success'      => 'Erfolgreich gelöscht: %s',
        'delete_failure'      => 'Löschen fehlgeschlagen: %s',
        'upload_error_type_not_supported' => 'Nicht unterstützter Dateityp: %s',
        'upload_error_move_failed'        => 'Upload fehlgeschlagen: %s',
        'confirm_clear_background' => 'Möchten Sie den Hintergrund wirklich löschen?',
        'background_cleared'      => 'Hintergrund wurde gelöscht!',
        'createShareLink' => 'Freigabelink erstellen',
        'closeButton' => 'Schließen',
        'expireTimeLabel' => 'Ablaufzeit',
        'expire1Hour' => '1 Stunde',
        'expire1Day' => '1 Tag',
        'expire7Days' => '7 Tage',
        'expire30Days' => '30 Tage',
        'maxDownloadsLabel' => 'Maximale Downloads',
        'max1Download' => '1 Mal',
        'max5Downloads' => '5 Mal',
        'max10Downloads' => '10 Mal',
        'maxUnlimited' => 'Unbegrenzt',
        'shareLinkLabel' => 'Freigabelink',
        'copyLinkButton' => 'Link kopieren',
        'closeButtonFooter' => 'Schließen',
        'generateLinkButton' => 'Link erstellen',
        'fileNotSelected' => 'Datei nicht ausgewählt',
        'httpError' => 'HTTP-Fehler',
        'linkGenerated' => '✅ Freigabelink generiert',
        'operationFailed' => '❌ Vorgang fehlgeschlagen',
        'generateLinkFirst' => 'Bitte generieren Sie zuerst den Freigabelink',
        'linkCopied' => '📋 Link kopiert',
        'copyFailed' => '❌ Kopieren fehlgeschlagen',
        'cleanExpiredButton' => 'Abgelaufene löschen',
        'deleteAllButton' => 'Alle löschen',
        'cleanSuccess' => '✅ Reinigung abgeschlossen, %s Elemente wurden entfernt',
        'deleteSuccess' => '✅ Alle Freigabelinks wurden gelöscht, %s Datei(en) wurden entfernt',
        'confirmDeleteAll' => '⚠️ Möchten Sie wirklich ALLE Freigabelinks löschen?',
        'operationFailed' => '❌ Vorgang fehlgeschlagen',
        'ip_info' => 'IP-Informationen',
        'ip_support' => 'IP-Support',
        'ip_address' => 'IP-Adresse',
        'location' => 'Standort',
        'isp' => 'Anbieter',
        'asn' => 'ASN',
        'timezone' => 'Zeitzone',
        'latitude_longitude' => 'Koordinaten',
        'latency_info' => 'Latenzinformationen',
        'current_fit_mode'    => 'Aktueller Modus',
        'fit_contain'    => 'Seitenverhältnis beibehalten',
        'fit_fill'       => 'Ausfüllen',
        'fit_none'       => 'Originalgröße',
        'fit_scale-down' => 'Skalieren falls nötig',
        'fit_cover'      => 'Zuschneiden',
        'advanced_color_settings' => 'Erweiterte Farbeinstellungen',
        'advanced_color_control' => 'Erweiterte Farbsteuerung',
        'color_control' => 'Farbsteuerung',
        'primary_hue' => 'Primärer Farbton',
        'chroma' => 'Sättigung',
        'lightness' => 'Helligkeit',
        'or_use_palette' => 'oder Palette verwenden',
        'reset_to_default' => 'Auf Standard zurücksetzen',
        'preview_and_contrast' => 'Vorschau und Kontrast',
        'color_preview' => 'Farbvorschau',
        'readability_check' => 'Lesbarkeitsprüfung',
        'contrast_between_text_and_bg' => 'Kontrast zwischen Text und Hintergrund:',
        'hue_adjustment' => 'Farbtonanpassung',
        'recent_colors' => 'Kürzlich verwendete Farben',
        'apply' => 'Anwenden',
        'excellent_aaa' => 'Ausgezeichnet (AAA)',
        'good_aa' => 'Gut (AA)',
        'poor_needs_improvement' => 'Schlecht (Verbesserung nötig)',
        'mount_point' => 'Einbindungspunkt:',
        'used_space'  => 'Verwendeter Speicher:',
        'file_summary' => '%d Dateien ausgewählt, insgesamt %s MB',
        'pageTitle' => 'Datei-Assistent',
        'uploadBtn' => 'Datei hochladen',
        'rootDirectory' => 'Stammverzeichnis',
        'permissions' => 'Berechtigungen',
        'actions' => 'Aktionen',
        'directory' => 'Verzeichnis',
        'file' => 'Datei',
        'confirmDelete' => 'Sind Sie sicher, dass Sie {0} löschen möchten? Diese Aktion kann nicht rückgängig gemacht werden.',
        'newName' => 'Neuer Name:',
        'setPermissions' => '🔒 Berechtigungen setzen',
        'modifiedTime' => 'Änderungszeit',
        'owner' => 'Besitzer',
        'create' => 'Erstellen',
        'newFolder' => 'Neuer Ordner',
        'newFile' => 'Neue Datei',
        'folderName' => 'Ordnername:',
        'searchFiles' => 'Dateien durchsuchen',
        'noMatchingFiles' => 'Keine passenden Dateien gefunden.',
        'moveTo' => 'Verschieben nach',
        'confirm' => 'Bestätigen',
        'goBack' => 'Zurück',
        'refreshDirectory' => 'Verzeichnis aktualisieren',
        'filePreview' => 'Dateivorschau',
        'unableToLoadImage' => 'Bild konnte nicht geladen werden:',
        'unableToLoadSVG' => 'SVG-Datei konnte nicht geladen werden:',
        'unableToLoadAudio' => 'Audio konnte nicht geladen werden:',
        'unableToLoadVideo' => 'Video konnte nicht geladen werden:',
        'fileAssistant' => 'Datei-Assistent',
        'errorSavingFile' => 'Fehler: Datei konnte nicht gespeichert werden.',
        'uploadFailed' => 'Upload fehlgeschlagen',
        'fileNotExistOrNotReadable' => 'Datei existiert nicht oder ist nicht lesbar.',
        'inputFileName' => 'Dateiname eingeben',
        'permissionValue' => 'Berechtigungswert (z.B.: 0644)',
        'inputThreeOrFourDigits' => 'Geben Sie 3 oder 4 Ziffern ein, z.B.: 0644 oder 0755',
        'fontSizeL' => 'Schriftgröße',
        'newNameCannotBeEmpty' => 'Neuer Name darf nicht leer sein',
        'fileNameCannotContainChars' => 'Dateiname darf folgende Zeichen nicht enthalten: < > : " / \\ | ? *',
        'folderNameCannotBeEmpty' => 'Ordnername darf nicht leer sein',
        'fileNameCannotBeEmpty' => 'Dateiname darf nicht leer sein',
        'searchError' => 'Fehler bei der Suche: ',
        'encodingChanged' => 'Kodierung wurde auf {0} geändert. Die tatsächliche Konvertierung erfolgt serverseitig beim Speichern.',
        'errorLoadingFileContent' => 'Fehler beim Laden des Dateiinhalts: ',
        'permissionHelp' => 'Bitte geben Sie einen gültigen Berechtigungswert ein (3 oder 4 Oktalziffern, z.B.: 644 oder 0755)',
        'permissionValueCannotExceed' => 'Berechtigungswert darf 0777 nicht überschreiten',
        'goBackTitle' => 'Zurück',
        'rootDirectoryTitle' => 'Zum Stammverzeichnis',
        'homeDirectoryTitle' => 'Zum Home-Verzeichnis',
        'refreshDirectoryTitle' => 'Verzeichnis aktualisieren',
        'selectAll' => 'Alle auswählen',
        'invertSelection' => 'Auswahl umkehren',
        'deleteSelected' => 'Ausgewählte löschen',
        'searchTitle' => 'Suchen',
        'createTitle' => 'Erstellen',
        'uploadTitle' => 'Hochladen',
        'dragHint' => 'Dateien hierher ziehen und ablegen oder klicken, um mehrere Dateien auszuwählen',
        'searchInputPlaceholder' => 'Dateiname eingeben',
        'search_placeholder' => 'Dateiname zum Suchen eingeben...',
        'advancedEdit' => 'Erweiterte Bearbeitung',
        'search' => 'Suchen',
        'format' => 'Formatieren',
        'goToParentDirectoryTitle' => 'Zum übergeordneten Verzeichnis',
        'alreadyAtRootDirectory' => 'Bereits im Stammverzeichnis, kann nicht zurück navigiert werden.',
        'fullscreen' => 'Vollbild',
        'exitFullscreen' => 'Vollbild beenden',
        'search_title' => 'Dateiinhalte durchsuchen',
        'json_format_success' => 'JSON erfolgreich formatiert',
        'js_format_success' => 'JavaScript erfolgreich formatiert',
        'format_not_supported' => 'Aktueller Modus unterstützt keine Formatierung',
        'format_error' => 'Formatierungsfehler: ',
        'json_syntax_valid' => 'JSON-Syntax korrekt',
        'json_syntax_error' => 'JSON-Syntaxfehler: ',
        'yaml_syntax_valid' => 'YAML-Syntax korrekt',
        'yaml_syntax_error' => 'YAML-Syntaxfehler: ',
        'yaml_format_success' => 'YAML erfolgreich formatiert',
        'yaml_format_error' => 'YAML-Formatierungsfehler: ',
        'search_placeholder' => 'Suchen...',
        'replace_placeholder' => 'Ersetzen durch...',
        'find_all' => 'Alle',
        'replace' => 'Ersetzen',
        'replace_all' => 'Alle ersetzen',
        'toggle_replace_mode' => 'Ersetzungsmodus umschalten',
        'toggle_regexp_mode' => 'Regulärer Ausdruck Suche',
        'toggle_case_sensitive' => 'Groß-/Kleinschreibung beachten',
        'toggle_whole_words' => 'Ganze Wörter suchen',
        'search_in_selection' => 'In Auswahl suchen',
        'search_counter_of' => 'von',
        'select_all' => 'Alle auswählen',
        'selected_info' => '{count} Dateien ausgewählt, insgesamt {size}',
        'selected_info_none' => '0 Elemente ausgewählt',
        'batch_delete' => 'Stapellöschung',
        'batch_delete_confirm' => 'Sind Sie sicher, dass Sie {count} ausgewählte Dateien/Ordner löschen möchten? Diese Aktion kann nicht rückgängig gemacht werden!',
        'batch_delete_no_selection' => 'Bitte wählen Sie zuerst die zu löschenden Dateien aus!',
        'chmod_invalid_input' => 'Bitte geben Sie einen gültigen Berechtigungswert ein (3 oder 4 Oktalziffern, z.B.: 644 oder 0755).',
        'delete_confirm' => '⚠️ Sind Sie sicher, dass Sie "{name}" löschen möchten? Diese Aktion kann nicht rückgängig gemacht werden!',
        'json_format_success' => 'JSON erfolgreich formatiert',
        'js_format_success' => 'JavaScript erfolgreich formatiert',
        'unsupported_format' => 'Aktueller Modus unterstützt keine Formatierung',
        'format_error' => 'Formatierungsfehler: {message}',
        'json_syntax_valid' => 'JSON-Syntax korrekt',
        'json_syntax_error' => 'JSON-Syntaxfehler: {message}',
        'yaml_syntax_valid' => 'YAML-Syntax korrekt',
        'yaml_syntax_error' => 'YAML-Syntaxfehler: {message}',
        'yaml_format_success' => 'YAML erfolgreich formatiert',
        'yaml_format_error' => 'YAML-Formatierungsfehler: {message}',
        'search_empty_input' => 'Bitte geben Sie einen Suchbegriff ein',
        'search_no_results' => 'Keine passenden Dateien gefunden',
        'search_error' => 'Suchfehler: {message}',
        'search_filename' => 'Dateiname',
        'search_path' => 'Pfad',
        'search_action' => 'Aktion',
        'search_move_to' => 'Verschieben nach',
        'edit_file_title' => 'Datei bearbeiten: {filename}',
        'fetch_content_error' => 'Dateiinhalt konnte nicht abgerufen werden: {message}',
        'save_file_success' => 'Datei erfolgreich gespeichert',
        'search.noResults' => 'Keine Ergebnisse',
        'search.previousMatch' => 'Vorherige Übereinstimmung (Shift+Enter)',
        'search.nextMatch' => 'Nächste Übereinstimmung (Enter)',
        'search.matchCase' => 'Groß-/Kleinschreibung beachten (Alt+C)',
        'search.matchWholeWord' => 'Ganze Wörter (Alt+W)',
        'search.useRegex' => 'Reguläre Ausdrücke verwenden (Alt+R)',
        'search.findInSelection' => 'In Auswahl suchen (Alt+L)',
        'search.close' => 'Schließen (Escape)',
        'search.toggleReplace' => 'Ersetzen umschalten',
        'search.preserveCase' => 'Groß-/Kleinschreibung beibehalten (Alt+P)',
        'search.replaceAll' => 'Alle ersetzen (Ctrl+Alt+Enter)',
        'search.replace' => 'Ersetzen (Enter)',
        'search.find' => 'Suchen',
        'search.replace' => 'Ersetzen',
        'format_success' => 'Formatierung erfolgreich',
        'format_unsupported' => 'Formatierung nicht unterstützt',
        'format_error' => 'Formatierungsfehler: {message}',
        'unsupported_format' => 'Aktueller Modus unterstützt keine Formatierung',
        'toggleComment' => 'Kommentar umschalten',
        'compare' => 'Vergleichen',
        'enterModifiedContent' => 'Bitte geben Sie den geänderten Inhalt zum Vergleich ein:',
        'closeDiff' => 'Differenzansicht schließen',
        "cancelButton" => "Abbrechen",
        "saveButton" => "Speichern",
        'toggleFullscreen' => 'Vollbild',
        "lineColumnDisplay" => "Zeile: {line}, Spalte: {column}",
        "charCountDisplay" => "Zeichen: {charCount}",
        "fileName" => "Dateiname",
        "fileSize" => "Größe",
        "fileType" => "Dateityp",
        'formatYaml' => 'YAML formatieren',
        'validateJson' => 'JSON-Syntax validieren',
         'total_items'  => 'Gesamt',
        'items'        => 'Elemente',
        'current_path' => 'Aktueller Pfad',
        'disk'         => 'Festplatte',
        'root'         => 'Stammverzeichnis',
        'validateYaml' => 'YAML-Syntax validieren'
    ],

    'fr' => [
        'select_language'        => 'Choisir la langue',
        'simplified_chinese'     => 'Chinois simplifié',
        'traditional_chinese'    => 'Chinois traditionnel',
        'english'                => 'Anglais',
        'korean'                 => 'Coréen',
        'vietnamese'             => 'Vietnamien',
        'thailand'               => 'Thaï',
        'japanese'               => 'Japonais',
        'russian'                => 'Russe',
        'germany'                => 'Allemand',
        'france'                 => 'Français',
        'arabic'                 => 'Arabe',
        'spanish'                => 'Espagnol',
        'bangladesh'             => 'Bengali',
        'oklch_values'     => 'Valeurs OKLCH :',
        'contrast_ratio'   => 'Taux de contraste :',
        'reset'            => 'Réinitialiser',
        'close'                  => 'Fermer',
        'save'                   => 'Enregistrer',
        'theme_download'         => 'Télécharger le thème',
        'select_all'             => 'Tout sélectionner',
        'batch_delete'           => 'Supprimer les fichiers sélectionnés par lot',
        'total'                  => 'Total :',
        'free'                   => 'Libre :',
        'hover_to_preview'       => 'Cliquez pour activer l\'aperçu',
        'mount_info'             => 'Point de montage : {{mount}}｜Espace utilisé : {{used}}',
        'spectra_config'         => 'Gestion des configurations Spectra',
        'current_mode'           => 'Mode actuel : Chargement...',
        'toggle_mode'            => 'Changer de mode',
        'check_update'           => 'Vérifier les mises à jour',
        'batch_upload'           => 'Sélectionner des fichiers pour un téléversement par lot',
        'add_to_playlist'        => 'Ajouter à la liste de lecture',
        'clear_background'       => 'Effacer l\'arrière-plan',
        'clear_background_label' => 'Effacer l\'arrière-plan',
        'file_list'              => 'Liste des fichiers',
        'component_bg_color'     => 'Choisir la couleur d\'arrière-plan du composant',
        'page_bg_color'          => 'Choisir la couleur d\'arrière-plan de la page',
        'toggle_font'            => 'Changer de police',
        'filename'               => 'Nom :',
        'filesize'               => 'Taille :',
        'duration'               => 'Durée :',
        'resolution'             => 'Résolution :',
        'bitrate'                => 'Débit :',
        'type'                   => 'Type :',
        'image'                  => 'Image',
        'video'                  => 'Vidéo',
        'audio'                  => 'Audio',
        'document'               => 'Document',
        'delete'                 => 'Supprimer',
        'rename'                 => 'Renommer',
        'download'               => 'Télécharger',
        'set_background'         => 'Définir comme arrière-plan',
        'preview'                => 'Aperçu',
        'toggle_fullscreen'      => 'Activer/désactiver le mode plein écran',
        'supported_formats'      => 'Formats pris en charge : [ jpg, jpeg, png, gif, webp, mp4, webm, mkv, mp3, wav, flac ]',
        'drop_files_here'        => 'Déposez les fichiers ici',
        'or'                     => 'ou',
        'select_files'           => 'Sélectionner les fichiers',
        'unlock_php_upload_limit'=> 'Déverrouiller la limite de téléversement PHP',
        'upload'                 => 'Téléverser',
        'cancel'                 => 'Annuler',
        'rename_file'            => 'Renommer le fichier',
        'new_filename'           => 'Nouveau nom du fichier',
        'invalid_filename_chars' => 'Le nom du fichier ne peut pas contenir les caractères suivants : \\/:*?"<>|',
        'confirm'                => 'Confirmer',
        'media_player'           => 'Lecteur multimédia',
        'playlist'               => 'Liste de lecture',
        'clear_list'             => 'Effacer la liste',
        'toggle_list'            => 'Masquer la liste',
        'picture_in_picture'     => 'Image dans l\'image',
        'fullscreen'             => 'Plein écran',
        'fetching_version'       => 'Récupération des informations de version...',
        'download_local'         => 'Télécharger localement',
        'change_language'        => 'Changer de langue',
        'hour_announcement'      => 'Annonce de l\'heure, il est actuellement',
        'hour_exact'             => 'heure(s) pile',
        'weekDays' => ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'],
        'labels' => [
            'year' => 'Année',
            'month' => 'Mois',
            'day' => 'Jour',
            'week' => 'Semaine'
        ],
        'error_loading_time' => 'Erreur lors de l\'affichage de l\'heure',
        'switch_to_light_mode' => 'Passer au mode clair',
        'switch_to_dark_mode' => 'Passer au mode sombre',
        'current_mode_dark' => 'Mode actuel : Mode sombre',
        'current_mode_light' => 'Mode actuel : Mode clair',
        'fetching_version' => 'Récupération des informations de version...',
        'latest_version' => 'Dernière version',
        'unable_to_fetch_version' => 'Impossible de récupérer la dernière version',
        'request_failed' => 'La requête a échoué, veuillez réessayer plus tard',
        'pip_not_supported' => 'Le média actuel ne prend pas en charge l\'image dans l\'image',
        'pip_operation_failed' => 'Échec de l\'opération image dans l\'image',
        'exit_picture_in_picture' => 'Quitter le mode image dans l\'image',
        'picture_in_picture' => 'Image dans l\'image',
        'hide_playlist' => 'Masquer la liste de lecture',
        'show_playlist' => 'Afficher la liste de lecture',
        'enter_fullscreen' => 'Activer le mode plein écran',
        'exit_fullscreen' => 'Quitter le mode plein écran',
        'confirm_update_php' => 'Êtes-vous sûr de vouloir mettre à jour la configuration PHP ?',
        'select_files_to_delete' => 'Veuillez d\'abord sélectionner les fichiers à supprimer !',
        'confirm_batch_delete' => 'Êtes-vous sûr de vouloir supprimer les %d fichiers sélectionnés ?',
        'clear_confirm' => 'Êtes-vous sûr de vouloir effacer la configuration ?',
        'back_to_first' => 'Retour à la première chanson de la liste de lecture',
        'font_default' => 'Police arrondie activée',
        'font_fredoka' => 'Police par défaut activée',
        'font_mono'    => 'Police manuscrite activée',
        'font_noto'    => 'Police avec empattement chinoise activée',
        'font_dm_serif'     => 'Passé à la police DM Serif Display',
        'batch_delete_success' => '✅ Suppression par lot réussie',
        'batch_delete_failed' => '❌ Échec de la suppression par lot',
        'confirm_delete' => 'Êtes-vous sûr de vouloir supprimer?',
        'unable_to_fetch_current_version' => 'Récupération de la version actuelle...',
        'current_version' => 'Version actuelle',
        'copy_command'     => 'Copier la commande',
        'command_copied'   => 'Commande copiée dans le presse-papiers !',
        "updateModalLabel" => "Statut de la mise à jour",
        "updateDescription" => "Le processus de mise à jour va bientôt commencer.",
        "waitingMessage" => "En attente du début de l'opération...",
        "update_plugin" => "Mettre à jour le plugin",
        "installation_complete" => "Installation terminée !",
        'confirm_title'         => 'Confirmer l\'action',
       'confirm_delete_file'   => 'Êtes-vous sûr de vouloir supprimer le fichier %s ?',
        'delete_success'      => 'Suppression réussie : %s',
        'delete_failure'      => 'Échec de la suppression : %s',
        'upload_error_type_not_supported' => 'Type de fichier non pris en charge : %s',
        'upload_error_move_failed'        => 'Échec du téléchargement : %s',
        'confirm_clear_background' => 'Voulez-vous vraiment effacer l\'arrière-plan?',
        'background_cleared'      => 'Arrière-plan effacé!',
        'createShareLink' => 'Créer un lien de partage',
        'closeButton' => 'Fermer',
        'expireTimeLabel' => 'Temps d\'expiration',
        'expire1Hour' => '1 Heure',
        'expire1Day' => '1 Jour',
        'expire7Days' => '7 Jours',
        'expire30Days' => '30 Jours',
        'maxDownloadsLabel' => 'Téléchargements maximum',
        'max1Download' => '1 fois',
        'max5Downloads' => '5 fois',
        'max10Downloads' => '10 fois',
        'maxUnlimited' => 'Illimité',
        'shareLinkLabel' => 'Lien de partage',
        'copyLinkButton' => 'Copier le lien',
        'closeButtonFooter' => 'Fermer',
        'generateLinkButton' => 'Générer le lien',
        'fileNotSelected' => 'Fichier non sélectionné',
        'httpError' => 'Erreur HTTP',
        'linkGenerated' => '✅ Lien de partage généré',
        'operationFailed' => '❌ Échec de l\'opération',
        'generateLinkFirst' => 'Veuillez d\'abord générer le lien de partage',
        'linkCopied' => '📋 Lien copié',
        'copyFailed' => '❌ Échec de la copie',
        'cleanExpiredButton' => 'Nettoyer expirés',
        'deleteAllButton' => 'Supprimer tout',
        'cleanSuccess' => '✅ Nettoyage terminé, %s élément(s) expiré(s) supprimé(s)',
        'deleteSuccess' => '✅ Tous les liens partagés ont été supprimés, %s fichier(s) supprimé(s)',
        'confirmDeleteAll' => '⚠️ Voulez-vous vraiment supprimer TOUS les enregistrements de partage ?',
        'operationFailed' => '❌ Échec de l\'opération',
        'ip_info' => 'Informations IP',
        'ip_support' => 'Support IP',
        'ip_address' => 'Adresse IP',
        'location' => 'Localisation',
        'isp' => 'Fournisseur',
        'asn' => 'ASN',
        'timezone' => 'Fuseau horaire',
        'latitude_longitude' => 'Coordonnées',
        'latency_info' => 'Informations de latence',
        'current_fit_mode'    => 'Mode actuel',
        'fit_contain'    => 'Proportions normales',
        'fit_fill'       => 'Remplir',
        'fit_none'       => 'Taille d’origine',
        'fit_scale-down' => 'Réduction automatique',
        'fit_cover'      => 'Rogner',
        'advanced_color_settings' => 'Paramètres avancés des couleurs',
        'advanced_color_control' => 'Contrôle avancé des couleurs',
        'color_control' => 'Contrôle des couleurs',
        'primary_hue' => 'Teinte principale',
        'chroma' => 'Chroma',
        'lightness' => 'Luminosité',
        'or_use_palette' => 'ou utiliser une palette ',
        'reset_to_default' => 'Réinitialiser par défaut',
        'preview_and_contrast' => 'Aperçu et contraste',
        'color_preview' => 'Aperçu des couleurs',
        'readability_check' => 'Vérification de lisibilité',
        'contrast_between_text_and_bg' => 'Contraste entre texte et fond :',
        'hue_adjustment' => 'Ajustement de la teinte',
        'recent_colors' => 'Couleurs récentes ',
        'apply' => 'Appliquer',
        'excellent_aaa' => 'Excellent (AAA)',
        'good_aa' => 'Bon (AA)',
        'poor_needs_improvement' => 'Insuffisant (À améliorer)',
        'mount_point' => 'Point de montage :',
        'used_space'  => 'Espace utilisé :',
        'file_summary' => '%d fichiers sélectionnés, total de %s Mo',
        'pageTitle' => 'Assistant de Fichiers',
        'uploadBtn' => 'Téléverser un Fichier',
        'rootDirectory' => 'Répertoire Racine',
        'permissions' => 'Permissions',
        'actions' => 'Actions',
        'directory' => 'Répertoire',
        'file' => 'Fichier',
        'confirmDelete' => 'Êtes-vous sûr de vouloir supprimer {0} ? Cette action est irréversible.',
        'newName' => 'Nouveau nom :',
        'setPermissions' => '🔒 Définir les Permissions',
        'modifiedTime' => 'Heure de Modification',
        'owner' => 'Propriétaire',
        'create' => 'Créer',
        'newFolder' => 'Nouveau Dossier',
        'newFile' => 'Nouveau Fichier',
        'folderName' => 'Nom du dossier :',
        'searchFiles' => 'Rechercher des Fichiers',
        'noMatchingFiles' => 'Aucun fichier correspondant trouvé.',
        'moveTo' => 'Déplacer vers',
        'confirm' => 'Confirmer',
        'goBack' => 'Retour',
        'refreshDirectory' => 'Actualiser le Répertoire',
        'filePreview' => 'Aperçu du Fichier',
        'unableToLoadImage' => 'Impossible de charger l\'image :',
        'unableToLoadSVG' => 'Impossible de charger le fichier SVG :',
        'unableToLoadAudio' => 'Impossible de charger l\'audio :',
        'unableToLoadVideo' => 'Impossible de charger la vidéo :',
        'fileAssistant' => 'Assistant de Fichiers',
        'errorSavingFile' => 'Erreur : Impossible de sauvegarder le fichier.',
        'uploadFailed' => 'Échec du Téléversement',
        'fileNotExistOrNotReadable' => 'Le fichier n\'existe pas ou n\'est pas lisible.',
        'inputFileName' => 'Entrer le nom du fichier',
        'permissionValue' => 'Valeur des permissions (ex : 0644)',
        'inputThreeOrFourDigits' => 'Entrez 3 ou 4 chiffres, ex : 0644 ou 0755',
        'fontSizeL' => 'Taille de Police',
        'newNameCannotBeEmpty' => 'Le nouveau nom ne peut pas être vide',
        'fileNameCannotContainChars' => 'Le nom du fichier ne peut pas contenir les caractères suivants : < > : " / \\ | ? *',
        'folderNameCannotBeEmpty' => 'Le nom du dossier ne peut pas être vide',
        'fileNameCannotBeEmpty' => 'Le nom du fichier ne peut pas être vide',
        'searchError' => 'Erreur lors de la recherche : ',
        'encodingChanged' => 'Encodage changé en {0}. La conversion réelle sera effectuée côté serveur lors de la sauvegarde.',
        'errorLoadingFileContent' => 'Erreur lors du chargement du contenu du fichier : ',
        'permissionHelp' => 'Veuillez entrer une valeur de permission valide (3 ou 4 chiffres octaux, ex : 644 ou 0755)',
        'permissionValueCannotExceed' => 'La valeur des permissions ne peut pas dépasser 0777',
        'goBackTitle' => 'Retour',
        'rootDirectoryTitle' => 'Retour au Répertoire Racine',
        'homeDirectoryTitle' => 'Retour au Répertoire Principal',
        'refreshDirectoryTitle' => 'Actualiser le Répertoire',
        'selectAll' => 'Tout Sélectionner',
        'invertSelection' => 'Inverser la Sélection',
        'deleteSelected' => 'Supprimer la Sélection',
        'searchTitle' => 'Rechercher',
        'createTitle' => 'Créer',
        'uploadTitle' => 'Téléverser',
        'dragHint' => 'Glissez-déposez les fichiers ici ou cliquez pour sélectionner plusieurs fichiers',
        'searchInputPlaceholder' => 'Entrer le nom du fichier',
        'search_placeholder' => 'Entrez le nom du fichier à rechercher...',
        'advancedEdit' => 'Édition Avancée',
        'search' => 'Rechercher',
        'format' => 'Formatter',
        'goToParentDirectoryTitle' => 'Retour au Répertoire Parent',
        'alreadyAtRootDirectory' => 'Déjà dans le répertoire racine, impossible de remonter.',
        'fullscreen' => 'Plein Écran',
        'exitFullscreen' => 'Quitter le Plein Écran',
        'search_title' => 'Rechercher dans le Contenu des Fichiers',
        'json_format_success' => 'JSON formaté avec succès',
        'js_format_success' => 'JavaScript formaté avec succès',
        'format_not_supported' => 'Le mode actuel ne supporte pas le formatage',
        'format_error' => 'Erreur de formatage : ',
        'json_syntax_valid' => 'Syntaxe JSON correcte',
        'json_syntax_error' => 'Erreur de syntaxe JSON : ',
        'yaml_syntax_valid' => 'Syntaxe YAML correcte',
        'yaml_syntax_error' => 'Erreur de syntaxe YAML : ',
        'yaml_format_success' => 'YAML formaté avec succès',
        'yaml_format_error' => 'Erreur de formatage YAML : ',
        'search_placeholder' => 'Rechercher...',
        'replace_placeholder' => 'Remplacer par...',
        'find_all' => 'Tout',
        'replace' => 'Remplacer',
        'replace_all' => 'Tout Remplacer',
        'toggle_replace_mode' => 'Basculer le mode de remplacement',
        'toggle_regexp_mode' => 'Recherche par expression régulière',
        'toggle_case_sensitive' => 'Recherche sensible à la casse',
        'toggle_whole_words' => 'Recherche de mots entiers',
        'search_in_selection' => 'Rechercher dans la sélection',
        'search_counter_of' => 'sur',
        'select_all' => 'Tout Sélectionner',
        'selected_info' => '{count} fichiers sélectionnés, total {size}',
        'selected_info_none' => '0 élément sélectionné',
        'batch_delete' => 'Suppression par Lot',
        'batch_delete_confirm' => 'Êtes-vous sûr de vouloir supprimer {count} fichiers/dossiers sélectionnés ? Cette action est irréversible !',
        'batch_delete_no_selection' => 'Veuillez d\'abord sélectionner les fichiers à supprimer !',
        'chmod_invalid_input' => 'Veuillez entrer une valeur de permission valide (3 ou 4 chiffres octaux, ex : 644 ou 0755).',
        'delete_confirm' => '⚠️ Êtes-vous sûr de vouloir supprimer "{name}" ? Cette action est irréversible !',
        'json_format_success' => 'JSON formaté avec succès',
        'js_format_success' => 'JavaScript formaté avec succès',
        'unsupported_format' => 'Le mode actuel ne supporte pas le formatage',
        'format_error' => 'Erreur de formatage : {message}',
        'json_syntax_valid' => 'Syntaxe JSON correcte',
        'json_syntax_error' => 'Erreur de syntaxe JSON : {message}',
        'yaml_syntax_valid' => 'Syntaxe YAML correcte',
        'yaml_syntax_error' => 'Erreur de syntaxe YAML : {message}',
        'yaml_format_success' => 'YAML formaté avec succès',
        'yaml_format_error' => 'Erreur de formatage YAML : {message}',
        'search_empty_input' => 'Veuillez entrer un mot-clé de recherche',
        'search_no_results' => 'Aucun fichier correspondant trouvé',
        'search_error' => 'Erreur de recherche : {message}',
        'search_filename' => 'Nom du fichier',
        'search_path' => 'Chemin',
        'search_action' => 'Action',
        'search_move_to' => 'Déplacer vers',
        'edit_file_title' => 'Éditer le Fichier : {filename}',
        'fetch_content_error' => 'Impossible de récupérer le contenu du fichier : {message}',
        'save_file_success' => 'Fichier sauvegardé avec succès',
        'search.noResults' => 'Aucun résultat',
        'search.previousMatch' => 'Correspondance précédente (Shift+Entrée)',
        'search.nextMatch' => 'Correspondance suivante (Entrée)',
        'search.matchCase' => 'Respecter la casse (Alt+C)',
        'search.matchWholeWord' => 'Mots entiers (Alt+W)',
        'search.useRegex' => 'Utiliser les expressions régulières (Alt+R)',
        'search.findInSelection' => 'Rechercher dans la sélection (Alt+L)',
        'search.close' => 'Fermer (Échap)',
        'search.toggleReplace' => 'Basculer le remplacement',
        'search.preserveCase' => 'Préserver la casse (Alt+P)',
        'search.replaceAll' => 'Tout Remplacer (Ctrl+Alt+Entrée)',
        'search.replace' => 'Remplacer (Entrée)',
        'search.find' => 'Rechercher',
        'search.replace' => 'Remplacer',
        'format_success' => 'Formatage réussi',
        'format_unsupported' => 'Formatage non supporté',
        'format_error' => 'Erreur de formatage : {message}',
        'unsupported_format' => 'Le mode actuel ne supporte pas le formatage',
        'toggleComment' => 'Basculer le commentaire',
        'compare' => 'Comparer',
        'enterModifiedContent' => 'Veuillez entrer le contenu modifié pour comparaison :',
        'closeDiff' => 'Fermer la vue des différences',
        "cancelButton" => "Annuler",
        "saveButton" => "Sauvegarder",
        'toggleFullscreen' => 'Plein Écran',
        "lineColumnDisplay" => "Ligne : {line}, Colonne : {column}",
        "charCountDisplay" => "Caractères : {charCount}",
        "fileName" => "Nom du fichier",
        "fileSize" => "Taille",
        "fileType" => "Type de fichier",
        'formatYaml' => 'Formatter YAML',
        'validateJson' => 'Valider la syntaxe JSON',
        'total_items'  => 'Total',
        'items'        => 'éléments',
        'current_path' => 'Chemin actuel',
        'disk'         => 'Disque',
        'root'         => 'Répertoire racine',
        'validateYaml' => 'Valider la syntaxe YAML'
    ],
    'en' => [
        'select_language'        => 'Select Language',
        'simplified_chinese'     => 'Simplified Chinese',
        'traditional_chinese'    => 'Traditional Chinese',
        'english'                => 'English',
        'korean'                 => 'Korean',
        'vietnamese'             => 'Vietnamese',
        'thailand'               => 'Thai',
        'japanese'               => 'Japanese',
        'russian'                => 'Russian',
        'germany'                => 'German',
        'france'                 => 'French',
        'arabic'                 => 'Arabic',
        'spanish'                => 'Spanish',
        'bangladesh'             => 'Bengali',
        'oklch_values'     => 'OKLCH Values:',
        'contrast_ratio'   => 'Contrast Ratio:',
        'reset'            => 'Reset',
        'close'                  => 'Close',
        'save'                   => 'Save',
        'theme_download'         => 'Theme Download',
        'select_all'             => 'Select All',
        'batch_delete'           => 'Delete Selected Files',
        'spectra_config'         => 'Spectra Configuration',
        'total'                  => 'Total:',
        'free'                   => 'Free:',
        'hover_to_preview'       => 'Click to activate hover preview',
        'mount_info'             => 'Mount point: {{mount}}｜Used: {{used}}',
        'current_mode'           => 'Current Mode: Loading...',
        'toggle_mode'            => 'Toggle Mode',
        'check_update'           => 'Check for Updates',
        'batch_upload'           => 'Select Files for Batch Upload',
        'add_to_playlist'        => 'Add Selected to Playlist',
        'clear_background'       => 'Clear Background',
        'clear_background_label' => 'Clear Background',
        'file_list'              => 'File List',
        'component_bg_color'     => 'Select Component Background Color',
        'page_bg_color'          => 'Select Page Background Color',
        'toggle_font'            => 'Toggle Font',
        'filename'               => 'Name:',
        'filesize'               => 'Size:',
        'duration'               => 'Duration:',
        'resolution'             => 'Resolution:',
        'bitrate'                => 'Bitrate:',
        'type'                   => 'Type:',
        'image'                  => 'Image',
        'video'                  => 'Video',
        'audio'                  => 'Audio',
        'document'               => 'Document',
        'delete'                 => 'Delete',
        'rename'                 => 'Rename',
        'download'               => 'Download',
        'set_background'         => 'Set Background',
        'preview'                => 'Preview',
        'toggle_fullscreen'      => 'Toggle Fullscreen',
        'supported_formats'      => 'Supported formats: [ jpg, jpeg, png, gif, webp, mp4, webm, mkv, mp3, wav, flac ]',
        'drop_files_here'        => 'Drop files here',
        'or'                     => 'or',
        'select_files'           => 'Select Files',
        'unlock_php_upload_limit'=> 'Unlock PHP Upload Limit',
        'upload'                 => 'Upload',
        'cancel'                 => 'Cancel',
        'rename_file'            => 'Rename',
        'new_filename'           => 'New Filename',
        'invalid_filename_chars' => 'Filename cannot contain the following characters: \\/:*?"<>|',
        'confirm'                => 'Confirm',
        'media_player'           => 'Media Player',
        'playlist'               => 'Playlist',
        'clear_list'             => 'Clear List',
        'toggle_list'            => 'Toggle List',
        'picture_in_picture'     => 'Picture-in-Picture',
        'fullscreen'             => 'Fullscreen',
        'fetching_version'       => 'Fetching version info...',
        'download_local'         => 'Download Locally',
        'change_language'        => 'Change Language',
        'hour_announcement_en'   => "It's",  
        'hour_exact_en'          => "o'clock",
        'weekDays' => ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
        'labels' => [
            'year' => '',
            'month' => '',
            'day' => '',
            'week' => ''
        ],
        'zodiacs' => ['Monkey','Rooster','Dog','Pig','Rat','Ox','Tiger','Rabbit','Dragon','Snake','Horse','Goat'],
        'heavenlyStems' => ['Jia','Yi','Bing','Ding','Wu','Ji','Geng','Xin','Ren','Gui'],
        'earthlyBranches' => ['Zi','Chou','Yin','Mao','Chen','Si','Wu','Wei','Shen','You','Xu','Hai'],
        'months' => ['1st','2nd','3rd','4th','5th','6th','7th','8th','9th','10th','11th','12th'],
        'days' => ['1st','2nd','3rd','4th','5th','6th','7th','8th','9th','10th',
                   '11th','12th','13th','14th','15th','16th','17th','18th','19th','20th',
                   '21st','22nd','23rd','24th','25th','26th','27th','28th','29th','30th'],
        'leap_prefix' => 'Leap ',
        'year_suffix' => ' Year',
        'month_suffix' => ' Month',
        'day_suffix' => '',
        'periods' => ['Zi', 'Chou', 'Yin', 'Mao', 'Chen', 'Si', 'Wu', 'Wei', 'Shen', 'You', 'Xu', 'Hai'],
        'default_period' => ' Time',
        'error_loading_time' => 'Error loading time',
        'switch_to_light_mode' => 'Switch to Light Mode',
        'switch_to_dark_mode' => 'Switch to Dark Mode',
        'current_mode_dark' => 'Current Mode: Dark Mode',
        'current_mode_light' => 'Current Mode: Light Mode',
        'fetching_version' => 'Fetching version info...',
        'latest_version' => 'Latest Version',
        'unable_to_fetch_version' => 'Unable to fetch the latest version info',
        'request_failed' => 'Request failed, please try again later',
        'pip_not_supported' => 'Current media does not support Picture-in-Picture',
        'pip_operation_failed' => 'Picture-in-Picture operation failed',
        'exit_picture_in_picture' => 'Exit Picture-in-Picture',
        'picture_in_picture' => 'Picture-in-Picture',
        'hide_playlist' => 'Hide Playlist',
        'show_playlist' => 'Show Playlist',
        'enter_fullscreen' => 'Enter Fullscreen',
        'exit_fullscreen' => 'Exit Fullscreen',
        'confirm_update_php' => 'Are you sure you want to update PHP configuration?',
        'select_files_to_delete' => 'Please select files to delete first!',
        'confirm_batch_delete' => 'Are you sure you want to delete the selected %d files?',
        'clear_confirm' => 'Are you sure you want to clear the config?',
        'back_to_first' => 'Returned to the first song in the playlist',
        'font_default' => 'Switched to rounded font',
        'font_fredoka' => 'Switched to default font',
        'font_mono'    => 'Switched to fun handwriting font',
        'font_noto'    => 'Switched to Chinese serif font',
        'font_dm_serif'     => 'Switched to DM Serif Display font',
        'batch_delete_success' => '✅ Batch delete successful',
        'batch_delete_failed' => '❌ Batch delete failed',
        'confirm_delete' => 'Are you sure you want to delete?',
        'unable_to_fetch_current_version' => 'Fetching current version...',
        'current_version' => 'Current Version',
        'copy_command'     => 'Copy Command',
        'command_copied'   => 'Command copied to clipboard!',
        "updateModalLabel" => "Update Status",
        "updateDescription" => "The update process is about to begin.",
        "waitingMessage" => "Waiting for the operation to start...",
        "update_plugin" => "Update Plugin",
        "installation_complete" => "Installation complete!",
        'confirm_title'         => 'Confirm Action',
        'confirm_delete_file'   => 'Are you sure you want to delete file %s?',
        'delete_success'      => 'Deleted successfully: %s',
        'delete_failure'      => 'Failed to delete: %s',
        'upload_error_type_not_supported' => 'Unsupported file type: %s',
        'upload_error_move_failed'        => 'Upload failed: %s',
        'confirm_clear_background' => 'Are you sure you want to clear the background?',
        'background_cleared'      => 'Background cleared!',
        'createShareLink' => 'Create Share Link',
        'closeButton' => 'Close',
        'expireTimeLabel' => 'Expiration Time',
        'expire1Hour' => '1 Hour',
        'expire1Day' => '1 Day',
        'expire7Days' => '7 Days',
        'expire30Days' => '30 Days',
        'maxDownloadsLabel' => 'Max Downloads',
        'max1Download' => '1 Time',
        'max5Downloads' => '5 Times',
        'max10Downloads' => '10 Times',
        'maxUnlimited' => 'Unlimited',
        'shareLinkLabel' => 'Share Link',
        'copyLinkButton' => 'Copy Link',
        'closeButtonFooter' => 'Close',
        'generateLinkButton' => 'Generate Link',
        'fileNotSelected' => 'File not selected',
        'httpError' => 'HTTP Error',
        'linkGenerated' => '✅ Share link generated',
        'operationFailed' => '❌ Operation failed',
        'generateLinkFirst' => 'Please generate the share link first',
        'linkCopied' => '📋 Link copied',
        'copyFailed' => '❌ Copy failed',
        'cleanExpiredButton' => 'Clean Expired',
        'deleteAllButton' => 'Delete All',
        'cleanSuccess' => '✅ Clean completed, %s expired item(s) removed',
        'deleteSuccess' => '✅ All share records deleted, %s file(s) removed',
        'confirmDeleteAll' => '⚠️ Are you sure you want to delete ALL share records?',
        'operationFailed' => '❌ Operation failed',
        'ip_info' => 'IP Details',
        'ip_support' => 'IP Support',
        'ip_address' => 'IP Address',
        'location' => 'Location',
        'isp' => 'ISP',
        'asn' => 'ASN',
        'timezone' => 'Timezone',
        'latitude_longitude' => 'Coordinates',
        'latency_info' => 'Latency Info',
        'current_fit_mode'    => 'Current mode',
        'fit_contain'    => 'Contain',
        'fit_fill'       => 'Fill',
        'fit_none'       => 'Original size',
        'fit_scale-down' => 'Scale down',
        'fit_cover'      => 'Cover',
        'advanced_color_settings' => 'Advanced Color Settings',
        'advanced_color_control' => 'Advanced Color Control',
        'color_control' => 'Color Control',
        'primary_hue' => 'Primary Hue',
        'chroma' => 'Chroma',
        'lightness' => 'Lightness',
        'or_use_palette' => 'or use palette',
        'reset_to_default' => 'Reset to Default',
        'preview_and_contrast' => 'Preview and Contrast',
        'color_preview' => 'Color Preview',
        'readability_check' => 'Readability Check',
        'contrast_between_text_and_bg' => 'Contrast between text and background:',
        'hue_adjustment' => 'Hue Adjustment',
        'recent_colors' => 'Recent Colors',
        'apply' => 'Apply',
        'excellent_aaa' => 'Excellent (AAA)',
        'good_aa' => 'Good (AA)',
        'poor_needs_improvement' => 'Poor (Needs Improvement)',
        'mount_point' => 'Mount Point:',
        'used_space' => 'Used Space:',
        'file_summary' => 'Selected %d files, total %s MB',
        'pageTitle' => 'File Assistant',
        'uploadBtn' => 'Upload File',
        'rootDirectory' => 'Root Directory',
        'permissions' => 'Permissions',
        'actions' => 'Actions',
        'directory' => 'Directory',
        'file' => 'File',
        'confirmDelete' => 'Are you sure you want to delete {0}? This action cannot be undone.',
        'newName' => 'New name:',
        'setPermissions' => '🔒 Set Permissions',
        'modifiedTime' => 'Modified Time',
        'owner' => 'Owner',
        'create' => 'Create',
        'newFolder' => 'New Folder',
        'newFile' => 'New File',
        'folderName' => 'Folder name:',
        'searchFiles' => 'Search Files',
        'noMatchingFiles' => 'No matching files found.',
        'moveTo' => 'Move to',
        'confirm' => 'Confirm',
        'goBack' => 'Go Back',
        'refreshDirectory' => 'Refresh Directory',
        'filePreview' => 'File Preview',
        'unableToLoadImage' => 'Unable to load image:',
        'unableToLoadSVG' => 'Unable to load SVG file:',
        'unableToLoadAudio' => 'Unable to load audio:',
        'unableToLoadVideo' => 'Unable to load video:',
        'fileAssistant' => 'File Assistant',
        'errorSavingFile' => 'Error: Unable to save file.',
        'uploadFailed' => 'Upload Failed',
        'fileNotExistOrNotReadable' => 'File does not exist or is not readable.',
        'inputFileName' => 'Enter file name',
        'permissionValue' => 'Permission value (e.g.: 0644)',
        'inputThreeOrFourDigits' => 'Enter 3 or 4 digits, e.g.: 0644 or 0755',
        'fontSizeL' => 'Font Size',
        'newNameCannotBeEmpty' => 'New name cannot be empty',
        'fileNameCannotContainChars' => 'File name cannot contain the following characters: < > : " / \\ | ? *',
        'folderNameCannotBeEmpty' => 'Folder name cannot be empty',
        'fileNameCannotBeEmpty' => 'File name cannot be empty',
        'searchError' => 'Error while searching: ',
        'encodingChanged' => 'Encoding changed to {0}. Actual conversion will be performed on the server side when saving.',
        'errorLoadingFileContent' => 'Error loading file content: ',
        'permissionHelp' => 'Please enter a valid permission value (3 or 4 octal digits, e.g.: 644 or 0755)',
        'permissionValueCannotExceed' => 'Permission value cannot exceed 0777',
        'goBackTitle' => 'Go Back',
        'rootDirectoryTitle' => 'Back to Root Directory',
        'homeDirectoryTitle' => 'Back to Home Directory',
        'refreshDirectoryTitle' => 'Refresh Directory',
        'selectAll' => 'Select All',
        'invertSelection' => 'Invert Selection',
        'deleteSelected' => 'Delete Selected',
        'searchTitle' => 'Search',
        'createTitle' => 'Create',
        'uploadTitle' => 'Upload',
        'dragHint' => 'Drag and drop files here or click to select multiple files',
        'searchInputPlaceholder' => 'Enter file name',
        'search_placeholder' => 'Enter file name to search...',
        'advancedEdit' => 'Advanced Edit',
        'search' => 'Search',
        'format' => 'Format',
        'goToParentDirectoryTitle' => 'Go to Parent Directory',
        'alreadyAtRootDirectory' => 'Already at root directory, cannot go back.',
        'fullscreen' => 'Fullscreen',
        'exitFullscreen' => 'Exit Fullscreen',
        'search_title' => 'Search File Content',
        'json_format_success' => 'JSON formatted successfully',
        'js_format_success' => 'JavaScript formatted successfully',
        'format_not_supported' => 'Current mode does not support formatting',
        'format_error' => 'Format error: ',
        'json_syntax_valid' => 'JSON syntax is correct',
        'json_syntax_error' => 'JSON syntax error: ',
        'yaml_syntax_valid' => 'YAML syntax is correct',
        'yaml_syntax_error' => 'YAML syntax error: ',
        'yaml_format_success' => 'YAML formatted successfully',
        'yaml_format_error' => 'YAML format error: ',
        'search_placeholder' => 'Search...',
        'replace_placeholder' => 'Replace with...',
        'find_all' => 'All',
        'replace' => 'Replace',
        'replace_all' => 'Replace All',
        'toggle_replace_mode' => 'Toggle Replace Mode',
        'toggle_regexp_mode' => 'Regular Expression Search',
        'toggle_case_sensitive' => 'Case Sensitive Search',
        'toggle_whole_words' => 'Whole Word Match Search',
        'search_in_selection' => 'Search in Selection',
        'search_counter_of' => 'of',
        'select_all' => 'Select All',
        'selected_info' => 'Selected {count} files, total {size}',
        'selected_info_none' => '0 items selected',
        'batch_delete' => 'Batch Delete',
        'batch_delete_confirm' => 'Are you sure you want to delete {count} selected files/folders? This action cannot be undone!',
        'batch_delete_no_selection' => 'Please select files to delete first!',
        'chmod_invalid_input' => 'Please enter a valid permission value (3 or 4 octal digits, e.g.: 644 or 0755).',
        'delete_confirm' => '⚠️ Are you sure you want to delete "{name}"? This action cannot be undone!',
        'json_format_success' => 'JSON formatted successfully',
        'js_format_success' => 'JavaScript formatted successfully',
        'unsupported_format' => 'Current mode does not support formatting',
        'format_error' => 'Format error: {message}',
        'json_syntax_valid' => 'JSON syntax is correct',
        'json_syntax_error' => 'JSON syntax error: {message}',
        'yaml_syntax_valid' => 'YAML syntax is correct',
        'yaml_syntax_error' => 'YAML syntax error: {message}',
        'yaml_format_success' => 'YAML formatted successfully',
        'yaml_format_error' => 'YAML format error: {message}',
        'search_empty_input' => 'Please enter search keyword',
        'search_no_results' => 'No matching files found',
        'search_error' => 'Search error: {message}',
        'search_filename' => 'File Name',
        'search_path' => 'Path',
        'search_action' => 'Action',
        'search_move_to' => 'Move to',
        'edit_file_title' => 'Edit File: {filename}',
        'fetch_content_error' => 'Unable to fetch file content: {message}',
        'save_file_success' => 'File saved successfully',
        'search.noResults' => 'No results',
        'search.previousMatch' => 'Previous match (Shift+Enter)',
        'search.nextMatch' => 'Next match (Enter)',
        'search.matchCase' => 'Match case (Alt+C)',
        'search.matchWholeWord' => 'Match whole word (Alt+W)',
        'search.useRegex' => 'Use regular expression (Alt+R)',
        'search.findInSelection' => 'Find in selection (Alt+L)',
        'search.close' => 'Close (Escape)',
        'search.toggleReplace' => 'Toggle replace',
        'search.preserveCase' => 'Preserve case (Alt+P)',
        'search.replaceAll' => 'Replace all (Ctrl+Alt+Enter)',
        'search.replace' => 'Replace (Enter)',
        'search.find' => 'Find',
        'search.replace' => 'Replace',
        'format_success' => 'Format successful',
        'format_unsupported' => 'Format not supported',
        'format_error' => 'Format error: {message}',
        'unsupported_format' => 'Current mode does not support formatting',
        'toggleComment' => 'Toggle Comment',
        'compare' => 'Compare',
        'enterModifiedContent' => 'Please enter modified content for comparison:',
        'closeDiff' => 'Close Diff View',
        "cancelButton" => "Cancel",
        "saveButton" => "Save",
        'toggleFullscreen' => 'Fullscreen',
        "lineColumnDisplay" => "Line: {line}, Column: {column}",
        "charCountDisplay" => "Characters: {charCount}",
        "fileName" => "File Name",
        "fileSize" => "Size",
        "fileType" => "File Type",
        'formatYaml' => 'Format YAML',
        'validateJson' => 'Validate JSON Syntax',
        'total_items'  => 'Total',
        'items'        => 'items',
        'current_path' => 'Path:',
        'disk'         => 'Disk',
        'root'         => 'root', 
        'validateYaml' => 'Validate YAML Syntax'
    ],
    'bn' => [
        'select_language'        => 'ভাষা নির্বাচন করুন',
        'simplified_chinese'     => 'সরলীকৃত চীনা',
        'traditional_chinese'    => 'প্রথাগত চীনা',
        'english'                => 'ইংরেজি',
        'korean'                 => 'কোরিয়ান',
        'vietnamese'             => 'ভিয়েতনামী',
        'thailand'               => 'থাই',
        'japanese'               => 'জাপানি',
        'russian'                => 'রাশিয়ান',
        'germany'                => 'জার্মান',
        'france'                 => 'ফরাসি',
        'arabic'                 => 'আরবি',
        'spanish'                => 'স্প্যানিশ',
        'bangladesh'             => 'বাংলা',
        'oklch_values'     => 'OKLCH মান:',
        'contrast_ratio'   => 'কনট্রাস্ট অনুপাত:',
        'reset'            => 'রিসেট করুন',
        'close'                  => 'বন্ধ',
        'save'                   => 'সংরক্ষণ',
        'theme_download'         => 'থিম ডাউনলোড',
        'select_all'             => 'সব নির্বাচন',
        'batch_delete'           => 'নির্বাচিত ফাইল একসাথে মুছুন',
        'batch_delete_success'   => '✅ একসাথে মুছুন সফল',
        'batch_delete_failed'    => '❌ একসাথে মুছুন ব্যর্থ',
        'confirm_delete'         => 'মুছে ফেলতে চান?',
        'total'                  => 'মোট:',
        'free'                   => 'অবশিষ্ট:',
        'hover_to_preview'       => 'প্লে করতে ক্লিক করুন',
        'spectra_config'         => 'Spectra কনফিগারেশন',
        'current_mode'           => 'বর্তমান মোড: লোড হচ্ছে...',
        'toggle_mode'            => 'মোড পরিবর্তন',
        'check_update'           => 'আপডেট চেক করুন',
        'batch_upload'           => 'একসাথে আপলোডের জন্য ফাইল নির্বাচন',
        'add_to_playlist'        => 'প্লেলিস্টে যোগ করতে চেক করুন',
        'clear_background'       => 'পটভূমি সাফ',
        'clear_background_label' => 'পটভূমি সাফ',
        'file_list'              => 'ফাইল তালিকা',
        'component_bg_color'     => 'কম্পোনেন্টের পটভূমি রং নির্বাচন',
        'page_bg_color'          => 'পৃষ্ঠার পটভূমি রং নির্বাচন',
        'toggle_font'            => 'ফন্ট পরিবর্তন',
        'filename'               => 'নাম:',
        'filesize'               => 'আকার:',
        'duration'               => 'সময়:',
        'resolution'             => 'রেজোলিউশন:',
        'bitrate'                => 'বিটরেট:',
        'type'                   => 'ধরণ:',
        'image'                  => 'ছবি',
        'video'                  => 'ভিডিও',
        'audio'                  => 'অডিও',
        'document'               => 'ডকুমেন্ট',
        'delete'                 => 'মুছুন',
        'rename'                 => 'নাম পরিবর্তন',
        'download'               => 'ডাউনলোড',
        'set_background'         => 'পটভূমি সেট করুন',
        'preview'                => 'প্রিভিউ',
        'toggle_fullscreen'      => 'ফুলস্ক্রিন পরিবর্তন',
        'supported_formats'      => 'সমর্থিত ফরম্যাট: [ jpg, jpeg, png, gif, webp, mp4, webm, mkv, mp3, wav, flac ]',
        'drop_files_here'        => 'ফাইল এখানে ড্রপ করুন',
        'or'                     => 'অথবা',
        'select_files'           => 'ফাইল নির্বাচন',
        'unlock_php_upload_limit'=> 'PHP আপলোড লিমিট আনলক',
        'upload'                 => 'আপলোড',
        'cancel'                 => 'বাতিল',
        'rename_file'            => 'নাম পরিবর্তন',
        'new_filename'           => 'নতুন ফাইলনাম',
        'invalid_filename_chars' => 'ফাইলনামে এই অক্ষর থাকতে পারবে না: \\/：*?"<>|',
        'confirm'                => 'নিশ্চিত',
        'media_player'           => 'মিডিয়া প্লেয়ার',
        'playlist'               => 'প্লেলিস্ট',
        'clear_list'             => 'তালিকা সাফ',
        'toggle_list'            => 'তালিকা লুকান',
        'picture_in_picture'     => 'পিকচার ইন পিকচার',
        'fullscreen'             => 'ফুলস্ক্রিন',
        'fetching_version'       => 'সংস্করণ তথ্য পাওয়া হচ্ছে...',
        'download_local'         => 'লোকালে ডাউনলোড',
        'change_language'        => 'ভাষা পরিবর্তন',
        'hour_announcement'      => 'ঘন্টা ঘোষণা, এখন বেইজিং সময়',
        'hour_exact'             => 'টা বাজে',
        'weekDays' => ['রবি', 'সোম', 'মঙ্গল', 'বুধ', 'বৃহস্পতি', 'শুক্র', 'শনি'],
        'labels' => [
            'year' => 'বছর',
            'month' => 'মাস',
            'day' => 'তারিখ',
            'week' => 'সপ্তাহ'
        ],
        'zodiacs' => ['বানর','মোরগ','কুকুর','শূকর','ইঁদুর','গরু','বাঘ','খরগোশ','ড্রাগন','সাপ','ঘোড়া','ছাগল'],
        'clear_confirm' =>'কনফিগারেশন সাফ করতে চান?', 
        'back_to_first' => 'প্লেলিস্টের প্রথম গানে ফিরে গেছে',
        'font_default' => 'গোলাকার ফন্টে পরিবর্তন করা হয়েছে',
        'font_fredoka' => 'ডিফল্ট ফন্টে পরিবর্তন করা হয়েছে',
        'font_mono'    => 'হাতের লেখা ফন্টে পরিবর্তন করা হয়েছে',
        'font_noto'    => 'চীনা সেরিফ ফন্টে পরিবর্তন করা হয়েছে',
        'font_dm_serif'     => 'DM Serif Display ফন্টে পরিবর্তিত হয়েছে',
        'error_loading_time' => 'সময় প্রদর্শনে ত্রুটি',
        'switch_to_light_mode' => 'হালকা মোডে পরিবর্তন',
        'switch_to_dark_mode' => 'অন্ধকার মোডে পরিবর্তন',
        'current_mode_dark' => 'বর্তমান মোড: অন্ধকার মোড',
        'current_mode_light' => 'বর্তমান মোড: হালকা মোড',
        'fetching_version' => 'সংস্করণ তথ্য পাওয়া হচ্ছে...',
        'latest_version' => 'সর্বশেষ সংস্করণ',
        'unable_to_fetch_version' => 'সর্বশেষ সংস্করণ তথ্য পাওয়া যায়নি',
        'request_failed' => 'অনুরোধ ব্যর্থ, পরে আবার চেষ্টা করুন',
        'pip_not_supported' => 'বর্তমান মিডিয়া পিকচার ইন পিকচার সমর্থন করে না',
        'pip_operation_failed' => 'পিকচার ইন পিকচার অপারেশন ব্যর্থ',
        'exit_picture_in_picture' => 'পিকচার ইন পিকচার থেকে বের হন',
        'picture_in_picture' => 'পিকচার ইন পিকচার',
        'hide_playlist' => 'তালিকা লুকান',
        'show_playlist' => 'তালিকা দেখান',
        'enter_fullscreen' => 'ফুলস্ক্রিনে যান',
        'exit_fullscreen' => 'ফুলস্ক্রিন থেকে বের হন',
        'confirm_update_php' => 'আপনি PHP কনফিগারেশন আপডেট করতে চান?',
        'select_files_to_delete' => 'দয়া করে প্রথমে মুছতে চাওয়া ফাইল নির্বাচন করুন!',
        'confirm_batch_delete' => '%d টি নির্বাচিত ফাইল মুছতে চান?',
        'unable_to_fetch_current_version' => 'বর্তমান সংস্করণ পাওয়া হচ্ছে...',
        'current_version' => 'বর্তমান সংস্করণ',
        'copy_command'     => 'কমান্ড কপি',
        'command_copied'   => 'কমান্ড ক্লিপবোর্ডে কপি হয়েছে!',
        "updateModalLabel" => "আপডেট অবস্থা",
        "updateDescription" => "আপডেট প্রক্রিয়া শুরু হতে চলেছে।",
        "waitingMessage" => "অপারেশন শুরু হওয়ার জন্য অপেক্ষা...",
        "update_plugin" => "প্লাগইন আপডেট",
        "installation_complete" => "ইনস্টলেশন সম্পূর্ণ!",
        'confirm_title'             => 'অপারেশন নিশ্চিত',
        'confirm_delete_file'   => '%s ফাইলটি মুছতে চান?',
        'delete_success'      => 'সফলভাবে মুছে ফেলা হয়েছে: %s',
        'delete_failure'      => 'মুছতে ব্যর্থ: %s',
        'upload_error_type_not_supported' => 'অসমর্থিত ফাইল টাইপ: %s',
        'upload_error_move_failed'        => 'ফাইল আপলোড ব্যর্থ: %s',
        'confirm_clear_background' => 'পটভূমি সাফ করতে চান?',
        'background_cleared'      => 'পটভূমি সাফ করা হয়েছে!',
        'fileNotSelected' => 'ফাইল নির্বাচন করা হয়নি',
        'httpError' => 'HTTP ত্রুটি',
        'linkGenerated' => '✅ শেয়ার লিঙ্ক তৈরি হয়েছে',
        'operationFailed' => '❌ অপারেশন ব্যর্থ',
        'generateLinkFirst' => 'দয়া করে আগে শেয়ার লিঙ্ক তৈরি করুন',
        'linkCopied' => '📋 লিঙ্ক কপি করা হয়েছে',
        'copyFailed' => '❌ কপি ব্যর্থ',
        'createShareLink' => 'শেয়ার লিঙ্ক তৈরি করুন',
        'closeButton' => 'বন্ধ করুন',
        'expireTimeLabel' => 'মেয়াদ শেষ হওয়ার সময়',
        'expire1Hour' => '1 ঘণ্টা',
        'expire1Day' => '1 দিন',
        'expire7Days' => '7 দিন',
        'expire30Days' => '30 দিন',
        'maxDownloadsLabel' => 'সর্বাধিক ডাউনলোড সংখ্যা',
        'max1Download' => '1 বার',
        'max5Downloads' => '5 বার',
        'max10Downloads' => '10 বার',
        'maxUnlimited' => 'অসীম',
        'shareLinkLabel' => 'শেয়ার লিঙ্ক',
        'copyLinkButton' => 'লিঙ্ক কপি করুন',
        'closeButtonFooter' => 'বন্ধ করুন',
        'generateLinkButton' => 'লিঙ্ক তৈরি করুন',
        'cleanExpiredButton' => 'মেয়াদোত্তীর্ণ পরিষ্কার করুন',
        'deleteAllButton' => 'সব মুছে ফেলুন',
        'cleanSuccess' => '✅ পরিষ্কার সম্পন্ন হয়েছে, %s আইটেম মুছে ফেলা হয়েছে',
        'deleteSuccess' => '✅ সব শেয়ার রেকর্ড মুছে ফেলা হয়েছে, %s ফাইল মুছে ফেলা হয়েছে',
        'confirmDeleteAll' => '⚠️ আপনি কি নিশ্চিত আপনি সব শেয়ার রেকর্ড মুছে ফেলতে চান?',
        'operationFailed' => '❌ অপারেশন ব্যর্থ হয়েছে',
        'ip_info' => 'আইপি বিবরণ',
        'ip_support' => 'আইপি সমর্থন',
        'ip_address' => 'আইপি ঠিকানা',
        'location' => 'অবস্থান',
        'isp' => 'সেবা প্রদানকারী',
        'asn' => 'ASN',
        'timezone' => 'সময় অঞ্চল',
        'latitude_longitude' => 'স্থানাঙ্ক',
        'latency_info' => 'বিলম্ব তথ্য',
        'current_fit_mode'    => 'বর্তমান মোড',
        'fit_contain'    => 'স্বাভাবিক অনুপাত',
        'fit_fill'       => 'সম্পূর্ণ ভরাট',
        'fit_none'       => 'মূল আকার',
        'fit_scale-down' => 'স্মার্ট মানানসই',
        'fit_cover'      => 'ক্রপ মোড',
        'advanced_color_settings' => 'উন্নত রঙ সেটিংস',
        'advanced_color_control' => 'উন্নত রঙ নিয়ন্ত্রণ',
        'color_control' => 'রঙ নিয়ন্ত্রণ',
        'primary_hue' => 'প্রাথমিক রঙ',
        'chroma' => 'সত্যতা',
        'lightness' => 'উজ্জ্বলতা',
        'or_use_palette' => 'অথবা প্যালেট ব্যবহার করুন',
        'reset_to_default' => 'ডিফল্টে রিসেট করুন',
        'preview_and_contrast' => 'পূর্বদর্শন এবং কনট্রাস্ট',
        'color_preview' => 'রঙের পূর্বরূপ',
        'readability_check' => 'পাঠযোগ্যতা পরীক্ষা',
        'contrast_between_text_and_bg' => 'পাঠ্য এবং পটভূমির মধ্যে কনট্রাস্ট:',
        'hue_adjustment' => 'রঙ সামঞ্জস্য',
        'recent_colors' => 'সাম্প্রতিক রঙ',
        'apply' => 'প্রয়োগ করুন',
        'excellent_aaa' => 'চমৎকার (AAA)',
        'good_aa' => 'ভাল (AA)',
        'poor_needs_improvement' => 'খারাপ (উন্নতি প্রয়োজন)',
        'mount_point' => 'মাউন্ট পয়েন্ট:',
        'used_space'  => 'ব্যবহৃত স্থান:',
        'file_summary' => '%d টি ফাইল নির্বাচিত, মোট %s MB',
        'pageTitle' => 'ফাইল সহায়ক',
        'uploadBtn' => 'ফাইল আপলোড করুন',
        'rootDirectory' => 'রুট ডিরেক্টরি',
        'permissions' => 'অনুমতি',
        'actions' => 'ক্রিয়াকলাপ',
        'directory' => 'ডিরেক্টরি',
        'file' => 'ফাইল',
        'confirmDelete' => 'আপনি কি নিশ্চিত যে {0} মুছে ফেলতে চান? এই ক্রিয়াটি পূর্বাবস্থায় ফেরানো যাবে না।',
        'newName' => 'নতুন নাম:',
        'setPermissions' => '🔒 অনুমতি সেট করুন',
        'modifiedTime' => 'সংশোধনের সময়',
        'owner' => 'মালিক',
        'create' => 'তৈরি করুন',
        'newFolder' => 'নতুন ফোল্ডার',
        'newFile' => 'নতুন ফাইল',
        'folderName' => 'ফোল্ডারের নাম:',
        'searchFiles' => 'ফাইল অনুসন্ধান করুন',
        'noMatchingFiles' => 'কোন মিল ফাইল পাওয়া যায়নি।',
        'moveTo' => 'স্থানান্তর করুন',
        'confirm' => 'নিশ্চিত করুন',
        'goBack' => 'পিছনে যান',
        'refreshDirectory' => 'ডিরেক্টরি রিফ্রেশ করুন',
        'filePreview' => 'ফাইল প্রিভিউ',
        'unableToLoadImage' => 'ছবি লোড করতে অক্ষম:',
        'unableToLoadSVG' => 'SVG ফাইল লোড করতে অক্ষম:',
        'unableToLoadAudio' => 'অডিও লোড করতে অক্ষম:',
        'unableToLoadVideo' => 'ভিডিও লোড করতে অক্ষম:',
        'fileAssistant' => 'ফাইল সহায়ক',
        'errorSavingFile' => 'ত্রুটি: ফাইল সংরক্ষণ করতে অক্ষম।',
        'uploadFailed' => 'আপলোড ব্যর্থ',
        'fileNotExistOrNotReadable' => 'ফাইলটি বিদ্যমান নেই বা পড়া যায় না।',
        'inputFileName' => 'ফাইলের নাম লিখুন',
        'permissionValue' => 'অনুমতির মান (উদা: 0644)',
        'inputThreeOrFourDigits' => '3 বা 4 সংখ্যা লিখুন, উদা: 0644 বা 0755',
        'fontSizeL' => 'ফন্টের আকার',
        'newNameCannotBeEmpty' => 'নতুন নাম খালি থাকতে পারে না',
        'fileNameCannotContainChars' => 'ফাইলের নামে নিম্নলিখিত অক্ষর থাকতে পারে না: < > : " / \\ | ? *',
        'folderNameCannotBeEmpty' => 'ফোল্ডারের নাম খালি থাকতে পারে না',
        'fileNameCannotBeEmpty' => 'ফাইলের নাম খালি থাকতে পারে না',
        'searchError' => 'অনুসন্ধান করতে গিয়ে ত্রুটি: ',
        'encodingChanged' => 'এনকোডিং {0} এ পরিবর্তিত হয়েছে। সংরক্ষণ করার সময় সার্ভার সাইডে প্রকৃত রূপান্তর করা হবে।',
        'errorLoadingFileContent' => 'ফাইল বিষয়বস্তু লোড করতে ত্রুটি: ',
        'permissionHelp' => 'দয়া করে একটি বৈধ অনুমতি মান লিখুন (3 বা 4 অক্টাল সংখ্যা, উদা: 644 বা 0755)',
        'permissionValueCannotExceed' => 'অনুমতির মান 0777 অতিক্রম করতে পারে না',
        'goBackTitle' => 'পিছনে যান',
        'rootDirectoryTitle' => 'রুট ডিরেক্টরিতে ফিরে যান',
        'homeDirectoryTitle' => 'হোম ডিরেক্টরিতে ফিরে যান',
        'refreshDirectoryTitle' => 'ডিরেক্টরি রিফ্রেশ করুন',
        'selectAll' => 'সব নির্বাচন করুন',
        'invertSelection' => 'নির্বাচন উল্টান',
        'deleteSelected' => 'নির্বাচিত মুছুন',
        'searchTitle' => 'অনুসন্ধান',
        'createTitle' => 'তৈরি করুন',
        'uploadTitle' => 'আপলোড',
        'dragHint' => 'এখানে ফাইলগুলি টেনে এনে ছেড়ে দিন বা একাধিক ফাইল নির্বাচন করতে ক্লিক করুন',
        'searchInputPlaceholder' => 'ফাইলের নাম লিখুন',
        'search_placeholder' => 'অনুসন্ধানের জন্য ফাইলের নাম লিখুন...',
        'advancedEdit' => 'উন্নত সম্পাদনা',
        'search' => 'অনুসন্ধান',
        'format' => 'ফরম্যাট',
        'goToParentDirectoryTitle' => 'প্যারেন্ট ডিরেক্টরিতে যান',
        'alreadyAtRootDirectory' => 'ইতিমধ্যে রুট ডিরেক্টরিতে, পিছনে যাওয়া যাবে না।',
        'fullscreen' => 'পূর্ণস্ক্রীন',
        'exitFullscreen' => 'পূর্ণস্ক্রীন থেকে প্রস্থান করুন',
        'search_title' => 'ফাইল বিষয়বস্তু অনুসন্ধান করুন',
        'json_format_success' => 'JSON সফলভাবে ফরম্যাট করা হয়েছে',
        'js_format_success' => 'JavaScript সফলভাবে ফরম্যাট করা হয়েছে',
        'format_not_supported' => 'বর্তমান মোড ফরম্যাটিং সমর্থন করে না',
        'format_error' => 'ফরম্যাট ত্রুটি: ',
        'json_syntax_valid' => 'JSON সিনট্যাক্স সঠিক',
        'json_syntax_error' => 'JSON সিনট্যাক্স ত্রুটি: ',
        'yaml_syntax_valid' => 'YAML সিনট্যাক্স সঠিক',
        'yaml_syntax_error' => 'YAML সিনট্যাক্স ত্রুটি: ',
        'yaml_format_success' => 'YAML সফলভাবে ফরম্যাট করা হয়েছে',
        'yaml_format_error' => 'YAML ফরম্যাট ত্রুটি: ',
        'search_placeholder' => 'অনুসন্ধান...',
        'replace_placeholder' => 'দিয়ে প্রতিস্থাপন করুন...',
        'find_all' => 'সব',
        'replace' => 'প্রতিস্থাপন',
        'replace_all' => 'সব প্রতিস্থাপন',
        'toggle_replace_mode' => 'প্রতিস্থাপন মোড টগল করুন',
        'toggle_regexp_mode' => 'রেগুলার এক্সপ্রেশন অনুসন্ধান',
        'toggle_case_sensitive' => 'কেস সেনসিটিভ অনুসন্ধান',
        'toggle_whole_words' => 'সম্পূর্ণ শব্দ মিল অনুসন্ধান',
        'search_in_selection' => 'নির্বাচনে অনুসন্ধান করুন',
        'search_counter_of' => 'এর',
        'select_all' => 'সব নির্বাচন করুন',
        'selected_info' => '{count}টি ফাইল নির্বাচিত, মোট {size}',
        'selected_info_none' => '0টি আইটেম নির্বাচিত',
        'batch_delete' => 'ব্যাচ মুছে ফেলুন',
        'batch_delete_confirm' => 'আপনি কি নিশ্চিত যে {count}টি নির্বাচিত ফাইল/ফোল্ডার মুছে ফেলতে চান? এই ক্রিয়াটি পূর্বাবস্থায় ফেরানো যাবে না!',
        'batch_delete_no_selection' => 'দয়া করে প্রথমে মুছে ফেলার জন্য ফাইল নির্বাচন করুন!',
        'chmod_invalid_input' => 'দয়া করে একটি বৈধ অনুমতি মান লিখুন (3 বা 4 অক্টাল সংখ্যা, উদা: 644 বা 0755)।',
        'delete_confirm' => '⚠️ আপনি কি নিশ্চিত যে "{name}" মুছে ফেলতে চান? এই ক্রিয়াটি পূর্বাবস্থায় ফেরানো যাবে না!',
        'json_format_success' => 'JSON সফলভাবে ফরম্যাট করা হয়েছে',
        'js_format_success' => 'JavaScript সফলভাবে ফরম্যাট করা হয়েছে',
        'unsupported_format' => 'বর্তমান মোড ফরম্যাটিং সমর্থন করে না',
        'format_error' => 'ফরম্যাট ত্রুটি: {message}',
        'json_syntax_valid' => 'JSON সিনট্যাক্স সঠিক',
        'json_syntax_error' => 'JSON সিনট্যাক্স ত্রুটি: {message}',
        'yaml_syntax_valid' => 'YAML সিনট্যাক্স সঠিক',
        'yaml_syntax_error' => 'YAML সিনট্যাক্স ত্রুটি: {message}',
        'yaml_format_success' => 'YAML সফলভাবে ফরম্যাট করা হয়েছে',
        'yaml_format_error' => 'YAML ফরম্যাট ত্রুটি: {message}',
        'search_empty_input' => 'দয়া করে অনুসন্ধান কীওয়ার্ড লিখুন',
        'search_no_results' => 'কোন মিল ফাইল পাওয়া যায়নি',
        'search_error' => 'অনুসন্ধান ত্রুটি: {message}',
        'search_filename' => 'ফাইলের নাম',
        'search_path' => 'পথ',
        'search_action' => 'ক্রিয়া',
        'search_move_to' => 'স্থানান্তর করুন',
        'edit_file_title' => 'ফাইল সম্পাদনা করুন: {filename}',
        'fetch_content_error' => 'ফাইল বিষয়বস্তু আনতে অক্ষম: {message}',
        'save_file_success' => 'ফাইল সফলভাবে সংরক্ষিত হয়েছে',
        'search.noResults' => 'কোন ফলাফল নেই',
        'search.previousMatch' => 'পূর্ববর্তী মিল (Shift+Enter)',
        'search.nextMatch' => 'পরবর্তী মিল (Enter)',
        'search.matchCase' => 'কেস মিল করুন (Alt+C)',
        'search.matchWholeWord' => 'সম্পূর্ণ শব্দ মিল করুন (Alt+W)',
        'search.useRegex' => 'রেগুলার এক্সপ্রেশন ব্যবহার করুন (Alt+R)',
        'search.findInSelection' => 'নির্বাচনে খুঁজুন (Alt+L)',
        'search.close' => 'বন্ধ করুন (Escape)',
        'search.toggleReplace' => 'প্রতিস্থাপন টগল করুন',
        'search.preserveCase' => 'কেস সংরক্ষণ করুন (Alt+P)',
        'search.replaceAll' => 'সব প্রতিস্থাপন করুন (Ctrl+Alt+Enter)',
        'search.replace' => 'প্রতিস্থাপন করুন (Enter)',
        'search.find' => 'খুঁজুন',
        'search.replace' => 'প্রতিস্থাপন',
        'format_success' => 'ফরম্যাট সফল',
        'format_unsupported' => 'ফরম্যাট সমর্থিত নয়',
        'format_error' => 'ফরম্যাট ত্রুটি: {message}',
        'unsupported_format' => 'বর্তমান মোড ফরম্যাটিং সমর্থন করে না',
        'toggleComment' => 'মন্তব্য টগল করুন',
        'compare' => 'তুলনা করুন',
        'enterModifiedContent' => 'তুলনার জন্য পরিবর্তিত বিষয়বস্তু লিখুন:',
        'closeDiff' => 'ডিফ ভিউ বন্ধ করুন',
        "cancelButton" => "বাতিল",
        "saveButton" => "সংরক্ষণ",
        'toggleFullscreen' => 'পূর্ণস্ক্রীন',
        "lineColumnDisplay" => "লাইন: {line}, কলাম: {column}",
        "charCountDisplay" => "অক্ষর সংখ্যা: {charCount}",
        "fileName" => "ফাইলের নাম",
        "fileSize" => "আকার",
        "fileType" => "ফাইলের ধরন",
        'formatYaml' => 'YAML ফরম্যাট করুন',
        'validateJson' => 'JSON সিনট্যাক্স যাচাই করুন',
        'total_items'  => 'মোট',
        'items'        => 'আইটেম',
        'current_path' => 'বর্তমান পথ',
        'disk'         => 'ডিস্ক',
        'root'         => 'রুট ডিরেক্টরি',
        'validateYaml' => 'YAML সিনট্যাক্স যাচাই করুন'
    ]
];

$flagMap = [
    'zh' => 'cn.svg',
    'hk' => 'hk.svg',
    'en' => 'us.svg',
    'ko' => 'kr.svg',
    'ja' => 'jp.svg',
    'ru' => 'ru.svg',
    'ar' => 'sa.svg',
    'es' => 'es.svg',
    'de' => 'de.svg',
    'fr' => 'fr.svg',
    'th' => 'th.svg',
    'bn' => 'bd.svg',
    'vi' => 'vn.svg',
];

if (!isset($currentLang) || empty($currentLang)) {
    $currentLang = 'en';
}

$flagFile = isset($flagMap[$currentLang]) ? $flagMap[$currentLang] : 'us.svg';

if (!file_exists($langFilePath)) {
    file_put_contents($langFilePath, $defaultLang);
    chmod($langFilePath, 0644);
}

function getSavedLanguage() {
    global $langFilePath, $langData, $defaultLang;
    $savedLang = @trim(file_get_contents($langFilePath));
    return isset($langData[$savedLang]) ? $savedLang : $defaultLang;
}

function saveLanguage($lang) {
    global $langFilePath, $langData;
    if (isset($langData[$lang])) {
        file_put_contents($langFilePath, $lang);
    }
}

function __($key) {
    global $translations;
    return $translations[$key] ?? $key;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['lang'])) {
    saveLanguage($_POST['lang']);
    echo 'Language updated to ' . $_POST['lang'];
    exit;
}

$currentLang = getSavedLanguage();
$translations = $langData[$currentLang];
?>

<script>
const langData = <?php echo json_encode($langData); ?>;
const currentLang = "<?php echo $currentLang; ?>";
let translations = langData[currentLang] || langData['en'];

function startLanguageMonitoring() {
    let currentLanguage = localStorage.getItem('language') || currentLang;   
    setInterval(() => {
        fetch('./save_language.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=get_language'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.language) {
                const newLanguage = data.language;
                if (newLanguage !== currentLanguage) {
                    currentLanguage = newLanguage;
                    updateLanguage(newLanguage);
                    updateFlagIcon(newLanguage);
                    document.getElementById("langSelect").value = newLanguage;
                    localStorage.setItem('language', newLanguage);
                    
                    console.log('Language updated to:', newLanguage);
                }
            }
        })
        .catch(error => {
            console.error('Error checking language:', error);
        });
    }, 2000);
}

document.addEventListener("DOMContentLoaded", () => {
    fetch('./save_language.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=get_language'
    })
    .then(response => response.json())
    .then(data => {
        const userLang = (data.success && data.language) ? data.language : currentLang;
        updateLanguage(userLang); 
        updateFlagIcon(userLang);  
        
        const langSelect = document.getElementById("langSelect");
        if (langSelect) {
            langSelect.value = userLang;
        }
        
        localStorage.setItem('language', userLang);
        startLanguageMonitoring();
    })
    .catch(error => {
        updateLanguage(currentLang); 
        updateFlagIcon(currentLang);  
        
        const langSelect = document.getElementById("langSelect");
        if (langSelect) {
            langSelect.value = currentLang;
        }
        
        localStorage.setItem('language', currentLang);
        startLanguageMonitoring();
    });
});

document.addEventListener('DOMContentLoaded', function() {
    initTooltips();
});

function updateLanguage(lang) {
    localStorage.setItem('language', lang); 
    translations = langData[lang] || langData['en'];  

    const translateElement = (el, attribute, property) => {
        const translationKey = el.getAttribute(attribute);
        if (translations[translationKey]) {
            el[property] = translations[translationKey];
        }
    };

    document.querySelectorAll('[data-translate]').forEach(el => {
        const translationKey = el.getAttribute('data-translate');
        const dynamicContent = el.getAttribute('data-dynamic-content') || '';

        if (translations[translationKey]) {
            if (el.tagName === 'OPTGROUP') {
                el.setAttribute('label', translations[translationKey]);
            } else {
                el.innerText = translations[translationKey] + dynamicContent; 
            }
        }
    });

    document.querySelectorAll('[data-translate-title]').forEach(el => {
        translateElement(el, 'data-translate-title', 'title');
    });

    document.querySelectorAll('[data-translate-placeholder]').forEach(el => {
        const translationKey = el.getAttribute('data-translate-placeholder');
        if (translations[translationKey]) {
            el.setAttribute('placeholder', translations[translationKey]);
            el.setAttribute('aria-label', translations[translationKey]);  
            el.setAttribute('title', translations[translationKey]); 
        }
    });

    document.querySelectorAll('[data-translate]').forEach(el => {
        const translationKey = el.getAttribute('data-translate');
        if (translationKey && translations[translationKey]) {
            el.setAttribute('label', translations[translationKey]);  
        }
    });

    document.querySelectorAll('[data-translate-tooltip]').forEach(el => {
        const translationKey = el.getAttribute('data-translate-tooltip');
        
        if (translations[translationKey]) {
            el.setAttribute('data-tooltip-text', translations[translationKey]);
        }
        
        const mountPoint = el.getAttribute('data-mount-point');
        const usedSpace = el.getAttribute('data-used-space');
        if (mountPoint && usedSpace && translations) {
            const translatedTooltip = `${translations['mount_point'] || 'Mount Point'}${mountPoint}｜${
                translations['used_space'] || 'Used Space'}${usedSpace}`;
            el.setAttribute('data-tooltip-text', translatedTooltip);
        }
    });

    const activeTooltip = document.querySelector('.custom-tooltip.active');
    if (activeTooltip && activeTooltip._activeTarget) {
        const target = activeTooltip._activeTarget;
        const text = target.getAttribute('data-tooltip-text');
        if (text) {
            activeTooltip.textContent = text;
            const rect = target.getBoundingClientRect();
            const tipRect = activeTooltip.getBoundingClientRect();
            
            let left = rect.left + (rect.width - tipRect.width) / 2;
            let top = rect.top - tipRect.height - 8;
            
            if (left < 10) left = 10;
            if (left + tipRect.width > window.innerWidth - 10) {
                left = window.innerWidth - tipRect.width - 10;
            }
            
            if (top < 10) {
                top = rect.bottom + 8;
                if (top + tipRect.height > window.innerHeight - 10) {
                    top = rect.top - tipRect.height - 8;
                }
            }
            
            activeTooltip.style.left = left + 'px';
            activeTooltip.style.top = top + 'px';
        }
    }

    fetch("./theme-switcher.php")
        .then(res => res.json())
        .then(data => {
            if(data.mode) {
                updateStatus(data.mode);
            }
        })
        .catch(error => {
            //console.error("Error retrieving mode: " + error);
        });
}

function initTooltips() {
    const existing = document.querySelector('.custom-tooltip');
    if (existing) existing.remove();

    const tooltip = document.createElement('div');
    tooltip.className = 'custom-tooltip';
    tooltip.style.zIndex = '2147483647';
    document.body.appendChild(tooltip);

    let activeTarget = null;
    let scrollTimer;

    function showTooltip(target) {
        const text = target.getAttribute('data-tooltip-text') || target.getAttribute('title');
        if (!text || activeTarget === target) return;

        activeTarget = target;
        tooltip._activeTarget = target;
        tooltip.textContent = text;
        tooltip.style.display = 'block';
        tooltip.style.opacity = '0';

        const originalTitle = target.getAttribute('title');
        if (originalTitle) {
            target._originalTitle = originalTitle;
            target.removeAttribute('title');
        }

        setTimeout(() => {
            const tipRect = tooltip.getBoundingClientRect();
            const rect = target.getBoundingClientRect();

            let left = rect.left + (rect.width - tipRect.width) / 2;
            let top = rect.top - tipRect.height - 8;
            let placement = 'top';

            if (left < 10) left = 10;
            if (left + tipRect.width > window.innerWidth - 10) {
                left = window.innerWidth - tipRect.width - 10;
            }

            if (top < 10) {
                top = rect.bottom + 8;
                placement = 'bottom';

                if (top + tipRect.height > window.innerHeight - 10) {
                    top = rect.top - tipRect.height - 8;
                    placement = 'top';

                    if (top < 10) {
                        top = rect.top + 8;
                        placement = 'bottom';
                    }
                }
            }

            tooltip.style.left = left + 'px';
            tooltip.style.top = top + 'px';
            tooltip.setAttribute('data-placement', placement);
            tooltip.style.opacity = '';
            tooltip.classList.add('active');
        }, 10);
    }

    function hideTooltip() {
        tooltip.classList.remove('active');
        
        if (activeTarget && activeTarget._originalTitle) {
            activeTarget.setAttribute('title', activeTarget._originalTitle);
            delete activeTarget._originalTitle;
        }
        
        activeTarget = null;
        delete tooltip._activeTarget;
        
        setTimeout(() => {
            if (!tooltip.classList.contains('active')) {
                tooltip.style.display = 'none';
            }
        }, 200);
    }

    document.addEventListener('mouseover', e => {
        const target = e.target.closest('[data-tooltip-text], [title]');
        if (!target) return hideTooltip();

        clearTimeout(target._tooltipTimer);
        target._tooltipTimer = setTimeout(() => showTooltip(target), 200);
    });

    document.addEventListener('mouseout', e => {
        const target = e.target.closest('[data-tooltip-text], [title]');
        if (!target) return;

        clearTimeout(target._tooltipTimer);
        if (!e.relatedTarget?.closest('[data-tooltip-text], [title], .custom-tooltip')) {
            hideTooltip();
        }
    });

    document.addEventListener('touchstart', e => {
        const target = e.target.closest('[data-tooltip-text], [title]');
        if (!target) return hideTooltip();

        clearTimeout(target._tooltipTimer);
        target._tooltipTimer = setTimeout(() => showTooltip(target), 500);
    });

    document.addEventListener('touchend', hideTooltip);

    window.addEventListener('scroll', () => {
        clearTimeout(scrollTimer);
        scrollTimer = setTimeout(hideTooltip, 100);
    }, true);

    window.addEventListener('resize', () => {
        if (activeTarget) {
            setTimeout(() => showTooltip(activeTarget), 100);
        }
    });

    document.addEventListener('click', e => {
        if (!tooltip.contains(e.target)) {
            hideTooltip();
        }
    });

    window.addEventListener('beforeunload', () => {
        document.querySelectorAll('[data-tooltip-text], [title]').forEach(el => {
            clearTimeout(el._tooltipTimer);
        });
    });
}

const languageStandardMap = {
    'zh': 'zh-CN',
    'hk': 'zh-TW', 
    'en': 'en-US',
    'ko': 'ko-KR',
    'ja': 'ja-JP',
    'ru': 'ru-RU',
    'ar': 'ar-EG',
    'es': 'es-ES',
    'de': 'de-DE',
    'fr': 'fr-FR',
    'th': 'th-TH',
    'bn': 'bn-BD',
    'vi': 'vi-VN'
};

const langToVoiceLangMap = {
    'zh': ['zh-CN', 'zh-HK', 'zh-TW'],
    'hk': ['zh-CN', 'zh-HK', 'zh-TW'],
    'en': ['en-US', 'en-GB', 'en-AU', 'en-CA', 'en-IN'],
    'ko': ['ko-KR'],
    'ja': ['ja-JP'],
    'vi': ['vi-VN'],
    'th': ['th-TH'],
    'ru': ['ru-RU'],
    'ar': ['ar-SA', 'ar-EG', 'ar-AE'],
    'es': ['es-ES', 'es-MX', 'es-US'],
    'de': ['de-DE', 'de-AT', 'de-CH'],
    'fr': ['fr-FR', 'fr-CA', 'fr-CH'],
    'bn': ['bn-BD', 'bn-IN']
};

function speakMessage(message) {
    const globalVoiceEnabled = localStorage.getItem('colorVoiceEnabled') !== 'false';
    if (!globalVoiceEnabled) return;

    function speakWithVoices() {
        const voices = speechSynthesis.getVoices();
        if (voices.length === 0) {
            setTimeout(speakWithVoices, 100);
            return;
        }

        const xhr = new XMLHttpRequest();
        xhr.open('GET', './lib/language.txt', true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                const lang = xhr.responseText.trim();
                
                const voiceKey = getVoicePreferenceKey(lang);
                const savedVoiceIndex = localStorage.getItem(voiceKey);
                
                let selectedVoice = null;
                
                if (savedVoiceIndex !== null) {
                    const targetLangs = langToVoiceLangMap[lang] || [languageStandardMap[lang] || lang];
                    const filteredVoices = voices.filter(voice => 
                        targetLangs.some(targetLang => voice.lang.startsWith(targetLang))
                    );
                    selectedVoice = filteredVoices[savedVoiceIndex];
                }
                
                if (!selectedVoice) {
                    const chineseVoiceKey = getChineseVoiceKey();
                    const savedChineseIndex = localStorage.getItem(chineseVoiceKey);
                    const chineseVoices = voices.filter(voice => 
                        ['zh-CN', 'zh-HK', 'zh-TW'].some(lang => voice.lang.startsWith(lang))
                    );
                    
                    if (savedChineseIndex !== null && chineseVoices[savedChineseIndex]) {
                        selectedVoice = chineseVoices[savedChineseIndex];
                    } else if (chineseVoices.length > 0) {
                        selectedVoice = chineseVoices[0];
                    }
                }
                
                if (!selectedVoice) {
                    selectedVoice = voices.find(voice => voice.lang.includes('zh')) || voices[0];
                }
                
                if (selectedVoice) {
                    const utterance = new SpeechSynthesisUtterance(message);
                    utterance.voice = selectedVoice;
                    speechSynthesis.speak(utterance);
                }
            }
        };
        xhr.send();
    }

    speakWithVoices();
}

function getVoicePreferenceKey(lang) {
    if (lang === 'zh' || lang === 'hk') {
        return 'voicePreference_chinese';
    }
    return `voicePreference_${lang}`;
}

function getChineseVoiceKey() {
    return 'voicePreference_chinese';
}

function updateFlagIcon(lang) {
    const flagImg = document.getElementById('flagIcon');
    if (!flagImg) return;
    
    const flagMap = {
        'zh': '/luci-static/ipip/flags/cn.svg',
        'hk': '/luci-static/ipip/flags/hk.svg',
        'en': '/luci-static/ipip/flags/us.svg',
        'ko': '/luci-static/ipip/flags/kr.svg',
        'ja': '/luci-static/ipip/flags/jp.svg',
        'ru': '/luci-static/ipip/flags/ru.svg',
        'ar': '/luci-static/ipip/flags/sa.svg',
        'es': '/luci-static/ipip/flags/es.svg',
        'de': '/luci-static/ipip/flags/de.svg',
        'fr': '/luci-static/ipip/flags/fr.svg',
        'th': '/luci-static/ipip/flags/th.svg',
        'bn': '/luci-static/ipip/flags/bd.svg',
        'vi': '/luci-static/ipip/flags/vn.svg'
    };
    
    flagImg.src = flagMap[lang] || flagMap['en'];
}

function changeLanguage(lang) {
    const languageText = document.querySelector(`#langSelect option[value="${lang}"]`)?.text || lang;
    
    fetch('./save_language.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=save_language&language=' + lang
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateLanguage(lang);  
            updateFlagIcon(lang);  
            localStorage.setItem('language', lang);
            
            const langLabelMap = {
                'zh': '语言已切换为简体中文',
                'hk': '語言已切換為繁體中文',
                'en': 'Language switched to English',
                'ko': '언어가 한국어로 변경되었습니다',
                'ja': '言語が日本語に変更されました',
                'ru': 'Язык переключен на русский',
                'ar': 'تم تغيير اللغة إلى العربية',
                'es': 'El idioma ha cambiado a español',
                'de': 'Sprache auf Deutsch umgestellt',
                'fr': 'Langue changée en français',
                'th': 'เปลี่ยนภาษาเป็นภาษาไทยแล้ว',
                'bn': 'ভাষা বাংলাতে পরিবর্তন করা হয়েছে',
                'vi': 'Đã chuyển ngôn ngữ sang tiếng Việt'
            };

            const message = langLabelMap[lang] || 'Language switched';

            if (typeof speakMessage === 'function') {
                speakMessage(message);
            }
            if (typeof showLogMessage === 'function') {
                showLogMessage(message);
            }
        }
    });
}
</script>

<style>
[data-theme="dark"] {
    --base-hue: 260;
    --base-chroma: 0.03;
    --danger-base: 15;
    --base-hue-1: 20;
    --base-hue-2: 200;
    --base-hue-3: 135;
    --base-hue-4: 80;
    --base-hue-5: 270;
    --base-hue-6: 170;
    --base-hue-7: 340;
    --l: 85%;
    --c: 0.25;
    --radius: 16px;
    --border-light: 1px solid oklch(60% 0.05 var(--base-hue) / 0.3);
    --border-strong: 1px solid oklch(70% 0.1 var(--base-hue) / 0.6);
    --bg-body: oklch(40% var(--base-chroma) var(--base-hue) / 90%);
    --bg-container: oklch(30% var(--base-chroma) var(--base-hue));
    --text-primary: oklch(95% 0 0);
    --accent-color: oklch(70% 0.2 calc(var(--base-hue) + 0));
    --accent-secondary: oklch(68% 0.22 calc(var(--base-hue) + 180));
    --accent-tertiary: oklch(72% 0.18 calc(var(--base-hue) + 120));
    --card-bg: oklch(25% var(--base-chroma) var(--base-hue));
    --header-bg: oklch(35% var(--base-chroma) var(--base-hue));
    --border-color: oklch(40% var(--base-chroma) var(--base-hue));
    --btn-primary-bg: oklch(50% 0.15 var(--base-hue));
    --btn-success-bg: oklch(50% 0.2 240);
    --nav-btn-color: oklch(95% 0 0 / 80%);
    --hover-tips-color: oklch(95% 0 0 / 80%);
    --playlist-text: oklch(95% 0 0);
    --text-secondary: oklch(75% 0 0);
    --item-border: 3px solid oklch(40% var(--base-chroma) var(--base-hue));
    --item-hover-bg: color-mix(in oklch, var(--btn-primary-bg), white 10%);
    --item-hover-shadow: 0 2px 8px oklch(var(--base-hue) 0.2 0.2 / 0.3);
    --drag-over-bg: oklch(30% var(--base-chroma) var(--base-hue) / 0.2);
    --drag-over-shadow: 0 0 20px oklch(var(--base-hue) 0.15 0 / 0.25);
    --file-list-bg: oklch(25% var(--base-chroma) var(--base-hue) / 0.3);
    --file-list-border: oklch(35% var(--base-chroma) var(--base-hue) / 0.4);
    --danger-color: oklch(65% 0.25 var(--danger-base));
    --danger-hover: oklch(75% 0.3 var(--danger-base));
    --btn-info-bg: oklch(50% 0.2 220);
    --btn-info-hover: color-mix(in oklch, var(--btn-info-bg), white 10%);
    --btn-warning-bg: oklch(70% 0.18 80);
    --btn-warning-hover: color-mix(in oklch, var(--btn-warning-bg), white 10%);
    --sunset-bg: oklch(40% var(--base-chroma) var(--base-hue) / 90%);
    --color-accent: oklch(55% 0.3 240);
    --ocean-bg: oklch(45% 0.3 calc(var(--base-hue) + 220));
    --forest-bg: oklch(40% 0.3 calc(var(--base-hue) + 140));
    --rose-bg: oklch(45% 0.3 calc(var(--base-hue) + 350));
    --lavender-bg: oklch(43% 0.3 calc(var(--base-hue) + 270));
    --sand-bg: oklch(42% 0.3 calc(var(--base-hue) + 60));
}

[data-theme="light"] {
    --base-hue: 200;
    --base-chroma: 0.01;
    --l: 60%;
    --c: 0.25;
    --radius: 16px;
    --border-light: 1px solid oklch(90% 0.02 var(--base-hue) / 0.4);
    --border-strong: 1px solid oklch(75% 0.05 var(--base-hue) / 0.8);
    --bg-body: oklch(95% var(--base-chroma) var(--base-hue) / 90%);
    --bg-container: oklch(99% var(--base-chroma) var(--base-hue));
    --text-primary: oklch(25% var(--base-chroma) var(--base-hue));
    --accent-color: oklch(60% 0.2 calc(var(--base-hue) + 60));
    --accent-secondary: oklch(58% 0.25 calc(var(--base-hue) + 180));
    --accent-tertiary: oklch(62% 0.2 calc(var(--base-hue) + 120));
    --card-bg: oklch(96% var(--base-chroma) var(--base-hue));
    --header-bg: oklch(88% var(--base-chroma) var(--base-hue));
    --border-color: oklch(85% var(--base-chroma) var(--base-hue));
    --btn-primary-bg: oklch(55% 0.3 var(--base-hue));
    --btn-success-bg: oklch(70% 0.2 240);
    --nav-btn-color: oklch(70% 0.2 calc(var(--base-hue) + 60));
    --playlist-text: oklch(25% 0 0);
    --text-secondary: oklch(40% 0 0);
    --item-border: 3px solid oklch(85% var(--base-chroma) var(--base-hue));
    --item-hover-bg: color-mix(in oklch, var(--accent-color), white 20%);
    --item-hover-shadow: 0 2px 12px oklch(var(--base-hue) 0.15 0.5 / 0.2);
    --drag-over-bg: oklch(90% var(--base-chroma) var(--base-hue) / 0.3);
    --drag-over-shadow: 0 0 25px oklch(var(--base-hue) 0.1 0 / 0.15);
    --file-list-bg: oklch(95% var(--base-chroma) var(--base-hue) / 0.4);
    --file-list-border: oklch(85% var(--base-chroma) var(--base-hue) / 0.6);
    --danger-color: oklch(50% 0.3 var(--danger-base));
    --danger-hover: oklch(40% 0.35 var(--danger-base));
    --btn-info-bg: oklch(55% 0.3 220);
    --btn-info-hover: color-mix(in oklch, var(--btn-info-bg), black 10%);
    --btn-warning-bg: oklch(55% 0.22 80);
    --btn-warning-hover: color-mix(in oklch, var(--btn-warning-bg), black 15%);
    --sunset-bg: oklch(50% var(--base-chroma) var(--base-hue) / 90%);
    --color-accent: oklch(55% 0.3 220);
    --ocean-bg: oklch(50% 0.3 calc(var(--base-hue) + 220));
    --forest-bg: oklch(50% 0.3 calc(var(--base-hue) + 140));
    --rose-bg: oklch(50% 0.3 calc(var(--base-hue) + 350));
    --lavender-bg: oklch(50% 0.3 calc(var(--base-hue) + 270));
    --sand-bg: oklch(50% 0.3 calc(var(--base-hue) + 60));
}

@font-face {
    font-family: 'NotoColorEmojiFlags';
    src: url('/luci-static/spectra/fonts/NotoColorEmoji-flagsonly-CWWDk9km.ttf') format('truetype');
}

@font-face {
    font-display: swap;
    font-family: 'Fredoka One';
    font-style: normal;
    font-weight: 400;
    src: url('/luci-static/spectra/fonts/fredoka-v16-latin-regular.woff2') format('woff2');
}

@font-face {
    font-display: swap;
    font-family: 'DM Serif Display';
    font-style: normal;
    font-weight: 400;
    src: url('/luci-static/spectra/fonts/dm-serif-display-v15-latin-regular.woff2') format('woff2');
}

@font-face {
    font-display: swap;
    font-family: 'Noto Serif SC';
    font-style: normal;
    font-weight: 400;
    src: url('/luci-static/spectra/fonts/noto-serif-sc-v31-latin-regular.woff2') format('woff2');
}

@font-face {
    font-display: swap;
    font-family: 'Comic Neue';
    font-style: normal;
    font-weight: 400;
    src: url('/luci-static/spectra/fonts/comic-neue-v8-latin-regular.woff2') format('woff2');
}

@font-face {
    font-display: swap;
    font-family: 'Noto Sans';
    font-style: normal;
    font-weight: 400;
    src: url('/luci-static/spectra/fonts/noto-sans-v39-regular.woff2') format('woff2');
}

@font-face {
    font-family: 'Cinzel Decorative';
    font-style: normal;
    font-weight: 700;
    src: url('/luci-static/spectra/fonts/cinzel-decorative-v17-latin-700.woff2') format('woff2');
}

.custom-tooltip {
    position: fixed;
    z-index: 2147483647 !important;
    padding: 6px 12px;
    font-size: 0.8rem;
    line-height: 1.4;
    background-color: color-mix(in oklch, var(--accent-color), transparent 10%);
    color: #fff;
    border-radius: 12px;
    box-shadow: 0 4px 12px oklch(0% 0 0 / 0.15);
    white-space: nowrap;
    pointer-events: none;
    opacity: 0;
    user-select: none;
    -webkit-user-select: none;
    transform: translateY(5px);
    transition: opacity 0.2s ease, transform 0.2s ease;
    backdrop-filter: var(--glass-blur, blur(10px));
    border: var(--glass-border);
    transition: opacity 0.2s ease, transform 0.2s ease;
}

.custom-tooltip.active {
    opacity: 1;
    transform: translateY(0);
}

.custom-tooltip::after {
    content: '';
    position: absolute;
    width: 0;
    height: 0;
    border-style: solid;
}

.custom-tooltip[data-placement="top"]::after {
    bottom: -6px;
    left: 50%;
    transform: translateX(-50%);
    border-width: 6px 6px 0 6px;
    border-color: color-mix(in oklch, var(--accent-color), transparent 10%)  transparent transparent transparent;
}

.custom-tooltip[data-placement="bottom"]::after {
    top: -6px;
    left: 50%;
    transform: translateX(-50%);
    border-width: 0 6px 6px 6px;
    border-color: transparent transparent color-mix(in oklch, var(--accent-color), transparent 10%)  transparent;
}

[data-theme="dark"] .custom-tooltip {
    background-color: color-mix(in oklch, var(--accent-tertiary), transparent 10%);
}

[data-theme="dark"] .custom-tooltip[data-placement="top"]::after {
    border-color: color-mix(in oklch, var(--accent-tertiary), transparent 10%)  transparent transparent transparent;
}

[data-theme="dark"] .custom-tooltip[data-placement="bottom"]::after {
    border-color: transparent transparent color-mix(in oklch, var(--accent-tertiary), transparent 10%)  transparent;
}

@keyframes tooltip-fade-in {
    from {
        opacity: 0;
        transform: translateY(5px) scale(0.95);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

</style>
    <link href="/luci-static/spectra/css/bootstrap-icons.css" rel="stylesheet">
    <link href="/luci-static/spectra/css/all.min.css" rel="stylesheet">
    <link href="/luci-static/spectra/css/bootstrap.min.css" rel="stylesheet">
    <link href="/luci-static/spectra/css/weather-icons.min.css" rel="stylesheet">
    <script src="/luci-static/spectra/js/bootstrap.bundle.min.js"></script>
    <script src="/luci-static/spectra/js/interact.min.js"></script>
    <script src="/luci-static/spectra/js/Sortable.min.js"></script>
    <script src="/luci-static/spectra/js/jquery.min.js"></script>

<div class="modal fade" id="confirmModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" style="z-index: 200000;">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" data-translate="confirm_title">Confirm Action</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="confirmModalMessage"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-translate="cancel">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmModalYes" data-translate="confirm">Confirm</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    window.showConfirmation = function(message, onConfirm) {
        const decodedMessage = decodeURIComponent(message);

        document.getElementById('confirmModalMessage').innerText = decodedMessage;

        const oldBtn = document.getElementById('confirmModalYes');
        const newBtn = oldBtn.cloneNode(true);
        oldBtn.parentNode.replaceChild(newBtn, oldBtn);

        newBtn.addEventListener('click', () => {
            const modal = bootstrap.Modal.getInstance(document.getElementById('confirmModal'));
            modal.hide();
            if (typeof onConfirm === 'function') onConfirm();
        });

        const modal = new bootstrap.Modal(document.getElementById('confirmModal'));
        modal.show();
    };

    window.handleDeleteConfirmation = function(file) {
        const decodedFile = decodeURIComponent(file); 
        const confirmMessage = (translations['confirm_delete_file'] || 'Are you sure you want to delete file %s?').replace('%s', decodedFile);
        showConfirmation(confirmMessage, () => {
            fetch(`?delete=${file}`)
                .then(response => {
                    if (response.ok) {
                        const successMsg = (translations['delete_success'] || 'Successfully deleted: %s').replace('%s', decodedFile);
                        showLogMessage(successMsg);
                        speakMessage(successMsg);
                        setTimeout(() => window.location.reload(), 9000); 
                    } else {
                        const errorMsg = (translations['delete_failure'] || 'Failed to delete: %s').replace('%s', decodedFile);
                        showLogMessage(errorMsg);
                        speakMessage(errorMsg);
                    }
                })
                .catch(() => { /*  */ });
        });
    };
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
  interact('.modal-dialog.draggable').draggable({
    allowFrom: '.modal-header',
    modifiers: [
      interact.modifiers.restrictRect({
        restriction: 'parent', 
        endOnly: true
      })
    ],
    listeners: {
      start(event) {
        event.target.style.transition = 'none';
        event.target.classList.add('dragging');
      },
      move(event) {
        const target = event.target;
        const x = (parseFloat(target.dataset.x) || 0) + event.dx;
        const y = (parseFloat(target.dataset.y) || 0) + event.dy;

        target.style.transform = `translate(${x}px, ${y}px)`;
        target.dataset.x = x;
        target.dataset.y = y;
      },
      end(event) {
        event.target.style.transition = '';
        event.target.classList.remove('dragging');
      }
    }
  });

  document.querySelectorAll('.modal').forEach(modal => {
    const dialog = modal.querySelector('.modal-dialog');
    dialog.classList.add('draggable');

    modal.addEventListener('show.bs.modal', () => {
      dialog.style.transform = ''; 
      dialog.dataset.x = 0;
      dialog.dataset.y = 0;
    });
  });
});

function applyFontFamily() {
    const savedFont = localStorage.getItem('selectedFont') || 'default';
    
    const fontMap = {
        'default': "-apple-system, BlinkMacSystemFont, sans-serif",
        'fredoka': "'Fredoka One', cursive",
        'dmserif': "'DM Serif Display', serif",
        'notoserif': "'Noto Serif SC', serif",
        'comicneue': "'Comic Neue', cursive",
        'notosans': "'Noto Sans', sans-serif",
        'cinzeldecorative': "'Cinzel Decorative', cursive"
    };
    
    const fontFamily = fontMap[savedFont] || fontMap.default;
    document.documentElement.style.setProperty('--font-family', fontFamily);
}

document.addEventListener('DOMContentLoaded', applyFontFamily);

window.addEventListener('storage', function(e) {
    if (e.key === 'selectedFont') applyFontFamily();
});
</script>

<script>
window.currentHue = 260;
window.currentChroma = 0.25;
window.currentLightness = 30;
window.recentColors = [];

window.debounce = function(func, wait, immediate) {
let timeout;
return function executedFunction(...args) {
    const later = () => {
        timeout = null;
        if (!immediate) func(...args);
    };
    const callNow = immediate && !timeout;
    clearTimeout(timeout);
    timeout = setTimeout(later, wait);
    if (callNow) func(...args);
};
};

window.hexToRgb = function(hex) {
const fullHex = hex.replace(/^#?([a-f\d])([a-f\d])([a-f\d])$/i, 
(_, r, g, b) => `#${r}${r}${g}${g}${b}${b}`);
const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(fullHex);
return result ? {
    r: parseInt(result[1], 16),
    g: parseInt(result[2], 16),
    b: parseInt(result[3], 16)
} : { r: 0, g: 0, b: 0 };
};

window.rgbToLinear = function(c) {
const normalized = c / 255;
return normalized <= 0.04045 
    ? normalized / 12.92 
    : Math.pow((normalized + 0.055) / 1.055, 2.4);
};

window.rgbToOklch = function(r, g, b) {
const [lr, lg, lb] = [r, g, b].map(rgbToLinear);
const l = 0.4122214708 * lr + 0.5363325363 * lg + 0.0514459929 * lb;
const m = 0.2119034982 * lr + 0.6806995451 * lg + 0.1073969566 * lb;
const s = 0.0883024619 * lr + 0.2817188376 * lg + 0.6299787005 * lb;
const l_ = Math.cbrt(l);
const m_ = Math.cbrt(m);
const s_ = Math.cbrt(s);
const L = 0.2104542553 * l_ + 0.7936177850 * m_ - 0.0040720468 * s_;
const a = 1.9779984951 * l_ - 2.4285922050 * m_ + 0.4505937099 * s_;
const b_ = 0.0259040371 * l_ + 0.7827717662 * m_ - 0.8086757660 * s_;
const c = Math.sqrt(a ** 2 + b_ ** 2);
let h = Math.atan2(b_, a) * 180 / Math.PI;
h = h >= 0 ? h : h + 360;
return { l: L * 100, c: c, h: h };
};

window.hexToOklch = function(hex) {
const { r, g, b } = hexToRgb(hex);
return rgbToOklch(r, g, b);
};

window.oklchToHex = function(h, c, l = 50) {
const L = l / 100;
const a = c * Math.cos(h * Math.PI / 180);
const b = c * Math.sin(h * Math.PI / 180);
const l_ = L + 0.3963377774 * a + 0.2158037573 * b;
const m_ = L - 0.1055613458 * a - 0.0638541728 * b;
const s_ = L - 0.0894841775 * a - 1.2914855480 * b;
const [lr, lg, lb] = [l_, m_, s_].map(v => v ** 3);
const r = 4.0767416621 * lr - 3.3077115913 * lg + 0.2309699292 * lb;
const g = -1.2684380046 * lr + 2.6097574011 * lg - 0.3413193965 * lb;
const bLinear = -0.0041960863 * lr - 0.7034186147 * lg + 1.7076147010 * lb;
const toSRGB = (v) => {
    v = Math.min(Math.max(v, 0), 1);
    return v > 0.0031308 
        ? 1.055 * (v ** (1/2.4)) - 0.055 
        : 12.92 * v;
};
const [R, G, B] = [r, g, bLinear].map(v => Math.round(toSRGB(v) * 255));
return `#${[R, G, B].map(x => x.toString(16).padStart(2, '0')).join('')}`.toUpperCase();
};

window.calculateContrastRatio = function(hexColor) {
const rgb = window.hexToRgb(hexColor);

const getLuminance = (c) => {
    c = c / 255;
    return c <= 0.03928 ? c / 12.92 : Math.pow((c + 0.055) / 1.055, 2.4);
};

const r = getLuminance(rgb.r);
const g = getLuminance(rgb.g);
const b = getLuminance(rgb.b);
const luminance = 0.2126 * r + 0.7152 * g + 0.0722 * b;
const contrastWithBlack = (luminance + 0.05) / 0.05;
const contrastWithWhite = 1.05 / (luminance + 0.05);
return Math.max(contrastWithBlack, contrastWithWhite);
};

window.updateAllCSSVariables = function() {
const root = document.documentElement;

root.style.setProperty('--base-hue', currentHue);
root.style.setProperty('--base-chroma', currentChroma);
root.style.setProperty('--base-hue-1', currentHue + 20);
root.style.setProperty('--base-hue-2', currentHue + 200);
root.style.setProperty('--base-hue-3', currentHue + 135);
root.style.setProperty('--base-hue-4', currentHue + 80);
root.style.setProperty('--base-hue-5', currentHue + 270);
root.style.setProperty('--base-hue-6', currentHue + 170);
root.style.setProperty('--base-hue-7', currentHue + 340);

const isLight = currentLightness > 60;
const theme = isLight ? 'light' : 'dark';
root.setAttribute('data-theme', theme);
//console.log('Updated theme to:', theme);
};

window.applyColorSettings = function(showMessage = false) {
const settings = {
    hue: currentHue,
    chroma: currentChroma,
    lightness: currentLightness,
    recentColors: recentColors || []
};
localStorage.setItem('appColorSettings', JSON.stringify(settings));

updateAllCSSVariables();
updateTextPrimary(currentLightness);
updateThemeIcon(currentLightness);

const colorPicker = document.getElementById('colorPicker');
if (colorPicker) {
    const hexColor = oklchToHex(currentHue, currentChroma, currentLightness);
    colorPicker.value = hexColor;
}

if (showMessage) {
    const hexColor = oklchToHex(currentHue, currentChroma, currentLightness);
    const translations = languageTranslations[currentLang] || languageTranslations['zh'];
    const successMsg = translations['backgroundColorApplied'].replace('%s', hexColor);

    //console.log(successMsg);

    if (typeof showLogMessage === 'function') {
        showLogMessage(successMsg);
    }
    if (colorVoiceEnabled) {
        speakMessage(successMsg);
    }
}
};

window.updateTextPrimary = function(currentL) {
const textL = currentL > 60 ? 20 : 95;
document.documentElement.style.setProperty('--text-primary', `oklch(${textL}% 0 0)`);
};

window.addToRecentColors = function(color) {
recentColors = recentColors.filter(c => c !== color);
recentColors.unshift(color);

if (recentColors.length > 10) {
    recentColors.pop();
}

localStorage.setItem('appColorSettings', JSON.stringify({
    recentColors,
    hue: currentHue,
    chroma: currentChroma,
    lightness: currentLightness
}));
};

window.initColorSettings = function() {
const savedSettings = localStorage.getItem('appColorSettings');
if (savedSettings) {
    const settings = JSON.parse(savedSettings);
    recentColors = settings.recentColors || [];
    currentHue = settings.hue || 347.01;
    currentChroma = settings.chroma || 0.061;
    currentLightness = settings.lightness || 99;
    const colorPicker = document.getElementById('colorPicker');
    if (colorPicker) {
        colorPicker.value = oklchToHex(currentHue, currentChroma, currentLightness);
    }
    applyColorSettings(false);
} else {
    currentHue = 347.01;
    currentChroma = 0.061;
    currentLightness = 99;
    const colorPicker = document.getElementById('colorPicker');
    if (colorPicker) {
        colorPicker.value = oklchToHex(currentHue, currentChroma, currentLightness);
    }
    applyColorSettings(false);
}
if (typeof updateUIText === 'function') {
    updateUIText();
}
};

window.updateThemeIcon = function(lightness) {
const colorPickerBtn = document.getElementById('colorPickerBtn');
if (!colorPickerBtn) return;

const isLight = lightness > 60;
const icon = colorPickerBtn.querySelector('i');

if (isLight) {
    icon.className = 'fas fa-sun';
} else {
    icon.className = 'bi bi-moon-stars-fill';
}
};

window.handleColorChange = window.debounce((color) => {
//console.log('Color picker selected:', color);

const { h, c, l } = window.hexToOklch(color);
window.currentHue = h;
window.currentChroma = c;
window.currentLightness = l;

window.applyColorSettings(true);
window.addToRecentColors(color);
}, 150);

const picker = document.getElementById("colorPicker");
if (picker) {
picker.addEventListener('input', (event) => {
    const color = event.target.value;
    window.handleColorChange(color);
});
}

window.initColorSettings();

function setGlassEffect(element, intensity = 'medium') {
const intensities = {
    light: { opacity: 0.7, blur: '12px' },
    medium: { opacity: 0.85, blur: '20px' },
    heavy: { opacity: 0.9, blur: '30px' }
};

const config = intensities[intensity] || intensities.medium;

element.style.setProperty('--glass-opacity', config.opacity);
element.style.setProperty('--glass-blur', `blur(${config.blur})`);
}

function applyGlassToElement(selector, intensity = 'medium') {
const elements = document.querySelectorAll(selector);
elements.forEach(element => {
    element.classList.add('glass-effect');
    setGlassEffect(element, intensity);
});
}

function initGlassEffects() {
applyGlassToElement('.card', 'medium');
applyGlassToElement('header', 'light');
applyGlassToElement('.sidebar', 'heavy');
applyGlassToElement('button, .btn', 'light');
}

document.addEventListener('DOMContentLoaded', function() {
initGlassEffects();
});

function checkColorChange() {
const saved = localStorage.getItem('appColorSettings');

if (saved) {
    const settings = JSON.parse(saved);

    const rootStyles = getComputedStyle(document.documentElement);
    const currentHueValue = parseFloat(rootStyles.getPropertyValue('--base-hue').trim()) || 0;
    const currentChromaValue = parseFloat(rootStyles.getPropertyValue('--base-chroma').trim()) || 0;

    if (Math.abs(settings.hue - currentHueValue) > 1 || Math.abs(settings.chroma - currentChromaValue) > 0.01) {
        document.documentElement.style.setProperty('--base-hue', settings.hue);
        document.documentElement.style.setProperty('--base-chroma', settings.chroma);

        const textL = settings.lightness > 60 ? 20 : 95;
        document.documentElement.style.setProperty('--text-primary', `oklch(${textL}% 0 0)`);

        const theme = settings.lightness > 60 ? 'light' : 'dark';
        document.documentElement.setAttribute('data-theme', theme);
    }
}
}

setInterval(checkColorChange, 1000);
</script>

<style>
body {
    margin: 0;
    color: var(--text-primary);
    background-attachment: fixed;
}

.log-box {
    position: fixed;
    left: 20px;
    padding: 12px 16px;
    background: var(--btn-success-bg);
    color: white;
    border-radius: 8px;
    z-index: 9999;
    max-width: 320px;
    font-size: 15px;
    word-wrap: break-word;
    line-height: 1.5;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.15);
    border: 1px solid rgba(255, 255, 255, 0.15);
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(2px);
    transform: translateY(0);
    opacity: 0;
    animation: scrollUp 12s cubic-bezier(0.25, 0.46, 0.45, 0.94) forwards;
    display: inline-block;
    margin-bottom: 10px;
    transition: transform 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}

@keyframes scrollUp {
    0% {
        top: 90%;
        opacity: 0;
    }
    20% {
        opacity: 1;
    }
    80% {
        top: 50%;
        opacity: 1;
    }
    100% {
        top: 45%;
        opacity: 0;
    }
}

.log-box.exiting {
    animation: fadeOut 0.3s forwards;
}

.log-content {
    padding: 6px 20px 6px 8px;
    color: white;
}

.close-btn {
    position: absolute;
    top: 6px;
    right: 10px;
    background: transparent;
    border: none;
    color: inherit;
    cursor: pointer;
    font-size: 20px;
    line-height: 1;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.2s;
}

.log-box:hover .close-btn {
    opacity: 0.7;
    pointer-events: auto;
}

.log-box:hover .close-btn:hover {
    opacity: 1;
}

@keyframes fadeOut {
    to { 
        opacity: 0;
        transform: translateY(-20px) scale(0.95);
    }
}

.log-icon {
    display: inline-block;
    width: 20px;
    height: 20px;
    margin-right: 3px;
    vertical-align: middle;
}

.log-box.error { background: linear-gradient(145deg, #ff4444, #cc0000); }
.log-box.warning { background: linear-gradient(145deg, #ffc107, #ffab00); }
.log-box.info { background: linear-gradient(145deg, #2196F3, #1976D2); }

@media (max-width: 768px) {
    .log-box {
        left: 10px;
        right: 10px;
        max-width: none;
        font-size: 14px;
    }
}

.list-group-item {
    cursor: pointer;
    color: var(--text-primary);
    background: var(--bg-container);
    border: 1px solid var(--border-color);
    transition: background 0.3s ease;
}

.list-group-item:hover {
    background: var(--item-hover-bg);
    color: white !important;
}

.list-group-item:hover .text-muted,
.list-group-item:hover .text-truncate {
    color: white !important;
}

.list-group-item.active {
    background: var(--accent-color);
    color: white;
    border: 1px solid var(--accent-color);
}

.list-group-item.active .badge,
.list-group-item.active .text-truncate,
.list-group-item.active small,
.list-group-item.active i {
    color: white !important;
}

.list-group-item .delete-item {
    cursor: pointer;
}

.modal-xl {
    max-width: 60% !important;  
    width: 90% !important;
}

@media (max-width: 768px) {
    .modal-xl {
        max-width: 95% !important;
        width: 95% !important;
        margin: 1rem auto !important; 
    }
}

@media (max-width: 576px) {
    .modal-xl {
        max-width: 100% !important;
        width: 100% !important;
        margin: 0.5rem auto !important;
    }
}
</style>

<script>
window.showLogMessage = (function() {
    const bgColors = [
        'var(--ocean-bg)',
        'var(--forest-bg)',
        'var(--lavender-bg)',
        'var(--sand-bg)'
    ];
    
    let currentIndex = 0;
    const activeLogs = new Set();
    const BASE_OFFSET = 20;
    const MARGIN = 10;

    function createIcon(type) {
        const icons = {
            error: 'M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z',
            warning: 'M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z',
            info: 'M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z'
        };
    
        const svg = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="#fff" d="${icons[type] || icons.info}"/></svg>`;
    
        return `data:image/svg+xml;base64,${btoa(svg)}`;
    }

    function updatePositions() {
        let verticalPos = BASE_OFFSET;
        activeLogs.forEach(log => {
            log.style.transform = `translateY(${verticalPos}px)`;
            verticalPos += log.offsetHeight + MARGIN;
        });
    }

    return function(message, type = '') {
        const logBox = document.createElement('div');
        logBox.className = `log-box ${type}`;
        
        if (!type) {
            logBox.style.background = bgColors[currentIndex];
            currentIndex = (currentIndex + 1) % bgColors.length;
        }

        logBox.innerHTML = `
            <div class="log-content">
                <span class="log-icon" style="background-image:url('${createIcon(type)}')"></span>
                ${decodeURIComponent(message)}
                <button class="close-btn">&times;</button>
            </div>
        `;

        logBox.querySelector('.close-btn').onclick = () => {
            logBox.classList.add('exiting');
            setTimeout(() => logBox.remove(), 300);
        };

        logBox.addEventListener('mouseenter', () => 
            logBox.style.animationPlayState = 'paused');
        logBox.addEventListener('mouseleave', () => 
            logBox.style.animationPlayState = 'running');

        document.body.appendChild(logBox);
        activeLogs.add(logBox);
        
        requestAnimationFrame(() => {
            logBox.classList.add('active');
            updatePositions();
        });

        setTimeout(() => {
            logBox.classList.add('exiting');
            setTimeout(() => {
                logBox.remove();
                activeLogs.delete(logBox);
                updatePositions();
            }, 300);
        }, 12000);
    };
})();
</script>