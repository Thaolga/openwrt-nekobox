<?php
include './cfg.php';

$str_cfg = substr($selected_config, strlen("$neko_dir/config") + 1);
$_IMG = '/luci-static/ssr/';
$singbox_bin = '/usr/bin/sing-box';
$singbox_log = '/var/log/singbox_log.txt';
$singbox_config_dir = '/etc/neko/config';
$log = '/etc/neko/tmp/log.txt';
$start_script_path = '/etc/neko/core/start.sh';

$log_dir = dirname($log);
if (!file_exists($log_dir)) {
    mkdir($log_dir, 0755, true);
}

$start_script_template = <<<'EOF'
#!/bin/bash

SINGBOX_LOG="%s"
CONFIG_FILE="%s"
SINGBOX_BIN="%s"

mkdir -p "$(dirname "$SINGBOX_LOG")"
touch "$SINGBOX_LOG"
chmod 644 "$SINGBOX_LOG"

exec >> "$SINGBOX_LOG" 2>&1

echo "[$(date)] Starting Sing-box with config: $CONFIG_FILE"

echo "Restarting firewall..."
/etc/init.d/firewall restart
sleep 2

if command -v fw4 > /dev/null; then
    echo "FW4 Detected."
    echo "Starting nftables."

    nft flush ruleset
    
    nft -f - <<'NFTABLES'
flush ruleset

table inet singbox {
  set local_ipv4 {
    type ipv4_addr
    flags interval
    elements = {
      10.0.0.0/8,
      127.0.0.0/8,
      169.254.0.0/16,
      172.16.0.0/12,
      192.168.0.0/16,
      240.0.0.0/4
    }
  }

  set local_ipv6 {
    type ipv6_addr
    flags interval
    elements = {
      ::ffff:0.0.0.0/96,
      64:ff9b::/96,
      100::/64,
      2001::/32,
      2001:10::/28,
      2001:20::/28,
      2001:db8::/32,
      2002::/16,
      fc00::/7,
      fe80::/10
    }
  }

  chain singbox-tproxy {
    fib daddr type { unspec, local, anycast, multicast } return
    ip daddr @local_ipv4 return
    ip6 daddr @local_ipv6 return
    udp dport { 123 } return
    meta l4proto { tcp, udp } meta mark set 1 tproxy to :9888 accept
  }

  chain singbox-mark {
    fib daddr type { unspec, local, anycast, multicast } return
    ip daddr @local_ipv4 return
    ip6 daddr @local_ipv6 return
    udp dport { 123 } return
    meta mark set 1
  }

  chain mangle-output {
    type route hook output priority mangle; policy accept;
    meta l4proto { tcp, udp } skgid != 1 ct direction original goto singbox-mark
  }

  chain mangle-prerouting {
    type filter hook prerouting priority mangle; policy accept;
    iifname { lo, eth0 } meta l4proto { tcp, udp } ct direction original goto singbox-tproxy
  }
}
NFTABLES

