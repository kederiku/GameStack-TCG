// GameStackr uses a single dark appearance across the React application.
export function initializeTheme(): void {
    if (typeof document === 'undefined') return;
    document.documentElement.classList.add('dark');
    document.documentElement.style.colorScheme = 'dark';
}

export function useAppearance() {
    return { appearance: 'dark', resolvedAppearance: 'dark' } as const;
}
