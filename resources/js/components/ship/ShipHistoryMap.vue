<script setup lang="ts">
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import maplibregl from 'maplibre-gl';
import 'maplibre-theme/classic.css';
import 'maplibre-theme/icons.lucide.css';
import { defineProps, onMounted, ref } from 'vue';
import MapHelper from './../../helpers/map';

// Define props com tipagem
defineProps<{
    ship?: Partial<{
        id: number;
        mmsi: number;
        name: string;
        callsign: string | null;
        imo: number | null;
        draught: number | null;
        created_at: string;
        updated_at: string;
    }>;
}>();

// Ref para armazenar o mapa
const map = ref<maplibregl.Map | null>(null);

onMounted(() => {
    if (!map.value) {
        const zoom = 2;
        const center: [number, number] = [0, 0];
        const bearing = 0;

        map.value = MapHelper.createMap('map', center, zoom, bearing) as maplibregl.Map;
    }
});
</script>

<template>
    <Card>
        <CardHeader>
            <CardTitle>Map</CardTitle>
            <CardDescription>Real-time ship positions</CardDescription>
        </CardHeader>
        <CardContent>
            <div id="map"></div>
        </CardContent>
    </Card>
</template>

<style>
#map {
    height: 570px;
    width: 100%;
    background-color: #000;
}

.maplibregl-map {
    --ml-font-icons: maplibregl-icons-lucide;
}
</style>
