<script>
export default {
    props: ["mapInstance"],

    watch: {
        mapInstance(newVal) {
            if (newVal) {
                this.initializeLayer();
            }
        },
    },

    mounted() {
        if (this.mapInstance) {
            this.initializeLayer();
        }
    },

    methods: {
        initializeLayer() {
            // Remove source and layer if already exists
            if (this.mapInstance.getSource("searoutes")) {
                this.mapInstance.removeLayer("searoutes");
                this.mapInstance.removeSource("searoutes");
            }

            // Add source for searoutes
            this.mapInstance.addSource("searoutes", {
                type: "vector",
                url: `http://localhost:3000/searoutes`,
            });

            // Add MVT Tile Layer for searoutes with line styling
            this.mapInstance.addLayer({
                id: "searoutes",
                type: "line",
                source: "searoutes",
                "source-layer": "searoutes",
                paint: {
                    "line-color": "rgba(0, 0, 0, 0.5)",
                    "line-dasharray": [2, 2],
                    "line-width": 1,
                },
                layout: {
                    visibility: "none",
                },
            });
        },

        // Toggle visibility of the layer
        toggleVisibility() {
            if (this.mapInstance) {
                let visibility = this.mapInstance.getLayoutProperty(
                    "searoutes",
                    "visibility"
                );

                visibility = visibility == "visible" ? "none" : "visible";

                this.mapInstance.setLayoutProperty(
                    "searoutes",
                    "visibility",
                    visibility
                );
            }
        },
    },
};
</script>

<template>
    <div></div>
</template>
