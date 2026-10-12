import http from '@/api/http';

export interface AiMessage {
    role: 'user' | 'assistant';
    content: string;
}

export interface AiConfig {
    enabled: boolean;
    configured: boolean;
    provider: string | null;
    provider_label: string | null;
    allow_console: boolean;
    allow_crash_analysis: boolean;
    console_buffer_lines: number;
    file_listing_limit: number;
}

export interface AiDirectoryEntry {
    name: string;
    is_file: boolean;
    size: number;
    modified_at: string | null;
}

export interface AiDirectoryListing {
    path: string;
    entries: AiDirectoryEntry[];
}

// The assistant is served from the daemon-agnostic server route, so the UUID
// alone identifies the target and no daemon type is needed.
const base = (uuid: string) => `/api/client/servers/${uuid}/ai`;

export const getAiConfig = async (uuid: string): Promise<AiConfig> => {
    const { data } = await http.get(`${base(uuid)}/config`);

    return data as AiConfig;
};

export const getAiDirectory = async (uuid: string, directory = '/'): Promise<AiDirectoryListing> => {
    const { data } = await http.get(`${base(uuid)}/files`, { params: { directory } });

    return data as AiDirectoryListing;
};

export const readAiFile = async (uuid: string, file: string): Promise<string> => {
    const { data } = await http.get(`${base(uuid)}/file`, { params: { file } });

    return (data?.contents as string) ?? '';
};

export const analyzeCrash = async (uuid: string, console: string[]): Promise<string> => {
    const { data } = await http.post(`${base(uuid)}/crash-analysis`, { console });

    return (data?.reply as string) ?? '';
};

/**
 * Stream a conversation turn.
 *
 * Uses fetch rather than axios because axios buffers the whole body, which
 * defeats the point of streaming. The server emits one JSON object per SSE
 * frame, so each event is handled explicitly instead of being wedged into the
 * OpenAI delta shape.
 */
export const streamChat = async (
    uuid: string,
    messages: AiMessage[],
    console: string[],
    onDelta: (text: string) => void,
    signal?: AbortSignal,
): Promise<string> => {
    const response = await fetch(`${base(uuid)}/stream`, {
        method: 'POST',
        credentials: 'include',
        headers: {
            'Content-Type': 'application/json',
            Accept: 'text/event-stream',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? '',
        },
        body: JSON.stringify({ messages, console }),
        signal,
    });

    if (!response.ok) {
        // Errors before the stream opens (rate limit, permission) come back as JSON.
        const text = await response.text();
        let message = `The assistant request failed (HTTP ${response.status}).`;

        try {
            const parsed = JSON.parse(text);
            message = parsed.errors?.[0]?.detail || parsed.message || message;
        } catch {
            // Not JSON, keep the generic message.
        }

        throw new Error(message);
    }

    const reader = response.body?.getReader();
    if (!reader) {
        throw new Error('The browser did not provide a readable stream.');
    }

    const decoder = new TextDecoder();
    let buffer = '';
    let accumulated = '';

    for (;;) {
        const { done, value } = await reader.read();
        if (done) break;

        buffer += decoder.decode(value, { stream: true });

        // SSE frames are separated by a blank line.
        const frames = buffer.split('\n\n');
        buffer = frames.pop() ?? '';

        for (const frame of frames) {
            const line = frame.split('\n').find((l) => l.startsWith('data:'));
            if (!line) continue;

            try {
                const payload = JSON.parse(line.slice(5).trim());

                if (payload.type === 'delta' && typeof payload.text === 'string') {
                    accumulated += payload.text;
                    onDelta(payload.text);
                } else if (payload.type === 'error') {
                    throw new Error(payload.message || 'The assistant request failed.');
                }
            } catch (error) {
                if (error instanceof Error && error.message !== 'Unexpected end of JSON input') {
                    throw error;
                }
            }
        }
    }

    return accumulated;
};
