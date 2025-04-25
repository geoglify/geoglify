<template>
    <div></div>
</template>

<script lang="js">
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
            updateQueue: new Set(),
            animationFrameId: null,
            visibilityThreshold: 1000,
            chunkSize: 200,
            visibleBounds: null
        };
    },

    async mounted() {
        await this.initializeLayer();
        await this.processInitialData();
        this.setupEventListeners();
        this.startUpdateLoop();
    },

    beforeDestroy() {
        this.cleanupEventListeners();
        cancelAnimationFrame(this.animationFrameId);
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
                    this.scheduleUpdate(ship.mmsi);
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
                    this.updateVisibleBounds();
                });
            });

            this.throttledUpdateVisibleBounds = throttle(() => {
                this.updateVisibleBounds();
            }, 500);

            this.mapInstance.on('move', this.throttledUpdateVisibleBounds);
        },

        handleRealTimeUpdates(data) {
            
            console.log('Real-time data received:', data);
            
            data.forEach(ship => {
                if (this.isMapInteracting) {
                    this.tempShipUpdates.set(ship.mmsi, ship);
                } else {
                    this.ships.set(ship.mmsi, this.processShipData(ship));
                    this.scheduleUpdate(ship.mmsi);
                }
            });
        },

        scheduleUpdate(mmsi) {
            this.updateQueue.add(mmsi);
        },

        applyPendingUpdates() {
            if (this.tempShipUpdates.size === 0) return;

            this.tempShipUpdates.forEach((ship, mmsi) => {
                this.ships.set(mmsi, this.processShipData(ship));
                this.scheduleUpdate(mmsi);
            });
            
            this.tempShipUpdates.clear();
        },

        updateVisibleBounds() {
            try {
                this.visibleBounds = this.mapInstance.getBounds();
            } catch (error) {
                console.warn('Could not update map bounds:', error);
            }
        },

        startUpdateLoop() {
            const update = () => {
                const now = Date.now();
                
                if (now - this.lastUpdate >= this.visibilityThreshold && !this.isMapInteracting) {
                    this.updateMapSource();
                    this.lastUpdate = now;
                }

                this.animationFrameId = requestAnimationFrame(update);
            };

            update();
        },

        updateMapSource() {
            
            if (this.updateQueue.size === 0) return;

            let features = [];
            const updateTime = Date.now();

            // Otimização: atualizar apenas navios modificados recentemente
            if (this.ships.size > 5000) {
                const visibleShips = this.visibleBounds 
                    ? this.getVisibleShips() 
                    : Array.from(this.ships.values());
                
                features = visibleShips
                    .filter(ship => 
                        this.updateQueue.has(ship.mmsi) && 
                        updateTime - ship.lastUpdated < 30000
                    )
                    .flatMap(ship => ship.features);
            } else {
                features = Array.from(this.ships.values())
                    .filter(ship => this.updateQueue.has(ship.mmsi))
                    .flatMap(ship => ship.features);
            }
            
            if (features.length > 0) {
                MapHelper.updateSource(this.mapInstance, 'shipSource', features);
            }

            this.updateQueue.clear();
        },

        getVisibleShips() {
            return Array.from(this.ships.values()).filter(ship => {
                try {
                    return this.visibleBounds.contains([ship.longitude, ship.latitude]);
                } catch (error) {
                    console.warn('Error checking ship visibility:', error);
                    return false;
                }
            });
        },

        cleanupEventListeners() {
            if (this.echoChannel) {
                window.Echo.leaveChannel('ships.latest_positions');
            }

            if (this.throttledUpdateVisibleBounds) {
                this.mapInstance.off('move', this.throttledUpdateVisibleBounds);
            }

            if (this.layerEventHandlers?.click) {
                this.mapInstance.off('click', 'shipLayer', this.layerEventHandlers.click);
            }
        }
    }
};
</script>
