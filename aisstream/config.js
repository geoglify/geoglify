module.exports = {
    AISSTREAM_API_KEY: process.env.AISSTREAM_API_KEY || "7fb1e16f93a4d520d83a95e325c55e69b3b4fc0b",
    API_URL: process.env.API_URL || "http://localhost/api/ships",
    BATCH_SIZE: 100,        // Number of events per chunk to send to Laravel
    FLUSH_INTERVAL: 2000,  // Time interval (in ms) for flushing ships to api
};
