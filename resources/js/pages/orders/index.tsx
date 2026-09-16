import { Head, Link, router } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import { FormEvent, useState } from 'react';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
type Order = {
    id: number;
    order_number: number;
    status: string;
    priority: string;
    customer: string | null;
    branch: string | null;
    technician: string | null;
    created_at: string | null;
};
export default function OrdersIndex({
    orders,
    filters,
}: {
    orders: {
        data: Order[];
        links: { url: string | null; label: string; active: boolean }[];
        last_page: number;
    };
    filters: { search?: string; status?: string };
}) {
    const [search, setSearch] = useState(filters.search ?? '');
    function submit(e: FormEvent) {
        e.preventDefault();
        router.get(
            '/orders',
            { search },
            { preserveState: true, replace: true },
        );
    }
    return (
        <>
            <Head title="Ordens de Serviço" />
            <div className="flex flex-col gap-6 p-6 md:p-8">
                <div className="flex flex-wrap justify-between gap-4">
                    <Heading
                        title="Ordens de Serviço"
                        description="Acompanhe a operação da assistência técnica."
                    />
                    <Button asChild>
                        <Link href="/orders/create">
                            <Plus /> Nova OS
                        </Link>
                    </Button>
                </div>
                <form
                    onSubmit={submit}
                    className="flex gap-2 rounded-xl border p-4"
                >
                    <Input
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        placeholder="Número da OS ou cliente"
                        className="max-w-sm"
                    />
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
                                <TableHead>Status</TableHead>
                                <TableHead>Prioridade</TableHead>
                                <TableHead>Unidade</TableHead>
                                <TableHead>Técnico</TableHead>
                                <TableHead />
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {orders.data.map((order) => (
                                <TableRow key={order.id}>
                                    <TableCell>#{order.order_number}</TableCell>
                                    <TableCell>
                                        {order.customer || '—'}
                                    </TableCell>
                                    <TableCell>{order.status}</TableCell>
                                    <TableCell>{order.priority}</TableCell>
                                    <TableCell>{order.branch || '—'}</TableCell>
                                    <TableCell>
                                        {order.technician || 'Não atribuído'}
                                    </TableCell>
                                    <TableCell>
                                        <Button
                                            asChild
                                            variant="ghost"
                                            size="sm"
                                        >
                                            <Link href={`/orders/${order.id}`}>
                                                Abrir
                                            </Link>
                                        </Button>
                                    </TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                </div>
            </div>
        </>
    );
}
