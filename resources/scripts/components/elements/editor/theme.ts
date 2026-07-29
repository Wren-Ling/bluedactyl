import { HighlightStyle } from '@codemirror/language';
import type { Extension } from '@codemirror/state';
import { EditorView } from '@codemirror/view';
import { tags as t } from '@lezer/highlight';

const highlightBackground = 'transparent';
const background = 'transparent';
const selection = '#34455A';
const cursor = '#ffffff';

export const darkTheme: Extension = EditorView.theme(
    {
        '&': {
            color: '#CBCCC6',
            backgroundColor: background,
        },
        '.cm-content': {
            caretColor: cursor,
            fontSize: '13px',
        },
        '&.cm-focused .cm-cursor': { borderLeftColor: cursor },
        '&.cm-focused .cm-selectionBackground, .cm-selectionBackground, ::selection': {
            backgroundColor: selection,
        },
        '.cm-button': { background: '#222222' },
        '.cm-panels': { backgroundColor: '#111111', color: '#CBCCC6' },
        '.cm-searchMatch': {
            backgroundColor: '#72a1ff59',
            outline: '1px solid #457dff',
        },
        '.cm-searchMatch.cm-searchMatch-selected': {
            backgroundColor: '#6199ff2f',
        },
        '.cm-activeLine': { backgroundColor: highlightBackground },
        '.cm-selectionMatch': { backgroundColor: '#aafe661a' },
        '.cm-matchingBracket, .cm-nonmatchingBracket': {
            backgroundColor: '#bad0f847',
            outline: '1px solid #515a6b',
        },
        '.cm-gutters': {
            backgroundColor: 'transparent',
            color: '#FF3333',
            border: 'none',
        },
        '.cm-gutterElement': {
            color: 'rgba(255, 255, 255, 0.21)',
        },
        '.cm-activeLineGutter': {
            backgroundColor: highlightBackground,
        },
        '.cm-foldPlaceholder': {
            backgroundColor: 'transparent',
            border: 'none',
            color: '#ddd',
        },
        '.cm-tooltip': {
            border: '1px solid #181a1f',
            backgroundColor: '#232834',
        },
        '.cm-tooltip-autocomplete': {
            '& > ul > li[aria-selected]': {
                backgroundColor: highlightBackground,
                color: '#CBCCC6',
            },
        },
    },
    { dark: true },
);

export const lightTheme: Extension = EditorView.theme(
    {
        '&': {
            color: '#1a1a2e',
            backgroundColor: background,
        },
        '.cm-content': {
            caretColor: '#1a1a2e',
            fontSize: '13px',
        },
        '&.cm-focused .cm-cursor': { borderLeftColor: '#1a1a2e' },
        '&.cm-focused .cm-selectionBackground, .cm-selectionBackground, ::selection': {
            backgroundColor: '#d4d4f0',
        },
        '.cm-button': { background: '#f0f0f0' },
        '.cm-panels': { backgroundColor: '#f5f5f5', color: '#1a1a2e' },
        '.cm-searchMatch': {
            backgroundColor: '#72a1ff33',
            outline: '1px solid #457dff',
        },
        '.cm-searchMatch.cm-searchMatch-selected': {
            backgroundColor: '#6199ff2f',
        },
        '.cm-activeLine': { backgroundColor: '#f0f0ff' },
        '.cm-selectionMatch': { backgroundColor: '#aafe661a' },
        '.cm-matchingBracket, .cm-nonmatchingBracket': {
            backgroundColor: '#bad0f847',
            outline: '1px solid #aaa',
        },
        '.cm-gutters': {
            backgroundColor: 'transparent',
            color: '#999',
            border: 'none',
        },
        '.cm-gutterElement': {
            color: '#bbb',
        },
        '.cm-activeLineGutter': {
            backgroundColor: '#f0f0ff',
        },
        '.cm-foldPlaceholder': {
            backgroundColor: 'transparent',
            border: 'none',
            color: '#999',
        },
        '.cm-tooltip': {
            border: '1px solid #ddd',
            backgroundColor: '#fff',
        },
        '.cm-tooltip-autocomplete': {
            '& > ul > li[aria-selected]': {
                backgroundColor: '#f0f0ff',
                color: '#1a1a2e',
            },
        },
    },
    { dark: false },
);

export const ayuMirageHighlightStyle = HighlightStyle.define([
    {
        tag: t.keyword,
        color: '#FFA759',
    },
    {
        tag: [t.name, t.deleted, t.character, t.propertyName, t.macroName],
        color: '#5CCFE6',
    },
    {
        tag: [t.function(t.variableName), t.labelName],
        color: '#CBCCC6',
    },
    {
        tag: [t.color, t.constant(t.name), t.standard(t.name)],
        color: '#F29E74',
    },
    {
        tag: [t.definition(t.name), t.separator],
        color: '#CBCCC6B3',
    },
    {
        tag: [t.typeName, t.className, t.number, t.changed, t.annotation, t.modifier, t.self, t.namespace],
        color: '#FFCC66',
    },
    {
        tag: [t.operator, t.operatorKeyword, t.url, t.escape, t.regexp, t.link, t.special(t.string)],
        color: '#5CCFE6',
    },
    {
        tag: [t.meta, t.comment],
        color: '#5C6773',
    },
    {
        tag: t.strong,
        fontWeight: 'bold',
    },
    {
        tag: t.emphasis,
        fontStyle: 'italic',
    },
    {
        tag: t.strikethrough,
        textDecoration: 'line-through',
    },
    {
        tag: t.link,
        color: '#FF3333',
        textDecoration: 'underline',
    },
    {
        tag: t.heading,
        fontWeight: 'bold',
        color: '#BAE67E',
    },
    {
        tag: [t.atom, t.bool, t.special(t.variableName)],
        color: '#5CCFE6',
    },
    {
        tag: [t.processingInstruction, t.string, t.inserted],
        color: '#BAE67E',
    },
    {
        tag: t.invalid,
        color: '#FF3333',
    },
]);
