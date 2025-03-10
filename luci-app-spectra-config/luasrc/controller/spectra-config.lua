module("luci.controller.spectra-config", package.seeall)

function index()
	if not nixio.fs.access('/www/luci-static/spectra/css/cascade.css') then
		return
	end

	local page = entry({"admin", "system", "spectra-config"}, form("spectra-config"), _("Spectra Config"), 90)
	page.acl_depends = { "luci-app-spectra-config" }
end
