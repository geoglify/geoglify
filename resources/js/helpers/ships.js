import * as turf from '@turf/turf';
import proj4 from 'proj4';

const DEFAULT_COLOR = '#51829B'; // Default color for ships

export default {
    // Generates properties for a ship feature
    generateShipProperties(ship) {
        return {
            mmsi: ship.mmsi, // Ship's MMSI (Maritime Mobile Service Identity)
            name: ship.name || 'N/A', // Ship's name or "N/A" if not available
            color: ship.cargo_category_color || DEFAULT_COLOR, // Ship's color based on cargo category or default color
            hdg: ship.hdg || 511, // Ship's heading or 511 (default value for unknown heading)
            image: ship.hdg && ship.hdg !== 511 ? 'shipIcon' : 'circleIcon', // Icon to use based on heading
            highlighted: ship.highlighted || false, // Flag to highlight the ship
        };
    },

    // Creates a ship feature for the map
    createShipFeature(ship) {
        const shipProperties = this.generateShipProperties(ship); // Generate ship properties

        // Create a point geometry from latitude and longitude
        const geometry = {
            type: 'Point',
            coordinates: [parseFloat(ship.longitude), parseFloat(ship.latitude)], // Use latitude and longitude
        };

        const centroid = this.calculateCentroid(geometry); // Calculate the centroid of the geometry

        if (!geometry || !centroid || !centroid.every(Number.isFinite)) {
            // Skip if geometry or centroid is invalid
            console.warn('Ship with MMSI ', ship.mmsi, ' has invalid geometry or centroid');
            return null; // Return null
        }

        // Return an array of two features: one for the icon and one for the text label
        return [
            {
                id: ship.mmsi * 100, // Unique ID for the icon
                type: 'Feature',
                geometry,
                properties: {
                    ...shipProperties,
                    type: 'icon', // Feature type for the icon
                },
            },
            {
                id: ship.mmsi * 1000, // Unique ID for the text label
                type: 'Feature',
                geometry: {
                    type: 'Point',
                    coordinates: centroid, // Centroid coordinates for the text label
                },
                properties: {
                    name: shipProperties.name, // Ship's name for the label
                    type: 'text', // Feature type for the text
                },
            },
            {
                id: ship.mmsi * 10000, // Unique ID for the ship skeleton
                type: 'Feature',
                geometry: this.createSkeleton(centroid, ship),
                properties: {
                    ...shipProperties,
                    type: 'skeleton', // Feature type for the ship skeleton
                },
            },
        ];
    },

    // Calculates the centroid of a geometry
    calculateCentroid(geometry) {
        if (geometry.type === 'Point') {
            // Use the coordinates directly for a point
            return geometry.coordinates;
        }
        return null; // Return null for unsupported geometry types
    },

    // Get UTM zone from longitude
    utmzone_from_lon(lon_deg) {
        return 1 + Math.floor((lon_deg + 180) / 6);
    },

    // Set projection definition based on longitude
    proj4_setdef(lon_deg) {
        const utm_zone = this.utmzone_from_lon(lon_deg);
        return `+proj=utm +zone=${utm_zone} +datum=WGS84 +units=m +no_defs`;
    },

    // Convert coordinates to meters
    convertCoordsToMeters(coords, target) {
        if (!coords || coords.length !== 2 || !coords.every(Number.isFinite)) {
            return null;
        }

        const source = '+proj=longlat +ellps=WGS84 +datum=WGS84 +units=degrees';
        return proj4(source, target, coords);
    },

    // Convert coordinates to WGS84
    convertCoordsToWGS84(coords, source) {
        if (!coords || coords.length !== 2 || !coords.every(Number.isFinite)) {
            return null;
        }

        const target = '+proj=longlat +ellps=WGS84 +datum=WGS84 +units=degrees';
        return proj4(source, target, coords);
    },

    // Create a ship skeleton polygon
    createSkeleton(coordinates, ship) {
        // Default values for ship dimensions
        let lengthA = ship.dim_a || 20;
        let lengthB = ship.dim_b || 20;
        let lengthC = ship.dim_c || 5;
        let lengthD = ship.dim_d || 5;
        let angle = ship.hdg || 0;

        // If hdg = 511, draw a circle
        if (!ship.hdg || ship.hdg === 511) {
            const radius = Math.max(lengthA, lengthB, lengthC, lengthD);
            return turf.circle(coordinates, radius, {
                steps: 10,
                units: 'meters',
            }).geometry;
        }

        // Calculate the target projection based on the coordinates
        const targetProjection = this.proj4_setdef(coordinates[0]);

        // Convert the coordinates to meters
        const centerInMeters = this.convertCoordsToMeters(coordinates, targetProjection);

        // Define the offsets for the ship skeleton
        const offsets = [
            { x: -lengthC, y: -lengthB },
            { x: -lengthC, y: lengthA - (lengthA + lengthB) * 0.1 },
            { x: lengthD - (lengthC + lengthD) / 2, y: lengthA },
            { x: lengthD, y: lengthA - (lengthA + lengthB) * 0.1 },
            { x: lengthD, y: -lengthB },
            { x: -lengthC, y: -lengthB },
        ];

        // Rotate the offsets based on the ship's heading
        const rotatedCoordinates = offsets.map(({ x, y }) => {
            const adjustedCoords = [centerInMeters[0] + x, centerInMeters[1] + y];

            // Convert the adjusted coordinates back to WGS84
            return this.convertCoordsToWGS84(adjustedCoords, targetProjection);
        });

        // Create a polygon from the rotated coordinates
        const polygon = turf.polygon([rotatedCoordinates]);

        // Rotate the polygon based on the ship's heading
        const rotatedPolygon = turf.transformRotate(polygon, angle, {
            pivot: coordinates,
        });

        // Truncate the rotated polygon to reduce the number of coordinates
        var options = { precision: 7, coordinates: 2 };
        const truncatedPolygon = turf.truncate(rotatedPolygon, options);

        // Return the rotated polygon geometry
        return truncatedPolygon.geometry;
    },
};
