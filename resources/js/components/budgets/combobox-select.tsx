import { useState } from 'react';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

export type ComboboxOption = { value: string; label: string };

export function ComboboxSelect({
    options,
    value,
    onValueChange,
    placeholder,
    searchPlaceholder,
}: {
    options: ComboboxOption[];
    value: string;
    onValueChange: (value: string) => void;
    placeholder?: string;
    searchPlaceholder?: string;
}) {
    const [query, setQuery] = useState('');
    const filtered =
        query.trim() === ''
            ? options
            : options.filter((option) =>
                  option.label
                      .toLowerCase()
                      .includes(query.trim().toLowerCase()),
              );

    return (
        <div className="grid gap-1.5">
            <Input
                value={query}
                onChange={(e) => setQuery(e.target.value)}
                placeholder={searchPlaceholder ?? 'Buscar…'}
                className="h-8 text-xs"
            />
            <Select value={value} onValueChange={onValueChange}>
                <SelectTrigger className="w-full">
                    <SelectValue placeholder={placeholder ?? 'Selecione'} />
                </SelectTrigger>
                <SelectContent>
                    {filtered.length === 0 && (
                        <div className="text-muted-foreground px-2 py-1.5 text-sm">
                            Nenhum resultado
                        </div>
                    )}
                    {filtered.map((option) => (
                        <SelectItem key={option.value} value={option.value}>
                            {option.label}
                        </SelectItem>
                    ))}
                </SelectContent>
            </Select>
        </div>
    );
}
