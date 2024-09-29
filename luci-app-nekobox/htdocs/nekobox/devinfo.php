<?php

$dt=json_decode((shell_exec("ubus call system board")), true);
// MACHINE INFO
$devices=$dt['model'];

// OS TYPE AND KERNEL VERSION
$kernelv=exec("cat /proc/sys/kernel/ostype").' '.exec("cat /proc/sys/kernel/osrelease");
$OSVer=$dt['release']['distribution']." ".$dt['release']['version'];

// MEMORY INFO
$tmpramTotal=exec("cat /proc/meminfo | grep MemTotal | awk '{print $2}'");
$tmpramAvailable=exec("cat /proc/meminfo | grep MemAvailable | awk '{print $2}'");

$ramTotal=number_format(($tmpramTotal/1000),1);
$ramAvailable=number_format(($tmpramAvailable/1000),1);
$ramUsage=number_format((($tmpramTotal-$tmpramAvailable)/1000),1);

// UPTIME
$raw_uptime = exec("cat /proc/uptime | awk '{print $1}'");
$days = floor($raw_uptime / 86400);
$hours = floor(($raw_uptime / 3600) % 24);
$minutes = floor(($raw_uptime / 60) % 60);
$seconds = $raw_uptime % 60;


// CPU FREQUENCY
/*  $cpuFreq = file_get_contents("/sys/devices/system/cpu/cpu0/cpufreq/scaling_cur_freq");
$cpuFreq = round($cpuFreq / 1000, 1);

// CPU TEMPERATURE
$cpuTemp = file_get_contents("/sys/class/thermal/thermal_zone0/temp");
$cpuTemp = round($cpuTemp / 1000, 1);
if ($cpuTemp >= 60) {
    $color = "red";
} elseif ($cpuTemp >= 50) {
    $color = "orange";
} else {
    $color = "white";
}

*/

// CPU LOAD AVERAGE
$cpuLoad = shell_exec("cat /proc/loadavg");
$cpuLoad = explode(' ', $cpuLoad);
$cpuLoadAvg1Min = round($cpuLoad[0], 2);
$cpuLoadAvg5Min = round($cpuLoad[1], 2);
$cpuLoadAvg15Min = round($cpuLoad[2], 2);

// CPU INFORMATION
/* $cpuInfo = shell_exec("lscpu");
$cpuCores = preg_match('/^CPU\(s\):\s+(\d+)/m', $cpuInfo, $matches);
$cpuThreads = preg_match('/^Thread\(s\) per core:\s+(\d+)/m', $cpuInfo, $matches);
$cpuModelName = preg_match('/^Model name:\s+(.+)/m', $cpuInfo, $matches);
$cpuFamily = preg_match('/^CPU family:\s+(.+)/m', $cpuInfo, $matches);
*/
?>

