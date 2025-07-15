const axios = require("axios");
const { API_URL, BATCH_SIZE } = require("./config");

async function flushEvents(eventQueue) {
    if (eventQueue.length === 0) return;
    
    // Separa eventos por tipo
    const positionEvents = eventQueue.filter(e => e.type === 'position');
    const infoEvents = eventQueue.filter(e => e.type === 'info');
    
    // Processa posições (com coordenadas)
    if (positionEvents.length > 0) {
        await flushPositions(positionEvents, eventQueue);
    }
    
    // Processa informações de navios (sem coordenadas)
    if (infoEvents.length > 0) {
        await flushShipInfo(infoEvents, eventQueue);
    }
}

async function flushPositions(positionEvents, eventQueue) {
    const chunk = positionEvents.slice(0, BATCH_SIZE);

    try {
        console.info(`Sending ${chunk.length} of ${positionEvents.length} AIS positions to the API`);

        const response = await axios.post(`${API_URL}/v1/ship-ais`, 
            { positions: chunk }, 
            {
                headers: { "Content-Type": "application/json" },
                timeout: 10000,
            }
        );

        if (response.status === 202) {
            console.info("AIS positions sent successfully to the API");
            // Remove apenas os eventos de posição processados
            chunk.forEach(event => {
                const index = eventQueue.findIndex(e => e === event);
                if (index !== -1) eventQueue.splice(index, 1);
            });
        }
    } catch (error) {
        console.error(`Error sending AIS positions to the API ${API_URL}/v1/ship-ais:`, error.message);
        handleFlushError(error, chunk, eventQueue);
    }
}

async function flushShipInfo(infoEvents, eventQueue) {
    const chunk = infoEvents.slice(0, BATCH_SIZE);

    try {
        console.info(`Sending ${chunk.length} of ${infoEvents.length} ship info updates to the API`);

        const response = await axios.post(`${API_URL}/v1/ship-info`, 
            { shipInfo: chunk }, 
            {
                headers: { "Content-Type": "application/json" },
                timeout: 10000,
            }
        );

        if (response.status === 202) {
            console.info("Ship info updates sent successfully to the API");
            // Remove apenas os eventos de info processados
            chunk.forEach(event => {
                const index = eventQueue.findIndex(e => e === event);
                if (index !== -1) eventQueue.splice(index, 1);
            });
        }
    } catch (error) {
        console.error(`Error sending ship info to the API ${API_URL}/v1/ship-info:`, error.message);
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
