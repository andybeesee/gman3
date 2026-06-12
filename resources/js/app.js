const THEME_STORAGE_KEY = 'theme';
const SIDEBAR_STORAGE_KEY = 'sidebar-collapsed';

const applyTheme = (theme) => {
    const html = document.documentElement;
    html.classList.remove('light', 'dark');

    if (theme === 'light') {
        html.classList.add('light');
    } else if (theme === 'dark') {
        html.classList.add('dark');
    }
};

const getStoredTheme = () => localStorage.getItem(THEME_STORAGE_KEY) ?? 'system';

applyTheme(getStoredTheme());

const closeMenus = (layout) => {
    layout.querySelectorAll('[data-theme-menu], [data-user-menu]').forEach((menu) => {
        menu.classList.remove('is-open');
    });

    layout.querySelector('[data-theme-toggle]')?.setAttribute('aria-expanded', 'false');
    layout.querySelector('[data-user-toggle]')?.setAttribute('aria-expanded', 'false');
};

const initMenu = (layout, { toggleSelector, menuSelector, onOpen }) => {
    const toggle = layout.querySelector(toggleSelector);
    const menu = layout.querySelector(menuSelector);

    if (! toggle || ! menu) {
        return;
    }

    toggle.addEventListener('click', (event) => {
        event.stopPropagation();

        const willOpen = ! menu.classList.contains('is-open');

        closeMenus(layout);

        if (willOpen) {
            menu.classList.add('is-open');
            toggle.setAttribute('aria-expanded', 'true');
            onOpen?.();
        }
    });
};

const initThemeMenu = (layout) => {
    const themeOptions = layout.querySelectorAll('[data-theme-option]');

    const setActiveThemeOption = (theme) => {
        themeOptions.forEach((option) => {
            option.classList.toggle('is-active', option.dataset.themeOption === theme);
        });
    };

    setActiveThemeOption(getStoredTheme());

    initMenu(layout, {
        toggleSelector: '[data-theme-toggle]',
        menuSelector: '[data-theme-menu]',
        onOpen: () => setActiveThemeOption(getStoredTheme()),
    });

    themeOptions.forEach((option) => {
        option.addEventListener('click', () => {
            const theme = option.dataset.themeOption;

            localStorage.setItem(THEME_STORAGE_KEY, theme);
            applyTheme(theme);
            setActiveThemeOption(theme);
            closeMenus(layout);
        });
    });
};

const initUserMenu = (layout) => {
    initMenu(layout, {
        toggleSelector: '[data-user-toggle]',
        menuSelector: '[data-user-menu]',
    });
};

const initSidebar = (layout) => {
    const toggleButtons = layout.querySelectorAll('[data-sidebar-toggle]');
    const collapsed = localStorage.getItem(SIDEBAR_STORAGE_KEY) === 'true';

    const setCollapsed = (isCollapsed) => {
        layout.classList.toggle('is-collapsed', isCollapsed);
        localStorage.setItem(SIDEBAR_STORAGE_KEY, String(isCollapsed));

        toggleButtons.forEach((toggleButton) => {
            toggleButton.setAttribute('aria-expanded', String(! isCollapsed));
        });

        layout.querySelectorAll('[data-sidebar-tooltip]').forEach((element) => {
            if (isCollapsed) {
                element.setAttribute('title', element.dataset.sidebarTooltip);
            } else {
                element.removeAttribute('title');
            }
        });

        if (isCollapsed) {
            closeMenus(layout);
        }
    };

    setCollapsed(collapsed);

    toggleButtons.forEach((toggleButton) => {
        toggleButton.addEventListener('click', () => {
            setCollapsed(! layout.classList.contains('is-collapsed'));
        });
    });
};

document.addEventListener('DOMContentLoaded', () => {
    const layout = document.querySelector('[data-app-layout]');

    if (! layout) {
        return;
    }

    initSidebar(layout);
    initThemeMenu(layout);
    initUserMenu(layout);

    document.addEventListener('click', (event) => {
        const clickedInsideMenu = event.target.closest('[data-theme-menu], [data-user-menu], [data-theme-toggle], [data-user-toggle]');

        if (! clickedInsideMenu) {
            closeMenus(layout);
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeMenus(layout);
        }
    });
});
