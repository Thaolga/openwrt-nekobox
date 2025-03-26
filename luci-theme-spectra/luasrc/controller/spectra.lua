module("luci.controller.spectra", package.seeall)

function index()
    entry({"admin", "services", "spectra"}, firstchild(), _("Spectra 主题设置"), 1).leaf = false
    entry({"admin", "services", "spectra", "index"}, template("spectra/index"), _("首页"), 2).leaf = true
    entry({"admin", "services", "spectra", "filekit"}, template("spectra/filekit"), _("文件助手"), 3).leaf = true
end
