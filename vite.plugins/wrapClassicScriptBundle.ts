import type { Plugin } from 'vite';

/**
 * Wrap production classic-script chunks in a private IIFE before content hash.
 *
 * Why: Vite may emit an unwrapped classic bundle. Minified top-level bindings
 * (e.g. Vue withCtx as `function _`) become `window` properties. Host apps that
 * assign Lodash to `window._` then clobber those bindings — later slot creation
 * returns Lodash wrappers and renderSlot throws `r is not a function`.
 *
 * Contract: every matching toolbar.prod JS chunk is wrapped before hashing.
 * Do not depend on specific minifier symbol names.
 */

export const CLASSIC_SCRIPT_IIFE_BANNER = '/*! laravel-toolbar classic-iife */';

const IIFE_OPEN = '(function(){';
const IIFE_CLOSE = '})();';

/**
 * True when code already carries a complete production classic-script wrap:
 * banner + IIFE open + IIFE close. Banner alone is not enough (malformed).
 */
export function isWrappedClassicScript(code: string): boolean {
    const trimmed = code.trimStart();
    if (!trimmed.startsWith(CLASSIC_SCRIPT_IIFE_BANNER)) {
        return false;
    }

    const rest = trimmed.slice(CLASSIC_SCRIPT_IIFE_BANNER.length);
    if (!rest.startsWith(IIFE_OPEN)) {
        return false;
    }

    // Closing delimiter must appear after the open (end-anchored after trim).
    const afterOpen = rest.slice(IIFE_OPEN.length);
    const closeAt = afterOpen.lastIndexOf(IIFE_CLOSE);
    if (closeAt === -1) {
        return false;
    }

    // Trailing whitespace/newlines after close are fine; junk text is not.
    const afterClose = afterOpen.slice(closeAt + IIFE_CLOSE.length).trim();
    return afterClose === '';
}

/**
 * Wrap classic-script body in a private IIFE (idempotent for a complete wrap).
 * Malformed banner-prefixed code is re-wrapped rather than treated as done.
 */
export function wrapClassicScriptBundle(code: string): string {
    if (isWrappedClassicScript(code)) {
        return code;
    }

    // If a partial banner/open is present, wrap the whole string (including junk)
    // so the output is always a complete, valid classic IIFE.
    return `${CLASSIC_SCRIPT_IIFE_BANNER}${IIFE_OPEN}${code}\n${IIFE_CLOSE}\n`;
}

/**
 * Strip our production IIFE wrap (test-only / diagnostics).
 * Leaves unknown shapes unchanged.
 */
export function unwrapClassicScriptBundle(code: string): string {
    const trimmed = code.trimStart();
    if (!trimmed.startsWith(CLASSIC_SCRIPT_IIFE_BANNER)) {
        return code;
    }

    let rest = trimmed.slice(CLASSIC_SCRIPT_IIFE_BANNER.length);
    if (!rest.startsWith(IIFE_OPEN)) {
        throw new Error(
            'unwrapClassicScriptBundle: banner present but IIFE open missing',
        );
    }

    rest = rest.slice(IIFE_OPEN.length);
    const closeAt = rest.lastIndexOf(IIFE_CLOSE);
    if (closeAt === -1) {
        throw new Error(
            'unwrapClassicScriptBundle: banner present but IIFE close missing',
        );
    }

    return rest.slice(0, closeAt);
}

function isToolbarProdJsChunk(chunk: {
    fileName: string;
    name?: string;
    modules: Record<string, unknown>;
}): boolean {
    if (!chunk.fileName.endsWith('.js')) {
        return false;
    }

    return (
        chunk.fileName.includes('toolbar.prod') ||
        chunk.name === 'toolbar.prod' ||
        Object.keys(chunk.modules).some((id) => id.includes('toolbar.prod'))
    );
}

/**
 * Vite plugin: IIFE-wrap toolbar.prod JS in renderChunk (hash participates).
 * Fail-closed if no toolbar.prod chunk is wrapped.
 */
export function wrapToolbarBundle(): Plugin {
    let wrappedToolbarChunk = false;

    return {
        name: 'laravel-toolbar-wrap-classic-script',
        apply: 'build',
        buildStart() {
            wrappedToolbarChunk = false;
        },
        renderChunk: {
            order: 'post',
            handler(code, chunk) {
                if (!isToolbarProdJsChunk(chunk)) {
                    return null;
                }

                const next = wrapClassicScriptBundle(code);

                if (!isWrappedClassicScript(next)) {
                    throw new Error(
                        `wrapToolbarBundle: wrap failed for ${chunk.fileName} (missing IIFE banner)`,
                    );
                }

                wrappedToolbarChunk = true;
                return { code: next, map: null };
            },
        },
        generateBundle() {
            if (!wrappedToolbarChunk) {
                throw new Error(
                    'wrapToolbarBundle: production build finished without IIFE-wrapping a toolbar.prod JS chunk',
                );
            }
        },
    };
}
