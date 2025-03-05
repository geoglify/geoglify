import MapboxDraw from '@mapbox/mapbox-gl-draw'; // Import Mapbox Draw library
import maplibregl from 'maplibre-gl'; // Import Maplibre GL library

// Override Mapbox Draw constants to use Maplibre GL classes
MapboxDraw.constants.classes.CONTROL_BASE = 'maplibregl-ctrl';
MapboxDraw.constants.classes.CONTROL_PREFIX = 'maplibregl-ctrl-';
MapboxDraw.constants.classes.CONTROL_GROUP = 'maplibregl-ctrl-group';

/**
 * Map helper functions
 */
export default {
    // Create a new map instance
    createMap(container, center = [0, 0], zoom = 1, bearing = 0) {
        return new maplibregl.Map({
            container: container, // HTML element ID or element to render the map
            style: 'https://basemaps.cartocdn.com/gl/positron-nolabels-gl-style/style.json', // Map style URL
            center: center, // Initial map center [longitude, latitude]
            zoom: zoom, // Initial zoom level
            bearing: bearing, // Initial bearing (rotation)
            antialias: true, // Enable antialiasing
            //hash: "map", // Enable URL hash for map state
            glyphs: 'https://demotiles.maplibre.org/font/{fontstack}/{range}.pbf', // Font glyphs URL
        });
    },

    // Add the navigation control to the map (zoom and rotation buttons)
    addNavigationControl(map) {
        map.addControl(new maplibregl.NavigationControl());
    },

    // Add the globe projection control to the map (toggle between 2D and 3D globe)
    addGlobeProjectionControl(map) {
        map.addControl(new maplibregl.GlobeControl(), 'top-right');
    },

    // Add an icon to the map
    async addIcon(map, id, imageUrl) {
        try {
            const icon = await this.loadImage(imageUrl); // Load the image

            // Check if the icon already exists and remove it if necessary
            if (map.hasImage(id)) {
                map.removeImage(id);
            }

            // Add the icon to the map
            if (icon) {
                map.addImage(id, icon, { sdf: true }); // SDF (Signed Distance Field) for dynamic styling
            }
        } catch (error) {
            console.error('Error loading image:', error);
        }
    },

    // Load an image asynchronously
    loadImage(imageUrl) {
        return new Promise((resolve, reject) => {
            const img = new Image(); // Create a new image element
            img.src = imageUrl; // Set the image source
            img.onload = () => {
                resolve(img); // Resolve the promise when the image loads
            };
            img.onerror = (error) => {
                reject(error); // Reject the promise if there's an error
            };
        });
    },

    // Add a method to center the map on given coordinates
    centerMapOnCoordinates(map, coordinates) {
        map.setCenter(coordinates);
        map.setZoom(12);
    },

    // Adds a ship layer to the map
    addLayer(map, id, source) {
        // Add the icon layer for ships
        map.addLayer({
            id: id,
            type: 'symbol',
            source: source,
            layout: {
                'icon-image': ['get', 'image'], // Use the ship's icon
                'icon-size': 1, // Set icon size
                'icon-rotate': ['get', 'hdg'], // Rotate icon based on heading
                'icon-rotation-alignment': 'map', // Align rotation with the map
                'icon-allow-overlap': true, // Allow icons to overlap
            },
            paint: {
                'icon-color': [
                    'case',
                    ['boolean', ['feature-state', 'highlighted'], false],
                    'yellow', // If highlighted is true, use yellow
                    ['get', 'color'], // Otherwise, use the color from the feature properties
                ],
            },
            filter: ['==', ['get', 'type'], 'icon'], // Filter for icon features
            minzoom: 0, // Set the minimum zoom level
            maxzoom: 15, // Set the maximum zoom level
        });

        // Add the ship skeleton layer
        map.addLayer({
            id: `${id}-skeleton`,
            type: 'fill',
            source: source,
            paint: {
                'fill-color': [
                    'case',
                    ['boolean', ['feature-state', 'highlighted'], false],
                    'yellow', // If highlighted is true, use yellow
                    ['get', 'color'], // Otherwise, use the color from the feature properties
                ],
                'fill-opacity': 0.6, // Set fill opacity
            },
            filter: ['==', ['get', 'type'], 'skeleton'], // Filter for skeleton features
            minzoom: 15, // Set the minimum zoom level
            maxzoom: 24, // Set the maximum zoom level
        });

        // Add the text label layer for ships
        map.addLayer({
            id: `${id}-text`,
            type: 'symbol',
            source: source,
            layout: {
                'text-field': ['get', 'name'], // Display the ship's name
                'text-font': ['Open Sans Bold'], // Set font
                'text-size': 16, // Set text size
                'text-anchor': 'center', // Center the text
                'text-offset': [0, 0], // No offset
            },
            paint: {
                'text-color': '#000000', // Set text color
                'text-halo-color': '#fff', // Set text halo color
                'text-halo-width': 2, // Set text halo width
            },
            filter: ['==', ['get', 'type'], 'text'], // Filter for text features
        });
    },

    // Removes ship layers from the map
    removeLayers(map, id) {
        if (map.getLayer(id)) map.removeLayer(id); // Remove the icon layer
        if (map.getLayer(`${id}-text`)) map.removeLayer(`${id}-text`); // Remove the text layer
        if (map.getLayer(`${id}-skeleton`)) map.removeLayer(`${id}-skeleton`); // Remove the skeleton layer
    },

    // Add a new GeoJSON source to the map
    addSource(map, id, data = { type: 'FeatureCollection', features: [] }) {
        map.addSource(id, {
            type: 'geojson', // Source type
            data: data, // GeoJSON data
            buffer: 0, // Buffer size in pixels
            maxzoom: 12, // Maximum zoom level
            tolerance: 100, // Simplification tolerance
        });
    },

    // Removes a ship source from the map
    removeSource(map, id) {
        if (map.getSource(id)) map.removeSource(id); // Remove the source if it exists
    },

    // Updates the ship source with new features
    updateSource(map, id, features) {
        const source = map.getSource(id); // Get the source by ID
        if (source) {
            source.setData({
                type: 'FeatureCollection',
                features: features, // Update the features
            });
        } // Update the source using MapHelper
    },
};
