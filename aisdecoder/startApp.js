const { createClient } = require("redis");
const { REDIS_HOST, REDIS_PORT, FLUSH_INTERVAL } = require("./config");
const { AntennaClient } = require("./antennaClient");
const { flushEvents } = require("./flushEvents");

const redisClient = createClient({
    socket: { host: REDIS_HOST, port: REDIS_PORT },
});

async function startApp(eventQueue, clients) {
    try {
        await redisClient.connect();
        console.log("Connected to Redis");

        const host = await redisClient.get("geoglify_database_ais_host");
        const port = parseInt(await redisClient.get("geoglify_database_ais_port"), 10);

        if (!host || !port) throw new Error("Invalid configuration received from Redis");

        const antennaClient = new AntennaClient(host, port, eventQueue, clients);
        antennaClient.start();

        setInterval(() => flushEvents(eventQueue), FLUSH_INTERVAL);
    } catch (error) {
        console.error(`Error fetching configuration from Redis: ${error}`);
        process.exit(1);
    }
}

module.exports = { startApp };
