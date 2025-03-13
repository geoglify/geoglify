<script setup lang="ts">
import { BarChart } from '@/components/ui/chart-bar';

// Principais categorias de tipos de navios AIS
const shipCategories = ['Cargo', 'Tanker', 'Passenger', 'Fishing', 'Other'];

// Função para gerar dados fictícios para as categorias de navios
const generateFakeShipData = () => {
    return shipCategories.map((category) => ({
        category,
        count: Math.floor(Math.random() * 1000) + 200, // Quantidade aleatória de navios
    }));
};

const shipData = generateFakeShipData();
</script>

<template>
    <BarChart
        class="h-[200px]"
        index="category"
        :data="shipData"
        :showLegend="false"
        :showXAxis="true"
        :categories="['count']"
        :y-formatter="
            (tick, i) => {
                return typeof tick === 'number' ? `${new Intl.NumberFormat('us').format(tick).toString()}` : '';
            }
        "
        :rounded-corners="4"
    />
</template>