elif command -v fw3 > /dev/null; then
    echo "FW3 Detected."
    echo "Starting iptables."

    iptables -t mangle -F
    iptables -t mangle -X
    iptables -t mangle -N singbox-mark
    iptables -t mangle -A singbox-mark -m addrtype --dst-type UNSPEC,LOCAL,ANYCAST,MULTICAST -j RETURN
    iptables -t mangle -A singbox-mark -d 10.0.0.0/8 -j RETURN
    iptables -t mangle -A singbox-mark -d 127.0.0.0/8 -j RETURN
    iptables -t mangle -A singbox-mark -d 169.254.0.0/16 -j RETURN
    iptables -t mangle -A singbox-mark -d 172.16.0.0/12 -j RETURN
    iptables -t mangle -A singbox-mark -d 192.168.0.0/16 -j RETURN
    iptables -t mangle -A singbox-mark -d 240.0.0.0/4 -j RETURN
    iptables -t mangle -A singbox-mark -p udp --dport 123 -j RETURN
    iptables -t mangle -A singbox-mark -j MARK --set-mark 1

    iptables -t mangle -N singbox-tproxy
    iptables -t mangle -A singbox-tproxy -m addrtype --dst-type UNSPEC,LOCAL,ANYCAST,MULTICAST -j RETURN
    iptables -t mangle -A singbox-tproxy -d 10.0.0.0/8 -j RETURN
    iptables -t mangle -A singbox-tproxy -d 127.0.0.0/8 -j RETURN
    iptables -t mangle -A singbox-tproxy -d 169.254.0.0/16 -j RETURN
    iptables -t mangle -A singbox-tproxy -d 172.16.0.0/12 -j RETURN
    iptables -t mangle -A singbox-tproxy -d 192.168.0.0/16 -j RETURN
    iptables -t mangle -A singbox-tproxy -d 240.0.0.0/4 -j RETURN
    iptables -t mangle -A singbox-tproxy -p udp --dport 123 -j RETURN
    iptables -t mangle -A singbox-tproxy -p tcp -j TPROXY --tproxy-mark 0x1/0x1 --on-port 9888
    iptables -t mangle -A singbox-tproxy -p udp -j TPROXY --tproxy-mark 0x1/0x1 --on-port 9888

    iptables -t mangle -A OUTPUT -p tcp -m cgroup ! --cgroup 1 -j singbox-mark
    iptables -t mangle -A OUTPUT -p udp -m cgroup ! --cgroup 1 -j singbox-mark
    iptables -t mangle -A PREROUTING -i lo -p tcp -j singbox-tproxy
    iptables -t mangle -A PREROUTING -i lo -p udp -j singbox-tproxy
    iptables -t mangle -A PREROUTING -i eth0 -p tcp -j singbox-tproxy
    iptables -t mangle -A PREROUTING -i eth0 -p udp -j singbox-tproxy

    ip6tables -t mangle -N singbox-mark
    ip6tables -t mangle -A singbox-mark -m addrtype --dst-type UNSPEC,LOCAL,ANYCAST,MULTICAST -j RETURN
    ip6tables -t mangle -A singbox-mark -d ::ffff:0.0.0.0/96 -j RETURN
    ip6tables -t mangle -A singbox-mark -d 64:ff9b::/96 -j RETURN
    ip6tables -t mangle -A singbox-mark -d 100::/64 -j RETURN
    ip6tables -t mangle -A singbox-mark -d 2001::/32 -j RETURN
    ip6tables -t mangle -A singbox-mark -d 2001:10::/28 -j RETURN
    ip6tables -t mangle -A singbox-mark -d 2001:20::/28 -j RETURN
    ip6tables -t mangle -A singbox-mark -d 2001:db8::/32 -j RETURN
    ip6tables -t mangle -A singbox-mark -d 2002::/16 -j RETURN
    ip6tables -t mangle -A singbox-mark -d fc00::/7 -j RETURN
    ip6tables -t mangle -A singbox-mark -d fe80::/10 -j RETURN
    ip6tables -t mangle -A singbox-mark -p udp --dport 123 -j RETURN
    ip6tables -t mangle -A singbox-mark -j MARK --set-mark 1

    ip6tables -t mangle -N singbox-tproxy
    ip6tables -t mangle -A singbox-tproxy -m addrtype --dst-type UNSPEC,LOCAL,ANYCAST,MULTICAST -j RETURN
    ip6tables -t mangle -A singbox-tproxy -d ::ffff:0.0.0.0/96 -j RETURN
    ip6tables -t mangle -A singbox-tproxy -d 64:ff9b::/96 -j RETURN
    ip6tables -t mangle -A singbox-tproxy -d 100::/64 -j RETURN
    ip6tables -t mangle -A singbox-tproxy -d 2001::/32 -j RETURN
    ip6tables -t mangle -A singbox-tproxy -d 2001:10::/28 -j RETURN
    ip6tables -t mangle -A singbox-tproxy -d 2001:20::/28 -j RETURN
    ip6tables -t mangle -A singbox-tproxy -d 2001:db8::/32 -j RETURN
    ip6tables -t mangle -A singbox-tproxy -d 2002::/16 -j RETURN
    ip6tables -t mangle -A singbox-tproxy -d fc00::/7 -j RETURN
    ip6tables -t mangle -A singbox-tproxy -d fe80::/10 -j RETURN
    ip6tables -t mangle -A singbox-tproxy -p udp --dport 123 -j RETURN
    ip6tables -t mangle -A singbox-tproxy -p tcp -j TPROXY --tproxy-mark 0x1/0x1 --on-port 9888
    ip6tables -t mangle -A singbox-tproxy -p udp -j TPROXY --tproxy-mark 0x1/0x1 --on-port 9888

    ip6tables -t mangle -A OUTPUT -p tcp -m cgroup ! --cgroup 1 -j singbox-mark
    ip6tables -t mangle -A OUTPUT -p udp -m cgroup ! --cgroup 1 -j singbox-mark
    ip6tables -t mangle -A PREROUTING -i lo -p tcp -j singbox-tproxy
    ip6tables -t mangle -A PREROUTING -i lo -p udp -j singbox-tproxy
    ip6tables -t mangle -A PREROUTING -i eth0 -p tcp -j singbox-tproxy
    ip6tables -t mangle -A PREROUTING -i eth0 -p udp -j singbox-tproxy

else
    echo "Neither fw3 nor fw4 detected, unable to configure firewall rules."
    exit 1
fi

echo "Firewall rules applied successfully"
echo "Starting sing-box with config: $CONFIG_FILE"
exec "$SINGBOX_BIN" run -c "$CONFIG_FILE"
EOF;

