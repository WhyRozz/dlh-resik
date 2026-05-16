class MobileHamburgerMenu {
    constructor(options = {}) {
        this.menuBtn = document.getElementById(options.menuBtnId || 'mobileMenuBtn');
        this.dropdown = document.getElementById(options.dropdownId || 'mobileMenuDropdown');
        this.submenuToggles = options.submenuToggles || '.mobile-submenu-toggle';
        
        this.init();
    }
    
    init() {
        if (!this.menuBtn || !this.dropdown) {
            console.warn('MobileHamburgerMenu: Required elements not found');
            return;
        }
        
        this.bindEvents();
        this.initSubmenus();
    }
    
    bindEvents() {
        // Toggle main menu
        this.menuBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            this.toggle();
        });
        
        // Close menu when clicking outside
        document.addEventListener('click', (e) => {
            if (!this.menuBtn.contains(e.target) && !this.dropdown.contains(e.target)) {
                this.close();
            }
        });
        
        // Close menu when clicking a menu item (not submenu)
        const directLinks = this.dropdown.querySelectorAll('.mobile-menu-link:not([data-submenu])');
        directLinks.forEach((link) => {
            link.addEventListener('click', () => {
                setTimeout(() => this.close(), 200);
            });
        });
        
        // Close menu on ESC key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.dropdown.classList.contains('active')) {
                this.close();
            }
        });
        
        // Prevent dropdown close when clicking inside
        this.dropdown.addEventListener('click', (e) => {
            e.stopPropagation();
        });
    }
    
    initSubmenus() {
        const toggles = this.dropdown.querySelectorAll(this.submenuToggles);
        
        toggles.forEach((toggle) => {
            toggle.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                
                const submenu = toggle.closest('.mobile-menu-item').querySelector('.mobile-submenu');
                if (submenu) {
                    submenu.classList.toggle('show');
                    toggle.classList.toggle('rotate');
                }
            });
        });
    }
    
    toggle() {
        this.dropdown.classList.toggle('active');
        
        // Update button icon
        const icon = this.menuBtn.querySelector('i');
        if (icon) {
            if (this.dropdown.classList.contains('active')) {
                icon.classList.remove('fa-bars');
                icon.classList.add('fa-times');
            } else {
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            }
        }
        
        // Prevent body scroll when menu is open
        if (this.dropdown.classList.contains('active')) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = '';
        }
    }
    
    open() {
        this.dropdown.classList.add('active');
        const icon = this.menuBtn.querySelector('i');
        if (icon) {
            icon.classList.remove('fa-bars');
            icon.classList.add('fa-times');
        }
        document.body.style.overflow = 'hidden';
    }
    
    close() {
        this.dropdown.classList.remove('active');
        const icon = this.menuBtn.querySelector('i');
        if (icon) {
            icon.classList.remove('fa-times');
            icon.classList.add('fa-bars');
        }
        document.body.style.overflow = '';
    }
    
    // Update badge count
    updateBadge(menuId, count) {
        const badge = this.dropdown.querySelector(`[data-menu="${menuId}"] .mobile-menu-badge`);
        if (badge) {
            if (count > 0) {
                badge.textContent = count > 99 ? '99+' : count;
                badge.style.display = 'inline-block';
            } else {
                badge.style.display = 'none';
            }
        }
    }
    
    // Set active menu
    setActiveMenu(url) {
        const links = this.dropdown.querySelectorAll('.mobile-menu-link');
        links.forEach((link) => {
            if (link.href === url) {
                link.classList.add('active');
            } else {
                link.classList.remove('active');
            }
        });
    }
}

// Auto-initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    window.mobileMenu = new MobileHamburgerMenu();
    
    // Set active menu based on current URL
    const currentUrl = window.location.href;
    if (window.mobileMenu) {
        window.mobileMenu.setActiveMenu(currentUrl);
    }
});

// Export for use in other scripts
window.MobileHamburgerMenu = MobileHamburgerMenu;