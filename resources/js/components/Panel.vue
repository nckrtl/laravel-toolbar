<script setup>
const props = defineProps({
    size: {
        type: String,
        default: 'sm',
    },
    minHeight: {
        type: String,
        default: 'auto',
    },
    align: {
        type: String,
        default: 'left',
    },
});
</script>

<template>
    <div
        class="fixed p-2"
        :class="[
            {
                'min-w-xxs max-w-xxs bottom-9 w-full': size === 'xxs',
                'bottom-9 w-full max-w-xs min-w-xs': size === 'xs',
                'bottom-9 w-full max-w-sm min-w-sm': size === 'sm',
                'bottom-9 w-full max-w-md min-w-md': size === 'md',
                'bottom-9 left-[5px] w-[calc(100%+5px)]': size === 'full',
                'translate-x-2': align === 'right',
                '-translate-x-2': align === 'left',
            },
            $attrs.class,
        ]"
    >

        <div
            class="text-xxs rounded-2xl border border-white/10 bg-[#111111]/95 p-2 text-white backdrop-blur-xl"
            :class="[minHeight]"
        >
            <slot />
        </div>
    </div>
</template>