function createStartScript($configFile) {
    global $start_script_template, $singbox_bin, $singbox_log;
    $script = sprintf($start_script_template, $singbox_log, $configFile, $singbox_bin);
    
    $dir = dirname('/etc/neko/core/start.sh');
    if (!file_exists($dir)) {
        mkdir($dir, 0755, true);
    }
    
    file_put_contents('/etc/neko/core/start.sh', $script);
    chmod('/etc/neko/core/start.sh', 0755);
    
    writeToLog("Created start script with config: $configFile");
    writeToLog("Singbox binary: $singbox_bin");
    writeToLog("Log file: $singbox_log");
}

function writeToLog($message) {
   global $log;
   $time = date('H:i:s');
   $logMessage = "[ $time ] $message\n";
   if (file_put_contents($log, $logMessage, FILE_APPEND) === false) {
       error_log("Failed to write to log file: $log");
   }
}

function rotateLogs($logFile, $maxSize = 1048576) {
   if (file_exists($logFile) && filesize($logFile) > $maxSize) {
       rename($logFile, $logFile . '.old');
       touch($logFile);
       chmod($logFile, 0644);
   }
}

function isSingboxRunning() {
   global $singbox_bin;
   $command = "pgrep -f " . escapeshellarg($singbox_bin);
   exec($command, $output);
   return !empty($output);
}

function isNekoBoxRunning() {
    global $neko_dir;
    $pid = trim(shell_exec("cat $neko_dir/tmp/neko.pid 2>/dev/null"));
    return !empty($pid) && file_exists("/proc/$pid");
}

function getSingboxPID() {
   global $singbox_bin;
   $command = "pgrep -f " . escapeshellarg($singbox_bin);
   exec($command, $output);
   return isset($output[0]) ? $output[0] : null;
}

function getRunningConfigFile() {
   global $singbox_bin;
   $command = "ps w | grep '$singbox_bin' | grep -v grep";
   exec($command, $output);
   foreach ($output as $line) {
       if (strpos($line, '-c') !== false) {
           $parts = explode('-c', $line);
           if (isset($parts[1])) {
               $configPath = trim(explode(' ', trim($parts[1]))[0]);
               return $configPath;
           }
       }
   }
   return null;
}

function getAvailableConfigFiles() {
   global $singbox_config_dir;
   return glob("$singbox_config_dir/*.json");
}

$availableConfigs = getAvailableConfigFiles();

writeToLog("Script started");

if(isset($_POST['neko'])){
   $dt = $_POST['neko'];
   writeToLog("Received neko action: $dt");
   if ($dt == 'start') {
       if (isSingboxRunning()) {
           writeToLog("Cannot start NekoBox: Sing-box is running");
       } else {
           shell_exec("$neko_dir/core/neko -s");
           writeToLog("NekoBox started successfully");
       }
   }
   if ($dt == 'disable') {
       shell_exec("$neko_dir/core/neko -k");
       writeToLog("NekoBox stopped");
   }
   if ($dt == 'restart') {
       if (isSingboxRunning()) {
           writeToLog("Cannot restart NekoBox: Sing-box is running");
       } else {
           shell_exec("$neko_dir/core/neko -r");
           writeToLog("NekoBox restarted successfully");
       }
   }
   if ($dt == 'clear') {
       shell_exec("echo \"Logs has been cleared...\" > $neko_dir/tmp/neko_log.txt");
       writeToLog("NekoBox logs cleared");
   }
   writeToLog("Neko action completed: $dt");
}

