#
# Copyright (C) 2008-2019 Jerrykuku
#
# This is free software, licensed under the Apache License, Version 2.0 .
#

include $(TOPDIR)/rules.mk

LUCI_TITLE:=Spectra Theme
LUCI_DEPENDS:=+curl +php8 +php8-cgi +php8-mod-curl +php8-mod-zip +php8-mod-mbstring +ffmpeg
PKG_VERSION:=27.564.67123~e5b1c8d3

LUCI_MINIFY_CSS:=0

include $(TOPDIR)/feeds/luci/luci.mk

# call BuildPackage - OpenWrt buildroot signature
