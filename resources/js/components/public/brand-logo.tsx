import { cn } from '@/lib/utils';

export function BrandLogo({ className }: { className?: string }) {
    return (
        <span
            className={cn('brand-logo', className)}
            role="img"
            aria-label="GameStackr TCG"
        >
            <span className="brand-logo-tag" aria-hidden="true">
                <span>TCG</span>
            </span>
            <span className="brand-logo-name" aria-hidden="true">
                GameStackr
            </span>
        </span>
    );
}
