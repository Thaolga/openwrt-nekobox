module("luci.controller.spectra", package.seeall)

function index()
    entry({"admin", "spectra"},
        firstchild(),
        _("Spectra"),
        41
    )

    entry({"admin", "spectra", "index"}, 
        template("spectra/index"),
        _("Home"), 
        2
    ).leaf = true
    
    entry({"admin", "spectra", "media"}, 
        template("spectra/media"),
        _("Media"), 
        3
    ).leaf = true
    
    entry({"admin", "spectra", "main"}, 
        template("spectra/main"),
        _("Music"), 
        4
    ).leaf = true
    
    --[[
    entry({"admin", "spectra", "filekit"}, 
        template("spectra/filekit"),
        _("Manager"), 
        5
    ).leaf = true
    --]]
end