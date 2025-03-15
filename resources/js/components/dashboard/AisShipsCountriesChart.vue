<script setup lang="ts">
import { BarChart } from '@/components/ui/chart-bar';
import { onMounted, onUnmounted, ref } from 'vue';

const data = ref([]);
let intervalId: number | null | undefined = null;

async function fetchData() {
    try {
        const response = await fetch('/realtime/ships/top-countries');
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
    <div>
        <BarChart
            class="h-[200px]"
            index="country"
            :data="data"
            :showLegend="false"
            :categories="['count']"
            :y-formatter="(tick: any) => tick"
            :rounded-corners="4"
        />
    </div>
</template>
