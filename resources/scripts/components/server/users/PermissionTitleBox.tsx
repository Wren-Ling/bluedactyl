import { useField } from 'formik';
import { memo, useCallback } from 'react';
import isEqual from 'react-fast-compare';

import { Input } from '@/components/ui/input';
import { Card, CardHeader, CardContent } from '@/components/ui/card';

interface Props {
    isEditable?: boolean;
    title: string;
    permissions: string[];
    className?: string;
    children: React.ReactNode;
}

const PermissionTitleBox: React.FC<Props> = memo(({ isEditable, title, permissions, className, children }) => {
    const [{ value }, , { setValue }] = useField<string[]>('permissions');

    const onCheckboxClicked = useCallback(
        (e: React.ChangeEvent<HTMLInputElement>) => {
            if (e.currentTarget.checked) {
                setValue([...value, ...permissions.filter((p) => !value.includes(p))]);
            } else {
                setValue(value.filter((p) => !permissions.includes(p)));
            }
        },
        [permissions, value],
    );

    return (
        <Card className={className}>
            <CardHeader>
                <div className={`flex items-center justify-between w-full`}>
                    <p className={`text-sm capitalize`}>{title}</p>
                    {isEditable && (
                        <Input
                            type={'checkbox'}
                            checked={permissions.every((p) => value.includes(p))}
                            onChange={onCheckboxClicked}
                        />
                    )}
                </div>
            </CardHeader>
            <CardContent>
                {children}
            </CardContent>
        </Card>
    );
}, isEqual);

PermissionTitleBox.displayName = 'PermissionTitleBox';

export default PermissionTitleBox;
