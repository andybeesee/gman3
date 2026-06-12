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

const initThemeMenu = (layout) => {
    const themeButton = layout.querySelector('[data-theme-toggle]');
    const themeMenu = layout.querySelector('[data-theme-menu]');
    const themeOptions = layout.querySelectorAll('[data-theme-option]');

    if (! themeButton || ! themeMenu) {
        return;
    }

    const setActiveThemeOption = (theme) => {
        themeOptions.forEach((option) => {
            option.classList.toggle('is-active', option.dataset.themeOption === theme);
        });
    };

    setActiveThemeOption(getStoredTheme());

    themeButton.addEventListener('click', (event) => {
        event.stopPropagation();
        themeMenu.classList.toggle('is-open');
    });

    themeOptions.forEach((option) => {
        option.addEventListener('click', () => {
            const theme = option.dataset.themeOption;

            localStorage.setItem(THEME_STORAGE_KEY, theme);
            applyTheme(theme);
            setActiveThemeOption(theme);
            themeMenu.classList.remove('is-open');
        });
    });

    document.addEventListener('click', (event) => {
        if (! layout.contains(event.target)) {
            themeMenu.classList.remove('is-open');
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            themeMenu.classList.remove('is-open');
        }
    });
};

const initSidebar = (layout) => {
    const toggleButton = layout.querySelector('[data-sidebar-toggle]');
    const collapsed = localStorage.getItem(SIDEBAR_STORAGE_KEY) === 'true';

    const setCollapsed = (isCollapsed) => {
        layout.classList.toggle('is-collapsed', isCollapsed);
        localStorage.setItem(SIDEBAR_STORAGE_KEY, String(isCollapsed));

        if (toggleButton) {
            toggleButton.setAttribute('aria-expanded', String(! isCollapsed));
        }

        layout.querySelectorAll('[data-sidebar-tooltip]').forEach((element) => {
            if (isCollapsed) {
                element.setAttribute('title', element.dataset.sidebarTooltip);
            } else {
                element.removeAttribute('title');
            }
        });
    };

    setCollapsed(collapsed);

    toggleButton?.addEventListener('click', () => {
        setCollapsed(! layout.classList.contains('is-collapsed'));
    });
};

document.addEventListener('DOMContentLoaded', () => {
    const layout = document.querySelector('[data-app-layout]');

    if (! layout) {
        return;
    }

    initSidebar(layout);
    initThemeMenu(layout);
});
