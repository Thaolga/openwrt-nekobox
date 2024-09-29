#!/bin/sh

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m'

log_message() {
    local message="\$1"
    local log_file='/var/log/neko_update.log'
    local timestamp=$(date '+%Y-%m-%d %H:%M:%S')
    echo "[$timestamp] $message" >> "$log_file"
}

get_version_info() {
    local component="\$1"
    local version_file
    local latest_version
    local current_version

    case $component in
        "neko")
            version_file='/etc/neko/version_neko.txt'
            ;;
        "core")
            version_file='/etc/neko/version_mihomo.txt'
            ;;
        "ui")
            version_file='/etc/neko/ui/metacubexd/version.txt'
            ;;
        "singbox")
            version_file='/etc/neko/version_singbox.txt'
            ;;
        *)
            return 1
    esac

    if [ -e "$version_file" ] && [ -s "$version_file" ]; then
        current_version=$(cat "$version_file")
    else
        current_version="未安装"
    fi

    if [ "$language_choice" = "cn" ]; then
        echo -e "${CYAN}组件: $component, 当前版本: $current_version${NC}"
    else
        echo -e "${CYAN}Component: $component, Current Version: $current_version${NC}"
    fi

    local releases_url
    case $component in
        "neko")
            releases_url="https://api.github.com/repos/Thaolga/neko/releases/latest"
            ;;
        "core")
            releases_url="https://api.github.com/repos/MetaCubeX/mihomo/releases/latest"
            ;;
        "ui")
            releases_url="https://api.github.com/repos/MetaCubeX/metacubexd/releases/latest"
            ;;
        "singbox")
            releases_url="https://api.github.com/repos/SagerNet/sing-box/releases/latest"
            ;;
    esac

    latest_version=$(curl -s "$releases_url" | grep '"tag_name":' | sed -E 's/.*"tag_name": "([^"]+)".*/\1/')

    if [ -z "$latest_version" ]; then
        if [ "$language_choice" = "cn" ]; then
            echo -e "${RED}获取最新版本失败。请检查网络连接或 GitHub API 状态。${NC}"
        else
            echo -e "${RED}Failed to get the latest version. Please check your internet connection or GitHub API status.${NC}"
        fi
        latest_version="获取失败"
    fi

    if [ "$language_choice" = "cn" ]; then
        echo -e "${CYAN}最新版本: $latest_version${NC}"
    else
        echo -e "${CYAN}Latest Version: $latest_version${NC}"
    fi
}

install_ipk() {
    repo_owner="Thaolga"
    repo_name="openwrt-nekobox"
    package_name="luci-app-nekobox"
    releases_url="https://api.github.com/repos/$repo_owner/$repo_name/releases/latest"

    echo -e "${CYAN}更新 opkg 软件包列表...${NC}"
    opkg update

    response=$(wget -qO- "$releases_url")
    if [ -z "$response" ]; then
        echo -e "${RED}无法访问 GitHub releases 页面。${NC}" 
        return 1
    fi

    new_version=$(echo "$response" | grep '"tag_name":' | sed -E 's/.*"tag_name": "([^"]+)".*/\1/')
    if [ -z "$new_version" ]; then
        echo -e "${RED}未找到最新版本。${NC}"
        return 1
    fi

    if [ -z "$language_choice" ]; then
        echo -e "${YELLOW}未找到语言选择，将默认使用 'en'。${NC}"
        language_choice="en"
    fi

    if [ "$language_choice" != "cn" ] && [ "$language_choice" != "en" ]; then
        echo -e "${RED}无效的语言选择，使用 'en' 作为默认值。${NC}"
        language_choice="en"
    fi

    download_url="https://github.com/$repo_owner/$repo_name/releases/download/$new_version/${package_name}_${new_version}-${language_choice}_all.ipk"

    echo -e "${CYAN}下载 URL: $download_url${NC}"

    local_file="/tmp/$package_name.ipk"
    curl -L -f -o "$local_file" "$download_url"
    if [ $? -ne 0 ]; then
        echo -e "${RED}下载失败！${NC}"
        return 1
    fi

    if [ ! -s "$local_file" ]; then
        echo -e "${RED}下载的文件为空或不存在。${NC}"
        return 1
    fi

    opkg install --force-reinstall "$local_file"
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}NeKoBox安装完成。版本号: $new_version${NC}"
        echo "$new_version" > /etc/neko/neko_version.txt
        echo "$new_version" > /etc/neko/version_neko.txt
        get_version_info "neko"
    else
        echo -e "${RED}NeKoBox安装失败。${NC}"
        return 1
    fi

    rm -f "$local_file"
}

