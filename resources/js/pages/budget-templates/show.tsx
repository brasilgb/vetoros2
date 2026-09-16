import { Head, Link, router } from '@inertiajs/react';
import { PencilIcon, PlusIcon, TrashIcon } from 'lucide-react';
import { TemplateItemFormDialog } from '@/components/budgets/template-item-form-dialog';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import budgetTemplates from '@/routes/budget-templates';
import type { BudgetItemType, BudgetTemplateDetail } from '@/types';

const ITEM_TYPE_LABELS: Record<string, string> = {
    service: 'Serviço',
    part: 'Peça',
    other: 'Outro',
};

export default function BudgetTemplateShow({
    template,
    itemTypes,
}: {
    template: BudgetTemplateDetail;
    itemTypes: BudgetItemType[];
}) {
    function removeItem(itemId: number) {
        if (window.confirm('Remover este item do modelo?')) {
            router.delete(
                budgetTemplates.items.destroy([template.id, itemId]).url,
                { preserveScroll: true },
            );
        }
    }

    return (
        <>
            <Head title={template.name} />

            <div className="mx-auto flex max-w-4xl flex-col gap-6 p-6 md:p-8">
                <div>
                    <Link
                        href={budgetTemplates.index().url}
                        className="text-muted-foreground text-sm hover:underline"
                    >
                        ← Orçamentos pré-definidos
                    </Link>
                    <div className="mt-1 flex flex-wrap items-center justify-between gap-4">
                        <div className="flex items-center gap-3">
                            <Heading
                                title={template.name}
                                description={template.description ?? undefined}
                            />
                            <Badge
                                variant={
                                    template.active ? 'default' : 'outline'
                                }
                            >
                                {template.active ? 'Ativo' : 'Inativo'}
                            </Badge>
                        </div>
                        <Button asChild variant="outline">
                            <Link href={budgetTemplates.edit(template.id).url}>
                                Editar modelo
                            </Link>
                        </Button>
                    </div>
                </div>

                <div className="space-y-3">
                    <div className="flex justify-end">
                        <TemplateItemFormDialog
                            templateId={template.id}
                            itemTypes={itemTypes}
                            trigger={
                                <Button
                                    type="button"
                                    variant="outline"
                                    size="sm"
                                >
                                    <PlusIcon />
                                    Adicionar item
                                </Button>
                            }
                        />
                    </div>

                    <div className="rounded-lg border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Descrição</TableHead>
                                    <TableHead>Tipo</TableHead>
                                    <TableHead>Qtd.</TableHead>
                                    <TableHead>Preço unit.</TableHead>
                                    <TableHead>Desconto</TableHead>
                                    <TableHead>Total</TableHead>
                                    <TableHead />
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {template.items.length === 0 && (
                                    <TableRow>
                                        <TableCell
                                            colSpan={7}
                                            className="text-muted-foreground py-6 text-center"
                                        >
                                            Nenhum item neste modelo.
                                        </TableCell>
                                    </TableRow>
                                )}
                                {template.items.map((item) => (
                                    <TableRow key={item.id}>
                                        <TableCell>
                                            {item.description}
                                        </TableCell>
                                        <TableCell>
                                            {ITEM_TYPE_LABELS[item.type] ??
                                                item.type}
                                        </TableCell>
                                        <TableCell>{item.quantity}</TableCell>
                                        <TableCell>
                                            R$ {item.unit_price}
                                        </TableCell>
                                        <TableCell>
                                            R$ {item.discount_amount}
                                        </TableCell>
                                        <TableCell>R$ {item.total}</TableCell>
                                        <TableCell>
                                            <div className="flex gap-1">
                                                <TemplateItemFormDialog
                                                    templateId={template.id}
                                                    item={item}
                                                    itemTypes={itemTypes}
                                                    trigger={
                                                        <Button
                                                            type="button"
                                                            variant="ghost"
                                                            size="icon"
                                                        >
                                                            <PencilIcon />
                                                        </Button>
                                                    }
                                                />
                                                <Button
                                                    type="button"
                                                    variant="ghost"
                                                    size="icon"
                                                    onClick={() =>
                                                        removeItem(item.id)
                                                    }
                                                >
                                                    <TrashIcon />
                                                </Button>
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    </div>
                </div>
            </div>
        </>
    );
}
