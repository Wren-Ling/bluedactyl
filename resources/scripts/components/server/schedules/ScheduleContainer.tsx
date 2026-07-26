import { Clock } from 'lucide-react';
import { useEffect, useState } from 'react';
import { NavLink } from 'react-router-dom';

import FlashMessageRender from '@/components/FlashMessageRender';
import { Button } from '@/components/ui/button';
import Can from '@/components/elements/Can';
import { MainPageHeader } from '@/components/elements/MainPageHeader';
import { PageListContainer, PageListItem } from '@/components/elements/pages/PageList';
import EditScheduleModal from '@/components/server/schedules/EditScheduleModal';
import ScheduleRow from '@/components/server/schedules/ScheduleRow';

import { httpErrorToHuman } from '@/api/http';
import getServerSchedules from '@/api/server/schedules/getServerSchedules';

import { ServerContext } from '@/state/server';

import useFlash from '@/plugins/useFlash';

function ScheduleContainer() {
    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
    const { clearFlashes, addError } = useFlash();
    const [loading, setLoading] = useState(true);
    const [visible, setVisible] = useState(false);

    const schedules = ServerContext.useStoreState((state) => state.schedules.data);
    const setSchedules = ServerContext.useStoreActions((actions) => actions.schedules.setSchedules);

    useEffect(() => {
        clearFlashes('schedules');

        getServerSchedules(uuid)
            .then((schedules) => setSchedules(schedules))
            .catch((error) => {
                addError({ message: httpErrorToHuman(error), key: 'schedules' });
                console.error(error);
            })
            .then(() => setLoading(false));
    }, []);

    return (
        <div className='mx-auto flex w-full max-w-[120rem] flex-1 flex-col gap-4 px-2 py-2 sm:px-14 sm:py-14'>
            <FlashMessageRender byKey={'schedules'} />
            <MainPageHeader
                direction='column'
                title={'Schedules'}
                titleChildren={
                    <Can action={'schedule.create'}>
                        <Button variant='default' onClick={() => setVisible(true)}>
                            New Schedule
                        </Button>
                    </Can>
                }
            >
                <p className='text-sm leading-relaxed text-muted-foreground'>
                    Automate server tasks with scheduled commands. Create recurring tasks to manage your server, run
                    backups, or execute custom commands.
                </p>
            </MainPageHeader>
            <Can action={'schedule.create'}>
                <EditScheduleModal visible={visible} onModalDismissed={() => setVisible(false)} />
            </Can>
            {!schedules.length && loading ? null : (
                <>
                    {schedules.length === 0 ? (
                        <div className='flex min-h-[60vh] flex-col items-center justify-center px-4 py-12'>
                            <div className='text-center'>
                                <div className='mx-auto mb-4 flex size-16 items-center justify-center rounded-full bg-muted'>
                                    <Clock className='size-8 text-muted-foreground' />
                                </div>
                                <h3 className='mb-2 text-lg font-medium text-foreground'>No schedules found</h3>
                                <p className='max-w-sm text-sm text-muted-foreground'>
                                    Your server does not have any scheduled tasks. Create one to automate server
                                    management.
                                </p>
                            </div>
                        </div>
                    ) : (
                        <PageListContainer data-pyro-schedules>
                            {schedules.map((schedule) => (
                                <NavLink key={schedule.id} to={`${schedule.id}`} end>
                                    <PageListItem>
                                        <ScheduleRow schedule={schedule} />
                                    </PageListItem>
                                </NavLink>
                            ))}
                        </PageListContainer>
                    )}
                </>
            )}
        </div>
    );
}

export default ScheduleContainer;