install_core() {
    latest_version=$(curl -s https://api.github.com/repos/MetaCubeX/mihomo/releases/latest | grep '"tag_name":' | sed -E 's/.*"([^"]+)".*/\1/')

    if [ -z "$latest_version" ]; then
        echo -e "${RED}无法获取最新核心版本号，请检查网络连接。${NC}"
        return 1
    fi

    current_version=''
    install_path='/etc/neko/core/mihomo'
    temp_file='/tmp/mihomo.gz'
    temp_extract_path='/tmp/mihomo_temp'

    if [ -e "$install_path/version.txt" ]; then
        current_version=$(cat "$install_path/version.txt" 2>/dev/null)
    fi

    case "$(uname -m)" in
        aarch64)
            download_url="https://github.com/MetaCubeX/mihomo/releases/download/$latest_version/mihomo-linux-arm64-$latest_version.gz"
            ;;
        armv7l)
            download_url="https://github.com/MetaCubeX/mihomo/releases/download/$latest_version/mihomo-linux-armv7l-$latest_version.gz"
            ;;
        x86_64)
            download_url="https://github.com/MetaCubeX/mihomo/releases/download/$latest_version/mihomo-linux-amd64-$latest_version.gz"
            ;;
        *)
            echo -e "${RED}未找到适合架构的下载链接: $(uname -m)${NC}"
            return 1
            ;;
    esac

    echo -e "${CYAN}最新版本: $latest_version${NC}"
    echo -e "${CYAN}下载链接: $download_url${NC}"

    if [ "$current_version" = "$latest_version" ]; then
        echo -e "${GREEN}当前版本已是最新版本。${NC}"
        return 0
    fi

    wget -O "$temp_file" "$download_url"
    if [ $? -ne 0 ]; then
        echo -e "${RED}下载失败！${NC}"
        return 1
    fi

    mkdir -p "$temp_extract_path"
    gunzip -f -c "$temp_file" > "$temp_extract_path/mihomo"
    if [ $? -ne 0 ]; then
        echo -e "${RED}解压失败！${NC}"
        return 1
    fi

    mv "$temp_extract_path/mihomo" "$install_path"
    chmod 0755 "$install_path"
    if [ $? -ne 0 ]; then
        echo -e "${RED}设置权限失败！${NC}"
        return 1
    fi

    echo "$latest_version" > "/etc/neko/version_mihomo.txt"
    echo -e "${GREEN}核心更新完成！当前版本: $latest_version${NC}"

    rm -f "$temp_file"
    rm -rf "$temp_extract_path"
}

install_singbox() {
    GREEN='\033[0;32m'
    RED='\033[0;31m'
    NC='\033[0m'

    local install_path='/usr/bin/sing-box'
    local temp_dir='/tmp/singbox_temp'
    local temp_file='/tmp/sing-box.tar.gz'

    latest_version=$(curl -s https://api.github.com/repos/SagerNet/sing-box/releases | grep '"tag_name":' | grep 'beta' | head -n 1 | sed -E 's/.*"([^"]+)".*/\1/')
    if [ -z "$latest_version" ]; then
        echo -e "${RED}无法获取最新 beta 版本号，请检查网络连接。${NC}"
        exit 1
    fi

    local current_arch=$(uname -m)
    local download_url

    case "$current_arch" in
        aarch64)
            download_url="https://github.com/SagerNet/sing-box/releases/download/$latest_version/sing-box-${latest_version#v}-linux-arm64.tar.gz"
            ;;
        x86_64)
            download_url="https://github.com/SagerNet/sing-box/releases/download/$latest_version/sing-box-${latest_version#v}-linux-amd64.tar.gz"
            ;;
        *)
            echo -e "${RED}未找到适合架构的下载链接: $current_arch${NC}"
            exit 1
            ;;
    esac

    wget -O "$temp_file" "$download_url"
    if [ $? -ne 0 ]; then
        echo -e "${RED}下载失败！${NC}"
        exit 1
    fi

    mkdir -p "$temp_dir"
    tar -xzf "$temp_file" -C "$temp_dir"
    if [ $? -ne 0 ]; then
        echo -e "${RED}解压失败！${NC}"
        exit 1
    fi

    if [ "$current_arch" = "x86_64" ]; then
        extracted_file="$temp_dir/sing-box-${latest_version#v}-linux-amd64/sing-box"
    elif [ "$current_arch" = "aarch64" ]; then
        extracted_file="$temp_dir/sing-box-${latest_version#v}-linux-arm64/sing-box"
    fi

    if [ -e "$extracted_file" ]; then
        mv "$extracted_file" "$install_path"
        chmod 0755 "$install_path"
        if [ $? -ne 0 ]; then
            echo -e "${RED}设置权限失败！${NC}"
            exit 1
        fi

        echo -e "更新/安装完成！版本: ${GREEN}$latest_version${NC}"
    else
        echo -e "${RED}解压后的文件 'sing-box' 不存在。${NC}"
        exit 1
    fi

    # Cleanup
    rm -f "$temp_file"
    rm -rf "$temp_dir"
}

