import type { LucideIcon } from 'lucide-react';
import type { ReactNode } from 'react';
import { cn } from '@/lib/utils';

export function EmptyState({
    icon: Icon,
    title,
    description,
    children,
    className,
    titleAs: Title = 'h3',
}: {
    icon: LucideIcon;
    title: string;
    description: string;
    children?: ReactNode;
    className?: string;
    titleAs?: 'h2' | 'h3';
}) {
    return (
        <div
            className={cn(
                'flex flex-col items-center rounded-2xl border border-dashed border-border bg-card/50 px-5 py-10 text-center',
                className,
            )}
        >
            <span className="mb-5 flex size-12 items-center justify-center rounded-2xl border border-border bg-secondary/60 text-brand-highlight">
                <Icon className="size-5" aria-hidden="true" />
            </span>
            <Title className="font-display text-2xl font-extrabold uppercase sm:text-3xl">
                {title}
            </Title>
            <p className="mt-3 max-w-md text-sm leading-relaxed text-muted-foreground">
                {description}
            </p>
            {children && <div className="mt-6">{children}</div>}
        </div>
    );
}
