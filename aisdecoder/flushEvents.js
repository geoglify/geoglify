const axios = require("axios");
const { API_URL, BATCH_SIZE } = require("./config");

async function flushEvents(eventQueue) {
    if (eventQueue.length === 0) return;
    const chunk = eventQueue.slice(0, BATCH_SIZE);

    try {
        console.info(
            `Sending ${chunk.length} of ${eventQueue.length} ships to the API`
        );

        const response = await axios.post(`${API_URL}/ais`, chunk, {
            headers: { "Content-Type": "application/json" },
        });

        if (response.status === 202) {
            console.info("Ships sent successfully to the API");
        }

        eventQueue.splice(0, chunk.length);
    } catch (error) {
        console.error(
            `Error sending ships to the API ${API_URL}/ais: ${error}`
        );
    }
}

module.exports = { flushEvents };
