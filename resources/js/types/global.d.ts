import type { Auth, Company, Tenant } from '@/types/auth';

declare module 'react' {
    interface InputHTMLAttributes<T> {
        passwordrules?: string;
    }
}

declare module '@inertiajs/core' {
    export interface InertiaConfig {
        sharedPageProps: {
            name: string;
            auth: Auth;
            tenant: Tenant | null;
            currentCompany: Company | null;
            availableCompanies: Company[];
            permissions: string[];
            sidebarOpen: boolean;
            [key: string]: unknown;
        };
    }
}
