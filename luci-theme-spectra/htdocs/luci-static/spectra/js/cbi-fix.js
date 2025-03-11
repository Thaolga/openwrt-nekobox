document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        var dropdowns = document.querySelectorAll('.cbi-dropdown');
        
        Array.prototype.forEach.call(dropdowns, function(dropdown) {
            dropdown.addEventListener('click', function() {
                setTimeout(function() {
                    var menu = this.querySelector('ul.preview, ul.dropdown');
                    if (!menu) return;

                    menu.style.position = 'fixed';
                    menu.style.zIndex = '2147483647';
                    
                    var rect = this.getBoundingClientRect();
                    menu.style.bottom = (window.innerHeight - rect.top + 5) + 'px';
                    menu.style.left = rect.left + 'px';
                    menu.style.right = (window.innerWidth - rect.right) + 'px';
                }.bind(this), 50); 
            });

            window.addEventListener('resize', function() {
                if (dropdown.hasAttribute('open')) {
                    dropdown.click(); 
                }
            });
        });
    }, 300); 
});