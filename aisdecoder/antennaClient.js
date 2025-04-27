const net = require("net");
const readline = require("readline");
const { AisDecode, NmeaDecode } = require("ggencoder");

class AntennaClient {
    constructor(host, port, eventQueue, clients) {
        this.antennaIp = host;
        this.antennaPort = port;
        this.eventQueue = eventQueue;
        this.clients = clients;
        this.retrying = false;
        this.session = {};
        this.client = new net.Socket();
        this.registerConnectionEvents();
    }

    async start() {
        await this.connectAntenna();
    }

    connectAntenna() {
        return new Promise((resolve) => {
            if (!this.retrying) {
                this.retrying = true;
                console.log(
                    "\nConnecting to:",
                    this.antennaIp,
                    this.antennaPort
                );
                this.client.connect(this.antennaPort, this.antennaIp);
                resolve();
            }
        });
    }

    handleSocketConnect() {
        console.log(
            "Connected to AIS Source:",
            this.antennaIp,
            this.antennaPort
        );
        this.retrying = false;

        const lineReader = readline.createInterface({
            input: this.client,
            output: this.client,
            terminal: false,
        });

        lineReader.on("line", (message) => {
            this.decodeAndQueueMessage(message);

            this.clients.forEach((client) => {
                client.write(`data: ${message}\n\n`);
            });
        });
    }

    decodeAndQueueMessage(rawMessage) {
        let aisPayload = rawMessage.split("\\")[2];
        if (!aisPayload) return;

        let decMsg = new NmeaDecode(aisPayload, this.session);
        if (decMsg.valid) {
            this.queueEvent(this.decodeStreamMessage(decMsg));
            return;
        }

        decMsg = new AisDecode(aisPayload, this.session);
        if (decMsg.valid) {
            this.queueEvent(this.decodeStreamMessage(decMsg));
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
        const existingIndex = this.eventQueue.findIndex(
            (e) => e.mmsi === event.mmsi
        );
        if (existingIndex !== -1) {
            this.eventQueue[existingIndex] = {
                ...this.eventQueue[existingIndex],
                ...event,
                last_updated: new Date(),
            };
        } else {
            this.eventQueue.push(event);
        }
    }

    handleSocketClose() {
        console.log("Connection closed:", this.antennaIp, this.antennaPort);
        this.retrying = false;
        setTimeout(() => this.connectAntenna(), 5000);
    }

    handleSocketEnd() {
        console.log("Connection ended:", this.antennaIp, this.antennaPort);
        this.retrying = false;
        setTimeout(() => this.connectAntenna(), 5000);
    }

    handleSocketTimeout() {
        console.log("Connection timeout:", this.antennaIp, this.antennaPort);
        this.retrying = false;
        if (this.client) this.client.end();
    }

    handleSocketError(err) {
        console.error("Connection error:", err.message);
        this.retrying = false;
        if (this.client) this.client.destroy();
        setTimeout(() => this.connectAntenna(), 5000);
    }

    registerConnectionEvents() {
        this.client.removeAllListeners();
        this.client.on("connect", this.handleSocketConnect.bind(this));
        this.client.on("close", this.handleSocketClose.bind(this));
        this.client.on("end", this.handleSocketEnd.bind(this));
        this.client.on("timeout", this.handleSocketTimeout.bind(this));
        this.client.on("error", this.handleSocketError.bind(this));
    }
}

module.exports = { AntennaClient };
