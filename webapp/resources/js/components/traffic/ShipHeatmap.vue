<script lang="ts">
import maplibregl from 'maplibre-gl';
import 'maplibre-theme/classic.css';
import { onMounted, ref } from 'vue';
import MapHelper from '../../helpers/map';

export default {
    setup() {
        const map = ref<maplibregl.Map | null>(null);

        const initializeMap = () => {
            const zoom = 2;
            const center = [0, 0];
            const bearing = 0;
            const isDarkMode = localStorage.getItem('appearance') === 'dark';

            map.value = MapHelper.createMap('heatmap', center, zoom, bearing, isDarkMode);

            map.value.on('load', () => {
                //loadHeatmapLayer();
                addGeoJSONToMap();
            });
        };

        const loadHeatmapLayer = async () => {
            if (!map.value) return;

            const response = await fetch('/traffic-heatmap');
            const geojson = await response.json();

            if (map.value.getSource('ships-heatmap')) {
                (map.value.getSource('ships-heatmap') as maplibregl.GeoJSONSource).setData(geojson);
            } else {
                map.value.addSource('ships-heatmap', {
                    type: 'geojson',
                    data: geojson,
                });

                map.value.addLayer({
                    id: 'heatmap-layer',
                    type: 'heatmap',
                    source: 'ships-heatmap',
                    maxzoom: 15,
                    paint: {
                        'heatmap-weight': ['interpolate', ['linear'], ['get', 'weight'], 0, 0, 200, 1],
                        'heatmap-intensity': ['interpolate', ['linear'], ['zoom'], 0, 1, 22, 10],
                        'heatmap-color': [
                            'interpolate',
                            ['linear'],
                            ['heatmap-density'],
                            0,
                            'rgba(33,102,172,0)',
                            0.1,
                            '#ffffb2',
                            0.3,
                            '#feb24c',
                            0.5,
                            '#fd8d3c',
                            0.7,
                            '#fc4e2a',
                            1,
                            '#e31a1c',
                        ],
                        'heatmap-radius': ['interpolate', ['linear'], ['zoom'], 0, 1, 15, 30],
                        'heatmap-opacity': 0.7,
                    },
                });
            }
        };

        // load resources/data/atleca.geojson
        const loadGeoJSON = async () => {
            const response = await fetch('/data/atleca.geojson');
            const geojson = await response.json();
            return geojson;
        };

        // add geojson to map
        const addGeoJSONToMap = async () => {
            const geojson = await loadGeoJSON();
            if (!map.value) return;

            map.value.addSource('atleca', {
                type: 'geojson',
                data: geojson,
            });

            map.value.addLayer({
                id: 'atleca-layer',
                type: 'fill',
                source: 'atleca',
                paint: {
                    'fill-color': '#ADEBB3',
                    'fill-opacity': 1,
                    'fill-outline-color': 'rgba(0, 0, 0, 0.4)'
                },
            });
        };

        onMounted(() => {
            initializeMap();
        });

        return {
            map,
        };
    },
};
</script>

<template>
    <div id="heatmap"></div>
</template>

<style scoped>
#heatmap {
    height: 100%;
    width: 100%;
    background-color: #000;
}

.maplibregl-map {
    --ml-font-icons: maplibregl-icons-lucide;
}
</style>