install_puernya() {
    local install_path="/usr/bin/sing-box"  
    local temp_file="/tmp/sing-box.tar.gz"   
    local temp_dir="/tmp/singbox_temp"       

    local current_arch=$(uname -m)
    local download_url=""

    case "$current_arch" in
        aarch64)
            download_url="https://github.com/Thaolga/luci-app-nekoclash/releases/download/sing-box/sing-box-puernya-linux-armv8.tar.gz"
            ;;
        x86_64)
            download_url="https://github.com/Thaolga/luci-app-nekoclash/releases/download/sing-box/sing-box-puernya-linux-amd64.tar.gz"
            ;;
        *)
            echo "Unsupported architecture: $current_arch"
            exit 1
            ;;
    esac

    echo "Downloading puernya core from $download_url..."
    wget -O "$temp_file" "$download_url"
    if [ $? -ne 0 ]; then
        echo "Download failed!"
        exit 1
    fi

    echo "Extracting files..."
    mkdir -p "$temp_dir"
    tar -xzf "$temp_file" -C "$temp_dir"
    if [ $? -ne 0 ]; then
        echo "Extraction failed!"
        exit 1
    fi

    local extracted_file="$temp_dir/CrashCore"  
    if [ -f "$extracted_file" ]; then
        cp -f "$extracted_file" "$install_path"
        chmod 0755 "$install_path"
        echo "更新完成！当前版本: $(basename "$download_url")" 
    else
        echo "解压后的文件 'CrashCore' 不存在。"
        exit 1
    fi

    rm -f "$temp_file"
    rm -rf "$temp_dir"

    echo "Puernya core installation completed successfully."
}

