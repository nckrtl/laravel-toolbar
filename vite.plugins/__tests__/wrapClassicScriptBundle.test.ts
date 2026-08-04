import { describe, expect, it } from 'vitest';
import {
    CLASSIC_SCRIPT_IIFE_BANNER,
    isWrappedClassicScript,
    unwrapClassicScriptBundle,
    wrapClassicScriptBundle,
} from '../wrapClassicScriptBundle';

const SAMPLE = 'const a=1;function foo(){return a}foo();';

describe('wrapClassicScriptBundle', () => {
    it('wraps arbitrary classic-script body in a private IIFE', () => {
        const out = wrapClassicScriptBundle(SAMPLE);

        expect(isWrappedClassicScript(out)).toBe(true);
        expect(out.startsWith(CLASSIC_SCRIPT_IIFE_BANNER)).toBe(true);
        expect(out).toContain('(function(){');
        expect(out.trimEnd().endsWith('})();')).toBe(true);
        expect(out).toContain(SAMPLE);
    });

    it('is idempotent when already fully wrapped', () => {
        const once = wrapClassicScriptBundle(SAMPLE);
        const twice = wrapClassicScriptBundle(once);

        expect(twice).toBe(once);
    });

    it('round-trips through unwrapClassicScriptBundle', () => {
        const wrapped = wrapClassicScriptBundle(SAMPLE);
        const unwrapped = unwrapClassicScriptBundle(wrapped);

        expect(unwrapped).toBe(SAMPLE + '\n');
        expect(isWrappedClassicScript(unwrapped)).toBe(false);
    });

    it('unwrap leaves already-unwrapped code unchanged', () => {
        expect(unwrapClassicScriptBundle(SAMPLE)).toBe(SAMPLE);
    });

    it('rejects malformed wrappers that only have the banner', () => {
        const bannerOnly = `${CLASSIC_SCRIPT_IIFE_BANNER}const x=1;`;
        expect(isWrappedClassicScript(bannerOnly)).toBe(false);

        // Not treated as done — re-wraps the malformed body.
        const repaired = wrapClassicScriptBundle(bannerOnly);
        expect(isWrappedClassicScript(repaired)).toBe(true);
        expect(repaired).not.toBe(bannerOnly);
    });

    it('rejects banner + open without close', () => {
        const openOnly = `${CLASSIC_SCRIPT_IIFE_BANNER}(function(){const x=1;`;
        expect(isWrappedClassicScript(openOnly)).toBe(false);
    });

    it('rejects banner + open + close with trailing junk', () => {
        const withJunk = `${CLASSIC_SCRIPT_IIFE_BANNER}(function(){const x=1;\n})();alert(1)`;
        expect(isWrappedClassicScript(withJunk)).toBe(false);
    });

    it('accepts complete wrap with trailing whitespace only', () => {
        const ok = `${CLASSIC_SCRIPT_IIFE_BANNER}(function(){const x=1;\n})();\n\n`;
        expect(isWrappedClassicScript(ok)).toBe(true);
    });
});
