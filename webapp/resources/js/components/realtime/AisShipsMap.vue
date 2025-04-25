<script lang="ts">
import 'maplibre-theme/classic.css';
import 'maplibre-theme/icons.lucide.css';
import MapHelper from '../../helpers/map';
import AisLayer from './AisLayer.vue';
import { Badge } from '@/components/ui/badge';
import { Ship } from 'lucide-vue-next';

export default {
    components: {
        AisLayer,
        Badge,
        Ship
    },

    props: {
        ships: Array, // Array of ship objects
        ship: Object, // Ship object
        realtimePosition: Object, // Real-time position object
    },

    data() {
        return {
            map: null,
            mapIsReady: false,
        };
    },

    mounted() {
        this.initializeMap();
    },

    methods: {
        initializeMap() {
            // If the map is not initialized
            if (!this.map) {
                const zoom = 2;
                const center = [0, 0];
                const bearing = 0;

                // Check if the user prefers dark mode
                const isDarkMode = localStorage.getItem('appearance') === 'dark';

                // Create the map
                this.map = MapHelper.createMap('map', center, zoom, bearing, isDarkMode);

                // Add the base layer
                this.map.on('load', async () => {
                    // Load the base layer
                    this.mapIsReady = true;
                });
            }
        },
    },
};
</script>

<template>
    <div id="map" class="rounded-br-lg rounded-bl-lg"></div>
    
    <div class="absolute top-0 right-0 z-10 p-4">
        <Badge>
            <Ship class="mr-2 h-4 w-4" />
            <span>{{ ships.length }} Ships</span>
        </Badge>
    </div>
    
    <AisLayer :mapInstance="map" :data="ships" v-if="mapIsReady" />
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
