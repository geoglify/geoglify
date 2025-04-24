const { queueEvent } = require('./queueManager');

// Decode AIS message and structure it into a ship data object
function decodeStreamMessage(message) {

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

// Process AIS message and add it to the queue
function processAisMessage(message) {
    const shipData = decodeStreamMessage(message);
    queueEvent(shipData); // Add decoded message to the event queue
}

module.exports = { processAisMessage };
