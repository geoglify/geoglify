<script setup lang="ts">
import { LineChart } from '@/components/ui/chart-line';
import { onMounted, onUnmounted, ref } from 'vue';

const aisData = ref([]);
let intervalId = null;

async function fetchAISData() {
    try {
        const response = await fetch('/realtime/ships/count', {
            credentials: 'include',
        });
        const data = await response.json();
        aisData.value = data;
    } catch (error) {
        console.error('Erro ao buscar dados da API:', error);
    }
}

onMounted(() => {
    fetchAISData(); // Busca os dados imediatamente ao montar o componente
    intervalId = setInterval(fetchAISData, 5000); // Atualiza a cada 5 segundos
});

onUnmounted(() => {
    if (intervalId) {
        clearInterval(intervalId); // Limpa o intervalo quando o componente é desmontado
    }
});
</script>

<template>
    <LineChart class="h-[200px]" :data="aisData" index="timestamp" :showLegend="false" :categories="['ships']" :y-formatter="(tick) => tick" />
</template>
