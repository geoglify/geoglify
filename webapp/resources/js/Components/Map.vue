<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import maplibregl from 'maplibre-gl';
import 'maplibre-gl/dist/maplibre-gl.css';

const mapContainer = ref(null);
const map = ref(null);
const MAPTILER_KEY = "Qbwxcd8lf8BWDwAZyp5B";

const initializeMap = () => {
    map.value = new maplibregl.Map({
        style: `https://api.maptiler.com/maps/streets-v2/style.json?key=${MAPTILER_KEY}`,
        center: [-8, 41],
        zoom: 3,
        pitch: 0,
        bearing: 0,
        container: mapContainer.value,
        canvasContextAttributes: { antialias: true }
    });
};

onMounted(() => {
    initializeMap();
});

onUnmounted(() => {
    if (map.value) {
        map.value.remove();
    }
});
</script>

<template>
    <div ref="mapContainer" class="map-container"></div>
</template>

<style scoped>
/* To ensure the map takes up the full height and width of its container */
.map-container {
    width: 100%;
    height: 100%;
}
</style>
