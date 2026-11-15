import { useId } from 'react';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { useLocale } from '@/hooks/use-locale';

export function FilterSelect({
    label,
    value,
    options,
    onValueChange,
}: {
    label: string;
    value: string;
    options: { value: string; label: string }[];
    onValueChange: (value: string) => void;
}) {
    const id = useId();
    const { locale } = useLocale();
    return (
        <div className="flex min-w-0 flex-col gap-2">
            <Label
                className="text-xs font-normal text-muted-foreground"
                htmlFor={id}
            >
                {label}
            </Label>
            <Select value={value} onValueChange={onValueChange}>
                <SelectTrigger
                    id={id}
                    aria-label={label}
                    className="w-full min-w-0 bg-card text-xs data-[size=default]:h-11"
                >
                    <SelectValue />
                </SelectTrigger>
                <SelectContent lang={locale} align="end">
                    {options.map((option) => (
                        <SelectItem key={option.value} value={option.value}>
                            {option.label}
                        </SelectItem>
                    ))}
                </SelectContent>
            </Select>
        </div>
    );
}
