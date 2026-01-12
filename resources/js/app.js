import 'flowbite';
import Alpine from 'alpinejs';
import { BarcodeScanner, addScannerButton } from './barcode-scanner.js';

// Exponer globalmente para uso en vistas
window.Alpine = Alpine;
window.BarcodeScanner = BarcodeScanner;
window.addScannerButton = addScannerButton;

Alpine.start();

// WAI-ARIA roving tabindex tabs (keyboard navigation)
// - ArrowLeft/ArrowRight (and ArrowUp/ArrowDown) move focus between tabs
// - Home/End jump to first/last
// - Enter/Space activate focused tab
// Mark tablist with: role="tablist" + data-roving-tabs
(() => {
    const isTab = (el) => el instanceof HTMLElement && el.getAttribute('role') === 'tab';
    const getTabs = (tablist) => {
        return Array.from(tablist.querySelectorAll('[role="tab"]')).filter((tab) => {
            const ariaDisabled = (tab.getAttribute('aria-disabled') || '').toLowerCase();
            return !tab.hasAttribute('disabled') && ariaDisabled !== 'true';
        });
    };

    const restoreFocus = (id) => {
        if (!id) return;
        const startedAt = Date.now();
        const timer = window.setInterval(() => {
            const el = document.getElementById(id);
            if (el) {
                try {
                    el.focus({ preventScroll: true });
                } catch {
                    el.focus();
                }
                window.clearInterval(timer);
                return;
            }
            if (Date.now() - startedAt > 2000) {
                window.clearInterval(timer);
            }
        }, 50);
    };

    document.addEventListener('keydown', (event) => {
        const target = event.target;
        if (!isTab(target)) return;

        const tablist = target.closest('[role="tablist"][data-roving-tabs]');
        if (!tablist) return;

        const tabs = getTabs(tablist);
        if (tabs.length === 0) return;

        const currentIndex = Math.max(0, tabs.indexOf(target));

        const key = event.key;
        const isHorizontal = (tablist.getAttribute('aria-orientation') || 'horizontal') !== 'vertical';

        const move = (nextIndex) => {
            const nextTab = tabs[(nextIndex + tabs.length) % tabs.length];
            if (!nextTab) return;
            event.preventDefault();
            nextTab.focus();
            // Activate to keep content in sync with focus (automatic activation)
            nextTab.click();
            restoreFocus(nextTab.id);
        };

        if (key === 'Home') return move(0);
        if (key === 'End') return move(tabs.length - 1);

        // Activate focused tab
        if (key === 'Enter' || key === ' ') {
            event.preventDefault();
            target.click();
            restoreFocus(target.id);
            return;
        }

        if (isHorizontal) {
            if (key === 'ArrowRight' || key === 'ArrowDown') return move(currentIndex + 1);
            if (key === 'ArrowLeft' || key === 'ArrowUp') return move(currentIndex - 1);
        } else {
            if (key === 'ArrowDown' || key === 'ArrowRight') return move(currentIndex + 1);
            if (key === 'ArrowUp' || key === 'ArrowLeft') return move(currentIndex - 1);
        }
    });
})();

document.addEventListener('DOMContentLoaded', () => {
    const themeToggleBtns = Array.from(document.querySelectorAll('[data-theme-toggle]'));
    if (themeToggleBtns.length === 0) return;

    const setButtonIcons = (btn, isDark) => {
        const darkIcon = btn.querySelector('[data-theme-icon="dark"]');
        const lightIcon = btn.querySelector('[data-theme-icon="light"]');
        if (!darkIcon || !lightIcon) return;
        lightIcon.classList.toggle('hidden', !isDark);
        darkIcon.classList.toggle('hidden', isDark);
    };

    const updateAllIcons = (isDark) => {
        themeToggleBtns.forEach((btn) => setButtonIcons(btn, isDark));
    };

    const stored = localStorage.getItem('color-theme');
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    const isDark = stored === 'dark' || (!stored && prefersDark) || document.documentElement.classList.contains('dark');
    updateAllIcons(isDark);

    themeToggleBtns.forEach((btn) => {
        btn.addEventListener('click', () => {
            const currentlyDark = document.documentElement.classList.contains('dark');
            const nextIsDark = !currentlyDark;
            document.documentElement.classList.toggle('dark', nextIsDark);
            localStorage.setItem('color-theme', nextIsDark ? 'dark' : 'light');
            updateAllIcons(nextIsDark);
        });
    });
});
