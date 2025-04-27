const WebSocket = require('ws');
const { AISSTREAM_API_KEY } = require('./config');

class AisStreamClient {
    constructor(eventQueue) {
        this.socket = null;
        this.eventQueue = eventQueue;
        this.retrying = false;
    }

    decodeStreamMessage(message) {
        return {
            mmsi: message.MetaData.MMSI.toString(),
            name: message.MetaData.ShipName.trim(),
            last_updated: new Date(message.MetaData.time_utc),
            latitude: message.MetaData.latitude,
            longitude: message.MetaData.longitude,
            cog: message?.Message?.PositionReport?.Cog || message?.Message?.StandardClassBPositionReport?.Cog,
            sog: message?.Message?.PositionReport?.Sog || message?.Message?.StandardClassBPositionReport?.Sog,
            hdg: message?.Message?.PositionReport?.TrueHeading || message?.Message?.StandardClassBPositionReport?.TrueHeading,
            dim_a: message?.Message?.ShipStaticData?.Dimension?.A,
            dim_b: message?.Message?.ShipStaticData?.Dimension?.B,
            dim_c: message?.Message?.ShipStaticData?.Dimension?.C,
            dim_d: message?.Message?.ShipStaticData?.Dimension?.D,
            imo: message?.Message?.ShipStaticData?.ImoNumber,
            destination: message?.Message?.ShipStaticData?.Destination,
            cargo: message?.Message?.ShipStaticData?.Type,
            callsign: message?.Message?.ShipStaticData?.CallSign,
            draught: message?.Message?.ShipStaticData?.MaximumStaticDraught,
            eta: message?.Message?.ShipStaticData?.Eta
                ? new Date(
                    message.Message.ShipStaticData.Eta.Year ?? new Date().getFullYear(),
                    message.Message.ShipStaticData.Eta.Month,
                    message.Message.ShipStaticData.Eta.Day,
                    message.Message.ShipStaticData.Eta.Hour,
                    message.Message.ShipStaticData.Eta.Minute
                )
                : null,
        };
    }

    start() {
        this.connectToAisStreamWithRetry();
    }

    connectToAisStreamWithRetry() {
        if (this.retrying) return;
        
        try {
            console.log("Connecting to AIS stream...");
            this.retrying = true;
            this.socket = new WebSocket("wss://stream.aisstream.io/v0/stream");

            this.socket.onopen = () => {
                console.log("Connected to AIS stream");
                this.sendSubscriptionMessage();
            };

            this.socket.onclose = () => {
                console.error("WebSocket closed, retrying...");
                this.retrying = false;
                setTimeout(() => this.connectToAisStreamWithRetry(), 5000);
            };

            this.socket.onerror = (err) => {
                console.error("WebSocket error:", err.message);
            };

            this.socket.onmessage = (event) => {
                const aisMessage = JSON.parse(event.data);
                if (aisMessage.error) {
                    console.error("AIS stream error:", aisMessage.error);
                    return;
                }

                const shipData = this.decodeStreamMessage(aisMessage);
                this.queueEvent(shipData);
            };
        } catch (err) {
            console.error("Failed to connect to AIS stream, retrying...");
            this.retrying = false;
            setTimeout(() => this.connectToAisStreamWithRetry(), 5000);
        }
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

    sendSubscriptionMessage() {
        const subscriptionMessage = {
            Apikey: AISSTREAM_API_KEY,
            BoundingBoxes: [
                [
                    [-90, -180],
                    [90, 180],
                ],
            ],
        };
        this.socket.send(JSON.stringify(subscriptionMessage));
    }
}

module.exports = AisStreamClient;
