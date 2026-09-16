import { Badge } from '@/components/ui/badge';

const LABELS: Record<string, string> = {
    draft: 'Rascunho',
    sent: 'Enviado',
    approved: 'Aprovado',
    rejected: 'Rejeitado',
    cancelled: 'Cancelado',
};

const VARIANTS: Record<
    string,
    'default' | 'secondary' | 'destructive' | 'outline'
> = {
    draft: 'secondary',
    sent: 'outline',
    approved: 'default',
    rejected: 'destructive',
    cancelled: 'destructive',
};

export function BudgetStatusBadge({ status }: { status: string }) {
    return (
        <Badge variant={VARIANTS[status] ?? 'outline'}>
            {LABELS[status] ?? status}
        </Badge>
    );
}
