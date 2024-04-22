<template>
  <div id="map" class="map"></div>
  <Ships v-if="!!ready" :map="map"></Ships>
  <ShipsList v-if="!!ready" style="z-index: 1000" :map="map"></ShipsList>
  <ShipsDetails v-if="!!ready"></ShipsDetails>
</template>
<script>
  
  // Import the necessary libraries
  import maplibregl from "maplibre-gl";
  import "maplibre-gl/dist/maplibre-gl.css";
  import BasemapsControl from "maplibre-gl-basemaps";
  import "maplibre-gl-basemaps/lib/basemaps.css";
  import configs from "~/helpers/configs";

  // Default map parameters
  const DEFAULT_MAP_CENTER = [0, 0];
  const DEFAULT_MAP_BEARING = 0;
  const DEFAULT_MAP_ZOOM = 1;
  const DEFAULT_MAP_PITCH = 0;

  // Initial view state for the map
  const INITIAL_VIEW_STATE = {
    latitude: DEFAULT_MAP_CENTER[1],
    longitude: DEFAULT_MAP_CENTER[0],
    zoom: DEFAULT_MAP_ZOOM,
    bearing: DEFAULT_MAP_BEARING,
    pitch: DEFAULT_MAP_PITCH,
    transitionDuration: "auto",
  };

  // Export the component
  export default {
    data() {
      return {
        ready: false,
        map: null,
        deck: null,
        currentViewState: { ...INITIAL_VIEW_STATE },
        basemaps: configs.getBaseMaps(),
      };
    },

    // When the component is mounted, create the map
    async mounted() {

      // Create a Mapbox GL map
      this.map = new maplibregl.Map({
        container: "map",
        style: {
          version: 8,
          sources: {},
          layers: [],
          glyphs: "https://fonts.openmaptiles.org/{fontstack}/{range}.pbf",
        },
        antialias: false,
        center: [this.currentViewState.longitude, this.currentViewState.latitude],
        zoom: this.currentViewState.zoom,
        bearing: this.currentViewState.bearing,
        pitch: this.currentViewState.pitch,
        maxPitch: 0,
      });

      // Add geolocate control to the map.
      this.map.addControl(
        new maplibregl.GeolocateControl({
          positionOptions: {
            enableHighAccuracy: true,
          },
          trackUserLocation: true,
        })
      );

      // Add the navigation control to the map
      this.map.addControl(new maplibregl.NavigationControl(), "top-right");

      // Add the basemap control to the map
      this.baseMapControl = new BasemapsControl({
        basemaps: this.basemaps,
        initialBasemap: "google_road",
        expandDirection: "bottom",
      });

      this.map.addControl(this.baseMapControl, "top-right");

      // Wait for the map to load
      while (!this.map.loaded()) {
        await new Promise((resolve) => setTimeout(resolve, 100));
      }

      nextTick(() => {

        this.ready = true;

        // Add the fullscreen control to the map
        this.map.addControl(new maplibregl.FullscreenControl(), "top-right");

        // if window resize, resize the map
        window.addEventListener("resize", () => {
          this.map.resize();
        });
        
      });
    },
  };
</script>
<style>
  .map {
    height: calc(100vh - 64px - 24px - 48px);
    width: 100%;
  }

  .maplibregl-ctrl-basemaps .basemap {
    width: 29px !important;
    height: 29px !important;
    margin: 0px !important;
    margin-left: 4px !important;
    border-radius: 5px !important;
    border: 1px solid white !important;
    box-shadow: 0 0 0 2px rgba(0, 0, 0, 0.1);
    padding: 1px;
    background: white;
  }

  .maplibregl-ctrl-basemaps .basemap.active {
    border: 1px solid white !important;
    box-shadow: 0 0 0 2px rgba(0, 0, 0, 0.1);
  }
</style>