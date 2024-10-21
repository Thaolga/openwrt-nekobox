<h1 align="center">
<img src="https://raw.githubusercontent.com/Thaolga/neko/refs/heads/main/nekobox.png" alt="nekobox" width="200"> <br>NeKoBox<br>
</h1>

<div align="center">
 <a target="_blank" href="https://github.com/Thaolga/openwrt-nekobox/releases"><img src="https://raw.githubusercontent.com/Thaolga/openwrt-nekobox/refs/heads/nekobox/luci-app-nekobox/htdocs/nekobox/assets/img/curent.svg"></a>
</div>

<p align="center">
  XRAY/V2ray, Shadowsocks, ShadowsocksR, etc.</br>
  Sing-box based Proxy
</p>

# NekoBox 默认的 Web 服务器是 Uhttpd。如果您正在使用 Nginx，请不要尝试使用此插件。

# NekoBox is a meticulously designed proxy tool for "Mihomo" and "Sing-box," specifically created for home users. It aims to provide a simple yet powerful proxy solution. Built on PHP and BASH technologies, NekoBox simplifies complex proxy configurations into an intuitive experience, allowing every user to easily enjoy an efficient and secure network environment.
---

- A user-friendly interface with intelligent configuration features for easy setup and management of "Mihomo" and "Sing-box" proxies.
- Ensures optimal proxy performance and stability through efficient scripts and automation.
- Designed for home users, balancing ease of use and functionality, ensuring every family member can conveniently use the proxy service.
## Support Core
- Mihomo Support: To address the complexity of configuration, we have introduced a new universal template designed to make using Mihomo simple and straightforward, with no technical barriers.
- Sing-box Support: Sing-box has been integrated and requires the use of firewall4 + nftables, offering you a smarter and more efficient traffic management solution.
- Introducing an intelligent conversion template to completely solve the configuration difficulties of Sing-box. Our goal is to enable zero-threshold use of Sing-box.

Depedencies
---
- Mihomo
  - ` php8 `
  - ` php8-cgi `
  - `php8-mod-curl`
  - ` firewall `
  - ` iptables `
   
- Sing-box
  - ` php8 `
  - ` php8-cgi `
  - `php8-mod-curl`
  - ` firewall `/` firewall4 `
  - ` kmod-tun `
  - ` iptables `/` xtables-nft `
 

# OpenWrt One-Click Installation Script
```bash
bash -c "$(wget -qO - 'https://mirror.ghproxy.com/https://raw.githubusercontent.com/Thaolga/openwrt-nekobox/nekobox/nekobox.sh')"

```
```bash

wget -O /root/nekobox.sh https://mirror.ghproxy.com/https://raw.githubusercontent.com/Thaolga/openwrt-nekobox/nekobox/nekobox.sh  && chmod 0755 /root/nekobox.sh && /root/nekobox.sh

```
# OpenWrt Compilation
---
## Cloning the Source Code:
---

```bash
git clone https://github.com/Thaolga/openwrt-nekobox package/openwrt-nekobox && cd package/openwrt-nekobox && git checkout nekobox

```

## Compile :
---

```bash
make package/openwrt-nekobox/luci-app-nekobox/{clean,compile} V=s
```
# Screenshoot
---
<details><summary>Home</summary>
 <p>
 <img src="https://raw.githubusercontent.com/Thaolga/luci-app-nekoclash/tmp/image_2024-09-03_16-50-26.png" alt="home">
 </p>
</details>

 <details><summary>Dasboard</summary>
 <p>
  <img src="https://raw.githubusercontent.com/Thaolga/luci-app-nekoclash/tmp/image_2024-09-03_16-50-53.png" alt="home">
 </p>
</details>
