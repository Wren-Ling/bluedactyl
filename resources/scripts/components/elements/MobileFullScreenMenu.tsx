import { Code, House, Key, Settings, X } from 'lucide-react';
import React, { useEffect, useState } from 'react';
import { NavLink } from 'react-router-dom';

import type { FeatureLimitKey, ServerRouteDefinition } from '@/routers/routes';
import { getServerNavRoutes } from '@/routers/routes';

import Can from '@/components/elements/Can';

import { getSubdomainInfo } from '@/api/server/network/subdomain';

import { ServerContext } from '@/state/server';

interface MobileFullScreenMenuProps {
    isVisible: boolean;
    onClose: () => void;
    children: React.ReactNode;
}

const MobileFullScreenMenu = ({ isVisible, onClose, children }: MobileFullScreenMenuProps) => {
    if (!isVisible) return null;

    return (
        <div className='lg:hidden fixed inset-0 z-9999 bg-background pt-16'>
            {/* Close button */}
            <button
                onClick={onClose}
                className='absolute top-4 right-4 p-2 text-muted-foreground hover:text-foreground hover:bg-white/10 rounded-lg transition-all duration-200'
                aria-label='Close menu'
            >
                <X size={22} />
            </button>

            {/* Full screen navigation menu */}
            <div className='h-full overflow-y-auto'>
                <div className='p-6'>
                    {/* Menu items */}
                    <nav className='space-y-2'>{children}</nav>
                </div>
            </div>
        </div>
    );
};

interface NavigationItemProps {
    to: string;
    icon: React.ComponentType<React.SVGProps<SVGSVGElement>>;
    children: React.ReactNode;
    end?: boolean;
    onClick: () => void;
}

const NavigationItem = ({ to, icon: Icon, children, end = false, onClick }: NavigationItemProps) => (
    <NavLink
        to={to}
        end={end}
        className={({ isActive }) =>
            `flex items-center gap-4 p-4 rounded-md transition-all duration-200 ${
                isActive
                    ? 'bg-muted border-l-4 border-foreground text-foreground'
                    : 'text-muted-foreground hover:text-foreground hover:bg-white/5 border-l-4 border-transparent'
            }`
        }
        onClick={onClick}
    >
        <div>
            <Icon size={22} />
        </div>
        <span className='text-lg font-medium'>{children}</span>
    </NavLink>
);

interface DashboardMobileMenuProps {
    isVisible: boolean;
    onClose: () => void;
}

export const DashboardMobileMenu = ({ isVisible, onClose }: DashboardMobileMenuProps) => {
    return (
        <MobileFullScreenMenu isVisible={isVisible} onClose={onClose}>
            <NavigationItem to='/' icon={House} end onClick={onClose}>
                Servers
            </NavigationItem>
            <NavigationItem to='/account/api' icon={Code} end onClick={onClose}>
                API Keys
            </NavigationItem>
            <NavigationItem to='/account/ssh' icon={Key} end onClick={onClose}>
                SSH Keys
            </NavigationItem>
            <NavigationItem to='/account' icon={Settings} end onClick={onClose}>
                Settings
            </NavigationItem>
        </MobileFullScreenMenu>
    );
};

interface ServerMobileNavItemProps {
    route: ServerRouteDefinition;
    serverId: string;
    onClose: () => void;
}

/**
 * Mobile navigation item that handles permission and feature limit checks.
 */
const ServerMobileNavItem = ({ route, serverId, onClose }: ServerMobileNavItemProps) => {
    const { icon: Icon, name, path, permission, featureLimit, end } = route;

    // Feature limits from server state
    const featureLimits = ServerContext.useStoreState((state) => state.server.data?.featureLimits);
    const uuid = ServerContext.useStoreState((state) => state.server.data?.uuid);

    // State for subdomain support check (only for network route)
    const [subdomainSupported, setSubdomainSupported] = useState(false);

    // Check subdomain support for network feature
    useEffect(() => {
        if (featureLimit !== 'network' || !uuid) return;

        const checkSubdomainSupport = async () => {
            try {
                const data = await getSubdomainInfo(uuid);
                setSubdomainSupported(data.supported);
            } catch {
                setSubdomainSupported(false);
            }
        };

        checkSubdomainSupport();
    }, [featureLimit, uuid]);

    // Check if the item should be visible based on feature limits
    const isVisible = (): boolean => {
        if (!featureLimit) return true;

        if (featureLimit === 'network') {
            const allocationLimit = featureLimits?.allocations ?? 0;
            return allocationLimit > 0 || subdomainSupported;
        }

        const limitValue = featureLimits?.[featureLimit as FeatureLimitKey] ?? 0;
        return limitValue !== 0;
    };

    if (!isVisible() || !Icon || !name) return null;

    const to = path ? `/server/${serverId}/${path}` : `/server/${serverId}`;

    const NavContent = (
        <NavigationItem to={to} icon={Icon} end={end} onClick={onClose}>
            {name}
        </NavigationItem>
    );

    if (permission === null || permission === undefined) {
        return NavContent;
    }

    return (
        <Can action={permission} matchAny>
            {NavContent}
        </Can>
    );
};

interface ServerMobileMenuProps {
    isVisible: boolean;
    onClose: () => void;
    serverId?: string;
    // These props are kept for backwards compatibility but are no longer used
    // The component now reads feature limits directly from ServerContext
    databaseLimit?: number | null;
    backupLimit?: number | null;
    allocationLimit?: number | null;
    subdomainSupported?: boolean;
}

export const ServerMobileMenu = ({ isVisible, onClose, serverId }: ServerMobileMenuProps) => {
    if (!serverId) return null;

    // Get navigation routes from centralized config
    const navRoutes = getServerNavRoutes();

    return (
        <MobileFullScreenMenu isVisible={isVisible} onClose={onClose}>
            {navRoutes.map((route) => (
                <ServerMobileNavItem key={route.path || 'home'} route={route} serverId={serverId} onClose={onClose} />
            ))}
        </MobileFullScreenMenu>
    );
};

export default MobileFullScreenMenu;
