import 'flowbite';

document.addEventListener('DOMContentLoaded', () => {
    const themeToggleBtn = document.getElementById('theme-toggle');
    const darkIcon = document.getElementById('theme-toggle-dark-icon');
    const lightIcon = document.getElementById('theme-toggle-light-icon');
    if (!themeToggleBtn || !darkIcon || !lightIcon) {
        return;
    }

    const setIcons = (isDark) => {
        lightIcon.classList.toggle('hidden', !isDark);
        darkIcon.classList.toggle('hidden', isDark);
    };

    const stored = localStorage.getItem('color-theme');
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    const isDark = stored === 'dark' || (!stored && prefersDark) || document.documentElement.classList.contains('dark');
    setIcons(isDark);

    themeToggleBtn.addEventListener('click', () => {
        const currentlyDark = document.documentElement.classList.contains('dark');
        const nextIsDark = !currentlyDark;
        document.documentElement.classList.toggle('dark', nextIsDark);
        localStorage.setItem('color-theme', nextIsDark ? 'dark' : 'light');
        setIcons(nextIsDark);
    });
});
