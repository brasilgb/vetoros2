import { Link, usePage } from '@inertiajs/react';
import { LayoutGrid, LogOut } from 'lucide-react';
import type { ReactNode } from 'react';
import AppLogo from '@/components/app-logo';
import { Button } from '@/components/ui/button';
import { logout } from '@/routes';
import admin from '@/routes/admin';
import type { User } from '@/types';

type Props = {
    children: ReactNode;
};

export default function AdminLayout({ children }: Props) {
    const { auth } = usePage().props as { auth: { user: User } };

    return (
        <div className="flex min-h-screen flex-col bg-background text-foreground">
            <header className="border-border flex h-16 items-center border-b px-6">
                <Link href={admin.dashboard()} className="flex items-center gap-2">
                    <AppLogo />
                </Link>
                <span className="text-muted-foreground ml-6 border-l pl-6 text-sm">
                    Administração
                </span>
                <div className="ml-auto flex items-center gap-4">
                    <span className="text-muted-foreground hidden text-sm sm:inline">
                        {auth.user.name}
                    </span>
                    <Button variant="outline" size="sm" asChild>
                        <Link href={logout()} method="post">
                            <LogOut />
                            Sair
                        </Link>
                    </Button>
                </div>
            </header>
            <div className="flex flex-1">
                <aside className="border-border hidden w-56 border-r p-4 md:block">
                    <nav aria-label="Navegação administrativa">
                        <Link
                            href={admin.dashboard()}
                            className="bg-accent text-accent-foreground flex items-center gap-2 rounded-md px-3 py-2 text-sm font-medium"
                        >
                            <LayoutGrid className="size-4" />
                            Dashboard
                        </Link>
                    </nav>
                </aside>
                <main className="min-w-0 flex-1">{children}</main>
            </div>
        </div>
    );
}
