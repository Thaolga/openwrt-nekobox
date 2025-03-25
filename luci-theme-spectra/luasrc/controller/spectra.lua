module("luci.controller.spectra", package.seeall)

function index()
    entry({"admin", "services", "spectra"}, template("spectra/index"), _("Spectra 主题设置"), 1)
end
