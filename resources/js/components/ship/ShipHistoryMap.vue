<script setup lang="ts">
import * as turf from '@turf/turf';
import axios from 'axios';
import maplibregl from 'maplibre-gl';
import 'maplibre-theme/classic.css';
import 'maplibre-theme/icons.lucide.css';
import { defineProps, onMounted, ref, watch } from 'vue';
import MapHelper from './../../helpers/map';

// Props passed to the component
const props = defineProps<{
    ship?: any;
    startDate?: string;
    endDate?: string;
    mapType?: string;
}>();

// Refs to store the map and ship positions
const map = ref<maplibregl.Map | null>(null);
const shipPositions = ref<any>(null);

// Function to fetch ship positions
const fetchShipPositions = async (shipId: number, startDate: string, endDate: string, mapType: string) => {
    try {
        const response = await axios.post(`/ships/${shipId}/last-positions`, {
            mapType,
            startDate,
            endDate,
        });

        // Check if the response is valid
        if (!response.data || !response.data.features) {
            console.error('Invalid response data:', response.data);
            return;
        }

        // Set the ship positions
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
const createShipPositionsSource = (mapType?: string) => {
    if (map.value) {
        map.value.addSource('shipPositions', {
            type: 'geojson',
            data: {
                type: 'FeatureCollection',
                features: [],
            },
        });

        // Add the lines layer if mapType is 'lines'
        if (mapType === 'lines') {
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

        // Add the polygon layer if mapType is 'grid' and use property color from each feature
        if (mapType === 'grid') {
            map.value.addLayer({
                id: 'shipPositions',
                type: 'fill',
                source: 'shipPositions',
                layout: {},
                paint: {
                    'fill-color': ['case', ['==', ['get', 'color'], '#000000'], '#000000', '#FF0000'],
                    'fill-opacity': 0.5,
                },
            });
        }
    }
};

// Function to add the ship positions to the map
onMounted(() => {
    if (!map.value) {
        const zoom = 2;
        const center: [number, number] = [0, 0];
        const bearing = 0;

        const isDarkMode = localStorage.getItem('appearance') === 'dark';

        // Create the map
        map.value = MapHelper.createMap('map', center, zoom, bearing, isDarkMode) as maplibregl.Map;

        // Add map controls
        map.value.on('load', () => {
            // Add the ship positions source
            createShipPositionsSource(props.mapType);

            // Check if the ship has an ID
            if (props.ship?.id && props.startDate && props.endDate) {
                // Fetch ship positions
                fetchShipPositions(props.ship.id, props.startDate, props.endDate);
            }
        });
    }
});

// Watch for changes in the ship ID, start date, end date, and map type
watch(
    () => [props.startDate, props.endDate, props.mapType],
    ([startDate, endDate, mapType]) => {
        // Clear previous ship positions
        if (map.value) {
            map.value.getSource('shipPositions')?.setData({
                type: 'FeatureCollection',
                features: [],
            });
        }

        if (props.ship?.id && !!startDate && !!endDate) {
            fetchShipPositions(props.ship.id, startDate, endDate, mapType);
        }
    },
);
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
