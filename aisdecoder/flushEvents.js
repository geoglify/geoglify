const axios = require("axios");
const { API_URL, BATCH_SIZE } = require("./config");

async function flushEvents(eventQueue) {
    if (eventQueue.length === 0) return;
    
    const chunk = eventQueue.slice(0, BATCH_SIZE);

    try {
        console.info(`Sending ${chunk.length} of ${eventQueue.length} AIS messages to the API`);

        const response = await axios.post(`${API_URL}/v1/ship-ais`, 
            { messages: chunk }, 
            {
                headers: { "Content-Type": "application/json" },
                timeout: 10000,
            }
        );

        if (response.status === 202) {
            console.info("AIS messages sent successfully to the API");
            // Remove os eventos processados
            chunk.forEach(event => {
                const index = eventQueue.findIndex(e => e === event);
                if (index !== -1) eventQueue.splice(index, 1);
            });
        }
    } catch (error) {
        console.error(`Error sending AIS messages to the API ${API_URL}/v1/ship-ais:`, error.message);
        handleFlushError(error, chunk, eventQueue);
    }
}

function handleFlushError(error, chunk, eventQueue) {
    // Em caso de erro, não remove os eventos para tentar novamente
    if (error.code === 'ECONNREFUSED' || error.response?.status >= 500) {
        console.warn("Will retry sending in next flush cycle");
    } else {
        // Para outros erros (400, 422, etc), remove os eventos inválidos
        chunk.forEach(event => {
            const index = eventQueue.findIndex(e => e === event);
            if (index !== -1) eventQueue.splice(index, 1);
        });
    }
}

module.exports = { flushEvents };
