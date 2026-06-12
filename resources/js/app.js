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

const resetTaskActionMenuPosition = (menu) => {
    menu.classList.remove('is-positioning', 'is-open-upward');
    menu.style.position = '';
    menu.style.top = '';
    menu.style.left = '';
    menu.style.zIndex = '';
    menu.style.visibility = '';
    menu.style.pointerEvents = '';
};

const positionTaskActionMenu = (toggle, menu) => {
    menu.classList.add('is-open', 'is-positioning');

    const toggleRect = toggle.getBoundingClientRect();
    const menuHeight = menu.offsetHeight;
    const menuWidth = menu.offsetWidth;
    const gap = 4;
    const viewportPadding = 8;

    const spaceBelow = window.innerHeight - toggleRect.bottom;
    const openUpward = spaceBelow < menuHeight + gap && toggleRect.top > menuHeight + gap;

    let top = openUpward
        ? toggleRect.top - menuHeight - gap
        : toggleRect.bottom + gap;

    let left = toggleRect.right - menuWidth;

    top = Math.max(viewportPadding, Math.min(top, window.innerHeight - menuHeight - viewportPadding));
    left = Math.max(viewportPadding, Math.min(left, window.innerWidth - menuWidth - viewportPadding));

    menu.style.position = 'fixed';
    menu.style.top = `${top}px`;
    menu.style.left = `${left}px`;
    menu.style.zIndex = '50';
    menu.classList.toggle('is-open-upward', openUpward);
    menu.classList.remove('is-positioning');
};

const closeTaskActionMenus = (exceptMenu = null) => {
    document.querySelectorAll('[data-task-actions-menu]').forEach((menu) => {
        if (menu !== exceptMenu) {
            menu.classList.remove('is-open');
            resetTaskActionMenuPosition(menu);
        }
    });

    document.querySelectorAll('[data-task-actions-toggle]').forEach((toggle) => {
        const menu = toggle.parentElement?.querySelector('[data-task-actions-menu]');

        if (! menu || menu !== exceptMenu) {
            toggle.setAttribute('aria-expanded', 'false');
        }
    });
};

const initPagination = () => {
    document.querySelectorAll('[data-pagination-page-select]').forEach((select) => {
        select.addEventListener('change', () => {
            if (select.value) {
                window.location.assign(select.value);
            }
        });
    });
};

const initTaskActionMenus = () => {
    document.querySelectorAll('[data-task-actions]').forEach((wrapper) => {
        const toggle = wrapper.querySelector('[data-task-actions-toggle]');
        const menu = wrapper.querySelector('[data-task-actions-menu]');

        if (! toggle || ! menu) {
            return;
        }

        toggle.addEventListener('click', (event) => {
            event.stopPropagation();

            const willOpen = ! menu.classList.contains('is-open');

            closeTaskActionMenus(willOpen ? menu : null);

            if (willOpen) {
                positionTaskActionMenu(toggle, menu);
                toggle.setAttribute('aria-expanded', 'true');
            } else {
                menu.classList.remove('is-open');
                resetTaskActionMenuPosition(menu);
                toggle.setAttribute('aria-expanded', 'false');
            }
        });
    });

    const closeOnViewportChange = () => closeTaskActionMenus();

    window.addEventListener('scroll', closeOnViewportChange, true);
    window.addEventListener('resize', closeOnViewportChange);
};

document.addEventListener('DOMContentLoaded', () => {
    const layout = document.querySelector('[data-app-layout]');

    if (layout) {
        initSidebar(layout);
        initThemeMenu(layout);
        initUserMenu(layout);

        document.addEventListener('click', (event) => {
            const clickedInsideMenu = event.target.closest('[data-theme-menu], [data-user-menu], [data-theme-toggle], [data-user-toggle]');

            if (! clickedInsideMenu) {
                closeMenus(layout);
            }
        });
    }

    initTaskActionMenus();
    initPagination();

    document.addEventListener('click', (event) => {
        if (! event.target.closest('[data-task-actions]')) {
            closeTaskActionMenus();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            if (layout) {
                closeMenus(layout);
            }

            closeTaskActionMenus();
        }
    });
});
