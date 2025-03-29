<script setup lang="ts">
import * as turf from '@turf/turf';
import axios from 'axios';
import maplibregl from 'maplibre-gl';
import 'maplibre-theme/classic.css';
import 'maplibre-theme/icons.lucide.css';
import { defineProps, onMounted, ref } from 'vue';
import MapHelper from './../../helpers/map';

// Props passed to the component
const props = defineProps<{
    ship?: object;
}>();

// Refs to store the map and ship positions
const map = ref<maplibregl.Map | null>(null);
const shipPositions = ref<any>(null);

// Function to fetch ship positions
const fetchShipPositions = async (shipId: number, seconds: number) => {
    try {
        const response = await axios.get(`/ships/${shipId}/last-positions/${seconds}`);
        shipPositions.value = response.data;

        // Check if the map and ship positions are available
        if (map.value && shipPositions.value) {
            const geoJsonData = shipPositions.value;
            map.value.getSource('shipPositions')?.setData(geoJsonData);

            // Find Bounding Box Of LineString
            const bbox = turf.bbox(geoJsonData);

            // Fit map to bounding box
            map.value.fitBounds(bbox, {
                padding: 50,
            });
        }
    } catch (error) {
        console.error('Error fetching ship positions:', error);
    }
};

// Create the ship positions source
const createShipPositionsSource = () => {
    if (map.value) {
        map.value.addSource('shipPositions', {
            type: 'geojson',
            data: {
                type: 'FeatureCollection',
                features: [],
            },
        });

        map.value.addLayer({
            id: 'shipPositions',
            type: 'line',
            source: 'shipPositions',
            layout: {
                'line-join': 'round',
                'line-cap': 'round',
            },
            paint: {
                'line-color': '#000000',
                'line-width': 2,
            },
        });
    }
};

// Function to add the ship positions to the map
onMounted(() => {
    if (!map.value) {
        const zoom = 2;
        const center: [number, number] = [0, 0];
        const bearing = 0;

        const isDarkMode = localStorage.getItem('appearance') === 'dark';

        map.value = MapHelper.createMap('map', center, zoom, bearing, isDarkMode) as maplibregl.Map;

        map.value.on('load', () => {
            // Add the ship positions source
            createShipPositionsSource();

            // Check if the ship has an ID
            if (props.ship?.id) {
                const rangeSeconds = 60 * 60 * 7; // 7 hours
                fetchShipPositions(props.ship.id, rangeSeconds);
            }
        });
    }
});
</script>

<template>
    <div id="map"></div>
</template>

<style>
#map {
    height: 100%;
    width: 100%;
    background-color: #000;
}

.maplibregl-map {
    --ml-font-icons: maplibregl-icons-lucide;
}
</style>