if (isset($_POST['singbox'])) {
   $action = $_POST['singbox'];
   $config_file = isset($_POST['config_file']) ? $_POST['config_file'] : '';
   
   writeToLog("Received singbox action: $action");
   writeToLog("Config file: $config_file");
   
   switch ($action) {
       case 'start':
           if (isNekoBoxRunning()) {
               writeToLog("Cannot start Sing-box: NekoBox is running");
           } else {
               writeToLog("Starting Sing-box");

               $singbox_version = trim(shell_exec("$singbox_bin version"));
               writeToLog("Sing-box version: $singbox_version");
               
               shell_exec("mkdir -p " . dirname($singbox_log));
               shell_exec("touch $singbox_log && chmod 644 $singbox_log");
               rotateLogs($singbox_log);
               
               createStartScript($config_file);
               $output = shell_exec("sh $start_script_path >> $singbox_log 2>&1 &");
               writeToLog("Shell output: " . ($output ?: "No output"));
               
               sleep(1);
               $pid = getSingboxPID();
               if ($pid) {
                   writeToLog("Sing-box Started successfully. PID: $pid");
               } else {
                   writeToLog("Failed to start Sing-box");
               }
           }
           break;
           
    case 'disable':
        writeToLog("Stopping Sing-box");
        $pid = getSingboxPID();
        if ($pid) {
            writeToLog("Killing Sing-box PID: $pid");
            shell_exec("kill $pid");
            if (file_exists('/usr/sbin/fw4')) {
                shell_exec("nft flush ruleset");
            } else {
                shell_exec("iptables -t mangle -F");
                shell_exec("iptables -t mangle -X");
        }
            shell_exec("/etc/init.d/firewall restart");
            writeToLog("Cleared firewall rules and restarted firewall");
            sleep(1);
            if (!isSingboxRunning()) {
                writeToLog("Sing-box has been stopped successfully");
            } else {
                writeToLog("Force killing Sing-box");
                shell_exec("kill -9 $pid");
                writeToLog("Sing-box has been force stopped");
            }
        } else {
            writeToLog("Sing-box is not running");
        }
        break;
           
       case 'restart':
           if (isNekoBoxRunning()) {
               writeToLog("Cannot restart Sing-box: NekoBox is running");
           } else {
               writeToLog("Restarting Sing-box");
               
               $pid = getSingboxPID();
               if ($pid) {
                   writeToLog("Killing Sing-box PID: $pid");
                   shell_exec("kill $pid");
                   sleep(1);
               }
               
               shell_exec("mkdir -p " . dirname($singbox_log));
               shell_exec("touch $singbox_log && chmod 644 $singbox_log");
               rotateLogs($singbox_log);
               
               createStartScript($config_file);
               shell_exec("sh $start_script_path >> $singbox_log 2>&1 &");
               
               sleep(1);
               $new_pid = getSingboxPID();
               if ($new_pid) {
                   writeToLog("Sing-box Restarted successfully. New PID: $new_pid");
               } else {
                   writeToLog("Failed to restart Sing-box");
               }
           }
           break;
   }
   
   sleep(2);
   
   $singbox_status = isSingboxRunning() ? '1' : '0';
   exec("uci set neko.cfg.singbox_enabled='$singbox_status'");
   exec("uci commit neko");
   writeToLog("Singbox status set to: $singbox_status");
}

if (isset($_POST['clear_singbox_log'])) {
   file_put_contents($singbox_log, '');
   writeToLog("Singbox log cleared");
}

if (isset($_POST['clear_plugin_log'])) {
    $plugin_log_file = "$neko_dir/tmp/log.txt";
    file_put_contents($plugin_log_file, '');
    writeToLog("NeKoBox log cleared");
}


$neko_status = exec("uci -q get neko.cfg.enabled");
$singbox_status = isSingboxRunning() ? '1' : '0';
exec("uci set neko.cfg.singbox_enabled='$singbox_status'");
exec("uci commit neko");

writeToLog("Final neko status: $neko_status");
writeToLog("Final singbox status: $singbox_status");

if ($singbox_status == '1') {
   $runningConfigFile = getRunningConfigFile();
   if ($runningConfigFile) {
       $str_cfg = htmlspecialchars(basename($runningConfigFile));
       writeToLog("Running config file: $str_cfg");
   } else {
       $str_cfg = 'Sing-box 配置文件：未找到运行中的配置文件';
       writeToLog("No running config file found");
   }
}

function readRecentLogLines($filePath, $lines = 1000) {
   if (!file_exists($filePath)) {
       return "日志文件不存在: $filePath";
   }
   if (!is_readable($filePath)) {
       return "无法读取日志文件: $filePath";
   }
   $command = "tail -n $lines " . escapeshellarg($filePath);
   $output = shell_exec($command);
   return $output ?: "日志为空";
}

function readLogFile($filePath) {
   if (file_exists($filePath)) {
       return nl2br(htmlspecialchars(readRecentLogLines($filePath, 1000), ENT_NOQUOTES));
   } else {
       return '日志文件不存在。';
   }
}

$neko_log_content = readLogFile("$neko_dir/tmp/neko_log.txt");
$singbox_log_content = readLogFile($singbox_log);
?>

<?php
$systemIP = $_SERVER['SERVER_ADDR'];
$dt=json_decode((shell_exec("ubus call system board")), true);
$devices=$dt['model'];

$kernelv = exec("cat /proc/sys/kernel/ostype"); 
$osrelease = exec("cat /proc/sys/kernel/osrelease"); 
$OSVer = $dt['release']['distribution'] . ' ' . $dt['release']['version']; 
$kernelParts = explode('.', $osrelease, 3);
$kernelv = 'Linux ' . 
           (isset($kernelParts[0]) ? $kernelParts[0] : '') . '.' . 
           (isset($kernelParts[1]) ? $kernelParts[1] : '') . '.' . 
           (isset($kernelParts[2]) ? $kernelParts[2] : '');
$kernelv = strstr($kernelv, '-', true) ?: $kernelv;
$fullOSInfo = $kernelv . ' ' . $OSVer;


$tmpramTotal=exec("cat /proc/meminfo | grep MemTotal | awk '{print $2}'");
$tmpramAvailable=exec("cat /proc/meminfo | grep MemAvailable | awk '{print $2}'");

