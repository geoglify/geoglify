<template>
    <div ref="mapContainer" class="map-container"></div>
</template>

<script setup>
import { onMounted, ref } from 'vue'

import maplibregl from 'maplibre-gl';
import 'maplibre-gl/dist/maplibre-gl.css';

const mapContainer = ref(null)
const MAPTILER_KEY = "Qbwxcd8lf8BWDwAZyp5B";

onMounted(() => {
    new maplibregl.Map({
        style: `https://api.maptiler.com/maps/streets-v2/style.json?key=${MAPTILER_KEY}`,
        center: [-8.639997955171657, 41.146846648292865],
        zoom: 15.5,
        pitch: 45,
        bearing: -17.6,
        container: 'map',
        canvasContextAttributes: { antialias: true },
        container: mapContainer.value,
    });
})
</script>

<style scoped>
.map-container {
    width: 100%;
    height: 100%;
}
</style>
