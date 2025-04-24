const axios = require('axios');
const { API_URL, BATCH_SIZE } = require('./config');

// Event queue to hold AIS events before sending them to Laravel in batches
let eventQueue = [];

// Add a new event to the queue, maintaining only the latest event for each mmsi
function queueEvent(event) {
    // Find if an event with the same mmsi already exists in the queue
    const existingEventIndex = eventQueue.findIndex(
        (e) => e.mmsi === event.mmsi,
    );

    if (existingEventIndex !== -1) {
        // Replace the existing event with the new one
        eventQueue[existingEventIndex] = event;
    } else {
        // Add the new event if it doesn't exist in the queue
        eventQueue.push(event);
    }
}

// Flush the queue, sending a batch of ships to API
async function flushEvents() {
    if (eventQueue.length === 0) return;

    // Extract a chunk from the queue to send
    const chunk = eventQueue.splice(0, BATCH_SIZE);

    try {
        const response = await axios.post(API_URL, chunk, {
            headers: { 'Content-Type': 'application/json' },
        });
        console.log(
            `Sent ${chunk.length} of ${
                eventQueue.length + chunk.length
            } ships to API. Status: ${response.status}`,
        );
    } catch (error) {
        console.error(`Failed to send ships to API:`, error.message);
        // Reinsert the chunk to the front of the queue on failure
        eventQueue = [...chunk, ...eventQueue];
    }
}

module.exports = { queueEvent, flushEvents };
