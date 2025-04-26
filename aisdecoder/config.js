module.exports = {
    API_URL: process.env.API_URL || "http://localhost:8081/api",
    BATCH_SIZE: process.env.BATCH_SIZE || 100, // Number of ships to send in one batch
    FLUSH_INTERVAL: process.env.FLUSH_INTERVAL || 5000, // Time in milliseconds to wait before sending a batch
};
