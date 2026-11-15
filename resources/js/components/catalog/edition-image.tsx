import { Layers2 } from 'lucide-react';
import { useState } from 'react';
import { cn } from '@/lib/utils';

export function EditionImage({
    imageUrl,
    className,
}: {
    imageUrl: string | null;
    className?: string;
}) {
    const [failedSource, setFailedSource] = useState<string | null>(null);
    return (
        <div
            aria-hidden="true"
            className={cn(
                'flex shrink-0 items-center justify-center bg-secondary/30 p-4',
                className,
            )}
        >
            {imageUrl && failedSource !== imageUrl ? (
                <img
                    src={imageUrl}
                    alt=""
                    loading="lazy"
                    className="max-h-full max-w-full object-contain"
                    onError={() => setFailedSource(imageUrl)}
                />
            ) : (
                <Layers2 className="size-9 stroke-1 text-muted-foreground/60" />
            )}
        </div>
    );
}
