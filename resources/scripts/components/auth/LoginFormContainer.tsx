import { Form } from 'formik';
import { forwardRef } from 'react';

import { cn } from '@/lib/utils';
import FlashMessageRender from '@/components/FlashMessageRender';

type Props = React.DetailedHTMLProps<React.FormHTMLAttributes<HTMLFormElement>, HTMLFormElement> & {
    title?: string;
};

const LoginFormContainer = forwardRef<HTMLFormElement, Props>(({ title, className, ...props }, ref) => (
    <div className={cn('w-full max-w-sm', className)}>
        {title && <h2 className='text-xl font-semibold tracking-tight text-center mb-6 text-foreground'>{title}</h2>}
        <FlashMessageRender />
        <Form {...props} ref={ref}>
            <div className='flex flex-col gap-5'>{props.children}</div>
        </Form>
    </div>
));

LoginFormContainer.displayName = 'LoginFormContainer';

export default LoginFormContainer;
