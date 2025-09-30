export interface Team {
    id: number;
    name: string;
    personal_team: boolean;
    user_id: number;
    created_at: string;
    updated_at: string;
    owner?: User;
    users?: User[];
    invitations?: TeamInvitation[];
}

export interface TeamInvitation {
    id: number;
    team_id: number;
    email: string;
    role: Role | null;
    created_at: string;
    updated_at: string;
    expires_at?: string;
    team?: Team;
}

export interface Role {
    key: string;
    name: string;
    description?: string;
}

export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at?: string;
    created_at: string;
    updated_at: string;
    pivot?: {
        role: Role;
        created_at: string;
        updated_at: string;
    };
}

declare global {
    interface Window {
        route: (name: string, params?: any) => string;
    }
    
    function route(name: string, params?: any): string;
}