$ramTotal=number_format(($tmpramTotal/1000),1);
$ramAvailable=number_format(($tmpramAvailable/1000),1);
$ramUsage=number_format((($tmpramTotal-$tmpramAvailable)/1000),1);

$raw_uptime = exec("cat /proc/uptime | awk '{print $1}'");
$days = floor($raw_uptime / 86400);
$hours = floor(($raw_uptime / 3600) % 24);
$minutes = floor(($raw_uptime / 60) % 60);
$seconds = $raw_uptime % 60;

$cpuLoad = shell_exec("cat /proc/loadavg");
$cpuLoad = explode(' ', $cpuLoad);
$cpuLoadAvg1Min = round($cpuLoad[0], 2);
$cpuLoadAvg5Min = round($cpuLoad[1], 2);
$cpuLoadAvg15Min = round($cpuLoad[2], 2);
?>

<!doctype html>
<html lang="en" data-bs-theme="<?php echo substr($neko_theme,0,-4) ?>">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Home - Neko</title>
    <link rel="icon" href="./assets/img/nekobox.png">
    <link href="./assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="./assets/css/custom.css" rel="stylesheet">
    <link href="./assets/theme/<?php echo $neko_theme ?>" rel="stylesheet">
    <script type="text/javascript" src="./assets/js/feather.min.js"></script>
    <script type="text/javascript" src="./assets/js/jquery-2.1.3.min.js"></script>
    <script type="text/javascript" src="./assets/js/neko.js"></script>
  </head>
  <body>

<div class="container-sm container-bg callout border border-3 rounded-4 col-11">
    <div class="row">
        <a href="#" class="col btn btn-lg">🏠 首页</a>
        <a href="./dashboard.php" class="col btn btn-lg">📊 面板</a>
        <a href="./configs.php" class="col btn btn-lg">⚙️ 配置</a>
        <a href="/nekobox/mon.php" class="col btn btn-lg d-flex align-items-center justify-content-center"></i>📦 订阅</a> 
        <a href="./settings.php" class="col btn btn-lg">🛠️ 设定</a>

    <div class="container-sm text-center col-8">
  <img src="./assets/img/nekobox.png">
<div id="version-info">
    <a id="version-link" href="https://github.com/Thaolga/openwrt-nekobox/releases" target="_blank">
        <img id="current-version" src="./assets/img/curent.svg" alt="Current Version" style="max-width: 100%; height: auto;" />
    </a>
</div>
</div>
<script>
$(document).ready(function() {
    $.ajax({
        url: 'check_update.php',
        method: 'GET',
        dataType: 'json',
        success: function(data) {
            if (data.hasUpdate) {
                $('#current-version').attr('src', 'https://raw.githubusercontent.com/Thaolga/neko/refs/heads/main/Latest.svg');
            }

            console.log('Current Version:', data.currentVersion);
            console.log('Latest Version:', data.latestVersion);
            console.log('Has Update:', data.hasUpdate);
        },
        error: function(jqXHR, textStatus, errorThrown) {
            //$('#version-info').text('Error fetching version information');
            console.error('AJAX Error:', textStatus, errorThrown);
        }
    });
});

</script>
 <h2 class="royal-style">NekoBox</h2>
 <div style="border: 1px solid black; padding: 10px; ">  
   <br>
