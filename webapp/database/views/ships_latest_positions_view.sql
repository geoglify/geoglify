DROP VIEW IF EXISTS ships_latest_positions_view;

CREATE VIEW ships_latest_positions_view AS
WITH latest_positions AS (
    SELECT 
        ship_id,
        updated_at,
        latitude,
        longitude,
        eta,
        destination,
        cog,
        sog,
        hdg,
        last_updated
    FROM (
        SELECT 
            ship_id,
            updated_at,
            latitude,
            longitude,
            eta,
            destination,
            cog,
            sog,
            hdg,
            last_updated,
            ROW_NUMBER() OVER (PARTITION BY ship_id ORDER BY updated_at DESC) AS rn
        FROM ship_realtime_positions
        WHERE latitude IS NOT NULL
          AND longitude IS NOT NULL
    ) subquery
    WHERE rn = 1
)
SELECT 
    LEFT(ships.mmsi::text, 3) AS country_code, -- PostgreSQL
    ships.id,
    ships.mmsi,
    ships.name,
    ships.callsign,
    ships.imo,
    ships.dim_a,
    ships.dim_b,
    ships.dim_c,
    ships.dim_d,
    cargo_types.code AS cargo_type_code,
    cargo_types.name AS cargo_type_name,
    cargo_categories.name AS cargo_category_name,
    cargo_categories.color AS cargo_category_color,
    cargo_categories.priority AS cargo_category_priority,
    ships.draught,
    lp.cog,
    lp.sog,
    lp.hdg,
    lp.last_updated,
    lp.eta,
    lp.destination,
    lp.latitude,
    lp.longitude,
    lp.updated_at AS position_updated_at,
    countries.iso_code AS country_iso_code,
    countries.name AS country_name
FROM ships
INNER JOIN latest_positions lp ON ships.id = lp.ship_id
LEFT JOIN cargo_types ON ships.cargo_type_id = cargo_types.id
LEFT JOIN cargo_categories ON cargo_types.cargo_category_id = cargo_categories.id
LEFT JOIN countries ON LEFT(ships.mmsi::text, 3) = countries.number::text;
