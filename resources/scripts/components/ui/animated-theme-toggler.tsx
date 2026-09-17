'use client';

import { Moon, Sun } from 'lucide-react';
import { useCallback, useRef } from 'react';
import { flushSync } from 'react-dom';

import { useTheme } from '@/components/ui/theme-provider';

import { cn } from '@/lib/utils';

interface AnimatedThemeTogglerProps extends React.ComponentPropsWithoutRef<'button'> {
    duration?: number;
}

export const AnimatedThemeToggler = ({ className, duration = 400, ...props }: AnimatedThemeTogglerProps) => {
    const { theme, setTheme } = useTheme();
    const buttonRef = useRef<HTMLButtonElement>(null);

    const isDark =
        theme === 'dark' || (theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);

    const toggleTheme = useCallback(() => {
        if (!buttonRef.current) return;

        const applyTheme = () => {
            const nextTheme = isDark ? 'light' : 'dark';
            setTheme(nextTheme);
        };

        if (typeof document === 'undefined' || !('startViewTransition' in document)) {
            applyTheme();
            return;
        }

        const transition = (document as any).startViewTransition(() => {
            flushSync(applyTheme);
        });

        const ready = transition?.ready;
        if (ready && typeof ready.then === 'function') {
            ready.then(() => {
                const button = buttonRef.current;
                if (!button) return;

                const { top, left, width, height } = button.getBoundingClientRect();

                const x = left + width / 2;
                const y = top + height / 2;

                const maxRadius = Math.hypot(
                    Math.max(left, window.innerWidth - left),
                    Math.max(top, window.innerHeight - top),
                );

                document.documentElement.animate(
                    {
                        clipPath: [`circle(0px at ${x}px ${y}px)`, `circle(${maxRadius}px at ${x}px ${y}px)`],
                    },
                    {
                        duration,
                        easing: 'ease-in-out',
                        pseudoElement: '::view-transition-new(root)',
                    },
                );
            });
        }
    }, [isDark, duration, setTheme]);

    return (
        <button
            ref={buttonRef}
            onClick={toggleTheme}
            className={cn(
                'flex items-center justify-center rounded-md transition-colors hover:bg-accent hover:text-accent-foreground size-9',
                className,
            )}
            {...props}
        >
            {isDark ? <Sun className='h-[1.2rem] w-[1.2rem]' /> : <Moon className='h-[1.2rem] w-[1.2rem]' />}
            <span className='sr-only'>Toggle theme</span>
        </button>
    );
};
