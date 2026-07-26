import { useEffect } from 'react';
import { useLocation } from 'react-router-dom';

import MessageBox from '@/components/MessageBox';
import ConfigureTwoFactorForm from '@/components/dashboard/forms/ConfigureTwoFactorForm';
import UpdateEmailAddressForm from '@/components/dashboard/forms/UpdateEmailAddressForm';
import UpdatePasswordForm from '@/components/dashboard/forms/UpdatePasswordForm';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

const AccountOverviewContainer = () => {
    const { state } = useLocation();

    useEffect(() => {
        document.title = 'Account Settings | Pyrodactyl';
    }, []);

    return (
        <div className='mx-auto flex w-full max-w-[120rem] flex-1 flex-col gap-4 px-2 py-2 sm:px-14 sm:py-14'>
            {state?.twoFactorRedirect && (
                <MessageBox title={'2-Factor Required'} type={'error'}>
                    Your account must have two-factor authentication enabled in order to continue.
                </MessageBox>
            )}

            <Card>
                <CardHeader>
                    <CardTitle>Account Email</CardTitle>
                </CardHeader>
                <CardContent>
                    <UpdateEmailAddressForm />
                </CardContent>
            </Card>

            <div className='space-y-4'>
                <Card>
                    <CardHeader>
                        <CardTitle>Account Password</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <UpdatePasswordForm />
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Multi-Factor Authentication</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <ConfigureTwoFactorForm />
                    </CardContent>
                </Card>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Panel Version</CardTitle>
                </CardHeader>
                <CardContent>
                    <p className='mb-4 text-sm text-muted-foreground'>
                        This is useful to provide Pyro staff if you run into an unexpected issue.
                    </p>
                    <div className='flex flex-col gap-4'>
                        <code className='block rounded bg-muted px-4 py-2 text-sm'>
                            Version: {import.meta.env.VITE_PYRODACTYL_VERSION} -{' '}
                            {import.meta.env.VITE_BRANCH_NAME}
                        </code>
                        <code className='block rounded bg-muted px-4 py-2 text-sm'>
                            Commit : {import.meta.env.VITE_COMMIT_HASH.slice(0, 7)}
                        </code>
                    </div>
                </CardContent>
            </Card>
        </div>
    );
};

export default AccountOverviewContainer;
