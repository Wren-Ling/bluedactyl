import { useSyncExternalStore } from 'react';

/**
 * A single shared ring buffer of console output.
 *
 * The console, the crash detector and the AI panel all need the same recent
 * output. Keeping one buffer at module scope means the assistant sees exactly
 * what the user sees, and it survives the console component remounting on
 * navigation.
 */
const MAX_LINES = 500;

// ANSI escape sequences are stripped so the model never sees colour codes.
// The escape character is built at runtime to keep the source free of a raw
// control character.
const ANSI_PATTERN = new RegExp(`${String.fromCharCode(27)}\\[[0-9;]*m`, 'g');

let lines: string[] = [];
const listeners = new Set<() => void>();

const emit = () => listeners.forEach((listener) => listener());

export const pushConsoleChunk = (chunk: string) => {
    const incoming = String(chunk)
        .split(/\r?\n/)
        .map((line) => line.replace(ANSI_PATTERN, '').trimEnd())
        .filter((line) => line !== '');

    if (incoming.length === 0) return;

    lines = [...lines, ...incoming].slice(-MAX_LINES);
    emit();
};

export const clearConsoleBuffer = () => {
    lines = [];
    emit();
};

export const getConsoleLines = (): string[] => lines;

const subscribe = (listener: () => void) => {
    listeners.add(listener);

    return () => {
        listeners.delete(listener);
    };
};

/**
 * Subscribe a component to the console buffer.
 *
 * `limit` trims the snapshot to the most recent N lines; the panel sends the
 * server-side configured amount, which is usually far smaller than the buffer.
 */
export const useConsoleBuffer = (limit?: number): string[] => {
    const snapshot = useSyncExternalStore(subscribe, getConsoleLines, getConsoleLines);

    if (!limit || limit >= snapshot.length) return snapshot;

    return snapshot.slice(-limit);
};

/**
 * One-shot read for event handlers, which should not subscribe.
 */
export const takeConsoleLines = (limit?: number): string[] => {
    const current = getConsoleLines();

    return limit && limit < current.length ? current.slice(-limit) : current;
};
