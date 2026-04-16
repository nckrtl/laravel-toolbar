# Design Rules — Laravel Toolbar

Design guidelines for building and maintaining toolbar tool components.

## Toolbar Layout

The toolbar is divided into three sections: `left`, `center`, and `right`. Each section contains one or more groups rendered as pill-shaped containers.

- **Left / Right sections** are absolutely positioned at their respective edges.
- **Center section** spans the full width with `justify-center`, so items sit in the middle of the bar.

## Panels

Panels are floating popups that appear above the toolbar when a tool item is hovered.

### Alignment

Panel alignment must match the section the tool lives in:

| Section  | `align` prop     |
| -------- | ---------------- |
| `left`   | `left` (default) |
| `center` | `center`         |
| `right`  | `right`          |

Center-aligned panels use `left-1/2 -translate-x-1/2` to anchor to the viewport centre, not to the toolbar item.

### Sizes

| Value  | Use case                                      |
| ------ | --------------------------------------------- |
| `xxs`  | Single-stat panels (PHP version, Vue version) |
| `xs`   | Small info panels                             |
| `sm`   | Default                                       |
| `md`   | Medium data panels (Timings, Memory)          |
| `lg`   | Larger panels                                 |
| `xl`   | Wide two-column panels (Request/Response)     |
| `full` | Full-width overlay panels                     |

## Tool Components

Each tool in `resources/js/tools/` follows this structure:

```vue
<template>
    <div @mouseenter="isOpen = true" @mouseleave="isOpen = false">
        <Panel v-if="isOpen" size="md" align="center">
            <!-- panel content -->
        </Panel>
        <ToolbarItem :isActive="isOpen" :class="itemClasses">
            <!-- toolbar button content -->
        </ToolbarItem>
    </div>
</template>
```

- `isOpen` defaults to `false`; the panel renders on hover.
- `itemClasses` is passed in from `Group` and must be forwarded to `ToolbarItem` for correct border/rounding.

## Sections and Data Lists

Use `Section` to group related rows inside a panel. Use `DataListItem` for key/value rows within a section.

```vue
<Section>
    <div class="px-1 text-white/50 uppercase">Route</div>
    <DataListItem>
        <template #label>Method</template>
        <template #value>GET</template>
    </DataListItem>
</Section>
```

- Section labels use `text-white/50 uppercase` for category headings.
- Values that may overflow use `whitespace-nowrap` with a `:title` for the full string.

## Tabbed Panels

When a panel has multiple tabs (e.g. Request/Response), follow these rules to avoid layout shift:

### Height stability

The **summary tab** is always the first tab shown and its natural content height determines the fixed height for all other tabs. Non-summary tabs are pinned to that height so the panel never resizes when switching tabs.

Implementation in the component:

- Measure `summaryRef.offsetHeight` on mount and whenever data changes.
- Apply `height: measuredHeight` (not `min-height`) to all non-summary tab content wrappers.
- When a panel has two columns (e.g. Request + Response side by side), use `Math.max` of both summary heights so the shorter column also fills the full height.

### Scrollable lists inside tabs

When a tab's content includes a long list (e.g. headers), the list must scroll without resizing the panel or the Section container:

1. The **section label** (e.g. "HEADERS") stays fixed — it is not part of the scrollable area.
2. Only the **data rows** scroll.
3. The scrollable list container uses `overflow-y-auto overflow-hidden` so rows are clipped cleanly at the container boundary, keeping the Section's outer border fully visible.
4. The list's `max-height` is computed dynamically: `contentHeight - ~50px` (Section overhead: `mt-1` + border + `p-2` vertical + label + `gap-2`).
5. The fade effect (`fade-to-bottom`, `fade-to-top`) is attached to this same scrollable container via a Vue `ref`.

```vue
<!-- Tab content wrapper — height pinned to summary height, no overflow -->
<div v-if="activeTab === 'headers'" class="flex flex-col gap-2" :style="{ height: contentHeight }">
    <Section>
        <div class="px-1 text-white/50 uppercase">Headers</div>
        <!-- Scrollable rows — clipped inside the Section border -->
        <div
            ref="headersList"
            class="flex flex-col gap-2 fade-to-bottom overflow-y-auto overflow-hidden"
            :style="{ maxHeight: headerListMaxHeight }"
        >
            <DataListItem v-for="header in headers" :key="header.label">
                <template #label>{{ header.label }}</template>
                <template #value>{{ header.value }}</template>
            </DataListItem>
        </div>
    </Section>
</div>
```

## Active Tab Styling

Active tab buttons use `text-white`. Inactive tabs use `text-white/40 hover:text-white/60`. No border or underline is used for the active state.

```vue
<button
    :class="activeTab === tab ? 'text-white' : 'text-white/40 hover:text-white/60'"
    class="text-xxs uppercase tracking-wide pb-1 transition-colors"
>{{ tab }}</button>
```

## Shadow DOM and CSS Limitations

The toolbar renders inside a Shadow Root for CSS isolation. This affects how certain Tailwind utilities work.

### Tailwind gradient utilities do not work inside Shadow DOM

Tailwind v4 implements gradient utilities (`bg-linear-to-tr`, `from-*`, `via-*`, `to-*`) through a chain of CSS custom properties. These variables are registered with `@property { inherits: false }` in the stylesheet. **`@property` declarations inside a Shadow Root do not reliably apply their `initial-value` to elements within that root in Chrome**, so the variable chain never resolves and `background-image` computes to `none`.

**Do not use Tailwind gradient utility classes for backgrounds inside the toolbar.** Use inline `:style` bindings instead:

```vue
<!-- ✗ Does not work in Shadow DOM -->
:class="{ 'bg-linear-to-tr from-white/25 via-white/15 to-white/25': isActive }"

<!-- ✓ Works correctly -->
:style="isActive ? { backgroundImage: 'linear-gradient(to top right, rgba(255,255,255,0.25),
rgba(255,255,255,0.15), rgba(255,255,255,0.25))' } : {}"
```

This limitation applies to any Tailwind utility that relies on `@property`-registered CSS custom properties (gradients, shadows with CSS variable stops, etc.). Standard utilities that set concrete CSS values directly (e.g. `bg-white`, `text-red-500`) are unaffected.

## Colour Conventions

| Semantic           | Class                                 |
| ------------------ | ------------------------------------- |
| Success / GET      | `text-emerald-300` or `text-lime-400` |
| Warning / redirect | `text-yellow-500`                     |
| Danger / error     | `text-danger`                         |
| POST               | `text-blue-400`                       |
| PUT                | `text-yellow-300`                     |
| DELETE             | `text-danger`                         |
| PATCH              | `text-indigo-400`                     |
| Muted labels       | `text-white/50`                       |
| Muted values       | `text-white/70`                       |
