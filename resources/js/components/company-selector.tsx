import { router, usePage } from '@inertiajs/react';
import { Building2 } from 'lucide-react';
import companies from '@/routes/companies';
import { Badge } from '@/components/ui/badge';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import type { Company } from '@/types';

function companyLabel(company: Company): string {
    return company.trade_name;
}

export function CompanySelector() {
    const { tenant, currentCompany, availableCompanies } = usePage().props;

    if (!currentCompany || availableCompanies.length === 0) {
        return null;
    }

    function selectCompany(companyId: string): void {
        if (Number(companyId) === currentCompany?.id) {
            return;
        }

        router.post(
            companies.switch().url,
            { company_id: Number(companyId) },
            { preserveScroll: true },
        );
    }

    return (
        <div className="flex min-w-0 items-center gap-2">
            <Building2 className="text-muted-foreground size-4 shrink-0" />
            <div className="hidden min-w-0 flex-col lg:flex">
                <span className="text-muted-foreground max-w-40 truncate text-[10px] leading-none">
                    {tenant?.name}
                </span>
                <span className="text-muted-foreground text-[10px] leading-none uppercase">
                    Empresa
                </span>
                <span className="max-w-48 truncate text-xs font-medium">
                    {companyLabel(currentCompany)}
                </span>
            </div>
            <Badge variant="outline" className="hidden sm:inline-flex">
                {currentCompany.type === 'branch' ? 'Filial' : 'Matriz'}
            </Badge>
            <Select
                value={String(currentCompany.id)}
                onValueChange={selectCompany}
            >
                <SelectTrigger className="h-9 max-w-52">
                    <SelectValue aria-label={companyLabel(currentCompany)} />
                </SelectTrigger>
                <SelectContent>
                    {availableCompanies.map((company) => (
                        <SelectItem key={company.id} value={String(company.id)}>
                            <span className="truncate">
                                {companyLabel(company)}
                            </span>
                            <span className="text-muted-foreground ml-2 text-xs">
                                {company.type === 'branch'
                                    ? 'Filial'
                                    : 'Matriz'}
                            </span>
                        </SelectItem>
                    ))}
                </SelectContent>
            </Select>
        </div>
    );
}
