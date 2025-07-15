const { FLUSH_INTERVAL } = require("./config");
const { AntennaClient } = require("./antennaClient");
const { flushEvents } = require("./flushEvents");

async function startApp(eventQueue) {
    try {
        const host = process.env.AIS_HOST || "localhost";
        const port = parseInt(process.env.AIS_PORT) || 4001;
        
        console.log(`Starting AIS decoder with configuration: ${host}:${port}`);

        let antennaClient = new AntennaClient(host, port, eventQueue);
        antennaClient.start();

        setInterval(() => flushEvents(eventQueue), FLUSH_INTERVAL);

    } catch (error) {
        console.error(`Error starting AIS decoder: ${error}`);
        process.exit(1);
    }
}

module.exports = { startApp };
