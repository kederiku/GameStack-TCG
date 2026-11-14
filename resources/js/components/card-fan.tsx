import { Diamond, Layers2, Sparkles, Star } from 'lucide-react';
import { cn } from '@/lib/utils';

export function CardFan({ className }: { className?: string }) {
    return (
        <div className={cn('card-fan', className)} aria-hidden="true">
            {[Diamond, Layers2, Sparkles, Star].map((Icon, index) => (
                <div className="fan-card" key={index}>
                    <span className="fan-card-edition">TCG</span>
                    <span className="fan-card-symbol">
                        <Icon strokeWidth={1} />
                    </span>
                    <span className="fan-card-brand">GameStackr</span>
                </div>
            ))}
        </div>
    );
}
