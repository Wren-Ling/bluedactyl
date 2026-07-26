import { ArrowDownToLine, Filter, History, RotateCcw, Search, X } from 'lucide-react';
import { useEffect, useMemo, useState } from 'react';

import FlashMessageRender from '@/components/FlashMessageRender';
import { Button } from '@/components/ui/button';
import { MainPageHeader } from '@/components/elements/MainPageHeader';
import Select from '@/components/elements/Select';
import Spinner from '@/components/elements/Spinner';
import ActivityLogEntry from '@/components/elements/activity/ActivityLogEntry';
import { Input } from '@/components/ui/input';
import PaginationFooter from '@/components/elements/table/PaginationFooter';

import { ActivityLogFilters } from '@/api/account/activity';
import { useActivityLogs } from '@/api/server/activity';

import { useFlashKey } from '@/plugins/useFlash';
import useLocationHash from '@/plugins/useLocationHash';

const ServerActivityLogContainer = () => {
    const { hash } = useLocationHash();
    const { clearAndAddHttpError } = useFlashKey('server:activity');
    const [filters, setFilters] = useState<ActivityLogFilters>({ page: 1, sorts: { timestamp: -1 } });
    const [searchTerm, setSearchTerm] = useState('');
    const [selectedEventType, setSelectedEventType] = useState('');
    const [showFilters, setShowFilters] = useState(false);
    const [dateRange, setDateRange] = useState('all');

    const { data, isValidating, error } = useActivityLogs(filters, {
        revalidateOnMount: true,
        revalidateOnFocus: false,
    });

    const eventTypes = useMemo(() => {
        if (!data?.items) return [];
        const types = [...new Set(data.items.map((item) => item.event))];
        return types.sort();
    }, [data?.items]);

    const filteredData = useMemo(() => {
        if (!data?.items) return data;
        let filtered = data.items;
        if (searchTerm) {
            filtered = filtered.filter(
                (item) =>
                    item.event.toLowerCase().includes(searchTerm.toLowerCase()) ||
                    item.ip?.toLowerCase().includes(searchTerm.toLowerCase()) ||
                    item.relationships.actor?.username?.toLowerCase().includes(searchTerm.toLowerCase()) ||
                    JSON.stringify(item.properties).toLowerCase().includes(searchTerm.toLowerCase()),
            );
        }
        if (selectedEventType) {
            filtered = filtered.filter((item) => item.event === selectedEventType);
        }
        if (dateRange !== 'all') {
            const now = new Date();
            const cutoff = new Date();
            switch (dateRange) {
                case '1h': cutoff.setHours(now.getHours() - 1); break;
                case '24h': cutoff.setDate(now.getDate() - 1); break;
                case '7d': cutoff.setDate(now.getDate() - 7); break;
                case '30d': cutoff.setDate(now.getDate() - 30); break;
            }
            filtered = filtered.filter((item) => new Date(item.timestamp) >= cutoff);
        }
        return { ...data, items: filtered };
    }, [data, searchTerm, selectedEventType, dateRange]);

    const exportLogs = () => {
        if (!filteredData?.items) return;
        const csvContent = [
            ['Timestamp', 'Event', 'Actor', 'IP Address', 'Properties'].join(','),
            ...filteredData.items.map((item) =>
                [
                    new Date(item.timestamp).toISOString(),
                    item.event,
                    item.relationships.actor?.username || 'System',
                    item.ip || '',
                    JSON.stringify(item.properties).replace(/"/g, '""'),
                ].map((field) => `"${field}"`).join(','),
            ),
        ].join('\n');
        const blob = new Blob([csvContent], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `server-activity-log-${new Date().toISOString().split('T')[0]}.csv`;
        a.click();
        window.URL.revokeObjectURL(url);
    };

    const clearAllFilters = () => {
        setFilters((value) => ({ ...value, filters: {} }));
        setSearchTerm('');
        setSelectedEventType('');
        setDateRange('all');
    };

    const hasActiveFilters = filters.filters?.event || filters.filters?.ip || searchTerm || selectedEventType || dateRange !== 'all';

    useEffect(() => {
        const handleKeyDown = (e: KeyboardEvent) => {
            if (e.ctrlKey || e.metaKey) {
                switch (e.key) {
                    case 'f': e.preventDefault(); setShowFilters(!showFilters); break;
                    case 'e': e.preventDefault(); exportLogs(); break;
                }
            }
        };
        window.addEventListener('keydown', handleKeyDown);
        return () => window.removeEventListener('keydown', handleKeyDown);
    }, [showFilters]);

    useEffect(() => {
        setFilters((value) => ({ ...value, filters: { ip: hash.ip, event: hash.event } }));
    }, [hash]);

    useEffect(() => {
        clearAndAddHttpError(error);
    }, [error]);

    return (
        <div className='mx-auto flex w-full max-w-[120rem] flex-1 flex-col gap-4 px-2 py-2 sm:px-14 sm:py-14'>
            <FlashMessageRender byKey={'server:activity'} />

            <MainPageHeader
                direction='column'
                title={'Activity Log'}
                titleChildren={
                    <div className='flex flex-wrap items-center gap-2'>
                        <Button variant='outline' onClick={() => setShowFilters(!showFilters)} className='flex items-center gap-2' title='Toggle Filters (Ctrl+F)'>
                            <Filter className='size-4' />
                            Filters
                            {hasActiveFilters && <span className='size-2 rounded-full bg-primary' />}
                        </Button>
                        <Button variant='outline' onClick={exportLogs} disabled={!filteredData?.items?.length} className='flex items-center gap-2' title='Export CSV (Ctrl+E)'>
                            <ArrowDownToLine className='size-4' />
                            Export
                        </Button>
                    </div>
                }
            >
                <p className='text-sm leading-relaxed text-muted-foreground'>
                    Monitor all server activity and track user actions. Filter events, search for specific
                    activities, and export logs for audit purposes.
                </p>
            </MainPageHeader>

            {showFilters && (
                <div className='rounded-xl border bg-card p-4 text-card-foreground shadow-sm'>
                    <div className='mb-4 flex items-center gap-2'>
                        <div className='flex size-5 items-center justify-center rounded-lg bg-muted'>
                            <Filter className='size-3 text-muted-foreground' />
                        </div>
                        <h3 className='text-base font-semibold text-foreground'>Filters</h3>
                    </div>

                    <div className='grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3'>
                        <div>
                            <label className='mb-2 block text-sm font-medium text-muted-foreground'>Search</label>
                            <div className='relative'>
                                <Search className='pointer-events-none absolute left-3 top-1/2 z-10 size-4 -translate-y-1/2 text-muted-foreground' />
                                <Input type='text' placeholder='Search events, IPs, users...' value={searchTerm} onChange={(e) => setSearchTerm(e.target.value)} className='pl-10' />
                            </div>
                        </div>

                        <div>
                            <label className='mb-2 block text-sm font-medium text-muted-foreground'>Event Type</label>
                            <Select value={selectedEventType} onChange={(e) => setSelectedEventType(e.target.value)}>
                                <option value=''>All Events</option>
                                {eventTypes.map((type) => (<option key={type} value={type}>{type}</option>))}
                            </Select>
                        </div>

                        <div>
                            <label className='mb-2 block text-sm font-medium text-muted-foreground'>Time Range</label>
                            <Select value={dateRange} onChange={(e) => setDateRange(e.target.value)}>
                                <option value='all'>All Time</option>
                                <option value='1h'>Last Hour</option>
                                <option value='24h'>Last 24 Hours</option>
                                <option value='7d'>Last 7 Days</option>
                                <option value='30d'>Last 30 Days</option>
                            </Select>
                        </div>

                        <div className='flex items-end'>
                            {hasActiveFilters && (
                                <Button variant='outline' onClick={clearAllFilters} className='flex w-full items-center gap-2'>
                                    <X className='size-4' />
                                    Clear All Filters
                                </Button>
                            )}
                        </div>
                    </div>
                </div>
            )}

            <div className='rounded-xl border bg-card p-4 text-card-foreground shadow-sm'>
                <div className='mb-4 flex items-center gap-2'>
                    <div className='flex size-5 items-center justify-center rounded-lg bg-muted'>
                        <History className='size-3 text-muted-foreground' />
                    </div>
                    <h3 className='text-base font-semibold text-foreground'>Events</h3>
                    {filteredData?.items && (
                        <span className='text-sm text-muted-foreground'>({filteredData.items.length} {filteredData.items.length === 1 ? 'event' : 'events'})</span>
                    )}
                </div>

                {!data && isValidating ? (
                    <Spinner centered />
                ) : !filteredData?.items?.length ? (
                    <div className='py-12 text-center'>
                        <RotateCcw className='mx-auto mb-4 size-5 text-muted-foreground' />
                        <h3 className='mb-2 text-lg font-semibold text-foreground'>
                            {hasActiveFilters ? 'No Matching Activity' : 'No Server Activity Yet'}
                        </h3>
                        <p className='mx-auto mb-4 max-w-lg text-sm leading-relaxed text-muted-foreground'>
                            {hasActiveFilters
                                ? "Try adjusting your filters or search terms to find the activity you're looking for."
                                : 'Server activity logs will appear here as you manage your server. Start your server or perform actions to see them here.'}
                        </p>
                        {hasActiveFilters && (
                            <div className='flex justify-center gap-2'>
                                <Button variant='outline' onClick={clearAllFilters}>Clear All Filters</Button>
                                <Button variant='outline' onClick={() => setShowFilters(true)}>Adjust Filters</Button>
                            </div>
                        )}
                    </div>
                ) : (
                    <div className='divide-y divide-border/30'>
                        {filteredData.items.map((activity) => (
                            <ActivityLogEntry key={activity.id} activity={activity}>
                                <span />
                            </ActivityLogEntry>
                        ))}
                    </div>
                )}

                {data && (
                    <div className='mt-4'>
                        <PaginationFooter
                            pagination={data.pagination}
                            onPageSelect={(page) => setFilters((value) => ({ ...value, page }))}
                        />
                    </div>
                )}
            </div>
        </div>
    );
};

export default ServerActivityLogContainer;
