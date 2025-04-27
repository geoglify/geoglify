const { startApp } = require("./startApp");
const { startSseServer } = require("./sseServer");

const eventQueue = [];
const clients = [];

startSseServer(clients);
startApp(eventQueue, clients);
