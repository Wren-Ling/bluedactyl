import { useEffect } from 'react';
import { useLocation } from 'react-router-dom';
import { useTranslation } from 'react-i18next';

import MessageBox from '@/components/MessageBox';
import ConfigureTwoFactorForm from '@/components/dashboard/forms/ConfigureTwoFactorForm';
import UpdateEmailAddressForm from '@/components/dashboard/forms/UpdateEmailAddressForm';
import UpdatePasswordForm from '@/components/dashboard/forms/UpdatePasswordForm';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

const AccountOverviewContainer = () => {
    const { t } = useTranslation();
    const { state } = useLocation();

    useEffect(() => {
        document.title = t('account:account_settings_title');
    }, [t]);

    return (
        <div className='mx-auto flex w-full max-w-[120rem] flex-1 flex-col gap-4 px-2 py-2 sm:px-14 sm:py-14'>
            {state?.twoFactorRedirect && (
                <MessageBox title={t('account:two_factor_required')} type={'error'}>
                    {t('account:two_factor_required_desc')}
                </MessageBox>
            )}

            <Card>
                <CardHeader>
                    <CardTitle>{t('account:account_email')}</CardTitle>
                </CardHeader>
                <CardContent>
                    <UpdateEmailAddressForm />
                </CardContent>
            </Card>

            <div className='space-y-4'>
                <Card>
                    <CardHeader>
                        <CardTitle>{t('account:account_password')}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <UpdatePasswordForm />
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>{t('account:multi_factor_auth')}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <ConfigureTwoFactorForm />
                    </CardContent>
                </Card>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>{t('account:panel_version')}</CardTitle>
                </CardHeader>
                <CardContent>
                    <p className='mb-4 text-sm text-muted-foreground'>
                        {t('account:panel_version_desc')}
                    </p>
                    <div className='flex flex-col gap-4'>
                        <code className='block rounded bg-muted px-4 py-2 text-sm'>
                            {t('account:version')}: {import.meta.env.VITE_PYRODACTYL_VERSION} -{' '}
                            {import.meta.env.VITE_BRANCH_NAME}
                        </code>
                        <code className='block rounded bg-muted px-4 py-2 text-sm'>
                            {t('account:commit')} : {import.meta.env.VITE_COMMIT_HASH.slice(0, 7)}
                        </code>
                    </div>
                </CardContent>
            </Card>
        </div>
    );
};

export default AccountOverviewContainer;
