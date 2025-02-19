<template>
    <!--div style="position: absolute; top: 75px; left: 10px; z-index: 5000">
        <v-chip label color="primary">{{ ships.length }} ships</v-chip>
    </div-->
</template>
<script>
import MapHelper from "@/Helpers/MapHelper";
import ShipHelper from "@/Helpers/ShipHelper";
import store from "@/store";

export default {
    props: ["mapInstance", "data"],

    data() {
        return {
            lastUpdate: Date.now(), // Time of the last update
            isMapInteracting: false, // Whether the user is interacting with the map
            tempShipUpdates: [], // Temporary storage for ship updates while interacting with the map
        };
    },

    async mounted() {
        // Initialize the ship layer and fetch initial ship data
        await this.initializeLayer();
        await this.fetchShips();

        // Listen for real-time ship position updates
        window.Echo.channel("realtime_ships").listen(
            "ShipPositionUpdated",
            (data) => {
                if (this.isMapInteracting) {
                    // Loop through incoming ship data
                    data.forEach((newShip) => {
                        // Check if a ship with the same MMSI already exists in tempShipUpdates
                        const existingShipIndex =
                            this.tempShipUpdates.findIndex(
                                (ship) => ship.mmsi === newShip.mmsi
                            );

                        if (existingShipIndex !== -1) {
                            // If the ship exists, update it with the more recent data
                            this.tempShipUpdates[existingShipIndex] = newShip;
                        } else {
                            // If the ship doesn't exist, add it to the array
                            this.tempShipUpdates.push(newShip);
                        }
                    });
                } else {
                    // If the map is not being interacted with, update the map immediately
                    data.forEach((ship) => {
                        store.dispatch("addOrUpdateShip", ship);
                    });
                    this.updateSource(); // Update the map immediately
                }
            }
        );

        // Monitor user interactions with the map
        this.mapInstance.on("movestart", () => {
            this.isMapInteracting = true; // User started moving the map
        });

        this.mapInstance.on("moveend", () => {
            this.isMapInteracting = false; // User stopped moving the map
            this.applyTempUpdates(); // Apply accumulated updates when interaction ends
        });

        this.mapInstance.on("zoomstart", () => {
            this.isMapInteracting = true; // User started zooming
        });

        this.mapInstance.on("zoomend", () => {
            this.isMapInteracting = false; // User stopped zooming
            this.applyTempUpdates(); // Apply accumulated updates when interaction ends
        });

        // Other interactions (pitch, rotate) follow the same pattern
    },

    computed: {
        ships() {
            return store.getters.getShips;
        },

        selectedShip() {
            return store.getters.getSelectedShip;
        },
    },

    watch: {
        selectedShip(newShip, oldShip) {
            if (oldShip) {
                this.dehighlightShip(oldShip);
            }

            if (newShip) {
                this.highlightShip(newShip);
            } else if (oldShip) {
                this.dehighlightShip(oldShip);
            }
        },
    },

    methods: {
        async initializeLayer() {
            await MapHelper.addIcon(
                this.mapInstance,
                "shipIcon",
                "/images/boat-sdf.png"
            );
            await MapHelper.addIcon(
                this.mapInstance,
                "circleIcon",
                "/images/circle-sdf.png"
            );
            ShipHelper.removeLayers(this.mapInstance, "shipLayer");
            ShipHelper.removeSource(this.mapInstance, "shipSource");

            MapHelper.addSource(this.mapInstance, "shipSource");
            ShipHelper.addLayer(this.mapInstance, "shipLayer", "shipSource");

            this.mapInstance.on("click", "shipLayer", (e) => {
                const ship = e.features[0].properties;
                this.highlightShip(ship);
            });
        },

        highlightShip(ship) {
            const clickedShipId = ship.mmsi;
            this.mapInstance.setFeatureState(
                { source: "shipSource", id: clickedShipId * 100 },
                { highlighted: true }
            );
            this.mapInstance.setFeatureState(
                { source: "shipSource", id: clickedShipId * 10000 },
                { highlighted: true }
            );
            store.dispatch("setSelectedShip", ship);
        },

        dehighlightShip(ship) {
            const shipId = ship.mmsi;
            this.mapInstance.setFeatureState(
                { source: "shipSource", id: shipId * 100 },
                { highlighted: false }
            );
            this.mapInstance.setFeatureState(
                { source: "shipSource", id: shipId * 10000 },
                { highlighted: false }
            );
        },

        async fetchShips() {
            try {
                this.data.forEach((ship) => {
                    store.dispatch("addOrUpdateShip", ship);
                });
                this.updateLoop();
            } catch (error) {
                console.error("API Error:", error);
            }
        },

        updateSource() {
            const features = this.ships.map((ship) => ship.features).flat();

            ShipHelper.updateSource(this.mapInstance, "shipSource", features);
        },

        updateLoop() {
            const now = Date.now();
            const delta = now - this.lastUpdate;

            if (delta >= 1000 && !this.isMapInteracting) {
                this.updateSource();
                this.lastUpdate = now;
            }

            requestAnimationFrame(() => this.updateLoop());
        },

        applyTempUpdates() {
            // If there are accumulated updates and the map is no longer being interacted with, apply them
            if (this.tempShipUpdates.length > 0) {
                this.tempShipUpdates.forEach((ship) => {
                    store.dispatch("addOrUpdateShip", ship);
                });
                this.tempShipUpdates = []; // Clear the temporary updates list
                this.updateSource(); // Update the map with new positions
            }
        },
    },
};
</script>
