const pendingNavigations = new Set<() => void>();

export function registerPendingNavigation(cancel: () => void) {
    pendingNavigations.add(cancel);
    return () => {
        pendingNavigations.delete(cancel);
    };
}

export function switchLocale(
    alternates: Readonly<Partial<Record<string, string>>>,
    locale: string,
    hash: string,
    visit: (url: string) => void,
) {
    const target = alternates[locale];
    if (!target || !target.startsWith('/') || target.startsWith('//')) return;
    for (const cancel of pendingNavigations) cancel();
    visit(`${target}${hash.startsWith('#') ? hash : ''}`);
}
