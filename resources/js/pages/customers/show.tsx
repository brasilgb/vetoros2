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
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Textarea } from '@/components/ui/textarea';

type Customer = {
    id: number;
    customer_number: number;
    name: string;
    trade_name: string | null;
    type: string;
    cpf: string | null;
    cnpj: string | null;
    email: string | null;
    phone: string | null;
    mobile: string | null;
    whatsapp: string | null;
    street: string | null;
    number: string | null;
    district: string | null;
    city: string | null;
    state: string | null;
    customer_equipments: {
        id: number;
        equipment_number: number;
        brand: string | null;
        model: string | null;
        serial_number: string | null;
        equipment_type: { name: string };
    }[];
};
export default function CustomersShow({
    customer,
    equipmentTypes,
    orders,
}: {
    customer: Customer;
    equipmentTypes: { id: number; name: string }[];
    orders: {
        data: {
            id: number;
            order_number: number;
            status: string;
            branch: string | null;
            equipment: string | null;
            technician: string | null;
            budget: number | null;
        }[];
    };
}) {
    const [form, setForm] = useState({
        equipment_type_id: '',
        brand: '',
        model: '',
        serial_number: '',
        imei: '',
        asset_tag: '',
        color: '',
        description: '',
        observations: '',
    });
    function submit(event: FormEvent) {
        event.preventDefault();
        router.post(`/customers/${customer.id}/equipment`, form);
    }
    return (
        <>
            <Head title={customer.name} />
            <div className="flex flex-col gap-6 p-6 md:p-8">
                <div className="flex flex-wrap items-start justify-between gap-4">
                    <Heading
                        title={customer.name}
                        description={`Cliente #${customer.customer_number}`}
                    />
                    <div className="flex gap-2">
                        <Button asChild variant="outline">
                            <Link href={`/customers/${customer.id}/edit`}>
                                Editar
                            </Link>
                        </Button>
                        <Button asChild>
                            <Link
                                href={`/orders/create?customer=${customer.id}`}
                            >
                                Nova OS
                            </Link>
                        </Button>
                    </div>
                </div>
                <Tabs defaultValue="geral">
                    <TabsList>
                        <TabsTrigger value="geral">Geral</TabsTrigger>
                        <TabsTrigger value="equipamentos">
                            Equipamentos ({customer.customer_equipments.length})
                        </TabsTrigger>
                        <TabsTrigger value="ordens">
                            Ordens de Serviço
                        </TabsTrigger>
                    </TabsList>
                    <TabsContent value="geral" className="mt-4">
                        <div className="grid gap-4 rounded-xl border p-5 sm:grid-cols-2">
                            <div>
                                <p className="text-muted-foreground text-sm">
                                    Documento
                                </p>
                                <p>{customer.cpf || customer.cnpj || '—'}</p>
                            </div>
                            <div>
                                <p className="text-muted-foreground text-sm">
                                    Contato
                                </p>
                                <p>
                                    {customer.mobile ||
                                        customer.phone ||
                                        customer.email ||
                                        '—'}
                                </p>
                            </div>
                            <div className="sm:col-span-2">
                                <p className="text-muted-foreground text-sm">
                                    Endereço
                                </p>
                                <p>
                                    {[
                                        customer.street,
                                        customer.number,
                                        customer.district,
                                        customer.city,
                                        customer.state,
                                    ]
                                        .filter(Boolean)
                                        .join(', ') || '—'}
                                </p>
                            </div>
                        </div>
                    </TabsContent>
                    <TabsContent
                        value="equipamentos"
                        className="mt-4 space-y-4"
                    >
                        <div className="rounded-xl border p-5">
                            <h2 className="mb-4 font-semibold">
                                Novo equipamento
                            </h2>
                            <form
                                onSubmit={submit}
                                className="grid gap-4 sm:grid-cols-3"
                            >
                                <div className="grid gap-2">
                                    <Label>Tipo</Label>
                                    <Select
                                        value={form.equipment_type_id}
                                        onValueChange={(value) =>
                                            setForm({
                                                ...form,
                                                equipment_type_id: value,
                                            })
                                        }
                                    >
                                        <SelectTrigger>
                                            <SelectValue placeholder="Selecione" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {equipmentTypes.map((type) => (
                                                <SelectItem
                                                    key={type.id}
                                                    value={String(type.id)}
                                                >
                                                    {type.name}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                </div>
                                {(
                                    [
                                        'brand',
                                        'model',
                                        'serial_number',
                                        'imei',
                                        'asset_tag',
                                        'color',
                                    ] as const
                                ).map((key) => (
                                    <div className="grid gap-2" key={key}>
                                        <Label>{key.replace('_', ' ')}</Label>
                                        <Input
                                            value={form[key]}
                                            onChange={(e) =>
                                                setForm({
                                                    ...form,
                                                    [key]: e.target.value,
                                                })
                                            }
                                        />
                                    </div>
                                ))}
                                <div className="sm:col-span-3">
                                    <Label>Descrição</Label>
                                    <Textarea
                                        value={form.description}
                                        onChange={(e) =>
                                            setForm({
                                                ...form,
                                                description: e.target.value,
                                            })
                                        }
                                    />
                                </div>
                                <Button type="submit" className="sm:w-fit">
                                    Cadastrar equipamento
                                </Button>
                            </form>
                        </div>
                        <div className="rounded-xl border">
                            <div className="divide-y">
                                {customer.customer_equipments.map(
                                    (equipment) => (
                                        <div
                                            className="flex flex-wrap items-center justify-between gap-3 p-4"
                                            key={equipment.id}
                                        >
                                            <div>
                                                <p className="font-medium">
                                                    #
                                                    {equipment.equipment_number}{' '}
                                                    ·{' '}
                                                    {
                                                        equipment.equipment_type
                                                            .name
                                                    }
                                                </p>
                                                <p className="text-muted-foreground text-sm">
                                                    {[
                                                        equipment.brand,
                                                        equipment.model,
                                                        equipment.serial_number,
                                                    ]
                                                        .filter(Boolean)
                                                        .join(' · ') ||
                                                        'Sem detalhes'}
                                                </p>
                                            </div>
                                            <Button asChild size="sm">
                                                <Link
                                                    href={`/orders/create?equipment=${equipment.id}&customer=${customer.id}`}
                                                >
                                                    Abrir OS
                                                </Link>
                                            </Button>
                                        </div>
                                    ),
                                )}
                            </div>
                        </div>
                    </TabsContent>
                    <TabsContent value="ordens" className="mt-4">
                        <div className="divide-y rounded-xl border">
                            {orders.data.map((order) => (
                                <Link
                                    className="hover:bg-muted flex flex-wrap justify-between gap-3 p-4"
                                    href={`/orders/${order.id}`}
                                    key={order.id}
                                >
                                    <span className="font-medium">
                                        OS #{order.order_number}
                                    </span>
                                    <span>{order.status}</span>
                                    <span>
                                        {order.equipment || 'Sem equipamento'}
                                    </span>
                                    <span>{order.branch || '—'}</span>
                                </Link>
                            ))}
                        </div>
                    </TabsContent>
                </Tabs>
            </div>
        </>
    );
}
