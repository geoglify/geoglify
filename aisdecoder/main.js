const net = require('net');
const readline = require('readline');
const { AisDecode, NmeaDecode } = require('ggencoder'); // Using ggencoder
const axios = require('axios');

// Configurations via environment variables
const HOST = process.env.AIS_ANTENNA_HOST || '153.44.253.27'; // AIS server host
const PORT = process.env.AIS_ANTENNA_PORT || 5631; // AIS server port
const API_URL = process.env.API_URL || 'http://localhost:8081/api/ais'; // API endpoint
const BATCH_SIZE = parseInt(process.env.BATCH_SIZE) || 100; // Batch size for sending data
const SEND_INTERVAL = parseInt(process.env.SEND_INTERVAL) || 1000; // Send interval in milliseconds (1 second)
const RETRY_TIMEOUT = 5000; // Retry timeout for reconnection

// State variables
const eventQueue = []; // Event queue for batch processing

// Class to handle AIS antenna connection
class AntennaClient {
    constructor(host, port) {
        // Source antenna
        this.antennaIp = host;
        this.antennaPort = port;
        this.retrying = false;
        this.session = {};
        this.client = new net.Socket();
        this.registerConnectionEvents();
    }

    // Connect to the AIS antenna
    connectAntenna() {
        return new Promise((resolve, reject) => {
            if (!this.retrying) {
                this.retrying = true;
                console.log('');
                console.log('Connecting to: ', this.antennaIp, this.antennaPort);
                this.client.connect(this.antennaPort, this.antennaIp);
                resolve();
            }
        });
    }

    // Handle socket connection
    handleSocketConnect() {
        console.log('Connected to AIS Source: ', this.antennaIp, this.antennaPort);
        this.retrying = false;

        // Create a line reader
        const lineReader = readline.createInterface({
            input: this.client,
            output: this.client,
            terminal: false,
        });

        // Process each incoming line (AIS message)
        lineReader.on('line', (message) => {
            this.decodeAndQueueMessage(message);
        });
    }

    // Decode and queue AIS messages
    decodeAndQueueMessage(rawMessage) {
        // Remove the initial metadata (e.g., \s:2573565,c:1737227486*06\)
        let aisPayload = rawMessage.split('\\')[2]; // Extract the part after the second backslash
        if (!aisPayload) return;

        // Replace text "!BSVDM" with "!AIVDM" to ensure proper decoding
        let decMsg = '';

        // Try decoding as an NMEA message
        decMsg = new NmeaDecode(aisPayload, this.session);
        if (decMsg.valid) {
            const shipData = this.decodeStreamMessage(decMsg);
            this.queueEvent(shipData);
            return;
        }

        // Decode the AIS payload
        decMsg = new AisDecode(aisPayload, this.session);
        if (decMsg.valid) {
            const shipData = this.decodeStreamMessage(decMsg);
            this.queueEvent(shipData);
            return;
        }
    }

    // Structure the decoded message into a ship data object
    decodeStreamMessage(message) {
        return {
            mmsi: message.mmsi?.toString() || null, // Ship's MMSI
            name: message.shipname || '', // Ship's name
            last_updated: new Date(), // Current timestamp
            location: {
                type: 'Point',
                coordinates: [message.lon, message.lat], // Longitude and Latitude
            },
            cog: message.cog || null, // Course Over Ground
            sog: message.sog || null, // Speed Over Ground
            hdg: message.hdg === 511 ? null : message.hdg, // Heading (511 means "not available")
            dim_a: message.dimA || null, // Dimension A
            dim_b: message.dimB || null, // Dimension B
            dim_c: message.dimC || null, // Dimension C
            dim_d: message.dimD || null, // Dimension D
            imo: message.imo || null, // IMO number
            destination: message.destination || null, // Destination
            cargo: message.cargo || null, // Cargo type
            callsign: message.callsign || null, // Call sign
            draught: message.draught || null, // Draught
            eta: null, // Estimated Time of Arrival
            navstatus: message.navstatus || null, // Navigation status
            rot: message.rot || null, // Rate of Turn
            length: message.length || null, // Ship length
            width: message.width || null, // Ship width
            aistype: message.aistype || null, // AIS message type
            repeat: message.repeat || null, // Repeat indicator
            channel: message.channel || null, // Communication channel
            utc: message.utc || null, // UTC timestamp
            smi: message.smi || null, // Ship and Mobile Identifier
        };
    }

