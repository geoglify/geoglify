<template>
    <div ref="mapContainer" class="map-container"></div>
</template>

<script setup>
import { onMounted, ref } from 'vue'

import maplibregl from 'maplibre-gl';
import 'maplibre-gl/dist/maplibre-gl.css';

const mapContainer = ref(null)

onMounted(() => {
    new maplibregl.Map({
        container: 'map', // container id
        style: 'https://demotiles.maplibre.org/style.json', // style URL
        center: [0, 0], // starting position [lng, lat]
        zoom: 1, // starting zoom
        container: mapContainer.value,
        container: mapContainer.value,
        canvasContextAttributes: { antialias: true }
    });
})
</script>

<style scoped>
.map-container {
    width: 100%;
    height: 100vh;
}
</style>
