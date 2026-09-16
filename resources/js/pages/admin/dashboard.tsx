import { Head, usePage } from '@inertiajs/react';
import type { User } from '@/types';

export default function AdminDashboard() {
    const { auth } = usePage().props as { auth: { user: User } };

    return (
        <>
            <Head title="Administração" />
            <div className="flex flex-col gap-6 p-6 md:p-8">
                <div>
                    <p className="text-muted-foreground text-sm">
                        Olá, {auth.user.name}
                    </p>
                    <h1 className="mt-1 text-2xl font-semibold tracking-tight">
                        Administração do VetorOS
                    </h1>
                </div>
                <section className="border-border bg-card rounded-xl border p-6 shadow-sm">
                    <p className="text-muted-foreground text-sm">
                        Este painel é exclusivo do rootAdmin e não usa contexto
                        de tenant ou Company.
                    </p>
                </section>
            </div>
        </>
    );
}
