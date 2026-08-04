import type { Plugin } from 'vite';

/**
 * Rewrite Vue esm-bundler shared registry keys inside the production toolbar
 * chunk so dual Vite/Inertia host apps cannot share __VUE_INSTANCE_SETTERS__.
 *
 * Must run in renderChunk so the transformed source participates in Vite's
 * content hash / manifest entry (post-build mutation keeps the old filename).
 *
 * Fails the production build hard if either expected key is missing, not
 * rewritten, or still present after rewrite — never warn/fail-open.
 */
export const VUE_SETTER_KEY_REPLACEMENTS: ReadonlyArray<readonly [string, string]> = [
    ['__VUE_INSTANCE_SETTERS__', '__LARAVEL_TOOLBAR_VUE_INSTANCE_SETTERS__'],
    ['__VUE_SSR_SETTERS__', '__LARAVEL_TOOLBAR_VUE_SSR_SETTERS__'],
];

/**
 * Pure rewrite used by the Vite plugin and unit tests.
 * Throws unless both original keys are present, both targets land, and no originals remain.
 */
export function rewriteVueSetterKeys(code: string, fileName = 'chunk'): string {
    for (const [from] of VUE_SETTER_KEY_REPLACEMENTS) {
        if (!code.includes(from)) {
            throw new Error(
                `isolateVueRuntime: expected original key ${from} in ${fileName} before rewrite`,
            );
        }
    }

    let next = code;
    for (const [from, to] of VUE_SETTER_KEY_REPLACEMENTS) {
        next = next.split(from).join(to);
    }

    for (const [from, to] of VUE_SETTER_KEY_REPLACEMENTS) {
        if (next.includes(from)) {
            throw new Error(
                `isolateVueRuntime: original key ${from} still present after rewrite in ${fileName}`,
            );
        }
        if (!next.includes(to)) {
            throw new Error(
                `isolateVueRuntime: expected rewritten key ${to} in ${fileName} after rewrite`,
            );
        }
    }

    return next;
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

export function isolateVueRuntime(): Plugin {
    let isolatedToolbarChunk = false;

    return {
        name: 'laravel-toolbar-isolate-vue-runtime',
        apply: 'build',
        enforce: 'post',
        buildStart() {
            isolatedToolbarChunk = false;
        },
        renderChunk(code, chunk) {
            if (!isToolbarProdJsChunk(chunk)) {
                return null;
            }

            const next = rewriteVueSetterKeys(code, chunk.fileName);
            isolatedToolbarChunk = true;
            return { code: next, map: null };
        },
        generateBundle() {
            if (!isolatedToolbarChunk) {
                throw new Error(
                    'isolateVueRuntime: production build finished without rewriting Vue setter keys in a toolbar.prod JS chunk',
                );
            }
        },
    };
}
