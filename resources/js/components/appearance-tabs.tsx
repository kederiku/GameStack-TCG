import type { LucideIcon } from 'lucide-react';
import { Check, Monitor, Moon, Sun } from 'lucide-react';
import type { HTMLAttributes } from 'react';
import type { Appearance } from '@/hooks/use-appearance';
import { useAppearance } from '@/hooks/use-appearance';
import { cn } from '@/lib/utils';

export default function AppearanceTabs({
    className,
    compact = false,
    preview = false,
    ...props
}: HTMLAttributes<HTMLDivElement> & { compact?: boolean; preview?: boolean }) {
    const { appearance, updateAppearance } = useAppearance();
    const tabs: {
        value: Appearance;
        icon: LucideIcon;
        label: string;
        description: string;
    }[] = [
        {
            value: 'light',
            icon: Sun,
            label: 'Clair',
            description: 'Lumineux, pour chaque détail.',
        },
        {
            value: 'dark',
            icon: Moon,
            label: 'Sombre',
            description: 'Vos cartes sous un autre jour.',
        },
        {
            value: 'system',
            icon: Monitor,
            label: 'Système',
            description: 'Au rythme de votre appareil.',
        },
    ];

    return (
        <div
            role="group"
            aria-label="Thème"
            lang="fr"
            className={cn(
                preview
                    ? 'grid gap-4 sm:grid-cols-3'
                    : 'inline-flex max-w-full flex-wrap gap-1 rounded-xl border border-border bg-secondary/50 p-1',
                className,
            )}
            {...props}
        >
            {tabs.map(({ value, icon: Icon, label, description }) => (
                <button
                    type="button"
                    key={value}
                    title={label}
                    aria-pressed={appearance === value}
                    onClick={() => updateAppearance(value)}
                    className={cn(
                        'min-w-0 transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ring',
                        preview
                            ? 'group rounded-xl border-2 p-3 text-left'
                            : 'flex min-h-9 items-center justify-center rounded-lg',
                        preview
                            ? appearance === value
                                ? 'border-primary bg-primary/5'
                                : 'border-border hover:border-muted-foreground/50 hover:bg-accent/30'
                            : appearance === value
                              ? 'bg-card text-brand-highlight shadow-xs'
                              : 'text-muted-foreground hover:bg-accent hover:text-foreground',
                        !preview && (compact ? 'size-9' : 'gap-2 px-3'),
                    )}
                >
                    {preview && (
                        <span
                            aria-hidden="true"
                            className={cn(
                                'mb-4 flex h-28 overflow-hidden rounded-lg border border-black/10 p-2.5',
                                value === 'light'
                                    ? 'bg-[#f4f6fa]'
                                    : value === 'dark'
                                      ? 'bg-[#181e29]'
                                      : 'bg-[linear-gradient(120deg,#f4f6fa_50%,#181e29_50%)]',
                            )}
                        >
                            <span
                                className={cn(
                                    'mr-2 flex w-1/4 flex-col gap-2 rounded-md p-1.5',
                                    value === 'dark'
                                        ? 'bg-[#283244]'
                                        : 'bg-white',
                                )}
                            >
                                <span className="h-2 w-3/4 rounded-sm bg-[#3767ef]" />
                                <span className="h-1.5 rounded-sm bg-slate-400/30" />
                                <span className="h-1.5 rounded-sm bg-slate-400/30" />
                                <span className="h-1.5 w-2/3 rounded-sm bg-slate-400/30" />
                            </span>
                            <span className="flex flex-1 flex-col gap-2 py-1">
                                <span className="h-2 w-2/3 rounded-sm bg-slate-400/50" />
                                <span className="flex flex-1 gap-1.5">
                                    <span className="flex-1 rounded-md border border-slate-400/20 bg-slate-400/15" />
                                    <span className="flex-1 rounded-md border border-slate-400/20 bg-slate-400/15" />
                                </span>
                                <span className="h-2 w-1/3 rounded-sm bg-[#3767ef]" />
                            </span>
                        </span>
                    )}
                    <span
                        className={cn(
                            'flex items-center',
                            preview ? 'gap-2' : compact ? '' : 'gap-2',
                        )}
                    >
                        <Icon className="size-4 shrink-0" aria-hidden="true" />
                        <span
                            className={
                                compact && !preview
                                    ? 'sr-only'
                                    : 'text-sm font-medium'
                            }
                        >
                            {label}
                        </span>
                        {preview && (
                            <span
                                className={cn(
                                    'ml-auto flex size-5 items-center justify-center rounded-full border',
                                    appearance === value
                                        ? 'border-primary bg-primary text-primary-foreground'
                                        : 'border-border',
                                )}
                                aria-hidden="true"
                            >
                                {appearance === value && (
                                    <Check className="size-3" />
                                )}
                            </span>
                        )}
                    </span>
                    {preview && (
                        <span className="mt-2 block text-xs leading-5 text-muted-foreground">
                            {description}
                        </span>
                    )}
                </button>
            ))}
        </div>
    );
}
