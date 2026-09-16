import { Head, Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
type Order = {
    id: number;
    order_number: number;
    status: string;
    priority: string;
    reported_issue: string;
    customer: { id: number; name: string } | null;
    customer_equipment: {
        id: number;
        brand: string | null;
        model: string | null;
        equipment_type: { name: string };
    } | null;
    branch: { name: string } | null;
    assignee: { name: string } | null;
    latest_budget: { id: number; budget_number: number; status: string } | null;
};
export default function OrdersShow({ order }: { order: Order }) {
    return (
        <>
            <Head title={`OS #${order.order_number}`} />
            <div className="mx-auto flex max-w-4xl flex-col gap-6 p-6 md:p-8">
                <div className="flex flex-wrap justify-between gap-4">
                    <div>
                        <Link
                            href="/orders"
                            className="text-muted-foreground text-sm hover:underline"
                        >
                            ← Ordens
                        </Link>
                        <h1 className="mt-2 text-2xl font-semibold">
                            OS #{order.order_number}
                        </h1>
                    </div>
                    {order.customer && (
                        <Button asChild variant="outline">
                            <Link href={`/customers/${order.customer.id}`}>
                                Ver cliente
                            </Link>
                        </Button>
                    )}
                </div>
                <div className="grid gap-4 rounded-xl border p-5 sm:grid-cols-2">
                    <div>
                        <p className="text-muted-foreground text-sm">Cliente</p>
                        <p>{order.customer?.name || '—'}</p>
                    </div>
                    <div>
                        <p className="text-muted-foreground text-sm">Status</p>
                        <p>{order.status}</p>
                    </div>
                    <div>
                        <p className="text-muted-foreground text-sm">
                            Equipamento
                        </p>
                        <p>
                            {order.customer_equipment
                                ? `${order.customer_equipment.equipment_type.name} · ${order.customer_equipment.brand || ''} ${order.customer_equipment.model || ''}`
                                : 'Sem equipamento físico'}
                        </p>
                    </div>
                    <div>
                        <p className="text-muted-foreground text-sm">
                            Unidade / técnico
                        </p>
                        <p>
                            {order.branch?.name || '—'} ·{' '}
                            {order.assignee?.name || 'Não atribuído'}
                        </p>
                    </div>
                    <div className="sm:col-span-2">
                        <p className="text-muted-foreground text-sm">
                            Defeito relatado
                        </p>
                        <p className="whitespace-pre-wrap">
                            {order.reported_issue}
                        </p>
                    </div>
                </div>
                {order.latest_budget && (
                    <div className="rounded-xl border p-5">
                        <p className="font-semibold">
                            Orçamento #{order.latest_budget.budget_number}
                        </p>
                        <p className="text-muted-foreground">
                            Status: {order.latest_budget.status}
                        </p>
                        <Button asChild className="mt-3">
                            <Link href={`/budgets/${order.latest_budget.id}`}>
                                Abrir orçamento
                            </Link>
                        </Button>
                    </div>
                )}
            </div>
        </>
    );
}
