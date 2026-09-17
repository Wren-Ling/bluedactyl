import { Globe } from 'lucide-react';
import { useState } from 'react';
import { useTranslation } from 'react-i18next';
import { useNavigate } from 'react-router-dom';

import { Button } from '@/components/ui/button';
import { CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuRadioGroup,
    DropdownMenuRadioItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { AnimatedThemeToggler as ModeToggle } from '@/components/ui/animated-theme-toggler';
import { Meteors } from '@/components/ui/meteors';
import { NeonGradientCard } from '@/components/ui/neon-gradient-card';

import login from '@/api/auth/login';

import i18n from '@/i18n/config';

import { store } from '@/state';

import { httpErrorToHuman } from '@/api/http';

const LANGUAGES = [
    { code: 'en', label: 'English' },
    { code: 'zh', label: '中文' },
];

const LoginContainer = () => {
    const { t } = useTranslation();
    const navigate = useNavigate();

    const [credential, setCredential] = useState('');
    const [password, setPassword] = useState('');
    const [error, setError] = useState('');
    const [loading, setLoading] = useState(false);

    const handleLogin = async (e: React.FormEvent) => {
        e.preventDefault();
        setError('');
        if (!credential || !password) return;
        setLoading(true);
        try {
            const res = await login({ user: credential, password });
            if (res.complete) {
                if (res.user) {
                    store.getActions().user.setUserData({
                        uuid: res.user.uuid,
                        username: res.user.username,
                        email: res.user.email,
                        language: res.user.language,
                        rootAdmin: res.user.root_admin,
                        useTotp: res.user.use_totp,
                        createdAt: new Date(res.user.created_at),
                        updatedAt: new Date(res.user.updated_at),
                    });
                }
                navigate(res.intended || '/');
            } else if (res.confirmationToken) {
                navigate(`/auth/login/checkpoint/${res.confirmationToken}`);
            } else {
                setError(res.error || t('auth:login_error'));
            }
        } catch (err: any) {
            setError(httpErrorToHuman(err));
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className='relative flex min-h-screen items-center justify-center overflow-hidden bg-background px-4 sm:px-6'>
            <Meteors number={25} angle={215} />

            <div className='fixed right-4 top-4 flex items-center gap-2'>
                <ModeToggle />
                <DropdownMenu>
                    <DropdownMenuTrigger asChild>
                        <button
                            type='button'
                            className='flex size-9 cursor-pointer items-center justify-center rounded-md text-muted-foreground transition-colors hover:bg-background hover:text-foreground'
                        >
                            <Globe size={22} className='size-5' />
                        </button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align='end'>
                        <DropdownMenuRadioGroup
                            value={i18n.language}
                            onValueChange={(v) => i18n.changeLanguage(v)}
                        >
                            {LANGUAGES.map((l) => (
                                <DropdownMenuRadioItem key={l.code} value={l.code}>
                                    {l.label}
                                </DropdownMenuRadioItem>
                            ))}
                        </DropdownMenuRadioGroup>
                    </DropdownMenuContent>
                </DropdownMenu>
            </div>

            <NeonGradientCard
                neonColors={{ firstColor: '#a3a3a3', secondColor: '#525252' }}
                borderSize={0.5}
                borderRadius={18}
                blurSize={2}
                className='w-full max-w-xl h-auto'
            >
                <CardHeader className='items-center gap-3 pb-0 text-center px-4 sm:px-6'>
                    <svg
                        viewBox='0 0 24 24'
                        className='h-8 text-foreground'
                        fill='none'
                        stroke='currentColor'
                        strokeWidth='1.5'
                        strokeLinecap='round'
                        strokeLinejoin='round'
                    >
                        <path d='M9.59 4.59A2 2 0 1 1 11 8H2m10.59 11.41A2 2 0 1 0 14 16H2m15.73-8.27A2.5 2.5 0 1 1 19.5 12H2' />
                    </svg>
                    <CardTitle className='text-xl'>{t('auth:login_title')}</CardTitle>
                </CardHeader>
                <CardContent className='pt-6 pb-4 px-4 sm:px-6'>
                    <form onSubmit={handleLogin} className='flex flex-col gap-5'>
                        <div className='flex flex-col gap-2'>
                            <Label htmlFor='credential'>{t('auth:username_or_email')}</Label>
                            <Input
                                id='credential'
                                type='text'
                                placeholder={t('auth:username_or_email')}
                                value={credential}
                                onChange={(e) => setCredential(e.target.value)}
                                required
                            />
                        </div>

                        <div className='flex flex-col gap-2'>
                            <Label htmlFor='password'>{t('auth:password')}</Label>
                            <Input
                                id='password'
                                type='password'
                                placeholder={t('auth:password')}
                                value={password}
                                onChange={(e) => setPassword(e.target.value)}
                                required
                            />
                        </div>

                        {error && <p className='text-sm text-destructive'>{error}</p>}

                        <Button
                            type='submit'
                            disabled={loading}
                            className='w-full'
                        >
                            {loading ? t('auth:loading') : t('auth:login')}
                        </Button>
                    </form>
                </CardContent>
            </NeonGradientCard>

        </div>
    );
};

export default LoginContainer;
