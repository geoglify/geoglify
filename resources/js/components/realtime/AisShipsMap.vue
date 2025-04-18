<script lang="ts">
import 'maplibre-theme/classic.css';
import 'maplibre-theme/icons.lucide.css';
import { mapActions, mapState } from 'vuex';
import MapHelper from '../../helpers/map';
import ShipLayer from './AisLayer.vue';

export default {
    components: {
        ShipLayer,
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

    computed: {
        // Mapping Vuex state to local computed properties
        ...mapState(['selectedShip']),
    },

    watch: {
        // Watch for changes in the selected ship
        realtimePosition(newPosition) {
            if (!!newPosition && this.mapIsReady) {
                this.centerMapOnShip(newPosition);
            }
        },

        // Watch for changes in the selected ship
        selectedShip(newShip) {
            if (!!newShip && !!this.realtimePosition && this.mapIsReady) {
                this.centerMapOnShip(this.realtimePosition);
            }
        },
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

                    // Define the base layer
                    if (!!this.realtimePosition) {
                        this.centerMapOnShip(this.realtimePosition);
                    }

                    // Define the base layer
                    if (!!this.ship) {
                        this.setSelectedShip(this.ship);
                    }
                });
            }
        },

        // Center the map on the ship
        centerMapOnShip(ship) {
            //find the ship with the same mmsi
            const selectedShip = this.ships.find((s) => s.mmsi === ship.mmsi);

            if (!selectedShip) return;

            this.map.setCenter([selectedShip.longitude, selectedShip.latitude]);
            this.map.setZoom(17);
        },

        // Add or update the ship
        ...mapActions(['addOrUpdateShip', 'setSelectedShip']),
    },
};
</script>

<template>
    <div id="map"></div>
    <ShipLayer :mapInstance="map" :data="ships" v-if="mapIsReady" />
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
