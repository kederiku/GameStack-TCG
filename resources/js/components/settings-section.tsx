import type { LucideIcon } from 'lucide-react';
import type { ReactNode } from 'react';
import { cn } from '@/lib/utils';

export default function SettingsSection({
    id,
    title,
    description,
    icon: Icon,
    action,
    children,
    destructive = false,
}: {
    id: string;
    title: string;
    description: string;
    icon: LucideIcon;
    action?: ReactNode;
    children: ReactNode;
    destructive?: boolean;
}) {
    return (
        <section
            aria-labelledby={`${id}-title`}
            className={cn(
                'min-w-0 rounded-2xl border bg-card text-card-foreground',
                destructive ? 'border-destructive/25' : 'border-border',
            )}
        >
            <header className="flex flex-wrap items-start justify-between gap-4 border-b border-border px-5 py-5 sm:px-6">
                <div className="flex min-w-0 flex-1 items-start gap-3">
                    <span
                        className={cn(
                            'flex size-10 shrink-0 items-center justify-center rounded-xl',
                            destructive
                                ? 'bg-destructive/10 text-destructive-foreground'
                                : 'bg-primary/10 text-brand-highlight',
                        )}
                    >
                        <Icon className="size-5" aria-hidden="true" />
                    </span>
                    <div className="min-w-0">
                        <h2
                            id={`${id}-title`}
                            className="font-display text-2xl leading-tight font-extrabold uppercase"
                        >
                            {title}
                        </h2>
                        <p className="mt-1 text-sm leading-6 text-muted-foreground">
                            {description}
                        </p>
                    </div>
                </div>
                {action}
            </header>
            <div className="min-w-0 px-5 py-6 sm:px-6">{children}</div>
        </section>
    );
}
