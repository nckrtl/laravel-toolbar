\
<script setup>
import { ref } from 'vue';
import ToolbarItem from '@/components/ToolbarItem.vue';

const isActive = ref(false);
const isOn = ref(false);
</script>

<template>
    <ToolbarItem
        @mouseenter="isActive = true"
        @mouseleave="isActive = false"
        :isActive="isActive"
        @click="isOn = !isOn"
        class="rounded-full px-0.5"
        innerPadding="px-0.5"
    >
        <div class="flex cursor-pointer items-center justify-center rounded-full text-white">
            <div v-if="!isOn" class="p-1">
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 16 16"
                    fill="currentColor"
                    class="size-3.5"
                >
                    <path
                        d="M5.28 4.22a.75.75 0 0 0-1.06 1.06L6.94 8l-2.72 2.72a.75.75 0 1 0 1.06 1.06L8 9.06l2.72 2.72a.75.75 0 1 0 1.06-1.06L9.06 8l2.72-2.72a.75.75 0 0 0-1.06-1.06L8 6.94 5.28 4.22Z"
                    />
                </svg>
            </div>
            <div v-else class="relative flex items-center justify-center">
                <div
                    class="absolute h-[calc(100%-2px)] w-[calc(100%-2px)] rounded-full bg-white"
                ></div>
                <svg
                    class="relative size-5.5 text-[#3C43C2]"
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 80 80"
                >
                    <path
                        d="M0 40a39.87 39.87 0 0 1 11.72-28.28A40 40 0 1 1 0 40zm34 10a4 4 0 0 1-4-4v-2a2 2 0 1 0-4 0v2a4 4 0 0 1-4 4h-2a2 2 0 1 0 0 4h2a4 4 0 0 1 4 4v2a2 2 0 1 0 4 0v-2a4 4 0 0 1 4-4h2a2 2 0 1 0 0-4h-2zm24-24a6 6 0 0 1-6-6v-3a3 3 0 0 0-6 0v3a6 6 0 0 1-6 6h-3a3 3 0 0 0 0 6h3a6 6 0 0 1 6 6v3a3 3 0 0 0 6 0v-3a6 6 0 0 1 6-6h3a3 3 0 0 0 0-6h-3zm-4 36a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM21 28a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"
                        fill="currentColor"
                    ></path>
                </svg>
            </div>
        </div>
    </ToolbarItem>
</template>
