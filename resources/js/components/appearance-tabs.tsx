import type { LucideIcon } from 'lucide-react';
import { Monitor, Moon, Sun } from 'lucide-react';
import type { HTMLAttributes } from 'react';
import type { Appearance } from '@/hooks/use-appearance';
import { useAppearance } from '@/hooks/use-appearance';
import { cn } from '@/lib/utils';

export default function AppearanceTabs({
    className,
    compact = false,
    ...props
}: HTMLAttributes<HTMLDivElement> & { compact?: boolean }) {
    const { appearance, updateAppearance } = useAppearance();
    const tabs: { value: Appearance; icon: LucideIcon; label: string }[] = [
        { value: 'light', icon: Sun, label: 'Clair' },
        { value: 'dark', icon: Moon, label: 'Sombre' },
        { value: 'system', icon: Monitor, label: 'Système' },
    ];

    return (
        <div
            role="group"
            aria-label="Thème"
            lang="fr"
            className={cn(
                'inline-flex max-w-full flex-wrap gap-1 rounded-xl border border-border bg-secondary/50 p-1',
                className,
            )}
            {...props}
        >
            {tabs.map(({ value, icon: Icon, label }) => (
                <button
                    type="button"
                    key={value}
                    title={label}
                    aria-pressed={appearance === value}
                    onClick={() => updateAppearance(value)}
                    className={cn(
                        'flex min-h-9 items-center justify-center rounded-lg transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ring',
                        compact ? 'size-9' : 'gap-2 px-3',
                        appearance === value
                            ? 'bg-card text-brand-highlight shadow-xs'
                            : 'text-muted-foreground hover:bg-accent hover:text-foreground',
                    )}
                >
                    <Icon className="size-4" aria-hidden="true" />
                    <span className={compact ? 'sr-only' : 'text-sm'}>
                        {label}
                    </span>
                </button>
            ))}
        </div>
    );
}
