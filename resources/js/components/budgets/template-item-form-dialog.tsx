import { router } from '@inertiajs/react';
import { type ReactNode, useState } from 'react';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import budgetTemplates from '@/routes/budget-templates';
import type { BudgetItemType, BudgetTemplateItem } from '@/types';

const TYPE_LABELS: Record<BudgetItemType, string> = {
    service: 'Serviço',
    part: 'Peça',
    other: 'Outro',
};

export function TemplateItemFormDialog({
    templateId,
    item,
    itemTypes,
    trigger,
}: {
    templateId: number;
    item?: BudgetTemplateItem;
    itemTypes: BudgetItemType[];
    trigger: ReactNode;
}) {
    const [open, setOpen] = useState(false);
    const [type, setType] = useState<BudgetItemType>(item?.type ?? 'service');
    const [description, setDescription] = useState(item?.description ?? '');
    const [quantity, setQuantity] = useState(item?.quantity ?? '1');
    const [unitPrice, setUnitPrice] = useState(item?.unit_price ?? '0.00');
    const [discountAmount, setDiscountAmount] = useState(
        item?.discount_amount ?? '0.00',
    );
    const [notes, setNotes] = useState(item?.notes ?? '');
    const [processing, setProcessing] = useState(false);

    function submit() {
        setProcessing(true);
        const payload = {
            type,
            description,
            quantity,
            unit_price: unitPrice,
            discount_amount: discountAmount,
            notes: notes || null,
        };
        const options = {
            preserveScroll: true,
            onSuccess: () => setOpen(false),
            onFinish: () => setProcessing(false),
        };

        if (item) {
            router.put(
                budgetTemplates.items.update([templateId, item.id]).url,
                payload,
                options,
            );
        } else {
            router.post(
                budgetTemplates.items.store(templateId).url,
                payload,
                options,
            );
        }
    }

    return (
        <Dialog open={open} onOpenChange={setOpen}>
            <DialogTrigger asChild>{trigger}</DialogTrigger>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>
                        {item ? 'Editar item' : 'Adicionar item'}
                    </DialogTitle>
                </DialogHeader>

                <div className="grid gap-4">
                    <div className="grid gap-2">
                        <Label>Tipo</Label>
                        <Select
                            value={type}
                            onValueChange={(value) =>
                                setType(value as BudgetItemType)
                            }
                        >
                            <SelectTrigger className="w-full">
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                {itemTypes.map((option) => (
                                    <SelectItem key={option} value={option}>
                                        {TYPE_LABELS[option] ?? option}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="template-item-description">
                            Descrição
                        </Label>
                        <Input
                            id="template-item-description"
                            value={description}
                            onChange={(e) => setDescription(e.target.value)}
                        />
                    </div>

                    <div className="grid grid-cols-3 gap-4">
                        <div className="grid gap-2">
                            <Label htmlFor="template-item-quantity">
                                Quantidade
                            </Label>
                            <Input
                                id="template-item-quantity"
                                value={quantity}
                                onChange={(e) => setQuantity(e.target.value)}
                                inputMode="decimal"
                            />
                        </div>
                        <div className="grid gap-2">
                            <Label htmlFor="template-item-unit-price">
                                Preço unit.
                            </Label>
                            <Input
                                id="template-item-unit-price"
                                value={unitPrice}
                                onChange={(e) => setUnitPrice(e.target.value)}
                                inputMode="decimal"
                            />
                        </div>
                        <div className="grid gap-2">
                            <Label htmlFor="template-item-discount">
                                Desconto
                            </Label>
                            <Input
                                id="template-item-discount"
                                value={discountAmount}
                                onChange={(e) =>
                                    setDiscountAmount(e.target.value)
                                }
                                inputMode="decimal"
                            />
                        </div>
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="template-item-notes">Observações</Label>
                        <Textarea
                            id="template-item-notes"
                            value={notes}
                            onChange={(e) => setNotes(e.target.value)}
                            rows={2}
                        />
                    </div>
                </div>

                <DialogFooter>
                    <Button
                        type="button"
                        disabled={processing}
                        onClick={submit}
                    >
                        Salvar
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
