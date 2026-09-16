import { Head, Link, router } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import { FormEvent, useState } from 'react';
import Heading from '@/components/heading';
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

type Customer = {
    id: number;
    customer_number: number;
    name: string;
    trade_name: string | null;
    type: string;
    cpf: string | null;
    cnpj: string | null;
    email: string | null;
    mobile: string | null;
    customer_equipments_count: number;
};
type Page<T> = {
    data: T[];
    links: { url: string | null; label: string; active: boolean }[];
    last_page: number;
};

export default function CustomersIndex({
    customers,
    filters,
}: {
    customers: Page<Customer>;
    filters: Record<string, string | undefined>;
}) {
    const [search, setSearch] = useState(filters.search ?? '');
    function submit(event: FormEvent) {
        event.preventDefault();
        router.get(
            '/customers',
            { search, type: filters.type, active: filters.active },
            { preserveState: true, replace: true },
        );
    }
    return (
        <>
            <Head title="Clientes" />
            <div className="flex flex-col gap-6 p-6 md:p-8">
                <div className="flex flex-wrap items-center justify-between gap-4">
                    <Heading
                        title="Clientes"
                        description="Cadastro compartilhado do tenant e histórico operacional."
                    />
                    <Button asChild>
                        <Link href="/customers/create">
                            <Plus /> Novo cliente
                        </Link>
                    </Button>
                </div>
                <form
                    onSubmit={submit}
                    className="flex flex-wrap items-end gap-3 rounded-xl border p-4"
                >
                    <div className="grid gap-1.5">
                        <label htmlFor="search" className="text-sm font-medium">
                            Buscar cliente
                        </label>
                        <Input
                            id="search"
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            placeholder="Nome, documento, telefone ou e-mail"
                            className="w-80"
                        />
                    </div>
                    <div className="grid gap-1.5">
                        <label className="text-sm font-medium">Tipo</label>
                        <Select
                            value={filters.type ?? 'all'}
                            onValueChange={(value) =>
                                router.get(
                                    '/customers',
                                    {
                                        search,
                                        type:
                                            value === 'all' ? undefined : value,
                                    },
                                    { replace: true },
                                )
                            }
                        >
                            <SelectTrigger className="w-40">
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">Todos</SelectItem>
                                <SelectItem value="individual">
                                    Pessoa física
                                </SelectItem>
                                <SelectItem value="company">
                                    Pessoa jurídica
                                </SelectItem>
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
                                <TableHead>Número</TableHead>
                                <TableHead>Cliente</TableHead>
                                <TableHead>Documento</TableHead>
                                <TableHead>Contato</TableHead>
                                <TableHead>Equipamentos</TableHead>
                                <TableHead />
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {customers.data.map((customer) => (
                                <TableRow key={customer.id}>
                                    <TableCell>
                                        #{customer.customer_number}
                                    </TableCell>
                                    <TableCell className="font-medium">
                                        {customer.name || customer.trade_name}
                                    </TableCell>
                                    <TableCell>
                                        {customer.cpf || customer.cnpj || '—'}
                                    </TableCell>
                                    <TableCell>
                                        {customer.mobile ||
                                            customer.email ||
                                            '—'}
                                    </TableCell>
                                    <TableCell>
                                        {customer.customer_equipments_count}
                                    </TableCell>
                                    <TableCell>
                                        <Button
                                            asChild
                                            variant="ghost"
                                            size="sm"
                                        >
                                            <Link
                                                href={`/customers/${customer.id}`}
                                            >
                                                Abrir
                                            </Link>
                                        </Button>
                                    </TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                </div>
                {customers.last_page > 1 && (
                    <div className="flex justify-center gap-2">
                        {customers.links.map(
                            (link) =>
                                link.url && (
                                    <Button
                                        key={link.label}
                                        asChild
                                        variant={
                                            link.active ? 'default' : 'outline'
                                        }
                                        size="sm"
                                    >
                                        <Link
                                            href={link.url}
                                            dangerouslySetInnerHTML={{
                                                __html: link.label,
                                            }}
                                        />
                                    </Button>
                                ),
                        )}
                    </div>
                )}
            </div>
        </>
    );
}
