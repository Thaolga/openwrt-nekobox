#
# Copyright (C) 2008-2019 Jerrykuku
#
# This is free software, licensed under the Apache License, Version 2.0 .
#

include $(TOPDIR)/rules.mk

LUCI_TITLE:=Spectra Theme
LUCI_DEPENDS:=+curl +jq +php8 +php8-cgi +php8-mod-curl +ffmpeg
PKG_VERSION:=27.505.62091~k3m5n7p

LUCI_MINIFY_CSS:=0

include $(TOPDIR)/feeds/luci/luci.mk

# call BuildPackage - OpenWrt buildroot signature
