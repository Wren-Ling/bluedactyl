import http from '@/api/http';

export interface LoginData {
    user: string;
    password: string;
    [key: string]: any; // Allow additional fields like captcha responses
}

export interface LoginResponse {
    complete: boolean;
    intended?: string;
    confirmationToken?: string;
    error?: string;
    user?: {
        uuid: string;
        username: string;
        email: string;
        language: string;
        root_admin: boolean;
        use_totp: boolean;
        created_at: string;
        updated_at: string;
    };
}

export default async (data: LoginData): Promise<LoginResponse> => {
    try {
        await http.get('/sanctum/csrf-cookie');

        // Pass through all data including captcha responses
        const payload: Record<string, any> = {
            ...data,
        };

        const response = await http.post('/auth/login', payload);

        if (!response.data || typeof response.data !== 'object') {
            if (response.status >= 200 && response.status < 300) {
                return {
                    complete: true,
                    intended: '/',
                };
            }
            throw new Error('Invalid server response format');
        }

        const body = response.data.data ?? response.data;

        return {
            complete: body.complete ?? false,
            intended: body.intended,
            confirmationToken: body.confirmation_token ?? body.confirmationToken,
            error: body.error ?? body.message,
            user: body.user,
        };
    } catch (error: any) {
        const loginError = new Error(
            error.response?.data?.error ??
                error.response?.data?.message ??
                error.message ??
                'Login failed. Please try again.',
        ) as any;

        loginError.response = error.response;
        loginError.detail = error.response?.data?.errors?.[0]?.detail;
        loginError.code = error.response?.data?.errors?.[0]?.code;

        console.error('Login API Error:', {
            status: error.response?.status,
            data: error.response?.data,
            detail: loginError.detail,
            message: loginError.message,
        });

        throw loginError;
    }
};
