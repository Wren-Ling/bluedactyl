import { cn } from '@/lib/utils';

const Label = ({
    as: Tag = 'label',
    isLight,
    className,
    ...props
}: React.HTMLAttributes<HTMLElement> & { as?: string; isLight?: boolean }) => (
    <Tag className={cn(className)} {...props} />
);

export default Label;
