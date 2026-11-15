import { Badge } from '@/components/ui/badge';
import { ToggleGroup, ToggleGroupItem } from '@/components/ui/toggle-group';

export function SegmentedPicker({
    label,
    value,
    onValueChange,
    options,
}: {
    label: string;
    value: string;
    onValueChange: (value: string) => void;
    options: { value: string; label: string; count?: number }[];
}) {
    return (
        <ToggleGroup
            type="single"
            value={value}
            onValueChange={(next) => {
                if (next) onValueChange(next);
            }}
            aria-label={label}
            className="mx-auto w-fit max-w-full flex-wrap justify-center gap-1.5 rounded-xl border border-border bg-card p-1.5"
        >
            {options.map((option) => (
                <ToggleGroupItem
                    key={option.value}
                    value={option.value}
                    className="h-11 max-w-full shrink rounded-lg px-4 text-xs font-medium whitespace-normal text-muted-foreground first:rounded-lg last:rounded-lg hover:bg-accent data-[state=on]:bg-primary data-[state=on]:text-primary-foreground"
                >
                    {option.label}
                    {option.count !== undefined && (
                        <Badge
                            variant="secondary"
                            className="ml-1 border-0 px-1.5 py-0 text-[10px] text-inherit"
                        >
                            {option.count}
                        </Badge>
                    )}
                </ToggleGroupItem>
            ))}
        </ToggleGroup>
    );
}
