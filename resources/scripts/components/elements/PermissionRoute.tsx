import type { JSX, ReactNode } from 'react';
import { useTranslation } from 'react-i18next';

import { ServerError } from '@/components/elements/ScreenBlock';

import { usePermissions } from '@/plugins/usePermissions';

interface Props {
    children?: ReactNode;
    permission?: string | string[];
}

function PermissionRoute({ children, permission }: Props): JSX.Element {
    const { t } = useTranslation();
    const can = usePermissions(permission || []);

    if (permission === undefined || permission === null) {
        return <>{children}</>;
    }

    if (can.filter((p) => p).length > 0) {
        return <>{children}</>;
    }

    return <ServerError title={t('common:access_denied')} message={t('common:no_permission_message')} />;
}

export default PermissionRoute;
