const axios = require("axios");
const { API_URL, BATCH_SIZE } = require("./config");

async function flushEvents(eventQueue) {
  if (eventQueue.length === 0) return;

  const chunk = eventQueue.slice(0, BATCH_SIZE);

  try {
    console.info(
      `Sending ${chunk.length} of ${eventQueue.length} AIS messages to the API`
    );

    const response = await axios.post(
      `${API_URL}/v1/ship-ais`,
      { messages: chunk },
      {
        headers: { "Content-Type": "application/json" },
        timeout: 10000,
      }
    );

    if (response.status === 202) {
      console.info("AIS messages sent successfully to the API");
      // Remove os eventos processados
      chunk.forEach((event) => {
        const index = eventQueue.findIndex((e) => e === event);
        if (index !== -1) eventQueue.splice(index, 1);
      });
    }
  } catch (error) {
    console.error(
      `Error sending AIS messages to the API ${API_URL}/v1/ship-ais:`,
      error.message
    );
    handleFlushError(error, chunk, eventQueue);
  }
}

function handleFlushError(error, chunk, eventQueue) {
  // If the error is a connection issue or server error, we can retry later
  if (error.code === "ECONNREFUSED" || error.response?.status >= 500) {
    console.warn("Will retry sending in next flush cycle");
  } else {
    // If the error is a client error (4xx), we log it and remove the events from the queue
    chunk.forEach((event) => {
      const index = eventQueue.findIndex((e) => e === event);
      if (index !== -1) eventQueue.splice(index, 1);
    });
  }
}

module.exports = { flushEvents };
