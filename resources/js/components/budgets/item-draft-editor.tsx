import { PlusIcon, TrashIcon } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import type { BudgetItemType } from '@/types';

export type ItemDraft = {
    type: BudgetItemType;
    description: string;
    quantity: string;
    unit_price: string;
    discount_amount: string;
};

const TYPE_LABELS: Record<BudgetItemType, string> = {
    service: 'Serviço',
    part: 'Peça',
    other: 'Outro',
};

export function ItemDraftEditor({
    items,
    itemTypes,
    onChange,
}: {
    items: ItemDraft[];
    itemTypes: BudgetItemType[];
    onChange: (items: ItemDraft[]) => void;
}) {
    function update(index: number, patch: Partial<ItemDraft>) {
        onChange(
            items.map((item, i) =>
                i === index ? { ...item, ...patch } : item,
            ),
        );
    }

    function remove(index: number) {
        onChange(items.filter((_, i) => i !== index));
    }

    function add() {
        onChange([
            ...items,
            {
                type: 'service',
                description: '',
                quantity: '1',
                unit_price: '0.00',
                discount_amount: '0.00',
            },
        ]);
    }

    const estimatedTotal = items.reduce((sum, item) => {
        const quantity = Number(item.quantity) || 0;
        const unitPrice = Number(item.unit_price) || 0;
        const discount = Number(item.discount_amount) || 0;

        return sum + quantity * unitPrice - discount;
    }, 0);

    return (
        <div className="space-y-3">
            <div className="rounded-lg border">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead className="w-32">Tipo</TableHead>
                            <TableHead>Descrição</TableHead>
                            <TableHead className="w-24">Qtd.</TableHead>
                            <TableHead className="w-28">Preço unit.</TableHead>
                            <TableHead className="w-28">Desconto</TableHead>
                            <TableHead className="w-10" />
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        {items.length === 0 && (
                            <TableRow>
                                <TableCell
                                    colSpan={6}
                                    className="text-muted-foreground py-6 text-center"
                                >
                                    Nenhum item adicionado.
                                </TableCell>
                            </TableRow>
                        )}
                        {items.map((item, index) => (
                            <TableRow key={index}>
                                <TableCell>
                                    <Select
                                        value={item.type}
                                        onValueChange={(value) =>
                                            update(index, {
                                                type: value as BudgetItemType,
                                            })
                                        }
                                    >
                                        <SelectTrigger className="w-full">
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {itemTypes.map((type) => (
                                                <SelectItem
                                                    key={type}
                                                    value={type}
                                                >
                                                    {TYPE_LABELS[type] ?? type}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                </TableCell>
                                <TableCell>
                                    <Input
                                        value={item.description}
                                        onChange={(e) =>
                                            update(index, {
                                                description: e.target.value,
                                            })
                                        }
                                        placeholder="Descrição do item"
                                    />
                                </TableCell>
                                <TableCell>
                                    <Input
                                        value={item.quantity}
                                        onChange={(e) =>
                                            update(index, {
                                                quantity: e.target.value,
                                            })
                                        }
                                        inputMode="decimal"
                                    />
                                </TableCell>
                                <TableCell>
                                    <Input
                                        value={item.unit_price}
                                        onChange={(e) =>
                                            update(index, {
                                                unit_price: e.target.value,
                                            })
                                        }
                                        inputMode="decimal"
                                    />
                                </TableCell>
                                <TableCell>
                                    <Input
                                        value={item.discount_amount}
                                        onChange={(e) =>
                                            update(index, {
                                                discount_amount: e.target.value,
                                            })
                                        }
                                        inputMode="decimal"
                                    />
                                </TableCell>
                                <TableCell>
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        size="icon"
                                        onClick={() => remove(index)}
                                    >
                                        <TrashIcon />
                                    </Button>
                                </TableCell>
                            </TableRow>
                        ))}
                    </TableBody>
                </Table>
            </div>

            <div className="flex items-center justify-between">
                <Button type="button" variant="outline" size="sm" onClick={add}>
                    <PlusIcon />
                    Adicionar item
                </Button>
                <p className="text-muted-foreground text-sm">
                    Estimativa (o backend recalcula o valor oficial):{' '}
                    <span className="font-medium">
                        R$ {estimatedTotal.toFixed(2)}
                    </span>
                </p>
            </div>
        </div>
    );
}
