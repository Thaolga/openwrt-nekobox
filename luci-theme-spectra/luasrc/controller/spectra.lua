module("luci.controller.spectra", package.seeall)

function index()
    entry({"admin", "services", "spectra"}, firstchild(), _("Spectra"), 1).leaf = false
    entry({"admin", "services", "spectra", "index"}, template("spectra/index"), _("Home"), 2).leaf = true
    entry({"admin", "services", "spectra", "media"}, template("spectra/media"), _("Media"), 3).leaf = true
    entry({"admin", "services", "spectra", "main"}, template("spectra/main"), _("Music"), 4).leaf = true
    -- entry({"admin", "services", "spectra", "filekit"}, template("spectra/filekit"), _("Manager"), 5).leaf = true
end
