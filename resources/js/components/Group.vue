<script setup>
const props = defineProps({
    config: {
        type: Object,
        required: true,
    },
});

const modules = import.meta.glob('../tools/*.vue', { eager: true });

const getComponent = (component) => {
    return modules[`../tools/${component}.vue`].default;
};
</script>

<template>
    <div class="relative flex overflow-hidden rounded-full">
        <div
            class="absolute h-full w-full rounded-full bg-[#101010]/95 backdrop-blur-sm dark:border dark:border-white/8"
        ></div>

        <template v-for="(toolConfig, component, index) in config.tools" :key="component">
            <component
                :is="getComponent(component)"
                :config="toolConfig"
                :itemClasses="{
                    'pl-[3px] rounded-l-full border-l': index === 0,
                    'pr-[3px] rounded-r-full border-r':
                        index === Object.keys(config.tools).length - 1,
                    'border-x-0': index > 0 && index < Object.keys(config.tools).length - 1,
                }"
            />
        </template>
    </div>
</template>
