module.exports = {
    API_URL: process.env.API_URL || "http://localhost:8081/api", // API URL to send the data to
    BATCH_SIZE: process.env.BATCH_SIZE || 1000, // Number of ships to send in one batch
    FLUSH_INTERVAL: process.env.FLUSH_INTERVAL || 1000, // Time in milliseconds to wait before sending a batch
    REDIS_HOST: process.env.REDIS_HOST || "localhost", // Redis host
    REDIS_PORT: process.env.REDIS_PORT || 6379, // Redis port
    CONFIG_CHECK_INTERVAL: process.env.CONFIG_CHECK_INTERVAL || 10000, // Time in milliseconds to check for configuration changes
};
