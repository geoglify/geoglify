const WebSocket = require('ws');
const { processAisMessage } = require('./aisProcessor');
const { flushEvents } = require('./queueManager');
const { AISSTREAM_API_KEY, FLUSH_INTERVAL } = require('./config');

// Main function to initiate AIS stream connection
async function main() {
  connectToAisStreamWithRetry(); // Connect to AIS stream with retry logic
  setInterval(flushEvents, FLUSH_INTERVAL); // Schedule batch processing of events
}

// Function to connect to the AIS stream with retry logic on connection failure
function connectToAisStreamWithRetry() {
  try {
    console.log("Connecting to AIS stream...");
    const socket = new WebSocket("wss://stream.aisstream.io/v0/stream");

    // On successful connection, subscribe to AIS data within the specified bounding box
    socket.onopen = function () {
      console.log("Connected to AIS stream");
      const subscriptionMessage = {
        Apikey: AISSTREAM_API_KEY,
        BoundingBoxes: [
          [
            [29.343875, -35.419922],
            [45.690833, 6.394043],
          ],
        ],
      };
      socket.send(JSON.stringify(subscriptionMessage));
    };

    // Retry connection upon closure of WebSocket
    socket.onclose = function () {
      console.error("WebSocket closed, retrying...");
      setTimeout(connectToAisStreamWithRetry, 5000);
    };

    // Handle errors in WebSocket connection
    socket.onerror = function (err) {
      console.error("WebSocket error:", err.message);
    };

    // On message, process the AIS message
    socket.onmessage = async (event) => {
      const aisMessage = JSON.parse(event.data);
      processAisMessage(aisMessage);
    };
  } catch (err) {
    console.error("Failed to connect to AIS stream, retrying...");
    setTimeout(connectToAisStreamWithRetry, 5000);
  }
}

main();