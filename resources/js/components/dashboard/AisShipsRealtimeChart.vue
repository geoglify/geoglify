<script setup lang="ts">
import { LineChart } from '@/components/ui/chart-line';
import { onMounted, onUnmounted, ref } from 'vue';

const data = ref([]);
let intervalId: number | null | undefined = null;

async function fetchData() {
    try {
        const response = await fetch('/realtime/ships/count');
        data.value = await response.json();
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
    <LineChart
        class="h-[200px]"
        :data="data"
        index="timestamp"
        :showLegend="false"
        :categories="['ships']"
        :y-formatter="(tick: any) => tick"
    ></LineChart>
</template>
