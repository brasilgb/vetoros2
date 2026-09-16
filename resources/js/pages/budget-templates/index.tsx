import { Head, Link, router } from '@inertiajs/react';
import { PlusIcon } from 'lucide-react';
import { FormEvent, useState } from 'react';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
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
import budgetTemplates from '@/routes/budget-templates';
import type { BudgetTemplateSummary, Paginated } from '@/types';

export default function BudgetTemplatesIndex({
    templates,
    filters,
}: {
    templates: Paginated<BudgetTemplateSummary>;
    filters: { search?: string; active?: string };
}) {
    const [form, setForm] = useState(filters);

    function apply(next: typeof filters) {
        setForm(next);
        router.get(budgetTemplates.index().url, next, {
            preserveState: true,
            replace: true,
        });
    }

    function handleSubmit(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();
        apply(form);
    }

    return (
        <>
            <Head title="Orçamentos pré-definidos" />

            <div className="flex flex-col gap-6 p-6 md:p-8">
                <div className="flex flex-wrap items-center justify-between gap-4">
                    <Heading
                        title="Orçamentos pré-definidos"
                        description="Modelos reutilizáveis para agilizar a criação de orçamentos."
                    />
                    <Button asChild>
                        <Link href={budgetTemplates.create().url}>
                            <PlusIcon />
                            Novo modelo
                        </Link>
                    </Button>
                </div>

                <form
                    onSubmit={handleSubmit}
                    className="flex flex-wrap items-end gap-3 rounded-xl border p-4"
                >
                    <div className="grid gap-1.5">
                        <Label htmlFor="search">Buscar</Label>
                        <Input
                            id="search"
                            value={form.search ?? ''}
                            onChange={(e) =>
                                setForm({ ...form, search: e.target.value })
                            }
                            placeholder="Nome do modelo"
                            className="w-64"
                        />
                    </div>
                    <div className="grid gap-1.5">
                        <Label>Situação</Label>
                        <Select
                            value={form.active ?? 'all'}
                            onValueChange={(value) =>
                                apply({
                                    ...form,
                                    active: value === 'all' ? undefined : value,
                                })
                            }
                        >
                            <SelectTrigger className="w-40">
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">Todos</SelectItem>
                                <SelectItem value="1">Ativos</SelectItem>
                                <SelectItem value="0">Inativos</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <Button type="submit" variant="secondary">
                        Buscar
                    </Button>
                </form>

                <div className="rounded-xl border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Nome</TableHead>
                                <TableHead>Descrição</TableHead>
                                <TableHead>Itens</TableHead>
                                <TableHead>Situação</TableHead>
                                <TableHead />
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {templates.data.length === 0 && (
                                <TableRow>
                                    <TableCell
                                        colSpan={5}
                                        className="text-muted-foreground py-8 text-center"
                                    >
                                        Nenhum orçamento pré-definido
                                        encontrado.
                                    </TableCell>
                                </TableRow>
                            )}
                            {templates.data.map((template) => (
                                <TableRow key={template.id}>
                                    <TableCell className="font-medium">
                                        {template.name}
                                    </TableCell>
                                    <TableCell className="text-muted-foreground">
                                        {template.description || '—'}
                                    </TableCell>
                                    <TableCell>
                                        {template.items_count}
                                    </TableCell>
                                    <TableCell>
                                        <Badge
                                            variant={
                                                template.active
                                                    ? 'default'
                                                    : 'outline'
                                            }
                                        >
                                            {template.active
                                                ? 'Ativo'
                                                : 'Inativo'}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>
                                        <Button
                                            asChild
                                            variant="ghost"
                                            size="sm"
                                        >
                                            <Link
                                                href={
                                                    budgetTemplates.show(
                                                        template.id,
                                                    ).url
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

                {templates.last_page > 1 && (
                    <div className="flex flex-wrap items-center justify-center gap-1">
                        {templates.links.map((link, index) => (
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
