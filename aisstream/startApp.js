const AisStreamClient = require("./aisStreamClient");
const { flushEvents } = require("./flushEvents");
const { FLUSH_INTERVAL } = require("./config");

async function startApp(eventQueue) {
    const aisStreamClient = new AisStreamClient(eventQueue);
    aisStreamClient.start();

    setInterval(() => flushEvents(eventQueue), FLUSH_INTERVAL);
}

module.exports = { startApp };
