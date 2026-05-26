export interface User {
    id: number;
    uuid: string | null;
    name: string;
    first_name: string | null;
    last_name: string | null;
    email: string | null;
    phone: string | null;
    username: string | null;
    avatar_id: number | null;
    status: 'active' | 'inactive' | 'suspended' | 'pending';
    email_verified_at: string | null;
    phone_verified_at: string | null;
    two_factor_enabled: boolean;
    timezone: string;
    locale: string;
    last_login_at: string | null;
    last_login_ip: string | null;
    metadata: Record<string, any> | null;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
}