install_ui() {
    GREEN='\033[0;32m'
    RED='\033[0;31m'
    NC='\033[0m'
    CYAN='\033[0;36m'

    local install_path='/etc/neko/ui/metacubexd'
    local temp_file='/tmp/metacubexd.tgz'
    local temp_extract_path='/tmp/metacubexd_temp'

    latest_version=$(curl -s https://api.github.com/repos/MetaCubeX/metacubexd/releases/latest | grep '"tag_name":' | sed -E 's/.*"([^"]+)".*/\1/')
    if [ -z "$latest_version" ]; then
        echo -e "${RED}无法获取最新 UI 版本号，请检查网络连接。${NC}"
        return 1
    fi

    local current_version=''
    if [ -e "$install_path/version.txt" ]; then
        current_version=$(cat "$install_path/version.txt" 2>/dev/null)
    fi

    if [ "$current_version" = "$latest_version" ]; then
        echo -e "${GREEN}当前版本已是最新版本。${NC}"
        return 0
    fi

    local download_url="https://github.com/MetaCubeX/metacubexd/releases/download/$latest_version/compressed-dist.tgz"

    echo -e "${CYAN}最新版本: $latest_version${NC}"
    echo -e "${CYAN}下载链接: $download_url${NC}"

    wget -O "$temp_file" "$download_url"
    if [ $? -ne 0 ]; then
        echo -e "${RED}下载失败！${NC}"
        return 1
    fi

    mkdir -p "$temp_extract_path"
    tar -xzf "$temp_file" -C "$temp_extract_path"
    if [ $? -ne 0 ]; then
        echo -e "${RED}解压失败！${NC}"
        return 1
    fi

    mkdir -p "$install_path"
    cp -r "$temp_extract_path/"* "$install_path/"
    if [ $? -ne 0 ]; then
        echo -e "${RED}拷贝文件失败！${NC}"
        return 1
    fi

    echo "$latest_version" > "$install_path/version.txt"
    echo -e "${GREEN}UI 更新完成！当前版本: $latest_version${NC}"

    rm -f "$temp_file"
    rm -rf "$temp_extract_path"
}

install_php() {
    GREEN="\033[32m"
    RED="\033[31m"
    YELLOW="\033[33m"
    RESET="\033[0m"

    ARCH=$(uname -m)

    if [ "$ARCH" == "aarch64" ]; then
        PHP_CGI_URL="https://github.com/Thaolga/neko/releases/download/core_neko/php8-cgi_8.3.10-1_aarch64_generic.ipk"
        PHP_URL="https://github.com/Thaolga/neko/releases/download/core_neko/php8_8.3.10-1_aarch64_generic.ipk"
        PHP_MOD_CURL_URL="https://github.com/Thaolga/neko/releases/download/core_neko/php8-mod-curl_8.3.10-1_aarch64_generic.ipk"
    elif [ "$ARCH" == "x86_64" ]; then
        PHP_CGI_URL="https://github.com/Thaolga/neko/releases/download/core_neko/php8-cgi_8.3.10-1_x86_64.ipk"
        PHP_URL="https://github.com/Thaolga/neko/releases/download/core_neko/php8_8.3.10-1_x86_64.ipk"
        PHP_MOD_CURL_URL="https://github.com/Thaolga/neko/releases/download/core_neko/php8-mod-curl_8.3.10-1_x86_64.ipk"
    else
        echo -e "${RED}Unsupported architecture: $ARCH${RESET}"
        exit 1
    fi

    echo -e "${GREEN}Downloading and installing PHP CGI...${RESET}"
    wget "$PHP_CGI_URL" -O /tmp/php8-cgi.ipk
    if opkg install --force-reinstall --force-overwrite /tmp/php8-cgi.ipk; then
        echo -e "${GREEN}PHP CGI installed successfully.${RESET}"
    else
        echo -e "${RED}PHP CGI installation failed.${RESET}"
    fi

    echo -e "${GREEN}Downloading and installing PHP...${RESET}"
    wget "$PHP_URL" -O /tmp/php8.ipk
    if opkg install --force-reinstall --force-overwrite /tmp/php8.ipk; then
        echo -e "${GREEN}PHP installed successfully.${RESET}"
    else
        echo -e "${RED}PHP installation failed.${RESET}"
    fi

    echo -e "${GREEN}Downloading and installing PHP curl module...${RESET}"
    wget "$PHP_MOD_CURL_URL" -O /tmp/php8-mod-curl.ipk
    if opkg install --force-reinstall --force-overwrite /tmp/php8-mod-curl.ipk; then
        echo -e "${GREEN}PHP curl module installed successfully.${RESET}"
    else
        echo -e "${RED}PHP curl module installation failed.${RESET}"
    fi

    rm -f /tmp/php8-cgi.ipk /tmp/php8.ipk /tmp/php8-mod-curl.ipk

    echo -e "${GREEN}安装完成。${RESET}"
    echo -e "${YELLOW}请重启服务器以应用更改。${RESET}"
}

reboot_router() {
    echo -e "${CYAN}正在重启路由器...${NC}"
    reboot
}

install_core_menu() {
    while true; do
        echo -e "${YELLOW}===================================${NC}"
        echo -e "${YELLOW}|   1. 安装 Sing-box 核心         |${NC}"
        echo -e "${YELLOW}|   2. 安装 puernya 核心          |${NC}"
        echo -e "${YELLOW}|   3. 返回主菜单                 |${NC}"
        echo -e "${YELLOW}===================================${NC}"

        read -p "请选择要安装的核心: " core_choice

        case $core_choice in
            1)
                install_singbox
                ;;
            2)
                install_puernya
                ;;
            3)
                return
                ;;
            *)
                echo -e "${RED}无效的选项，请重试。${NC}"
                ;;
        esac
    done
}

while true; do
    echo -e "${YELLOW}===================================${NC}"
    echo -e "${YELLOW}|   1. 安装 NeKoBox 中文版        |${NC}"
    echo -e "${YELLOW}|   2. 安装 NeKoBox (Eng)         |${NC}"
    echo -e "${YELLOW}|   3. 安装 Mihomo 核心           |${NC}"
    echo -e "${YELLOW}|   4. 安装 Sing-box 核心         |${NC}"
    echo -e "${YELLOW}|   5. 安装 UI 控制面板           |${NC}"
    echo -e "${YELLOW}|   6. 安装 PHP8 和 PHP8-CGI      |${NC}"
    echo -e "${YELLOW}|   7. 重启路由器                 |${NC}"
    echo -e "${YELLOW}|   0. 退出                       |${NC}"
    echo -e "${YELLOW}===================================${NC}"

    read -p "请输入选项并按回车: " choice

    case $choice in
        1)
            language_choice="cn"
            install_ipk
            ;;
        2)
            language_choice="en"
            install_ipk
            ;;
        3)
            install_core
            ;;
        4)
            install_core_menu
            ;;
        5)
            install_ui
            ;;
        6)
            install_php
            ;;
        7)
            reboot_router
            ;;
        0)
            echo -e "${GREEN}退出程序。${NC}"
            exit 0
            ;;
        *)
            echo -e "${RED}无效的选项，请重试。${NC}"
            ;;
    esac
done
