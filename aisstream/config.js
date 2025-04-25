module.exports = {
    AISSTREAM_API_KEY: process.env.AISSTREAM_API_KEY || "7fb1e16f93a4d520d83a95e325c55e69b3b4fc0b",
    API_URL: process.env.API_URL || "http://localhost/api/ships",
    BATCH_SIZE: process.env.BATCH_SIZE || 100, // Number of ships to send in one batch
    FLUSH_INTERVAL: process.env.FLUSH_INTERVAL || 5000, // Time in milliseconds to wait before sending a batch
};
