const net = require("net");
const http = require("http");
const readline = require("readline");
const { AisDecode, NmeaDecode } = require("ggencoder");
const axios = require("axios");
const { BATCH_SIZE, FLUSH_INTERVAL, API_URL } = require("./config");

const RETRY_TIMEOUT = 5000;
const eventQueue = [];
const clients = [];

class AntennaClient {
    constructor(host, port) {
        this.antennaIp = host;
        this.antennaPort = port;
        this.retrying = false;
        this.session = {};
        this.client = new net.Socket();
        this.registerConnectionEvents();
    }

    connectAntenna() {
        return new Promise((resolve) => {
            if (!this.retrying) {
                this.retrying = true;
                console.log("\nConnecting to: ", this.antennaIp, this.antennaPort);
                this.client.connect(this.antennaPort, this.antennaIp);
                resolve();
            }
        });
    }

    handleSocketConnect() {
        console.log("Connected to AIS Source: ", this.antennaIp, this.antennaPort);
        this.retrying = false;

        const lineReader = readline.createInterface({
            input: this.client,
            output: this.client,
            terminal: false,
        });

        lineReader.on("line", (message) => {
            this.decodeAndQueueMessage(message);
            
            clients.forEach((client) => {
                client.write(`data: ${message}\n\n`);
            });
        });
    }

    decodeAndQueueMessage(rawMessage) {
        let aisPayload = rawMessage.split("\\")[2];
        if (!aisPayload) return;

        let decMsg = new NmeaDecode(aisPayload, this.session);
        if (decMsg.valid) {
            const shipData = this.decodeStreamMessage(decMsg);
            this.queueEvent(shipData);
            return;
        }

        decMsg = new AisDecode(aisPayload, this.session);
        if (decMsg.valid) {
            const shipData = this.decodeStreamMessage(decMsg);
            this.queueEvent(shipData);
        }
    }

    decodeStreamMessage(message) {
        return {
            mmsi: message.mmsi?.toString() || null,
            name: message.shipname || "",
            last_updated: new Date(),
            latitude: message.lat || null,
            longitude: message.lon || null,
            cog: message.cog || null,
            sog: message.sog || null,
            hdg: message.hdg === 511 ? null : message.hdg,
            dim_a: message.dimA || null,
            dim_b: message.dimB || null,
            dim_c: message.dimC || null,
            dim_d: message.dimD || null,
            imo: message.imo || null,
            destination: message.destination || null,
            cargo: message.cargo || null,
            callsign: message.callsign || null,
            draught: message.draught || null,
            eta: null,
            navstatus: message.navstatus || null,
            rot: message.rot || null,
            length: message.length || null,
            width: message.width || null,
            aistype: message.aistype || null,
            repeat: message.repeat || null,
            channel: message.channel || null,
            utc: message.utc || null,
            smi: message.smi || null,
        };
    }

    queueEvent(event) {
        const existingIndex = eventQueue.findIndex((e) => e.mmsi === event.mmsi);
        if (existingIndex !== -1) {
            eventQueue[existingIndex] = {
                ...eventQueue[existingIndex],
                ...event,
                last_updated: new Date(),
            };
        } else {
            eventQueue.push(event);
        }
    }

    handleSocketClose() {
        console.log("Connection closed: ", this.antennaIp, this.antennaPort);
        this.retrying = false;
        setTimeout(() => this.connectAntenna(), RETRY_TIMEOUT);
    }

    handleSocketEnd() {
        console.log("Connection ended: ", this.antennaIp, this.antennaPort);
        this.retrying = false;
        setTimeout(() => this.connectAntenna(), RETRY_TIMEOUT);
    }

    handleSocketTimeout() {
        console.log("Connection timeout: ", this.antennaIp, this.antennaPort);
        this.retrying = false;
        if (this.client) this.client.end();
    }

    handleSocketError(err) {
        console.error("Connection error: ", err.message);
        this.retrying = false;
        if (this.client) this.client.destroy();
        setTimeout(() => this.connectAntenna(), RETRY_TIMEOUT);
    }

    registerConnectionEvents() {
        this.client.removeAllListeners();
        this.client.on("connect", this.handleSocketConnect.bind(this));
        this.client.on("close", this.handleSocketClose.bind(this));
        this.client.on("end", this.handleSocketEnd.bind(this));
        this.client.on("timeout", this.handleSocketTimeout.bind(this));
        this.client.on("error", this.handleSocketError.bind(this));
    }

    async start() {
        await this.connectAntenna();
    }
}

async function flushEvents() {
    if (eventQueue.length === 0) return;
    const chunk = eventQueue.slice(0, BATCH_SIZE);

    try {
        console.info(`Sending ${chunk.length} of ${eventQueue.length} ships to the API`);

        const response = await axios.post(`${API_URL}/ais`, chunk, {
            headers: { "Content-Type": "application/json" },
        });

        if (response.status === 202) {
            console.info("Ships sent successfully to the API");
        }

        eventQueue.splice(0, chunk.length);
    } catch (error) {
        console.error(`Error sending ships to the API ${API_URL}/ais: ${error}`);
    }
}

setInterval(flushEvents, FLUSH_INTERVAL);

const sseServer = http.createServer((req, res) => {
    if (req.url === "/stream") {
        res.writeHead(200, {
            "Content-Type": "text/event-stream",
            "Cache-Control": "no-cache",
            Connection: "keep-alive",
            "Access-Control-Allow-Origin": "*",
        });

        res.write("\n");
        clients.push(res);

        req.on("close", () => {
            const idx = clients.indexOf(res);
            if (idx !== -1) clients.splice(idx, 1);
        });
    } else {
        res.writeHead(404);
        res.end();
    }
});

sseServer.listen(9002, () => {
    console.log("SSE server listening on http://localhost:9002/stream");
});

async function startApp() {
    try {
        const { data } = await axios.get(`${API_URL}/ais-configuration`, {
            headers: { "Content-Type": "application/json" },
        });

        const host = data.ais_host;
        const port = parseInt(data.ais_port, 10);

        if (!host || !port) throw new Error("Invalid configuration received");

        const antennaClient = new AntennaClient(host, port);
        antennaClient.start();
    } catch (error) {
        console.error(`Error fetching configuration from ${API_URL}/ais-configuration: ${error}`);
        process.exit(1);
    }
}

startApp();
