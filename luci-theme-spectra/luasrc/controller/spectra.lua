module("luci.controller.spectra", package.seeall)

function index()
    entry({"admin", "services", "spectra"}, firstchild(), _("Spectra Config"), 1).leaf = false
    entry({"admin", "services", "spectra", "index"}, template("spectra/index"), _("Home"), 2).leaf = true
    entry({"admin", "services", "spectra", "filekit"}, template("spectra/filekit"), _("Manager"), 3).leaf = true
end
