import { Head, Link, router } from '@inertiajs/react';
import { FormEvent, useState } from 'react';
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
import { Textarea } from '@/components/ui/textarea';
type Option = {
    id: number;
    name: string;
    trade_name?: string | null;
    company_id?: number;
};
export default function OrdersCreate({
    customer,
    equipment,
    customers,
    equipmentTypes,
    companies,
    branches,
    priorities,
    technicians,
}: {
    customer: Option | null;
    equipment: (Option & { equipment_type: Option }) | null;
    customers: Option[];
    equipmentTypes: Option[];
    companies: Option[];
    branches: Option[];
    priorities: string[];
    technicians: Option[];
}) {
    const [form, setForm] = useState({
        customer_id: customer ? String(customer.id) : '',
        customer_equipment_id: equipment ? String(equipment.id) : '',
        equipment_type_id: equipment ? String(equipment.equipment_type.id) : '',
        company_id: companies[0] ? String(companies[0].id) : '',
        branch_id: '',
        priority: 'normal',
        reported_issue: '',
        assigned_to: '',
    });
    const set = (key: string, value: string) =>
        setForm({ ...form, [key]: value });
    function submit(e: FormEvent) {
        e.preventDefault();
        router.post('/orders', {
            ...form,
            customer_equipment_id: form.customer_equipment_id || null,
            assigned_to: form.assigned_to || null,
        });
    }
    const selectedCompany = branches.filter(
        (branch) => String(branch.company_id) === form.company_id,
    );
    return (
        <>
            <Head title="Nova Ordem de Serviço" />
            <div className="mx-auto flex max-w-4xl flex-col gap-6 p-6 md:p-8">
                <Heading
                    title="Nova Ordem de Serviço"
                    description="Abra o atendimento a partir do cliente ou equipamento."
                />
                <form onSubmit={submit} className="space-y-6">
                    <section className="grid gap-4 rounded-xl border p-5 sm:grid-cols-2">
                        <h2 className="font-semibold sm:col-span-2">
                            Contexto
                        </h2>
                        <div className="grid gap-2">
                            <Label>Cliente</Label>
                            <Select
                                value={form.customer_id}
                                onValueChange={(v) => set('customer_id', v)}
                            >
                                <SelectTrigger>
                                    <SelectValue placeholder="Selecione" />
                                </SelectTrigger>
                                <SelectContent>
                                    {customers.map((item) => (
                                        <SelectItem
                                            key={item.id}
                                            value={String(item.id)}
                                        >
                                            {item.name || item.trade_name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>
                        <div className="grid gap-2">
                            <Label>Tipo de equipamento</Label>
                            <Select
                                value={form.equipment_type_id}
                                onValueChange={(v) =>
                                    set('equipment_type_id', v)
                                }
                            >
                                <SelectTrigger>
                                    <SelectValue placeholder="Selecione" />
                                </SelectTrigger>
                                <SelectContent>
                                    {equipmentTypes.map((item) => (
                                        <SelectItem
                                            key={item.id}
                                            value={String(item.id)}
                                        >
                                            {item.name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>
                        <div className="grid gap-2">
                            <Label>Equipamento físico (opcional)</Label>
                            <Input
                                value={form.customer_equipment_id}
                                onChange={(e) =>
                                    set('customer_equipment_id', e.target.value)
                                }
                                placeholder="ID preenchido pelo cliente"
                            />
                        </div>
                        <div className="grid gap-2">
                            <Label>Empresa</Label>
                            <Select
                                value={form.company_id}
                                onValueChange={(v) => {
                                    setForm({
                                        ...form,
                                        company_id: v,
                                        branch_id: '',
                                    });
                                }}
                            >
                                <SelectTrigger>
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    {companies.map((item) => (
                                        <SelectItem
                                            key={item.id}
                                            value={String(item.id)}
                                        >
                                            {item.trade_name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>
                        <div className="grid gap-2">
                            <Label>Filial</Label>
                            <Select
                                value={form.branch_id}
                                onValueChange={(v) => set('branch_id', v)}
                            >
                                <SelectTrigger>
                                    <SelectValue placeholder="Selecione" />
                                </SelectTrigger>
                                <SelectContent>
                                    {selectedCompany.map((item) => (
                                        <SelectItem
                                            key={item.id}
                                            value={String(item.id)}
                                        >
                                            {item.name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>
                    </section>
                    <section className="grid gap-4 rounded-xl border p-5 sm:grid-cols-2">
                        <h2 className="font-semibold sm:col-span-2">
                            Atendimento
                        </h2>
                        <div className="grid gap-2">
                            <Label>Prioridade</Label>
                            <Select
                                value={form.priority}
                                onValueChange={(v) => set('priority', v)}
                            >
                                <SelectTrigger>
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    {priorities.map((value) => (
                                        <SelectItem key={value} value={value}>
                                            {value}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>
                        <div className="grid gap-2">
                            <Label>Técnico inicial</Label>
                            <Select
                                value={form.assigned_to || 'none'}
                                onValueChange={(v) =>
                                    set('assigned_to', v === 'none' ? '' : v)
                                }
                            >
                                <SelectTrigger>
                                    <SelectValue placeholder="Não atribuir" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="none">
                                        Não atribuir
                                    </SelectItem>
                                    {technicians.map((item) => (
                                        <SelectItem
                                            key={item.id}
                                            value={String(item.id)}
                                        >
                                            {item.name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>
                        <div className="grid gap-2 sm:col-span-2">
                            <Label>Defeito relatado</Label>
                            <Textarea
                                required
                                value={form.reported_issue}
                                onChange={(e) =>
                                    set('reported_issue', e.target.value)
                                }
                            />
                        </div>
                    </section>
                    <div className="flex gap-2">
                        <Button type="submit">Abrir OS</Button>
                        <Button asChild type="button" variant="outline">
                            <Link
                                href={
                                    customer
                                        ? `/customers/${customer.id}`
                                        : '/orders'
                                }
                            >
                                Cancelar
                            </Link>
                        </Button>
                    </div>
                </form>
            </div>
        </>
    );
}
