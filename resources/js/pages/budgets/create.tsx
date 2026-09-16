import { Head, router } from '@inertiajs/react';
import type { FormDataConvertible } from '@inertiajs/core';
import { FormEvent, useMemo, useState } from 'react';
import { ComboboxSelect } from '@/components/budgets/combobox-select';
import {
    ItemDraftEditor,
    type ItemDraft,
} from '@/components/budgets/item-draft-editor';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
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
import { ToggleGroup, ToggleGroupItem } from '@/components/ui/toggle-group';
import budgets from '@/routes/budgets';
import type {
    BranchOption,
    BudgetItemType,
    BudgetTemplateDetail,
    CompanyOption,
    CustomerOption,
    OrderOption,
} from '@/types';

type Mode = 'order' | 'standalone';
type Origin = 'manual' | 'template';

function customerLabel(customer: CustomerOption): string {
    return customer.name || customer.trade_name || `Cliente #${customer.id}`;
}

export default function BudgetsCreate({
    customers,
    orders,
    companies,
    branches,
    templates,
    itemTypes,
}: {
    customers: CustomerOption[];
    orders: OrderOption[];
    companies: CompanyOption[];
    branches: BranchOption[];
    templates: BudgetTemplateDetail[];
    itemTypes: BudgetItemType[];
}) {
    const [mode, setMode] = useState<Mode>(
        orders.length > 0 ? 'order' : 'standalone',
    );
    const [origin, setOrigin] = useState<Origin>('manual');
    const [orderId, setOrderId] = useState('');
    const [customerId, setCustomerId] = useState('');
    const [companyId, setCompanyId] = useState(
        companies[0] ? String(companies[0].id) : '',
    );
    const [branchId, setBranchId] = useState('');
    const [templateId, setTemplateId] = useState('');
    const [discountAmount, setDiscountAmount] = useState('0.00');
    const [validUntil, setValidUntil] = useState('');
    const [notes, setNotes] = useState('');
    const [items, setItems] = useState<ItemDraft[]>([]);
    const [processing, setProcessing] = useState(false);
    const [errors, setErrors] = useState<Record<string, string>>({});

    const selectedOrder = orders.find((order) => String(order.id) === orderId);
    const selectedTemplate = templates.find(
        (template) => String(template.id) === templateId,
    );
    const branchesForCompany = branches.filter(
        (branch) => String(branch.company_id) === companyId,
    );

    const orderOptions = useMemo(
        () =>
            orders.map((order) => ({
                value: String(order.id),
                label: `#${order.order_number} — ${customerLabelForOrder(order)}`,
            })),
        [orders],
    );

    function customerLabelForOrder(order: OrderOption): string {
        const customer = customers.find((c) => c.id === order.customer_id);

        return customer
            ? customerLabel(customer)
            : `Cliente #${order.customer_id}`;
    }

    const customerOptions = useMemo(
        () =>
            customers.map((customer) => ({
                value: String(customer.id),
                label: customerLabel(customer),
            })),
        [customers],
    );

    function handleSubmit(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();
        setProcessing(true);

        const payload: Record<string, FormDataConvertible> = {
            discount_amount: discountAmount || '0.00',
            valid_until: validUntil || null,
            notes: notes || null,
        };

        if (mode === 'order') {
            payload.order_id = orderId;
        } else {
            payload.customer_id = customerId;
            payload.company_id = companyId;
            payload.branch_id = branchId || null;
        }

        if (origin === 'template') {
            payload.budget_template_id = templateId;
        } else {
            payload.items = items;
        }

        router.post(budgets.store().url, payload, {
            onError: (formErrors) =>
                setErrors(formErrors as Record<string, string>),
            onFinish: () => setProcessing(false),
        });
    }

    return (
        <>
            <Head title="Novo orçamento" />

            <div className="mx-auto flex max-w-4xl flex-col gap-6 p-6 md:p-8">
                <Heading
                    title="Novo orçamento"
                    description="Escolha o contexto e os itens do orçamento."
                />

                <form onSubmit={handleSubmit} className="space-y-8">
                    <section className="space-y-4 rounded-xl border p-4">
                        <Label className="text-base">Contexto</Label>
                        <ToggleGroup
                            type="single"
                            value={mode}
                            onValueChange={(value) =>
                                value && setMode(value as Mode)
                            }
                        >
                            <ToggleGroupItem value="order">
                                Orçamento para uma OS
                            </ToggleGroupItem>
                            <ToggleGroupItem value="standalone">
                                Orçamento sem OS
                            </ToggleGroupItem>
                        </ToggleGroup>

                        {mode === 'order' ? (
                            <div className="grid gap-2">
                                <Label>Ordem de Serviço</Label>
                                <ComboboxSelect
                                    options={orderOptions}
                                    value={orderId}
                                    onValueChange={setOrderId}
                                    placeholder="Selecione a OS"
                                    searchPlaceholder="Buscar por número da OS…"
                                />
                                <InputError message={errors.order_id} />
                                {selectedOrder && (
                                    <p className="text-muted-foreground text-sm">
                                        Cliente, empresa e unidade serão
                                        herdados automaticamente desta Ordem de
                                        Serviço.
                                    </p>
                                )}
                            </div>
                        ) : (
                            <div className="grid gap-4 sm:grid-cols-3">
                                <div className="grid gap-2 sm:col-span-1">
                                    <Label>Cliente</Label>
                                    <ComboboxSelect
                                        options={customerOptions}
                                        value={customerId}
                                        onValueChange={setCustomerId}
                                        placeholder="Selecione o cliente"
                                        searchPlaceholder="Buscar cliente…"
                                    />
                                    <InputError message={errors.customer_id} />
                                </div>
                                <div className="grid gap-2">
                                    <Label>Empresa</Label>
                                    <Select
                                        value={companyId}
                                        onValueChange={(value) => {
                                            setCompanyId(value);
                                            setBranchId('');
                                        }}
                                    >
                                        <SelectTrigger className="w-full">
                                            <SelectValue placeholder="Selecione" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {companies.map((company) => (
                                                <SelectItem
                                                    key={company.id}
                                                    value={String(company.id)}
                                                >
                                                    {company.trade_name}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    <InputError message={errors.company_id} />
                                </div>
                                <div className="grid gap-2">
                                    <Label>Unidade (opcional)</Label>
                                    <Select
                                        value={branchId || 'none'}
                                        onValueChange={(value) =>
                                            setBranchId(
                                                value === 'none' ? '' : value,
                                            )
                                        }
                                    >
                                        <SelectTrigger className="w-full">
                                            <SelectValue placeholder="Nenhuma" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="none">
                                                Nenhuma
                                            </SelectItem>
                                            {branchesForCompany.map(
                                                (branch) => (
                                                    <SelectItem
                                                        key={branch.id}
                                                        value={String(
                                                            branch.id,
                                                        )}
                                                    >
                                                        {branch.name}
                                                    </SelectItem>
                                                ),
                                            )}
                                        </SelectContent>
                                    </Select>
                                    <InputError message={errors.branch_id} />
                                </div>
                            </div>
                        )}
                    </section>

                    <section className="space-y-4 rounded-xl border p-4">
                        <Label className="text-base">Origem dos itens</Label>
                        <ToggleGroup
                            type="single"
                            value={origin}
                            onValueChange={(value) =>
                                value && setOrigin(value as Origin)
                            }
                        >
                            <ToggleGroupItem value="manual">
                                Começar vazio
                            </ToggleGroupItem>
                            <ToggleGroupItem value="template">
                                Usar orçamento pré-definido
                            </ToggleGroupItem>
                        </ToggleGroup>

                        {origin === 'template' ? (
                            <div className="grid gap-2">
                                <Label>Orçamento pré-definido</Label>
                                <Select
                                    value={templateId}
                                    onValueChange={setTemplateId}
                                >
                                    <SelectTrigger className="w-full">
                                        <SelectValue placeholder="Selecione um orçamento pré-definido" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {templates.map((template) => (
                                            <SelectItem
                                                key={template.id}
                                                value={String(template.id)}
                                            >
                                                {template.name} (
                                                {template.items.length} itens)
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                <InputError
                                    message={errors.budget_template_id}
                                />
                                {selectedTemplate && (
                                    <ul className="text-muted-foreground list-inside list-disc text-sm">
                                        {selectedTemplate.items.map((item) => (
                                            <li key={item.id}>
                                                {item.description} —{' '}
                                                {item.quantity} × R${' '}
                                                {item.unit_price}
                                            </li>
                                        ))}
                                    </ul>
                                )}
                            </div>
                        ) : (
                            <ItemDraftEditor
                                items={items}
                                itemTypes={itemTypes}
                                onChange={setItems}
                            />
                        )}
                    </section>

                    <section className="grid gap-4 rounded-xl border p-4 sm:grid-cols-3">
                        <div className="grid gap-2">
                            <Label htmlFor="discount">
                                Desconto do orçamento
                            </Label>
                            <Input
                                id="discount"
                                value={discountAmount}
                                onChange={(e) =>
                                    setDiscountAmount(e.target.value)
                                }
                                inputMode="decimal"
                            />
                            <InputError message={errors.discount_amount} />
                        </div>
                        <div className="grid gap-2">
                            <Label htmlFor="valid_until">Válido até</Label>
                            <Input
                                id="valid_until"
                                type="date"
                                value={validUntil}
                                onChange={(e) => setValidUntil(e.target.value)}
                            />
                            <InputError message={errors.valid_until} />
                        </div>
                        <div className="grid gap-2 sm:col-span-1">
                            <Label htmlFor="notes">Observações</Label>
                            <Textarea
                                id="notes"
                                value={notes}
                                onChange={(e) => setNotes(e.target.value)}
                                rows={1}
                            />
                            <InputError message={errors.notes} />
                        </div>
                    </section>

                    <div className="flex justify-end gap-3">
                        <Button type="submit" disabled={processing}>
                            Criar orçamento
                        </Button>
                    </div>
                </form>
            </div>
        </>
    );
}
