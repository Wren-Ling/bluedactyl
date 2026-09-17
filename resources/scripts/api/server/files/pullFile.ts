import http from '@/api/http';
import { getGlobalDaemonType } from '@/api/server/getServer';

export default async (uuid: string, directory: string, url: string): Promise<void> => {
    await http.post(`/api/client/servers/${getGlobalDaemonType()}/${uuid}/files/pull`, {
        directory,
        url,
        foreground: false,
    });
};
