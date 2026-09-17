import type { LoginResponse } from '@/api/auth/login';
import http from '@/api/http';

export default (token: string, code: string, recoveryToken?: string): Promise<LoginResponse> => {
    return new Promise((resolve, reject) => {
        http.post('/auth/login/checkpoint', {
            confirmation_token: token,
            authentication_code: code,
            recovery_token: recoveryToken && recoveryToken.length > 0 ? recoveryToken : undefined,
        })
            .then((response) => {
                if (!response.data || typeof response.data !== 'object') {
                    if (response.status >= 200 && response.status < 300) {
                        resolve({ complete: true, intended: '/' });
                        return;
                    }
                    reject(new Error('Invalid server response format'));
                    return;
                }

                const data = response.data.data ?? response.data;
                resolve({
                    complete: data.complete ?? true,
                    intended: data.intended || undefined,
                });
            })
            .catch(reject);
    });
};
