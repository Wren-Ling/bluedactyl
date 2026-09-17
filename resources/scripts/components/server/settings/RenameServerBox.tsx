import { Actions, useStoreActions } from 'easy-peasy';
import { Form, Formik } from 'formik';
import { toast } from 'sonner';
import { useTranslation } from 'react-i18next';
import { object, string } from 'yup';

import { Button } from '@/components/ui/button';
import Field from '@/components/elements/Field';
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';

import { httpErrorToHuman } from '@/api/http';
import renameServer from '@/api/server/renameServer';

import { ApplicationStore } from '@/state';
import { ServerContext } from '@/state/server';

interface Values {
    name: string;
    description: string;
}

const RenameServerForm = () => {
    const { t } = useTranslation('settings');
    return (
        <Card>
            <CardHeader>
                <CardTitle className='text-xl font-extrabold tracking-tight'>{t('server_details')}</CardTitle>
            </CardHeader>
            <CardContent>
                <Form className='flex flex-col gap-4'>
                    <Field id={'name'} name={'name'} label={t('server_name')} type={'text'} />
                    <Field id={'description'} name={'description'} label={t('server_description')} type={'text'} />
                    <div className={`mt-6 text-right`}>
                        <Button type={'submit'}>
                            {t('save')}
                        </Button>
                    </div>
                </Form>
            </CardContent>
        </Card>
    );
};

const RenameServerBox = () => {
    const server = ServerContext.useStoreState((state) => state.server.data!);
    const setServer = ServerContext.useStoreActions((actions) => actions.server.setServer);
    const { addError, clearFlashes } = useStoreActions((actions: Actions<ApplicationStore>) => actions.flashes);

    const { t } = useTranslation('settings');
    const submit = ({ name, description }: Values) => {
        clearFlashes('settings');
        toast(t('updating'));
        renameServer(server.uuid, name, description)
            .then(() => setServer({ ...server, name, description }))
            .catch((error) => {
                console.error(error);
                addError({ key: 'settings', message: httpErrorToHuman(error) });
            })
            .then(() => toast.success(t('updated')));
    };

    return (
        <Formik
            onSubmit={submit}
            initialValues={{
                name: server.name,
                description: server.description,
            }}
            validationSchema={object().shape({
                name: string().required().min(1),
                description: string().nullable(),
            })}
        >
            <RenameServerForm />
        </Formik>
    );
};

export default RenameServerBox;
