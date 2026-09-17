import { useEffect, useState } from 'react';
import { NavLink } from 'react-router-dom';

import type { FeatureLimitKey, ServerRouteDefinition } from '@/routers/routes';

import Can from '@/components/elements/Can';
import {
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';

import { getSubdomainInfo } from '@/api/server/network/subdomain';

import { ServerContext } from '@/state/server';

interface ServerSidebarNavItemProps {
    route: ServerRouteDefinition;
    serverId: string;
    onClick?: () => void;
}

const ServerSidebarNavItem = ({ route, serverId, onClick }: ServerSidebarNavItemProps) => {
    const { icon: Icon, name, path, permission, featureLimit, end } = route;

    const featureLimits = ServerContext.useStoreState((state) => state.server.data?.featureLimits);
    const uuid = ServerContext.useStoreState((state) => state.server.data?.uuid);

    const [subdomainSupported, setSubdomainSupported] = useState(false);

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

    const isVisible = (): boolean => {
        if (!featureLimit) return true;
        if (featureLimits?.[featureLimit] === null) return true;
        if (featureLimit === 'network') {
            if (featureLimits?.allocations === null) return true;
            const allocationLimit = featureLimits?.allocations ?? 0;
            return allocationLimit > 0 || subdomainSupported;
        }
        const limitValue = featureLimits?.[featureLimit as FeatureLimitKey] ?? 0;
        return limitValue !== 0;
    };

    if (!isVisible()) return null;

    const to = path ? `/server/${serverId}/${path}` : `/server/${serverId}`;

    const NavContent = (
        <NavLink to={to} end={end} onClick={onClick}>
            <SidebarMenuItem>
                <SidebarMenuButton
                    tooltip={name}
                    className='data-[active]:bg-sidebar-accent data-[active]:font-medium data-[active]:text-sidebar-accent-foreground'
                >
                    {Icon && <Icon className='size-5 shrink-0' />}
                    <span>{name}</span>
                </SidebarMenuButton>
            </SidebarMenuItem>
        </NavLink>
    );

    if (permission === null || permission === undefined) {
        return NavContent;
    }

    return <Can action={permission} matchAny>{NavContent}</Can>;
};

export default ServerSidebarNavItem;
