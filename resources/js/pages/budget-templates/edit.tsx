import { Head, router } from '@inertiajs/react';
import { FormEvent, useState } from 'react';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import budgetTemplates from '@/routes/budget-templates';
import type { BudgetTemplateDetail } from '@/types';

export default function BudgetTemplateEdit({
    template,
}: {
    template: BudgetTemplateDetail;
}) {
    const [name, setName] = useState(template.name);
    const [description, setDescription] = useState(template.description ?? '');
    const [active, setActive] = useState(template.active);
    const [notes, setNotes] = useState(template.notes ?? '');
    const [processing, setProcessing] = useState(false);
    const [errors, setErrors] = useState<Record<string, string>>({});

    function handleSubmit(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();
        setProcessing(true);
        router.put(
            budgetTemplates.update(template.id).url,
            {
                name,
                description: description || null,
                active,
                notes: notes || null,
            },
            {
                onError: (formErrors) =>
                    setErrors(formErrors as Record<string, string>),
                onFinish: () => setProcessing(false),
            },
        );
    }

    return (
        <>
            <Head title={`Editar ${template.name}`} />

            <div className="mx-auto flex max-w-2xl flex-col gap-6 p-6 md:p-8">
                <Heading title="Editar orçamento pré-definido" />

                <form onSubmit={handleSubmit} className="space-y-6">
                    <div className="grid gap-2">
                        <Label htmlFor="name">Nome</Label>
                        <Input
                            id="name"
                            value={name}
                            onChange={(e) => setName(e.target.value)}
                            required
                        />
                        <InputError message={errors.name} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="description">Descrição</Label>
                        <Textarea
                            id="description"
                            value={description}
                            onChange={(e) => setDescription(e.target.value)}
                            rows={2}
                        />
                        <InputError message={errors.description} />
                    </div>

                    <div className="flex items-center gap-3">
                        <Checkbox
                            id="active"
                            checked={active}
                            onCheckedChange={(checked) =>
                                setActive(checked === true)
                            }
                        />
                        <Label htmlFor="active">Modelo ativo</Label>
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="notes">Observações internas</Label>
                        <Textarea
                            id="notes"
                            value={notes}
                            onChange={(e) => setNotes(e.target.value)}
                            rows={2}
                        />
                        <InputError message={errors.notes} />
                    </div>

                    <div className="flex justify-end">
                        <Button type="submit" disabled={processing}>
                            Salvar alterações
                        </Button>
                    </div>
                </form>
            </div>
        </>
    );
}
