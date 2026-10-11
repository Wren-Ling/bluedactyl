import { memo } from 'react';
import Markdown, { type Components } from 'react-markdown';
import remarkGfm from 'remark-gfm';

import { cn } from '@/lib/utils';

/**
 * Renders an assistant reply as Markdown.
 *
 * The model is asked to answer in Markdown, so its output is rendered rather
 * than dumped into a <pre>. react-markdown builds a React tree instead of using
 * dangerouslySetInnerHTML and ignores raw HTML by default, so a reply cannot
 * inject markup into the panel even if the model is talked into emitting a
 * <script> or an event-handler attribute. react-markdown also rewrites link and
 * image URLs, dropping anything with a protocol outside http, https, mailto and
 * tel, so a `javascript:` link in a reply is neutralised.
 *
 * Styling is applied through child selectors rather than the typography plugin
 * so it inherits the panel theme tokens and reads the same in the side panel
 * and in the crash-analysis dialog.
 */
interface AiMarkdownProps {
    content: string;
    className?: string;
}

/**
 * Links inside the assistant open in a new tab: the panel is a single-page app,
 * and a plain link would navigate the whole console away.
 */
const components: Components = {
    a: ({ node: _node, ...props }) => <a {...props} target='_blank' rel='noopener noreferrer' />,
};

const AiMarkdown = memo(({ content, className }: AiMarkdownProps) => (
    <div
        className={cn(
            'text-sm leading-relaxed break-words',
            // Paragraphs and inline text
            '[&_p]:my-2 [&_p]:first:mt-0 [&_p]:last:mb-0',
            '[&_strong]:font-semibold',
            '[&_em]:italic',
            '[&_a]:text-primary [&_a]:underline [&_a]:underline-offset-2 [&_a:hover]:text-primary/80',
            // Headings
            '[&_h1]:mt-4 [&_h1]:mb-2 [&_h1]:text-base [&_h1]:font-semibold',
            '[&_h2]:mt-4 [&_h2]:mb-2 [&_h2]:text-sm [&_h2]:font-semibold',
            '[&_h3]:mt-3 [&_h3]:mb-1.5 [&_h3]:text-sm [&_h3]:font-semibold',
            '[&_h4]:mt-3 [&_h4]:mb-1.5 [&_h4]:text-sm [&_h4]:font-medium',
            // Lists
            '[&_ul]:my-2 [&_ul]:list-disc [&_ul]:space-y-1 [&_ul]:pl-5',
            '[&_ol]:my-2 [&_ol]:list-decimal [&_ol]:space-y-1 [&_ol]:pl-5',
            '[&_li]:marker:text-muted-foreground',
            '[&_li_ul]:my-1 [&_li_ol]:my-1',
            // Inline code
            '[&_code]:rounded [&_code]:bg-muted [&_code]:px-1 [&_code]:py-0.5 [&_code]:font-mono [&_code]:text-[0.85em]',
            // Fenced code blocks. A block's <code> also matches the inline rule,
            // so it is reset here back to plain monospace text.
            '[&_pre]:my-2 [&_pre]:overflow-x-auto [&_pre]:rounded-md [&_pre]:border [&_pre]:border-border [&_pre]:bg-muted/50 [&_pre]:p-2.5',
            '[&_pre_code]:block [&_pre_code]:bg-transparent [&_pre_code]:p-0 [&_pre_code]:text-xs [&_pre_code]:leading-relaxed',
            // Block quotes
            '[&_blockquote]:my-2 [&_blockquote]:border-l-2 [&_blockquote]:border-border [&_blockquote]:pl-3 [&_blockquote]:text-muted-foreground',
            // Tables (GitHub-flavoured Markdown)
            '[&_table]:my-2 [&_table]:w-full [&_table]:border-collapse [&_table]:text-xs',
            '[&_th]:border [&_th]:border-border [&_th]:bg-muted/50 [&_th]:px-2 [&_th]:py-1 [&_th]:text-left [&_th]:font-medium',
            '[&_td]:border [&_td]:border-border [&_td]:px-2 [&_td]:py-1',
            // Horizontal rules
            '[&_hr]:my-3 [&_hr]:border-border',
            // Keep the very first block flush with the top of the bubble
            '[&>*:first-child]:mt-0',
            className,
        )}
    >
        <Markdown remarkPlugins={[remarkGfm]} components={components}>
            {content}
        </Markdown>
    </div>
));

AiMarkdown.displayName = 'AiMarkdown';

export default AiMarkdown;
