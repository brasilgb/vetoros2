export type User = {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    [key: string]: unknown;
};

export type Auth = {
    user: User;
};

export type CompanyType = 'headquarters' | 'branch';

export type Company = {
    id: number;
    trade_name: string;
    type: CompanyType;
    parent_id: number | null;
};

export type Tenant = {
    id: number;
    name: string;
    slug: string;
};
