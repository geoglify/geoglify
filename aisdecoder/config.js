module.exports = {
    API_URL: process.env.API_URL || "http://localhost:8000/api", // Laravel API URL
    BATCH_SIZE: process.env.BATCH_SIZE || 100, // Number of positions to send in one batch
    FLUSH_INTERVAL: process.env.FLUSH_INTERVAL || 5000, // Time in milliseconds to wait before sending a batch
};
