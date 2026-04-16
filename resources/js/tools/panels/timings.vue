<script setup>
import { ref, computed } from "vue";
import { useToolbar } from "@/composables/useToolbar";
import SectionHeader from "@/components/SectionHeader.vue";
import Section from "@/components/Section.vue";
import DataList from "@/components/DataList.vue";
import DataListItem from "@/components/DataListItem.vue";

const { data } = useToolbar();
const hoverIndex = ref(null);

const computedStages = computed(() => {
    return data.value.profiler?.stages.filter((stage) => {
        return stage.wall_time.measurement.value !== 0;
    });
});
</script>

<template>
    <div class="pb-2">
        <SectionHeader>
            <template #icon>
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 16 16"
                    fill="currentColor"
                    class="size-3.5"
                >
                    <path
                        fill-rule="evenodd"
                        d="M1 8a7 7 0 1 1 14 0A7 7 0 0 1 1 8Zm7.75-4.25a.75.75 0 0 0-1.5 0V8c0 .414.336.75.75.75h3.25a.75.75 0 0 0 0-1.5h-2.5v-3.5Z"
                        clip-rule="evenodd"
                    />
                </svg>
            </template>
            <template #label> Timings </template>
            <template #secondaryLabel>
                {{ data.profiler?.total_wall_time?.formattedValue }}
            </template>
        </SectionHeader>

        <Section>
            <div class="flex w-full px-0.5">
                <template v-for="(requestStage, index) in computedStages" :key="index">
                    <div
                        @mouseenter="hoverIndex = index"
                        @mouseleave="hoverIndex = null"
                        class="flex min-w-2 py-1.5"
                        :style="{ width: requestStage.wall_time.percentage + '%' }"
                    >
                        <div
                            class="mx-auto h-1 rounded-full"
                            :style="{
                                backgroundColor: requestStage.color,
                                width: 'calc(100% - 4px)',
                            }"
                        ></div>
                    </div>
                </template>
            </div>

            <DataList>
                <DataListItem
                    v-for="(requestStage, index) in data.profiler?.stages"
                    :key="index"
                    :class="{ 'opacity-35': hoverIndex !== index && hoverIndex !== null }"
                >
                    <template #label>
                        <div class="flex items-center gap-1.5">
                            <div
                                class="h-1.5 w-1.5 rounded-full"
                                :style="{ backgroundColor: requestStage.color }"
                            ></div>
                            <span>{{ requestStage.label }}</span>
                        </div>
                    </template>
                    <template #value>
                        <span v-if="requestStage.wall_time.measurement.value === 0">N/A</span>
                        <span v-else-if="hoverIndex !== index">{{
                            requestStage.wall_time.measurement.formattedValue
                        }}</span>
                        <span v-else>{{ requestStage.wall_time.percentage }}%</span>
                    </template>
                </DataListItem>
            </DataList>
        </Section>
    </div>
</template>
