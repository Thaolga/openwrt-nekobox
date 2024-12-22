include $(TOPDIR)/rules.mk

PKG_NAME:=luci-app-spectra-config
PKG_VERSION:=25.088.28463-56b29d4

PKG_MAINTAINER:=jerrykuku <jerrykuku@qq.com>

LUCI_TITLE:=LuCI page for Spectra Config
LUCI_PKGARCH:=all
LUCI_DEPENDS:=+luci-compat

define Package/$(PKG_NAME)/conffiles
/etc/config/spectra
endef

include $(TOPDIR)/feeds/luci/luci.mk

# call BuildPackage - OpenWrt buildroot signature
