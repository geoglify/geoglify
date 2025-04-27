module.exports = {
    AISSTREAM_API_KEY: process.env.AISSTREAM_API_KEY || "ecee1eee9dd77a3f63c44820715217c00f7f3358",
    API_URL: process.env.API_URL || "http://localhost:8081/api",
    BATCH_SIZE: process.env.BATCH_SIZE || 100, // Number of ships to send in one batch
    FLUSH_INTERVAL: process.env.FLUSH_INTERVAL || 5000, // Time in milliseconds to wait before sending a batch
};
