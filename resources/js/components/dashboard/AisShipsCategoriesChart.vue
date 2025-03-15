<script setup lang="ts">
import { BarChart } from '@/components/ui/chart-bar';
import { onMounted, onUnmounted, ref } from 'vue';

const data = ref([]);
const colors = ref<Record<string, string>>({}); // Stores colors per category
let intervalId: number | null | undefined = null;

async function fetchData() {
    try {
        const response = await fetch('/realtime/ships/top-categories');
        const result = await response.json();

        // Update data and ensure each item has its color
        data.value = result.map((item: any) => ({
            category: item.category,
            count: item.count
        }));

        // Update the colors map dynamically
        colors.value = result.map((item: any) => {
            return item.color;
        }, {});

    } catch (error) {
        console.error('Error fetching data from API:', error);
    }
}

onMounted(() => {
    fetchData();
    intervalId = setInterval(fetchData, 5000);
});

onUnmounted(() => {
    if (intervalId) {
        clearInterval(intervalId);
    }
});
</script>

<template>
    <BarChart
        class="h-[200px]"
        index="category"
        :data="data"
        :showLegend="false"
        :showXAxis="true"
        :categories="['count']"
        :y-formatter="(tick: any) => tick"
        :rounded-corners="4"
        :colors="colors"
    />
</template>
