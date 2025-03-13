<script setup lang="ts">
import { BarChart } from '@/components/ui/chart-bar';

// Lista de países fictícios
const countries = ['USA', 'China', 'Japan', 'Germany', 'UK'];

// Função para gerar dados fictícios para a contagem de navios por país
const generateFakeShipDataByCountry = () => {
    return countries.map((country) => ({
        country,
        count: Math.floor(Math.random() * 1000) + 200, // Quantidade aleatória de navios
    }));
};

// Função para filtrar os top 5 países com mais navios
const getTop5Countries = (data: { country: string; count: number }[]) => {
    return data.sort((a, b) => b.count - a.count).slice(0, 5);
};

// Dados de navios por país
const shipDataByCountry = generateFakeShipDataByCountry();
const top5Countries = getTop5Countries(shipDataByCountry);
</script>

<template>
    <div>
        <BarChart
            class="h-[200px]"
            index="country"
            :data="top5Countries"
            :showLegend="false"
            :categories="['count']"
            :y-formatter="
                (tick, i) => {
                    return typeof tick === 'number' ? `${new Intl.NumberFormat('us').format(tick).toString()}` : '';
                }
            "
            :rounded-corners="4"
        />
    </div>
</template>
