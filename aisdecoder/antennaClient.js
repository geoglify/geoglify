const net = require("net");
const readline = require("readline");
const { AisDecode, NmeaDecode } = require("ggencoder");

class AntennaClient {
  constructor(host, port, eventQueue) {
    this.antennaIp = host;
    this.antennaPort = port;
    this.eventQueue = eventQueue;
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
        console.log("\nConnecting to:", this.antennaIp, this.antennaPort);
        this.client.connect(this.antennaPort, this.antennaIp, () => {
          resolve();
        });
      }
    });
  }

  reconnect(newHost, newPort) {
    console.log(`Reconnecting to new host: ${newHost}:${newPort}`);
    this.antennaIp = newHost;
    this.antennaPort = newPort;

    // Limpa o socket atual completamente
    if (this.client) {
      this.client.removeAllListeners();
      this.client.destroy();
    }

    // Cria novo socket
    this.client = new net.Socket();
    this.registerConnectionEvents();
    this.start();
  }

  handleSocketConnect() {
    console.log("Connected to AIS Source:", this.antennaIp, this.antennaPort);
    this.retrying = false;

    const lineReader = readline.createInterface({
      input: this.client,
      output: this.client,
      terminal: false,
    });

    lineReader.on("line", (message) => {
      this.decodeAndQueueMessage(message);
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
      mmsi: parseInt(message.mmsi) || null,
      name: message.shipname || null,
      timestamp: new Date().toISOString(),
      latitude: message.lat || null,
      longitude: message.lon || null,
      cog: message.cog || null,
      sog: message.sog || null,
      heading: message.hdg === 511 ? null : message.hdg,
      nav_status: message.navstatus || null,
      dim_a: message.dimA || null,
      dim_b: message.dimB || null,
      dim_c: message.dimC || null,
      dim_d: message.dimD || null,
      imo: message.imo || null,
      call_sign: message.callsign || null,
      ais_type: message.aistype || null,
      destination: message.destination || null,
      cargo: message.cargo || null,
      draught: message.draught || null,
      rot: message.rot || null,
      length: message.length || null,
      width: message.width || null,
      repeat: message.repeat || null,
      channel: message.channel || null,
      utc: message.utc || null,
      smi: message.smi || null,
    };
  }

  queueEvent(event) {
    if (!event.mmsi) {
      return; // MMSI é sempre obrigatório
    }

    // Eventos de posição (com coordenadas) - para ship_positions
    if (event.latitude && event.longitude) {
      this.queuePositionEvent(event);
    }

    // Eventos de informação (sem coordenadas, mas com outros dados úteis) - para ship_info
    if (this.hasRelevantShipInfo(event)) {
      this.queueInfoEvent(event);
    }
  }

  queuePositionEvent(event) {
    const existingIndex = this.eventQueue.findIndex(
      (e) => e.mmsi === event.mmsi && e.type === "position"
    );

    const positionEvent = {
      ...event,
      type: "position",
      timestamp: new Date().toISOString(),
    };

    if (existingIndex !== -1) {
      this.eventQueue[existingIndex] = positionEvent;
    } else {
      this.eventQueue.push(positionEvent);
    }
  }

  queueInfoEvent(event) {
    // Para info, sempre adiciona novo registro (não sobrescreve)
    const infoEvent = {
      ...event,
      type: "info",
      timestamp: new Date().toISOString(),
    };

    this.eventQueue.push(infoEvent);
  }

  hasRelevantShipInfo(event) {
    // Verifica se tem dados informativos relevantes
    return !!(
      event.name ||
      event.imo ||
      event.call_sign ||
      event.destination ||
      event.draught ||
      event.cargo ||
      event.ais_type ||
      event.dim_a ||
      event.dim_b ||
      event.dim_c ||
      event.dim_d ||
      event.length ||
      event.width
    );
  }

  handleSocketClose() {
    console.log("Connection closed:", this.antennaIp, this.antennaPort);
    this.retrying = false;

    // Limpa listeners antes de tentar reconectar
    if (this.client) {
      this.client.removeAllListeners();
    }

    setTimeout(() => {
      this.client = new net.Socket();
      this.registerConnectionEvents();
      this.connectAntenna();
    }, 5000);
  }

  handleSocketEnd() {
    console.log("Connection ended:", this.antennaIp, this.antennaPort);
    this.retrying = false;

    // Limpa listeners antes de tentar reconectar
    if (this.client) {
      this.client.removeAllListeners();
    }

    setTimeout(() => {
      this.client = new net.Socket();
      this.registerConnectionEvents();
      this.connectAntenna();
    }, 5000);
  }

  handleSocketTimeout() {
    console.log("Connection timeout:", this.antennaIp, this.antennaPort);
    this.retrying = false;

    if (this.client) {
      this.client.removeAllListeners();
      this.client.end();
    }
  }

  handleSocketError(err) {
    console.error("Connection error:", err.message);
    this.retrying = false;

    if (this.client) {
      this.client.removeAllListeners();
      this.client.destroy();
    }

    setTimeout(() => {
      this.client = new net.Socket();
      this.registerConnectionEvents();
      this.connectAntenna();
    }, 5000);
  }

  registerConnectionEvents() {
    // Remove todos os listeners existentes antes de adicionar novos
    this.client.removeAllListeners();

    this.client.on("connect", this.handleSocketConnect.bind(this));
    this.client.on("close", this.handleSocketClose.bind(this));
    this.client.on("end", this.handleSocketEnd.bind(this));
    this.client.on("timeout", this.handleSocketTimeout.bind(this));
    this.client.on("error", this.handleSocketError.bind(this));
  }
}

module.exports = { AntennaClient };
