export type BudgetItemType = 'service' | 'part' | 'other';
export type BudgetStatusValue =
    | 'draft'
    | 'sent'
    | 'approved'
    | 'rejected'
    | 'cancelled';

export type BudgetItem = {
    id: number;
    type: BudgetItemType;
    description: string;
    quantity: string;
    unit_price: string;
    discount_amount: string;
    total: string;
    sort_order: number;
    source_template_item_id: number | null;
    notes: string | null;
};

export type BudgetSummary = {
    id: number;
    budget_number: number;
    status: BudgetStatusValue;
    total: string;
    created_at: string | null;
    customer: { id: number; name: string } | null;
    company: { id: number; trade_name: string } | null;
    branch: { id: number; name: string } | null;
    order: { id: number; order_number: number } | null;
    creator: { id: number; name: string } | null;
    items_count: number;
};

export type BudgetDetail = Omit<BudgetSummary, 'customer' | 'order'> & {
    subtotal: string;
    discount_amount: string;
    valid_until: string | null;
    notes: string | null;
    customer: { id: number; name: string; document: string | null } | null;
    order: { id: number; order_number: number; status: string } | null;
    items: BudgetItem[];
    can_edit: boolean;
    can_send: boolean;
    can_approve: boolean;
    can_reject: boolean;
    can_cancel: boolean;
};

export type BudgetTemplateSummary = {
    id: number;
    name: string;
    description: string | null;
    active: boolean;
    items_count: number;
};

export type BudgetTemplateItem = {
    id: number;
    type: BudgetItemType;
    description: string;
    quantity: string;
    unit_price: string;
    discount_amount: string;
    total: string;
    sort_order: number;
    notes: string | null;
};

export type BudgetTemplateDetail = {
    id: number;
    name: string;
    description: string | null;
    active: boolean;
    notes: string | null;
    items: BudgetTemplateItem[];
};

export type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

export type Paginated<T> = {
    data: T[];
    links: PaginationLink[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
};

export type CustomerOption = {
    id: number;
    name: string | null;
    trade_name: string | null;
    cpf: string | null;
    cnpj: string | null;
    type: 'individual' | 'company';
};

export type CompanyOption = { id: number; trade_name: string };

export type BranchOption = { id: number; name: string; company_id: number };

export type OrderOption = {
    id: number;
    order_number: number;
    customer_id: number;
    status: string;
};
