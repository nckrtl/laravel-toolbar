import { afterEach, beforeEach, describe, expect, it, vi } from "vitest";
import { setupCacheSaving } from "@/core/utils/cache";

describe("toolbar shell cache", () => {
    beforeEach(() => {
        vi.useFakeTimers();
        sessionStorage.clear();
    });

    afterEach(() => {
        vi.useRealTimers();
        sessionStorage.clear();
    });

    it("stores the asset build version with the cached shell", () => {
        window.__LARAVEL_TOOLBAR_ASSET_VERSION__ = "build-2026";

        const shadowRoot = {
            adoptedStyleSheets: [],
            getElementById: () => ({ innerHTML: "<div>Toolbar</div>" }),
        } as unknown as ShadowRoot;

        setupCacheSaving(shadowRoot);
        vi.advanceTimersByTime(2000);

        expect(sessionStorage.getItem("laravel-toolbar-asset-version")).toBe("build-2026");
    });
});
