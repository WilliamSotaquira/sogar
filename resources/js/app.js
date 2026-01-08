import 'flowbite';
import Alpine from 'alpinejs';
import { BarcodeScanner, addScannerButton } from './barcode-scanner.js';

// Exponer globalmente para uso en vistas
window.Alpine = Alpine;
window.BarcodeScanner = BarcodeScanner;
window.addScannerButton = addScannerButton;

Alpine.start();

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
