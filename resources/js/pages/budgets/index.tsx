import { Head, Link, router } from '@inertiajs/react';
import { PlusIcon } from 'lucide-react';
import { FormEvent, useState } from 'react';
import { BudgetStatusBadge } from '@/components/budgets/budget-status-badge';
import Heading from '@/components/heading';
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
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import budgets from '@/routes/budgets';
import type {
    BranchOption,
    BudgetSummary,
    CompanyOption,
    Paginated,
} from '@/types';

type Filters = {
    search?: string;
    status?: string;
    company_id?: string;
    branch_id?: string;
    linked?: string;
    from?: string;
    to?: string;
};

export default function BudgetsIndex({
    budgets: paginated,
    filters,
    statusOptions,
    companies,
    branches,
}: {
    budgets: Paginated<BudgetSummary>;
    filters: Filters;
    statusOptions: string[];
    companies: CompanyOption[];
    branches: BranchOption[];
}) {
    const [form, setForm] = useState<Filters>(filters);

    function applyFilters(next: Filters) {
        setForm(next);
        router.get(budgets.index().url, next, {
            preserveState: true,
            replace: true,
        });
    }

    function handleSearchSubmit(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();
        applyFilters(form);
    }

    return (
        <>
            <Head title="Orçamentos" />

            <div className="flex flex-col gap-6 p-6 md:p-8">
                <div className="flex flex-wrap items-center justify-between gap-4">
                    <Heading
                        title="Orçamentos"
                        description="Orçamentos vinculados ou não a uma Ordem de Serviço."
                    />
                    <Button asChild>
                        <Link href={budgets.create().url}>
                            <PlusIcon />
                            Novo orçamento
                        </Link>
                    </Button>
                </div>

                <form
                    onSubmit={handleSearchSubmit}
                    className="flex flex-wrap items-end gap-3 rounded-xl border p-4"
                >
                    <div className="grid gap-1.5">
                        <Label htmlFor="search">Buscar</Label>
                        <Input
                            id="search"
                            placeholder="Número, cliente, documento ou OS"
                            value={form.search ?? ''}
                            onChange={(e) =>
                                setForm({ ...form, search: e.target.value })
                            }
                            className="w-64"
                        />
                    </div>

                    <div className="grid gap-1.5">
                        <Label>Status</Label>
                        <Select
                            value={form.status ?? 'all'}
                            onValueChange={(value) =>
                                applyFilters({
                                    ...form,
                                    status: value === 'all' ? undefined : value,
                                })
                            }
                        >
                            <SelectTrigger className="w-40">
                                <SelectValue placeholder="Todos" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">Todos</SelectItem>
                                {statusOptions.map((status) => (
                                    <SelectItem key={status} value={status}>
                                        <BudgetStatusBadge status={status} />
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    </div>

                    <div className="grid gap-1.5">
                        <Label>Empresa</Label>
                        <Select
                            value={form.company_id ?? 'all'}
                            onValueChange={(value) =>
                                applyFilters({
                                    ...form,
                                    company_id:
                                        value === 'all' ? undefined : value,
                                })
                            }
                        >
                            <SelectTrigger className="w-44">
                                <SelectValue placeholder="Todas" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">Todas</SelectItem>
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
                    </div>

                    <div className="grid gap-1.5">
                        <Label>Unidade</Label>
                        <Select
                            value={form.branch_id ?? 'all'}
                            onValueChange={(value) =>
                                applyFilters({
                                    ...form,
                                    branch_id:
                                        value === 'all' ? undefined : value,
                                })
                            }
                        >
                            <SelectTrigger className="w-44">
                                <SelectValue placeholder="Todas" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">Todas</SelectItem>
                                {branches.map((branch) => (
                                    <SelectItem
                                        key={branch.id}
                                        value={String(branch.id)}
                                    >
                                        {branch.name}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    </div>

                    <div className="grid gap-1.5">
                        <Label>Vínculo</Label>
                        <Select
                            value={form.linked ?? 'all'}
                            onValueChange={(value) =>
                                applyFilters({
                                    ...form,
                                    linked: value === 'all' ? undefined : value,
                                })
                            }
                        >
                            <SelectTrigger className="w-40">
                                <SelectValue placeholder="Todos" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">Todos</SelectItem>
                                <SelectItem value="1">Com OS</SelectItem>
                                <SelectItem value="0">Sem OS</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div className="grid gap-1.5">
                        <Label htmlFor="from">De</Label>
                        <Input
                            id="from"
                            type="date"
                            value={form.from ?? ''}
                            onChange={(e) =>
                                applyFilters({
                                    ...form,
                                    from: e.target.value || undefined,
                                })
                            }
                            className="w-40"
                        />
                    </div>

                    <div className="grid gap-1.5">
                        <Label htmlFor="to">Até</Label>
                        <Input
                            id="to"
                            type="date"
                            value={form.to ?? ''}
                            onChange={(e) =>
                                applyFilters({
                                    ...form,
                                    to: e.target.value || undefined,
                                })
                            }
                            className="w-40"
                        />
                    </div>

                    <Button type="submit" variant="secondary">
                        Buscar
                    </Button>
                </form>

                <div className="rounded-xl border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Número</TableHead>
                                <TableHead>Cliente</TableHead>
                                <TableHead>OS</TableHead>
                                <TableHead>Empresa / Unidade</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead>Total</TableHead>
                                <TableHead>Criado em</TableHead>
                                <TableHead>Criado por</TableHead>
                                <TableHead />
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {paginated.data.length === 0 && (
                                <TableRow>
                                    <TableCell
                                        colSpan={9}
                                        className="text-muted-foreground py-8 text-center"
                                    >
                                        Nenhum orçamento encontrado.
                                    </TableCell>
                                </TableRow>
                            )}
                            {paginated.data.map((budget) => (
                                <TableRow key={budget.id}>
                                    <TableCell className="font-medium">
                                        #{budget.budget_number}
                                    </TableCell>
                                    <TableCell>
                                        {budget.customer?.name ?? '—'}
                                    </TableCell>
                                    <TableCell>
                                        {budget.order
                                            ? `#${budget.order.order_number}`
                                            : '—'}
                                    </TableCell>
                                    <TableCell>
                                        {budget.company?.trade_name}
                                        {budget.branch
                                            ? ` / ${budget.branch.name}`
                                            : ''}
                                    </TableCell>
                                    <TableCell>
                                        <BudgetStatusBadge
                                            status={budget.status}
                                        />
                                    </TableCell>
                                    <TableCell>R$ {budget.total}</TableCell>
                                    <TableCell>
                                        {budget.created_at
                                            ? new Date(
                                                  budget.created_at,
                                              ).toLocaleDateString('pt-BR')
                                            : '—'}
                                    </TableCell>
                                    <TableCell>
                                        {budget.creator?.name ?? '—'}
                                    </TableCell>
                                    <TableCell>
                                        <Button
                                            asChild
                                            variant="ghost"
                                            size="sm"
                                        >
                                            <Link
                                                href={
                                                    budgets.show(budget.id).url
                                                }
                                            >
                                                Ver
                                            </Link>
                                        </Button>
                                    </TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                </div>

                {paginated.last_page > 1 && (
                    <div className="flex flex-wrap items-center justify-center gap-1">
                        {paginated.links.map((link, index) => (
                            <Button
                                key={index}
                                asChild={link.url !== null}
                                variant={link.active ? 'default' : 'outline'}
                                size="sm"
                                disabled={link.url === null}
                            >
                                {link.url !== null ? (
                                    <Link
                                        href={link.url}
                                        preserveScroll
                                        dangerouslySetInnerHTML={{
                                            __html: link.label,
                                        }}
                                    />
                                ) : (
                                    <span
                                        dangerouslySetInnerHTML={{
                                            __html: link.label,
                                        }}
                                    />
                                )}
                            </Button>
                        ))}
                    </div>
                )}
            </div>
        </>
    );
}
