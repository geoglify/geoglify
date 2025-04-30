<template>
    <div></div>
</template>

<script lang="ts">
import MapHelper from '@/helpers/map';
import ShipHelper from "@/helpers/ships";
import { throttle } from 'lodash-es';

export default {
    props: ['mapInstance', 'data'],

    data() {
        return {
            ships: new Map(),
            lastUpdate: Date.now(),
            isMapInteracting: false,
            tempShipUpdates: new Map(),
            animationFrameId: 0,
            visibilityThreshold: 5000,
            chunkSize: 200,
            latestUpdate: null as Date | null,
            cleanupInterval: 0,
            layerEventHandlers: {} as { click?: (e: any) => void },
            echoChannel: null,
            isUpdatingMap: false,
            lastMapUpdate: 0
        };
    },

    async mounted() {
        await this.initializeLayer();
        await this.processInitialData();
        this.setupEventListeners();
        this.startUpdateLoop();
        this.cleanupInterval = setInterval(this.cleanupOldShips, 60 * 1000);
    },

    beforeUnmount() {
        this.cleanupEventListeners();
        cancelAnimationFrame(this.animationFrameId);
        clearInterval(this.cleanupInterval);
    },

    methods: {
        async initializeLayer() {
            await Promise.all([
                MapHelper.addIcon(this.mapInstance, 'shipIcon', '/images/boat-sdf.png'),
                MapHelper.addIcon(this.mapInstance, 'circleIcon', '/images/circle-sdf.png')
            ]);

            MapHelper.removeLayers(this.mapInstance, 'shipLayer');
            MapHelper.removeSource(this.mapInstance, 'shipSource');

            MapHelper.addSource(this.mapInstance, 'shipSource');
            MapHelper.addLayer(this.mapInstance, 'shipLayer', 'shipSource');

            this.setupLayerInteractions();
        },

        setupLayerInteractions() {
            const handleShipClick = (e) => {
                const ship = e.features[0].properties;
                window.location.href = `/ships/${ship.id}`;
            };

            this.mapInstance.on('click', 'shipLayer', handleShipClick);
            this.mapInstance.on('mouseenter', 'shipLayer', () => {
                this.mapInstance.getCanvas().style.cursor = 'pointer';
            });

            this.layerEventHandlers = {
                click: handleShipClick
            };
        },

        async processInitialData() {
            if (!this.data?.length) return;

            const processBatch = async (startIndex) => {
                const endIndex = Math.min(startIndex + this.chunkSize, this.data.length);

                for (let i = startIndex; i < endIndex; i++) {
                    const ship = this.data[i];
                    this.ships.set(ship.mmsi, this.processShipData(ship));
                }

                if (endIndex < this.data.length) {
                    await new Promise(resolve => setTimeout(resolve, 0));
                    await processBatch(endIndex);
                }
            };

            await processBatch(0);
        },

        processShipData(ship) {
            return {
                ...ship,
                features: ShipHelper.createShipFeature(ship) || [],
                lastUpdated: Date.now()
            };
        },

        setupEventListeners() {
            this.echoChannel = window.Echo.channel('ships.latest_positions')
                .listen('ShipsLatestPositionsUpdated', this.handleRealTimeUpdates);

            const interactionEvents = ['movestart', 'zoomstart', 'rotatestart'];
            const interactionEndEvents = ['moveend', 'zoomend', 'rotateend'];

            interactionEvents.forEach(event => {
                this.mapInstance.on(event, () => {
                    this.isMapInteracting = true;
                });
            });

            interactionEndEvents.forEach(event => {
                this.mapInstance.on(event, () => {
                    this.isMapInteracting = false;
                    this.applyPendingUpdates();
                });
            });
        },

        handleRealTimeUpdates(data) {
            
            console.log('Realtime data received length:', data.length);
            
            data.forEach(ship => {
                const processedShip = this.processShipData(ship);
                if (this.isMapInteracting) {
                    this.tempShipUpdates.set(ship.mmsi, processedShip);
                } else {
                    this.ships.set(ship.mmsi, processedShip);
                }
            });
        },

        applyPendingUpdates() {
            if (this.tempShipUpdates.size === 0) return;

            this.tempShipUpdates.forEach((ship, mmsi) => {
                this.ships.set(mmsi, ship);
            });

            this.tempShipUpdates.clear();

            this.updateMapSource(true);
        },

        startUpdateLoop() {
            this.animationFrameId = setInterval(() => {
                if (!this.isMapInteracting) {
                    this.updateMapSource();
                }
            }, 5000);
        },

        cleanupOldShips() {
            const twoHoursAgo = Date.now() - 2 * 60 * 60 * 1000;
            let changed = false;
            for (const [mmsi, ship] of this.ships.entries()) {
                if (ship.lastUpdated < twoHoursAgo) {
                    this.ships.delete(mmsi);
                    changed = true;
                }
            }
            if (changed) {
                console.log(`Cleanup removed old ships. Current count: ${this.ships.size}`);
            }
        },

        updateMapSource(force = false) {
            if (this.isUpdatingMap) return;

            const now = Date.now();
            const timeSinceLast = now - this.lastMapUpdate;

            if (!force && timeSinceLast < this.visibilityThreshold) return;

            this.isUpdatingMap = true;
            const start = performance.now();

            const features = [];

            for (const ship of this.ships.values()) {
                if (ship.features && ship.features.length) {
                    features.push(...ship.features);
                }
            }

            MapHelper.updateSource(this.mapInstance, 'shipSource', features);

            this.latestUpdate = features.length
                ? new Date(Math.max(...Array.from(this.ships.values()).map(ship => ship.lastUpdated)))
                : null;

            this.$emit('lastUpdate', this.latestUpdate);
            this.$emit('shipsCount', this.ships.size);

            const duration = performance.now() - start;
            console.log(`[updateMapSource] Took ${duration.toFixed(2)} ms for ${features.length} features and ${this.ships.size} total ships`);

            this.isUpdatingMap = false;
            this.lastMapUpdate = now;
        },

        cleanupEventListeners() {
            if (this.echoChannel) {
                window.Echo.leaveChannel('ships.latest_positions');
            }

            if (this.layerEventHandlers?.click) {
                this.mapInstance.off('click', 'shipLayer', this.layerEventHandlers.click);
            }
        }
    }
};
</script>
