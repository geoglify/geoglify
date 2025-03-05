<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Amp\Websocket\Client\WebsocketHandshake;
use function Amp\Websocket\Client\connect;
use App\Jobs\StoreAisData;
use DateTime;
use Carbon\Carbon;

class AisWebSocketClient extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ais:websocket-client';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Listen to the AIS WebSocket stream and process messages in real-time';

    /**
     * Array to accumulate ship data.
     *
     * @var array
     */
    private $shipsData = [];

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info("Connecting to AIS WebSocket...");

        // Initial handshake configuration
        $handshake = (new WebsocketHandshake(env('AISSTREAM_WS_URL')))
            ->withHeader('Sec-WebSocket-Protocol', 'dumb-increment-protocol');

        // Connect to the WebSocket server
        $connection = connect($handshake);

        // Create the payload
        $payload = json_encode([
            'Apikey' => env('AISSTREAM_WS_KEY'),
            'BoundingBoxes' => [
                [
                    [29.343875, -35.419922],
                    [45.690833, 6.394043],
                ],
            ],
        ]);

        // Send the payload to the server
        $connection->sendText($payload);

        // Variable to track the last time the job was dispatched
        $lastDispatchTime = Carbon::now();

        // Process incoming messages
        foreach ($connection as $message) {
            // Get the data from the message
            $data = $message->buffer();

            // Process the AIS message
            $this->processAisMessage($data);

            // Check if 5 seconds have passed since the last dispatch
            if ($lastDispatchTime->diffInSeconds(Carbon::now()) >= 1) {

                // Log the dispatch
                $this->info("AIS Data Dispatched: " . count($this->shipsData) . " ships");

                // Dispatch a job to store the accumulated AIS data
                StoreAisData::dispatch($this->shipsData);

                // Clear the accumulated data
                $this->shipsData = [];

                // Update the last dispatch time
                $lastDispatchTime = Carbon::now();
            }

            // Close the connection if the data is '100' (end of stream)
            if ($data === '100') {
                $connection->close();
                $this->info("Connection closed.");
                break;
            }
        }

        return 0; // Success
    }

    /**
     * Process the AIS message and accumulate ship data.
     *
     * @param string $msg The raw AIS message in JSON format.
     */
    private function processAisMessage(string $msg)
    {
        // Decode the JSON payload
        $aisMessage = json_decode($msg, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->error("Failed to decode JSON: " . json_last_error_msg());
            return;
        }

        // Extract relevant data from the AIS message
        $shipData = [
            'mmsi' => $aisMessage['MetaData']['MMSI'] ?? $aisMessage['Message']['PositionReport']['UserID'],
            'latitude' => $aisMessage['Message']['PositionReport']['Latitude'] ?? $aisMessage['MetaData']['latitude'],
            'longitude' => $aisMessage['Message']['PositionReport']['Longitude'] ?? $aisMessage['MetaData']['longitude'],
            'sog' => $aisMessage['Message']['PositionReport']['Sog'] ?? null, // Speed Over Ground
            'cog' => $aisMessage['Message']['PositionReport']['Cog'] ?? null, // Course Over Ground
            'hdg' => $aisMessage['Message']['PositionReport']['TrueHeading'] ?? null, // True Heading
            'name' => trim($aisMessage['MetaData']['ShipName'] ?? 'Unknown Ship'),
            'last_updated' => isset($aisMessage['MetaData']['time_utc'])
                ? (new DateTime($aisMessage['MetaData']['time_utc']))->format('Y-m-d H:i:s')
                : null,
            'dim_a' => $aisMessage['Message']['ShipStaticData']['Dimension']['A'] ?? null,
            'dim_b' => $aisMessage['Message']['ShipStaticData']['Dimension']['B'] ?? null,
            'dim_c' => $aisMessage['Message']['ShipStaticData']['Dimension']['C'] ?? null,
            'dim_d' => $aisMessage['Message']['ShipStaticData']['Dimension']['D'] ?? null,
            'imo' => $aisMessage['Message']['ShipStaticData']['ImoNumber'] ?? null,
            'destination' => trim($aisMessage['Message']['ShipStaticData']['Destination'] ?? "Unknown"),
            'cargo' => $aisMessage['Message']['ShipStaticData']['Type'] ?? null,
            'callsign' => $aisMessage['Message']['ShipStaticData']['CallSign'] ?? null,
            'draught' => $aisMessage['Message']['ShipStaticData']['MaximumStaticDraught'] ?? null,
            'eta' => isset($aisMessage['Message']['ShipStaticData']['Eta'])
                ? (new DateTime())
                ->setDate(
                    $aisMessage['Message']['ShipStaticData']['Eta']['Year'] ?? date('Y'),
                    $aisMessage['Message']['ShipStaticData']['Eta']['Month'] ?? 1,
                    $aisMessage['Message']['ShipStaticData']['Eta']['Day'] ?? 1
                )
                ->setTime(
                    $aisMessage['Message']['ShipStaticData']['Eta']['Hour'] ?? 0,
                    $aisMessage['Message']['ShipStaticData']['Eta']['Minute'] ?? 0,
                    0 // Segundos (definidos como 0 por padrão)
                )
                ->format('Y-m-d H:i:s')
                : null,
        ];

        // Add the ship data to the accumulated array
        $this->shipsData[] = $shipData;

        // Log the processed data
        //$this->info("AIS Message Processed: " . $shipData['mmsi']);
    }
}
