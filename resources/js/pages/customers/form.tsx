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

const fields = [
    ['name', 'Nome / Razão social'],
    ['legal_name', 'Razão social'],
    ['trade_name', 'Nome fantasia'],
    ['cpf', 'CPF'],
    ['cnpj', 'CNPJ'],
    ['birth_date', 'Nascimento'],
    ['email', 'E-mail'],
    ['phone', 'Telefone'],
    ['mobile', 'Celular'],
    ['whatsapp', 'WhatsApp'],
    ['contact_name', 'Contato responsável'],
    ['contact_phone', 'Telefone do contato'],
    ['contact_email', 'E-mail do contato'],
    ['zip_code', 'CEP'],
    ['street', 'Endereço'],
    ['number', 'Número'],
    ['complement', 'Complemento'],
    ['district', 'Bairro'],
    ['city', 'Cidade'],
    ['state', 'UF'],
] as const;
type Customer = Record<string, string | number | boolean | null> & {
    id?: number;
    type?: string;
};

export default function CustomerForm({
    customer,
    editing = false,
}: {
    customer?: Customer;
    editing?: boolean;
}) {
    const [form, setForm] = useState<Record<string, string>>({
        type: customer?.type === 'company' ? 'company' : 'individual',
        ...Object.fromEntries(
            fields.map(([key]) => [key, String(customer?.[key] ?? '')]),
        ),
    });
    const [errors, setErrors] = useState<Record<string, string>>({});
    function submit(event: FormEvent) {
        event.preventDefault();
        const action = editing ? `/customers/${customer?.id}` : '/customers';
        router[editing ? 'put' : 'post'](action, form, {
            onError: (e) => setErrors(e as Record<string, string>),
        });
    }
    return (
        <>
            <Head title={editing ? 'Editar cliente' : 'Novo cliente'} />
            <div className="mx-auto flex max-w-5xl flex-col gap-6 p-6 md:p-8">
                <Heading
                    title={editing ? 'Editar cliente' : 'Novo cliente'}
                    description="Identificação, contatos e endereço do cadastro."
                />
                <form onSubmit={submit} className="space-y-6">
                    <section className="grid gap-4 rounded-xl border p-5 sm:grid-cols-2">
                        <h2 className="font-semibold sm:col-span-2">
                            Identificação
                        </h2>
                        <div className="grid gap-2">
                            <Label>Tipo</Label>
                            <Select
                                value={form.type}
                                onValueChange={(value) =>
                                    setForm({ ...form, type: value })
                                }
                            >
                                <SelectTrigger>
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="individual">
                                        Pessoa física
                                    </SelectItem>
                                    <SelectItem value="company">
                                        Pessoa jurídica
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        {fields.slice(0, 6).map(([key, label]) => (
                            <div className="grid gap-2" key={key}>
                                <Label htmlFor={key}>{label}</Label>
                                <Input
                                    id={key}
                                    type={
                                        key === 'birth_date' ? 'date' : 'text'
                                    }
                                    value={form[key]}
                                    onChange={(e) =>
                                        setForm({
                                            ...form,
                                            [key]: e.target.value,
                                        })
                                    }
                                />
                                {errors[key] && (
                                    <p className="text-destructive text-sm">
                                        {errors[key]}
                                    </p>
                                )}
                            </div>
                        ))}
                    </section>
                    <section className="grid gap-4 rounded-xl border p-5 sm:grid-cols-2">
                        <h2 className="font-semibold sm:col-span-2">Contato</h2>
                        {fields.slice(6, 14).map(([key, label]) => (
                            <div className="grid gap-2" key={key}>
                                <Label htmlFor={key}>{label}</Label>
                                <Input
                                    id={key}
                                    value={form[key]}
                                    onChange={(e) =>
                                        setForm({
                                            ...form,
                                            [key]: e.target.value,
                                        })
                                    }
                                />
                                {errors[key] && (
                                    <p className="text-destructive text-sm">
                                        {errors[key]}
                                    </p>
                                )}
                            </div>
                        ))}
                    </section>
                    <section className="grid gap-4 rounded-xl border p-5 sm:grid-cols-2">
                        <h2 className="font-semibold sm:col-span-2">
                            Endereço
                        </h2>
                        {fields.slice(14).map(([key, label]) => (
                            <div className="grid gap-2" key={key}>
                                <Label htmlFor={key}>{label}</Label>
                                <Input
                                    id={key}
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
                        <div className="grid gap-2 sm:col-span-2">
                            <Label htmlFor="observations">Observações</Label>
                            <Textarea
                                id="observations"
                                value={form.observations ?? ''}
                                onChange={(e) =>
                                    setForm({
                                        ...form,
                                        observations: e.target.value,
                                    })
                                }
                            />
                        </div>
                    </section>
                    <div className="flex gap-2">
                        <Button type="submit">Salvar cliente</Button>
                        <Button type="button" variant="outline" asChild>
                            <Link
                                href={
                                    editing
                                        ? `/customers/${customer?.id}`
                                        : '/customers'
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