<?php
$translate = [
    'United States' => '美国',
    'China' => '中国',
    'ISP' => '互联网服务提供商',
    'Japan' => '日本',
    'South Korea' => '韩国',
    'Germany' => '德国',
    'France' => '法国',
    'United Kingdom' => '英国',
    'Canada' => '加拿大',
    'Australia' => '澳大利亚',
    'Russia' => '俄罗斯',
    'India' => '印度',
    'Brazil' => '巴西',
    'Netherlands' => '荷兰',
    'Singapore' => '新加坡',
    'Hong Kong' => '香港',
    'Saudi Arabia' => '沙特阿拉伯',
    'Turkey' => '土耳其',
    'Italy' => '意大利',
    'Spain' => '西班牙',
    'Thailand' => '泰国',
    'Malaysia' => '马来西亚',
    'Indonesia' => '印度尼西亚',
    'South Africa' => '南非',
    'Mexico' => '墨西哥',
    'Israel' => '以色列',
    'Sweden' => '瑞典',
    'Switzerland' => '瑞士',
    'Norway' => '挪威',
    'Denmark' => '丹麦',
    'Belgium' => '比利时',
    'Finland' => '芬兰',
    'Poland' => '波兰',
    'Austria' => '奥地利',
    'Greece' => '希腊',
    'Portugal' => '葡萄牙',
    'Ireland' => '爱尔兰',
    'New Zealand' => '新西兰',
    'United Arab Emirates' => '阿拉伯联合酋长国',
    'Argentina' => '阿根廷',
    'Chile' => '智利',
    'Colombia' => '哥伦比亚',
    'Philippines' => '菲律宾',
    'Vietnam' => '越南',
    'Pakistan' => '巴基斯坦',
    'Egypt' => '埃及',
    'Nigeria' => '尼日利亚',
    'Kenya' => '肯尼亚',
    'Morocco' => '摩洛哥',
    'Google' => '谷歌',
    'Amazon' => '亚马逊',
    'Microsoft' => '微软',
    'Facebook' => '脸书',
    'Apple' => '苹果',
    'IBM' => 'IBM',
    'Alibaba' => '阿里巴巴',
    'Tencent' => '腾讯',
    'Baidu' => '百度',
    'Verizon' => '威瑞森',
    'AT&T' => '美国电话电报公司',
    'T-Mobile' => 'T-移动',
    'Vodafone' => '沃达丰',
    'China Telecom' => '中国电信',
    'China Unicom' => '中国联通',
    'China Mobile' => '中国移动', 
    'Chunghwa Telecom' => '中华电信',   
    'Amazon Web Services (AWS)' => '亚马逊网络服务 (AWS)',
    'Google Cloud Platform (GCP)' => '谷歌云平台 (GCP)',
    'Microsoft Azure' => '微软Azure',
    'Oracle Cloud' => '甲骨文云',
    'Alibaba Cloud' => '阿里云',
    'Tencent Cloud' => '腾讯云',
    'DigitalOcean' => '数字海洋',
    'Linode' => '林诺德',
    'OVHcloud' => 'OVH 云',
    'Hetzner' => '赫兹纳',
    'Vultr' => '沃尔特',
    'OVH' => 'OVH',
    'DreamHost' => '梦想主机',
    'InMotion Hosting' => '动态主机',
    'HostGator' => '主机鳄鱼',
    'Bluehost' => '蓝主机',
    'A2 Hosting' => 'A2主机',
    'SiteGround' => '站点地',
    'Liquid Web' => '液态网络',
    'Kamatera' => '卡玛特拉',
    'IONOS' => 'IONOS',
    'InterServer' => '互联服务器',
    'Hostwinds' => '主机之风',
    'ScalaHosting' => '斯卡拉主机',
    'GreenGeeks' => '绿色极客'
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
        body {
            font-family: 'Montserrat', sans-serif;
            line-height: 1.6;
        }
        .status {
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: left;
            flex-direction: row;
            height: 50px;
            letter-spacing: 0.5px;
        }
        .img-con {
            margin-right: 3rem;
        }
        .img-con img {
            width: 80px;
            height: auto;
        }
        .block {
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .ip-address {
            color: #2dce89;
            font-weight: bold;
            font-size: 1.1rem;
            margin: 0;
        }
        .info {
            color: #fb6340;
            font-style: italic;
            font-size: 1rem;
            margin: 0;
        }
    </style>
</head>
<body>
<?php if (in_array($lang, ['zh-cn', 'en', 'auto'])): ?>
    <fieldset class="cbi-section">
        <div class="status">
            <div class="img-con">
                <img src="/nekobox/assets/neko/img/loading.svg" id="flag" class="pure-img" title="国旗">
            </div>
            <div class="block">
                <p id="d-ip" class="green ip-address">Checking...</p>
                <p id="ipip" class="info"></p>
            </div>
        </div>
    </fieldset>
<?php endif; ?>

<script src="/nekobox/assets/neko/js/jquery.min.js"></script>
<script type="text/javascript">
    const _IMG = '/nekobox/assets/neko/';
    const translate = <?php echo json_encode($translate, JSON_UNESCAPED_UNICODE); ?>;
    let cachedIP = null;
    let cachedInfo = null;
    let random = parseInt(Math.random() * 100000000);

    let IP = {
        get: (url, type) =>
            fetch(url, { method: 'GET' }).then((resp) => {
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
        Ipip: (ip, elID) => {
            if (ip === cachedIP && cachedInfo) {
                console.log("Using cached IP info");
                IP.updateUI(cachedInfo, elID);
            } else {
                IP.get(`https://api.ip.sb/geoip/${ip}`, 'json')
                    .then(resp => {
                        cachedIP = ip;  
                        cachedInfo = resp.data;  
                        IP.updateUI(resp.data, elID);
                    })
                    .catch(error => {
                        console.error("Error in Ipip function:", error);
                    });
            }
        },
        updateUI: (data, elID) => {
            let country = translate[data.country] || data.country;
            let isp = translate[data.isp] || data.isp;
            let asnOrganization = translate[data.asn_organization] || data.asn_organization;

            if (data.country === 'Taiwan') {
                country = (navigator.language === 'en') ? 'China Taiwan' : '中国台湾';
            }

            document.getElementById(elID).innerHTML = `${country} ${isp} ${asnOrganization}`;
            $("#flag").attr("src", _IMG + "flags/" + data.country + ".png");
            document.getElementById(elID).style.color = '#FF00FF';
        },
        getIpipnetIP: () => {
            if (cachedIP) {
                document.getElementById('d-ip').innerHTML = cachedIP;
                IP.updateUI(cachedInfo, 'ipip');
            } else {
                IP.get(`https://api.ipify.org?format=json&z=${random}`, 'json')
                    .then((resp) => {
                        let ip = resp.data.ip;
                        cachedIP = ip; 
                        document.getElementById('d-ip').innerHTML = ip;
                        return ip;
                    })
                    .then(ip => {
                        IP.Ipip(ip, 'ipip');
                    })
                    .catch(error => {
                        console.error("Error in getIpipnetIP function:", error);
                    });
            }
        }
    }

    IP.getIpipnetIP();
    setInterval(IP.getIpipnetIP, 5000);
</script>
</body>
</html>
<tbody>
    <tr>
   <br>

<table class="table table-borderless mb-2">
    <tbody>
        <tr>
            <style>
                .btn-group .btn {
                    width: 100%;
                }
            </style>
            <td>状态</td>
            <td class="d-grid">
                <div class="btn-group" role="group" aria-label="ctrl">
                    <?php
                    if ($neko_status == 1) {
                        echo "<button type=\"button\" class=\"btn btn-success\">Mihomo 运行中</button>\n";
                    } else {
                        echo "<button type=\"button\" class=\"btn btn-outline-danger\">Mihomo 未运行</button>\n";
                    }
                    echo "<button type=\"button\" class=\"btn btn-deepskyblue\">$str_cfg</button>\n";
                    if ($singbox_status == 1) {
                        echo "<button type=\"button\" class=\"btn btn-success\">Sing-box 运行中</button>\n";
                    } else {
                        echo "<button type=\"button\" class=\"btn btn-outline-danger\">Sing-box 未运行</button>\n";
                    }
                    ?>
                </div>
            </td>
        </tr>
        <tr>
            <td>控制</td>
            <form action="index.php" method="post">
                <td class="d-grid">
                    <div class="btn-group col" role="group" aria-label="ctrl">
                        <button type="submit" name="neko" value="start" class="btn btn<?php if ($neko_status == 1) echo "-outline" ?>-success <?php if ($neko_status == 1) echo "disabled" ?> d-grid">启用 Mihomo</button>
                        <button type="submit" name="neko" value="disable" class="btn btn<?php if ($neko_status == 0) echo "-outline" ?>-danger <?php if ($neko_status == 0) echo "disabled" ?> d-grid">停用 Mihomo</button>
                        <button type="submit" name="neko" value="restart" class="btn btn<?php if ($neko_status == 0) echo "-outline" ?>-warning <?php if ($neko_status == 0) echo "disabled" ?> d-grid">重启 Mihomo</button>
                    </div>
                </td>
            </form>
            <form action="index.php" method="post">
                <td class="d-grid">
                    <select name="config_file" id="config_file" class="form-select">
                        <?php foreach ($availableConfigs as $config): ?>
                            <option value="<?= htmlspecialchars($config) ?>" <?= isset($_POST['config_file']) && $_POST['config_file'] === $config ? 'selected' : '' ?>>
                                <?= htmlspecialchars(basename($config)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="btn-group col" role="group" aria-label="ctrl">
                        <button type="submit" name="singbox" value="start" class="btn btn<?php echo ($singbox_status == 1) ? "-outline" : "" ?>-success <?php echo ($singbox_status == 1) ? "disabled" : "" ?> d-grid">启用 Sing-box</button>
                        <button type="submit" name="singbox" value="disable" class="btn btn<?php echo ($singbox_status == 0) ? "-outline" : "" ?>-danger <?php echo ($singbox_status == 0) ? "disabled" : "" ?> d-grid">停用 Sing-box</button>
                        <button type="submit" name="singbox" value="restart" class="btn btn<?php echo ($singbox_status == 0) ? "-outline" : "" ?>-warning <?php echo ($singbox_status == 0) ? "disabled" : "" ?> d-grid">重启 Sing-box</button>
                    </div>
                </td>
            </form>
        </tr>
        <tr>
            <td>运行模式</td>
            <td class="d-grid">
                <?php
                $mode_placeholder = '';
                if ($neko_status == 1) {
                    $mode_placeholder = $neko_cfg['echanced'] . " | " . $neko_cfg['mode'];
                } elseif ($singbox_status == 1) {
                    $mode_placeholder = "Rule 模式";
                } else {
                    $mode_placeholder = "未运行";
                }
                ?>
                <input class="form-control text-center" name="mode" type="text" placeholder="<?php echo $mode_placeholder; ?>" disabled>
            </td>
        </tr>
    </tbody>
</table>

    <style>
        .icon-container { display: flex; justify-content: space-between; margin-top: 20px; }
        .icon { text-align: center; width: 30%; }
        .icon i { font-size: 48px; }
    </style>
    <link rel="stylesheet" href="./assets/bootstrap/all.min.css">
    <div class="container">
    <h2 class="text-center p-2" >系统状态</h2>
    <table class="table table-borderless rounded-4 mb-2">
        <tbody>
                <td>系统信息</td>
                <td class="col-7"><?php echo  $devices . ' - ' . $fullOSInfo; ?></td>
            </tr>
            <tr>
                <td>内存</td>
                <td class="col-7"><?php echo "$ramUsage/$ramTotal MB" ?></td>
            </tr>
            <tr>
                <td>平均负载</td>
                <td class="col-7"><?php echo "$cpuLoadAvg1Min $cpuLoadAvg5Min $cpuLoadAvg15Min" ?></td>
            </tr>
            <tr>
                <td>运行时间</td>
                <td class="col-7"><?php echo "{$days}天 {$hours}小时 {$minutes}分钟 {$seconds}秒" ?></td>
            </tr>
        </tbody>
    </table>

        <div class="icon-container">
            <div class="icon">
                <i class="fas fa-microchip"></i>
                <p>CPU</p>
                <p><?php echo isset($cpuLoadAvg1Min) ? $cpuLoadAvg1Min : 'N/A'; ?></p>
            </div>
            <div class="icon">
                <i class="fas fa-memory"></i>
                <p>内存</p>
                <p><?php echo (isset($ramUsage) && isset($ramTotal)) ? $ramUsage . ' / ' . $ramTotal . ' MB' : 'N/A'; ?></p>
            </div>
            <div class="icon">
                <i class="fas fa-exchange-alt"></i>
                <p>交换空间</p>
                <p>N/A</p>
            </div>
        </div>
    </div>

    <div style="border: 1px solid black; padding: 10px; text-align: center;">
        <table style="width: 100%;">
            <tbody>
                <tr>
                    <td style="width: 50%;">下载-总计</td>
                    <td style="width: 50%;">上传-总计</td>
                </tr>
                <tr>
                    <td><span id="downtotal">-</span></td>
                    <td><span id="uptotal">-</span></td>
                </tr>
            </tbody>
        </table>
    </div>

<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        .log-container {
            height: 300px;
            overflow-y: auto;
            overflow-x: hidden;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        .log-card {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <h2 class="text-center my-4">日志</h2>
    <div class="row">
        <div class="col-md-4">
            <div class="card log-card">
                <div class="card-header">
                    <h4 class="card-title text-center mb-0">NeKoBox 日志</h4>
                </div>
                <div class="card-body">
                    <pre id="plugin_log" class="log-container form-control"></pre>
                </div>
                <div class="card-footer text-center">
                    <form action="index.php" method="post">
                        <button type="submit" name="clear_plugin_log" class="btn btn-danger">🗑️ 清空日志</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card log-card">
                <div class="card-header">
                    <h4 class="card-title text-center mb-0">Mihomo 日志</h4>
                </div>
                <div class="card-body">
                    <pre id="bin_logs" class="log-container form-control"></pre>
                </div>
                <div class="card-footer text-center">
                    <form action="index.php" method="post">
                        <button type="submit" name="neko" value="clear" class="btn btn-danger">🗑️ 清空日志</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card log-card">
                <div class="card-header">
                    <h4 class="card-title text-center mb-0">Sing-box 日志</h4>
                </div>
                <div class="card-body">
                    <pre id="singbox_log" class="log-container form-control"></pre>
                </div>
                <div class="card-footer text-center">
                    <form action="index.php" method="post">
                        <button type="submit" name="clear_singbox_log" class="btn btn-danger">🗑️ 清空日志</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="./assets/js/bootstrap.bundle.min.js"></script>
    <script>
        function scrollToBottom(elementId) {
            var logElement = document.getElementById(elementId);
            logElement.scrollTop = logElement.scrollHeight;
        }
        function fetchLogs() {
            Promise.all([
                fetch('fetch_logs.php?file=plugin_log'),
                fetch('fetch_logs.php?file=mihomo_log'),
                fetch('fetch_logs.php?file=singbox_log')
            ])
            .then(responses => Promise.all(responses.map(res => res.text())))
            .then(data => {
                document.getElementById('plugin_log').textContent = data[0];
                document.getElementById('bin_logs').textContent = data[1];
                document.getElementById('singbox_log').textContent = data[2];
                scrollToBottom('plugin_log');
                scrollToBottom('bin_logs');
                scrollToBottom('singbox_log');
            })
            .catch(err => console.error('Error fetching logs:', err));
        }
        fetchLogs();
        setInterval(fetchLogs, 5000);
    </script>
</body>
</html>
    <footer class="text-center">
        <p><?php echo isset($message) ? $message : ''; ?></p>
        <p><?php echo $footer; ?></p>
    </footer>
</body>
</html>
