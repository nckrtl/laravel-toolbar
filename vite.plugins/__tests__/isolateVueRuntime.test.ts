import { describe, expect, it } from 'vitest';
import { rewriteVueSetterKeys } from '../isolateVueRuntime';

const SAMPLE =
    'register("__VUE_INSTANCE_SETTERS__", s); register("__VUE_SSR_SETTERS__", t);';

describe('rewriteVueSetterKeys', () => {
    it('rewrites both keys and fails closed when either is missing', () => {
        const out = rewriteVueSetterKeys(SAMPLE);

        expect(out).toContain('__LARAVEL_TOOLBAR_VUE_INSTANCE_SETTERS__');
        expect(out).toContain('__LARAVEL_TOOLBAR_VUE_SSR_SETTERS__');
        expect(out).not.toContain('__VUE_INSTANCE_SETTERS__');
        expect(out).not.toContain('__VUE_SSR_SETTERS__');

        expect(() => rewriteVueSetterKeys('const x = 1')).toThrow();
        expect(() => rewriteVueSetterKeys('only __VUE_INSTANCE_SETTERS__')).toThrow();
    });
});
