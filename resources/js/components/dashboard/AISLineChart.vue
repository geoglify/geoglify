<script setup lang="ts">
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { LineChart } from '@/components/ui/chart-line';

// Função para gerar dados fake
const generateFakeAISData = () => {
    const data = [];
    const now = new Date();
    // Fixa o horário em 00:00:00
    now.setHours(0, 0, 0, 0);

    const endTime = new Date(now.getTime() + 15 * 60000); // 15 minutos depois

    while (now <= endTime) {
        data.push({
            timestamp: new Date(now).toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' }),
            ships: Math.floor(Math.random() * 100), // Número aleatório de navios entre 0 e 100
        });
        now.setMinutes(now.getMinutes() + 1); // Incrementa 1 minuto
    }

    return data;
};

const aisData = generateFakeAISData();

</script>

<template>
    <Card>
        <CardHeader>
            <CardTitle>Realtime</CardTitle>
            <CardDescription>Ships in the last 15 minutes</CardDescription>
        </CardHeader>
        <CardContent>
            <LineChart
                style="height: 150px"
                :data="aisData"
                index="timestamp"
                :showLegend="false"
                :categories="['ships']"
                :y-formatter="(tick) => tick"
            />
        </CardContent>
    </Card>
</template>
