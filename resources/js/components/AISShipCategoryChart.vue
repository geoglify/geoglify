<script setup lang="ts">
import { BarChart } from '@/components/ui/chart-bar';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';

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
    <Card>
        <CardHeader>
            <CardTitle>Cargo Types</CardTitle>
            <CardDescription>Ships by category</CardDescription>
        </CardHeader>
        <CardContent>
            <BarChart
                style="height: 150px"
                index="category"
                :data="shipData"
                :showLegend="false"
                :categories="['count']"
                :y-formatter="
                    (tick, i) => {
                        return typeof tick === 'number' ? `${new Intl.NumberFormat('us').format(tick).toString()}` : '';
                    }
                "
                :rounded-corners="4"
            />
        </CardContent>
    </Card>
</template>
