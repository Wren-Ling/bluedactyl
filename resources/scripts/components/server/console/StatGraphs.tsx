import { ArrowDownToLine, ArrowUpToLine } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import { Area, AreaChart, CartesianGrid, XAxis, YAxis } from 'recharts';
import { useTranslation } from 'react-i18next';

import { ChartContainer, type ChartConfig } from '@/components/ui/chart';
import { SocketEvent } from '@/components/server/events';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';

import { bytesToString } from '@/lib/formatters';

import { ServerContext } from '@/state/server';

import useWebsocketEvent from '@/plugins/useWebsocketEvent';

const MAX_POINTS = 20;

const cpuConfig: ChartConfig = {
    value: { label: 'CPU', color: 'var(--color-chart-1)' },
};
const memConfig: ChartConfig = {
    value: { label: 'Memory', color: 'var(--color-chart-2)' },
};
const netConfig: ChartConfig = {
    tx: { label: 'Inbound', color: 'var(--color-chart-4)' },
    rx: { label: 'Outbound', color: 'var(--color-chart-3)' },
};

const initArray = (length: number) => Array.from({ length }, (_, i) => ({ time: i, value: -5 }));

type DataPoint = { time: number; value: number };
type NetDataPoint = { time: number; tx: number; rx: number };

const StatGraphs = () => {
    const { t } = useTranslation();
    const status = ServerContext.useStoreState((state) => state.status.value);
    const limits = ServerContext.useStoreState((state) => state.server.data!.limits);
    const previousNet = useRef({ tx: -1, rx: -1 });

    const [cpuData, setCpuData] = useState<DataPoint[]>(initArray(MAX_POINTS));
    const [memData, setMemData] = useState<DataPoint[]>(initArray(MAX_POINTS));
    const [netData, setNetData] = useState<NetDataPoint[]>(
        Array.from({ length: MAX_POINTS }, (_, i) => ({ time: i, tx: 0, rx: 0 })),
    );

    useEffect(() => {
        if (status === 'offline') {
            setCpuData(initArray(MAX_POINTS));
            setMemData(initArray(MAX_POINTS));
            setNetData(Array.from({ length: MAX_POINTS }, (_, i) => ({ time: i, tx: 0, rx: 0 })));
        }
    }, [status]);

    useWebsocketEvent(SocketEvent.STATS, (data: string) => {
        let values: any = {};
        try {
            values = JSON.parse(data);
        } catch {
            return;
        }

        setCpuData((prev) => {
            const next = [...prev.slice(1), { time: prev.length, value: values.cpu_absolute }];
            return next;
        });

        setMemData((prev) => {
            const next = [...prev.slice(1), { time: prev.length, value: Math.floor(values.memory_bytes / 1024 / 1024) }];
            return next;
        });

        setNetData((prev) => {
            const tx = previousNet.current.tx < 0 ? 0 : Math.max(0, values.network.tx_bytes - previousNet.current.tx);
            const rx = previousNet.current.rx < 0 ? 0 : Math.max(0, values.network.rx_bytes - previousNet.current.rx);
            previousNet.current = { tx: values.network.tx_bytes, rx: values.network.rx_bytes };
            const next = [...prev.slice(1), { time: prev.length, tx, rx }];
            return next;
        });
    });

    return (
        <TooltipProvider>
            <div
                className='transform-gpu skeleton-anim-2'
                style={{
                    animationDelay: '250ms',
                    animationTimingFunction:
                        'linear(0,0.01,0.04 1.6%,0.161 3.3%,0.816 9.4%,1.046,1.189 14.4%,1.231,1.254 17%,1.259,1.257 18.6%,1.236,1.194 22.3%,1.057 27%,0.999 29.4%,0.955 32.1%,0.942,0.935 34.9%,0.933,0.939 38.4%,1 47.3%,1.011,1.017 52.6%,1.016 56.4%,1 65.2%,0.996 70.2%,1.001 87.2%,1)',
                }}
            >
                <div className='group h-full rounded-xl border bg-card p-3 text-card-foreground shadow-sm transition-all duration-150 hover:border-foreground/20 sm:p-4'>
                    <div className='mb-3 flex items-center justify-between sm:mb-4'>
                        <h3 className='text-sm font-semibold'>{t('console:cpu')}</h3>
                    </div>
                    <div className='z-10 h-40 overflow-hidden rounded-lg sm:h-48'>
                        <ChartContainer config={cpuConfig} className='h-full w-full'>
                            <AreaChart data={cpuData} margin={{ top: 8, right: 0, bottom: 0, left: 0 }}>
                                <defs>
                                    <linearGradient id='fillCpu' x1='0' y1='0' x2='0' y2='1'>
                                        <stop offset='5%' stopColor='var(--color-chart-1)' stopOpacity={0.3} />
                                        <stop offset='95%' stopColor='var(--color-chart-1)' stopOpacity={0.05} />
                                    </linearGradient>
                                </defs>
                                <CartesianGrid vertical={false} stroke='var(--color-border)' strokeDasharray='3 3' />
                                <XAxis hide dataKey='time' />
                                <YAxis
                                    domain={[0, limits.cpu || 100]}
                                    tickCount={3}
                                    tickLine={false}
                                    axisLine={false}
                                    tick={{ fontSize: 11, fontWeight: 600 }}
                                    tickFormatter={(v) => `${v}%`}
                                    width={40}
                                />
                                <Area
                                    type='monotone'
                                    dataKey='value'
                                    stroke='var(--color-chart-1)'
                                    strokeWidth={2}
                                    fill='url(#fillCpu)'
                                    dot={false}
                                    isAnimationActive={false}
                                />
                            </AreaChart>
                        </ChartContainer>
                    </div>
                </div>
            </div>
            <div
                className='transform-gpu skeleton-anim-2'
                style={{
                    animationDelay: '275ms',
                    animationTimingFunction:
                        'linear(0,0.01,0.04 1.6%,0.161 3.3%,0.816 9.4%,1.046,1.189 14.4%,1.231,1.254 17%,1.259,1.257 18.6%,1.236,1.194 22.3%,1.057 27%,0.999 29.4%,0.955 32.1%,0.942,0.935 34.9%,0.933,0.939 38.4%,1 47.3%,1.011,1.017 52.6%,1.016 56.4%,1 65.2%,0.996 70.2%,1.001 87.2%,1)',
                }}
            >
                <div className='group h-full rounded-xl border bg-card p-3 text-card-foreground shadow-sm transition-all duration-150 hover:border-foreground/20 sm:p-4'>
                    <div className='mb-3 flex items-center justify-between sm:mb-4'>
                        <h3 className='text-sm font-semibold'>{t('console:memory')}</h3>
                    </div>
                    <div className='z-10 h-40 overflow-hidden rounded-lg sm:h-48'>
                        <ChartContainer config={memConfig} className='h-full w-full'>
                            <AreaChart data={memData} margin={{ top: 8, right: 0, bottom: 0, left: 0 }}>
                                <defs>
                                    <linearGradient id='fillMem' x1='0' y1='0' x2='0' y2='1'>
                                        <stop offset='5%' stopColor='var(--color-chart-2)' stopOpacity={0.3} />
                                        <stop offset='95%' stopColor='var(--color-chart-2)' stopOpacity={0.05} />
                                    </linearGradient>
                                </defs>
                                <CartesianGrid vertical={false} stroke='var(--color-border)' strokeDasharray='3 3' />
                                <XAxis hide dataKey='time' />
                                <YAxis
                                    domain={[0, limits.memory || 4096]}
                                    tickCount={3}
                                    tickLine={false}
                                    axisLine={false}
                                    tick={{ fontSize: 11, fontWeight: 600 }}
                                    tickFormatter={(v) => `${v} MiB`}
                                    width={55}
                                />
                                <Area
                                    type='monotone'
                                    dataKey='value'
                                    stroke='var(--color-chart-2)'
                                    strokeWidth={2}
                                    fill='url(#fillMem)'
                                    dot={false}
                                    isAnimationActive={false}
                                />
                            </AreaChart>
                        </ChartContainer>
                    </div>
                </div>
            </div>
            <div
                className='transform-gpu skeleton-anim-2'
                style={{
                    animationDelay: '300ms',
                    animationTimingFunction:
                        'linear(0,0.01,0.04 1.6%,0.161 3.3%,0.816 9.4%,1.046,1.189 14.4%,1.231,1.254 17%,1.259,1.257 18.6%,1.236,1.194 22.3%,1.057 27%,0.999 29.4%,0.955 32.1%,0.942,0.935 34.9%,0.933,0.939 38.4%,1 47.3%,1.011,1.017 52.6%,1.016 56.4%,1 65.2%,0.996 70.2%,1.001 87.2%,1)',
                }}
            >
                <div className='group h-full rounded-xl border bg-card p-3 text-card-foreground shadow-sm transition-all duration-150 hover:border-foreground/20 sm:p-4'>
                    <div className='mb-3 flex items-center justify-between sm:mb-4'>
                        <h3 className='text-sm font-semibold'>{t('console:network_activity')}</h3>
                        <div className='flex items-center text-xs text-muted-foreground sm:text-sm'>
                            <div className='flex gap-2'>
                                <Tooltip>
                                    <TooltipTrigger asChild>
                                        <div className='flex items-center cursor-default'>
                                            <ArrowDownToLine size={22} className='mr-2 text-yellow-400' />
                                        </div>
                                    </TooltipTrigger>
                                    <TooltipContent side='top' sideOffset={5}>
                                        {t('console:inbound')}
                                    </TooltipContent>
                                </Tooltip>

                                <Tooltip>
                                    <TooltipTrigger asChild>
                                        <div className='flex items-center cursor-default'>
                                            <ArrowUpToLine size={22} className='text-blue-400' />
                                        </div>
                                    </TooltipTrigger>
                                    <TooltipContent side='top' sideOffset={5}>
                                        {t('console:outbound')}
                                    </TooltipContent>
                                </Tooltip>
                            </div>
                        </div>
                    </div>
                    <div className='z-10 h-40 overflow-hidden rounded-lg sm:h-48'>
                        <ChartContainer config={netConfig} className='h-full w-full'>
                            <AreaChart data={netData} margin={{ top: 8, right: 0, bottom: 0, left: 0 }}>
                                <defs>
                                    <linearGradient id='fillNetTx' x1='0' y1='0' x2='0' y2='1'>
                                        <stop offset='5%' stopColor='var(--color-chart-4)' stopOpacity={0.3} />
                                        <stop offset='95%' stopColor='var(--color-chart-4)' stopOpacity={0.05} />
                                    </linearGradient>
                                    <linearGradient id='fillNetRx' x1='0' y1='0' x2='0' y2='1'>
                                        <stop offset='5%' stopColor='var(--color-chart-3)' stopOpacity={0.3} />
                                        <stop offset='95%' stopColor='var(--color-chart-3)' stopOpacity={0.05} />
                                    </linearGradient>
                                </defs>
                                <CartesianGrid vertical={false} stroke='var(--color-border)' strokeDasharray='3 3' />
                                <XAxis hide dataKey='time' />
                                <YAxis
                                    tickCount={3}
                                    tickLine={false}
                                    axisLine={false}
                                    tick={{ fontSize: 11, fontWeight: 600 }}
                                    tickFormatter={(v) => bytesToString(v)}
                                    width={60}
                                />
                                <Area
                                    type='monotone'
                                    dataKey='tx'
                                    stroke='var(--color-chart-4)'
                                    strokeWidth={2}
                                    fill='url(#fillNetTx)'
                                    dot={false}
                                    isAnimationActive={false}
                                />
                                <Area
                                    type='monotone'
                                    dataKey='rx'
                                    stroke='var(--color-chart-3)'
                                    strokeWidth={2}
                                    fill='url(#fillNetRx)'
                                    dot={false}
                                    isAnimationActive={false}
                                />
                            </AreaChart>
                        </ChartContainer>
                    </div>
                </div>
            </div>
        </TooltipProvider>
    );
};

export default StatGraphs;
