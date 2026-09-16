import { Head, Link, router } from '@inertiajs/react';
import { PencilIcon, PlusIcon, TrashIcon } from 'lucide-react';
import { useState } from 'react';
import { BudgetItemFormDialog } from '@/components/budgets/budget-item-form-dialog';
import { BudgetStatusBadge } from '@/components/budgets/budget-status-badge';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import budgets from '@/routes/budgets';
import type { BudgetDetail, BudgetItemType, OrderOption } from '@/types';

const ORDER_STATUS_LABELS: Record<string, string> = {
    open: 'Aberta',
    cancelled: 'Cancelada',
    budget_generated: 'Orçamento gerado',
    budget_approved: 'Orçamento aprovado',
    budget_rejected: 'Orçamento rejeitado',
    in_progress: 'Em andamento',
    completed: 'Concluída',
    not_executed: 'Não executada',
    waiting_customer: 'Aguardando cliente',
    delivered: 'Entregue',
};

const ITEM_TYPE_LABELS: Record<string, string> = {
    service: 'Serviço',
    part: 'Peça',
    other: 'Outro',
};

export default function BudgetShow({
    budget,
    itemTypes,
    compatibleOrders,
}: {
    budget: BudgetDetail;
    itemTypes: BudgetItemType[];
    compatibleOrders: OrderOption[];
}) {
    const [attachOrderId, setAttachOrderId] = useState('');

    function post(url: string) {
        router.post(url, {}, { preserveScroll: true });
    }

    function confirmPost(url: string, message: string) {
        if (window.confirm(message)) {
            post(url);
        }
    }

    function removeItem(itemId: number) {
        if (window.confirm('Remover este item do orçamento?')) {
            router.delete(budgets.items.destroy([budget.id, itemId]).url, {
                preserveScroll: true,
            });
        }
    }

    return (
        <>
            <Head title={`Orçamento #${budget.budget_number}`} />

            <div className="mx-auto flex max-w-4xl flex-col gap-6 p-6 md:p-8">
                <div className="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <Link
                            href={budgets.index().url}
                            className="text-muted-foreground text-sm hover:underline"
                        >
                            ← Orçamentos
                        </Link>
                        <div className="mt-1 flex items-center gap-3">
                            <Heading
                                title={`Orçamento #${budget.budget_number}`}
                            />
                            <BudgetStatusBadge status={budget.status} />
                        </div>
                    </div>

                    <div className="flex flex-wrap gap-2">
                        {budget.can_send && (
                            <Button
                                onClick={() =>
                                    post(budgets.send(budget.id).url)
                                }
                            >
                                Enviar
                            </Button>
                        )}
                        {budget.can_approve && (
                            <Button
                                onClick={() =>
                                    post(budgets.approve(budget.id).url)
                                }
                            >
                                Aprovar
                            </Button>
                        )}
                        {budget.can_reject && (
                            <Button
                                variant="destructive"
                                onClick={() =>
                                    confirmPost(
                                        budgets.reject(budget.id).url,
                                        'Rejeitar este orçamento?',
                                    )
                                }
                            >
                                Rejeitar
                            </Button>
                        )}
                        {budget.can_cancel && (
                            <Button
                                variant="outline"
                                onClick={() =>
                                    confirmPost(
                                        budgets.cancel(budget.id).url,
                                        'Cancelar este orçamento?',
                                    )
                                }
                            >
                                Cancelar
                            </Button>
                        )}
                        {budget.order?.status === 'budget_rejected' && (
                            <Button
                                variant="secondary"
                                onClick={() =>
                                    confirmPost(
                                        budgets.reopen(budget.id).url,
                                        'Reabrir a Ordem de Serviço para um novo orçamento?',
                                    )
                                }
                            >
                                Reabrir OS
                            </Button>
                        )}
                    </div>
                </div>

                <Tabs defaultValue="geral">
                    <TabsList>
                        <TabsTrigger value="geral">Geral</TabsTrigger>
                        <TabsTrigger value="itens">
                            Itens ({budget.items.length})
                        </TabsTrigger>
                        <TabsTrigger value="vinculos">
                            Vínculos e status
                        </TabsTrigger>
                    </TabsList>

                    <TabsContent value="geral">
                        <dl className="grid gap-4 rounded-xl border p-4 sm:grid-cols-2">
                            <Field
                                label="Cliente"
                                value={budget.customer?.name}
                            />
                            <Field
                                label="Documento"
                                value={budget.customer?.document ?? undefined}
                            />
                            <Field
                                label="Empresa"
                                value={budget.company?.trade_name}
                            />
                            <Field
                                label="Unidade"
                                value={budget.branch?.name ?? '—'}
                            />
                            <Field
                                label="Subtotal"
                                value={`R$ ${budget.subtotal}`}
                            />
                            <Field
                                label="Desconto"
                                value={`R$ ${budget.discount_amount}`}
                            />
                            <Field label="Total" value={`R$ ${budget.total}`} />
                            <Field
                                label="Válido até"
                                value={
                                    budget.valid_until
                                        ? new Date(
                                              budget.valid_until,
                                          ).toLocaleDateString('pt-BR')
                                        : '—'
                                }
                            />
                            <Field
                                label="Criado por"
                                value={budget.creator?.name ?? '—'}
                            />
                            <Field
                                label="Criado em"
                                value={
                                    budget.created_at
                                        ? new Date(
                                              budget.created_at,
                                          ).toLocaleString('pt-BR')
                                        : '—'
                                }
                            />
                            <div className="sm:col-span-2">
                                <dt className="text-muted-foreground text-sm">
                                    Observações
                                </dt>
                                <dd className="whitespace-pre-wrap">
                                    {budget.notes || '—'}
                                </dd>
                            </div>
                        </dl>
                    </TabsContent>

                    <TabsContent value="itens">
                        <div className="space-y-3">
                            {budget.can_edit ? (
                                <div className="flex justify-end">
                                    <BudgetItemFormDialog
                                        budgetId={budget.id}
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
                            ) : (
                                <p className="text-muted-foreground text-sm">
                                    Os itens deste orçamento não podem mais ser
                                    alterados neste status.
                                </p>
                            )}

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
                                            {budget.can_edit && <TableHead />}
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {budget.items.length === 0 && (
                                            <TableRow>
                                                <TableCell
                                                    colSpan={7}
                                                    className="text-muted-foreground py-6 text-center"
                                                >
                                                    Nenhum item neste orçamento.
                                                </TableCell>
                                            </TableRow>
                                        )}
                                        {budget.items.map((item) => (
                                            <TableRow key={item.id}>
                                                <TableCell>
                                                    {item.description}
                                                </TableCell>
                                                <TableCell>
                                                    {ITEM_TYPE_LABELS[
                                                        item.type
                                                    ] ?? item.type}
                                                </TableCell>
                                                <TableCell>
                                                    {item.quantity}
                                                </TableCell>
                                                <TableCell>
                                                    R$ {item.unit_price}
                                                </TableCell>
                                                <TableCell>
                                                    R$ {item.discount_amount}
                                                </TableCell>
                                                <TableCell>
                                                    R$ {item.total}
                                                </TableCell>
                                                {budget.can_edit && (
                                                    <TableCell>
                                                        <div className="flex gap-1">
                                                            <BudgetItemFormDialog
                                                                budgetId={
                                                                    budget.id
                                                                }
                                                                item={item}
                                                                itemTypes={
                                                                    itemTypes
                                                                }
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
                                                                    removeItem(
                                                                        item.id,
                                                                    )
                                                                }
                                                            >
                                                                <TrashIcon />
                                                            </Button>
                                                        </div>
                                                    </TableCell>
                                                )}
                                            </TableRow>
                                        ))}
                                    </TableBody>
                                </Table>
                            </div>
                        </div>
                    </TabsContent>

                    <TabsContent value="vinculos">
                        <div className="space-y-4 rounded-xl border p-4">
                            {budget.order ? (
                                <p>
                                    Vinculado à Ordem de Serviço{' '}
                                    <span className="font-medium">
                                        #{budget.order.order_number}
                                    </span>
                                    , atualmente{' '}
                                    <span className="font-medium">
                                        {ORDER_STATUS_LABELS[
                                            budget.order.status
                                        ] ?? budget.order.status}
                                    </span>
                                    .
                                </p>
                            ) : (
                                <div className="space-y-3">
                                    <p className="text-muted-foreground text-sm">
                                        Este orçamento ainda não está vinculado
                                        a uma Ordem de Serviço.
                                    </p>
                                    {budget.can_edit &&
                                        compatibleOrders.length > 0 && (
                                            <div className="flex flex-wrap items-end gap-3">
                                                <div className="grid gap-1.5">
                                                    <span className="text-sm font-medium">
                                                        Vincular à Ordem de
                                                        Serviço
                                                    </span>
                                                    <Select
                                                        value={attachOrderId}
                                                        onValueChange={
                                                            setAttachOrderId
                                                        }
                                                    >
                                                        <SelectTrigger className="w-64">
                                                            <SelectValue placeholder="Selecione a OS" />
                                                        </SelectTrigger>
                                                        <SelectContent>
                                                            {compatibleOrders.map(
                                                                (order) => (
                                                                    <SelectItem
                                                                        key={
                                                                            order.id
                                                                        }
                                                                        value={String(
                                                                            order.id,
                                                                        )}
                                                                    >
                                                                        #
                                                                        {
                                                                            order.order_number
                                                                        }
                                                                    </SelectItem>
                                                                ),
                                                            )}
                                                        </SelectContent>
                                                    </Select>
                                                </div>
                                                <Button
                                                    type="button"
                                                    disabled={!attachOrderId}
                                                    onClick={() =>
                                                        router.post(
                                                            budgets.attachToOrder(
                                                                budget.id,
                                                            ).url,
                                                            {
                                                                order_id:
                                                                    attachOrderId,
                                                            },
                                                            {
                                                                preserveScroll: true,
                                                            },
                                                        )
                                                    }
                                                >
                                                    Vincular
                                                </Button>
                                            </div>
                                        )}
                                </div>
                            )}

                            <dl className="grid gap-3 sm:grid-cols-2">
                                <Field
                                    label="Status atual"
                                    value={budget.status}
                                />
                                <Field
                                    label="Pode editar itens"
                                    value={budget.can_edit ? 'Sim' : 'Não'}
                                />
                            </dl>
                        </div>
                    </TabsContent>
                </Tabs>
            </div>
        </>
    );
}

function Field({ label, value }: { label: string; value?: string }) {
    return (
        <div>
            <dt className="text-muted-foreground text-sm">{label}</dt>
            <dd>{value || '—'}</dd>
        </div>
    );
}