    // Add an event to the queue, updating existing events for the same MMSI
    queueEvent(event) {
        const existingEventIndex = eventQueue.findIndex((e) => e.mmsi === event.mmsi);

        if (existingEventIndex !== -1) {
            // Merge the existing event with the new event
            eventQueue[existingEventIndex] = {
                ...eventQueue[existingEventIndex], // Keep existing data
                ...event, // Overwrite with new data
                last_updated: new Date(), // Update the timestamp
            };
        } else {
            // Add new event if it doesn't exist in the queue
            eventQueue.push(event);
        }
    }

    // Handle socket close event
    handleSocketClose() {
        console.log('Connection closed: ', this.antennaIp, this.antennaPort);
        this.retrying = false;
        setTimeout(() => {
            this.connectAntenna();
        }, RETRY_TIMEOUT);
    }

    // Handle socket end event
    handleSocketEnd() {
        console.log('Connection ended: ', this.antennaIp, this.antennaPort);
        this.retrying = false;
        setTimeout(() => {
            this.connectAntenna();
        }, RETRY_TIMEOUT);
    }

    // Handle socket timeout event
    handleSocketTimeout() {
        console.log('Connection timeout: ', this.antennaIp, this.antennaPort);
        this.retrying = false;
        if (this.client) {
            this.client.end();
        }
    }

    // Handle socket error event
    handleSocketError(err) {
        console.error('Connection error: ', err.message);
        this.retrying = false;
        if (this.client) {
            this.client.destroy(); // Destroy the socket to ensure cleanup
        }
        setTimeout(() => {
            this.connectAntenna();
        }, RETRY_TIMEOUT);
    }

    // Register socket events
    registerConnectionEvents() {
        if (this.client) {
            this.client.off('connect', this.handleSocketConnect.bind(this));
            this.client.on('connect', this.handleSocketConnect.bind(this));

            this.client.off('close', this.handleSocketClose.bind(this));
            this.client.on('close', this.handleSocketClose.bind(this));

            this.client.off('end', this.handleSocketEnd.bind(this));
            this.client.on('end', this.handleSocketEnd.bind(this));

            this.client.off('timeout', this.handleSocketTimeout.bind(this));
            this.client.on('timeout', this.handleSocketTimeout.bind(this));

            this.client.off('error', this.handleSocketError.bind(this));
            this.client.on('error', this.handleSocketError.bind(this));
        }
    }

    // Start the antenna client
    async start() {
        await this.connectAntenna();
    }
}

// Function to send data to the API in batches
async function flushEvents() {
    if (eventQueue.length === 0) return;

    const chunk = eventQueue.slice(0, BATCH_SIZE); // Extract a batch from the queue

    try {
        console.info(`Sending ${chunk.length} of ${eventQueue.length} ships to the API`);

        const response = await axios.post(API_URL, chunk, {
            headers: { 'Content-Type': 'application/json' },
        });

        if (response.status === 202) {
            console.info('Ships sent successfully to the API');
        }

        // Remove the sent chunk from the queue only if the API request is successful
        eventQueue.splice(0, chunk.length);
    } catch (error) {
        console.error(`Error sending ships to the API ${API_URL}: ${error}`);
        // Do not clear the queue on failure; retry in the next interval
    }
}

// Configure periodic sending of data
setInterval(flushEvents, SEND_INTERVAL);

// Example usage
const antennaClient = new AntennaClient(HOST, PORT);
antennaClient.start();
