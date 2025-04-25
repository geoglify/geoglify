<script setup lang="ts">
import 'maplibre-theme/classic.css';
import 'maplibre-theme/icons.lucide.css';
import MapHelper from '../../helpers/map';
import AisLayer from './AisLayer.vue';
import { Badge } from '@/components/ui/badge';
import { Ship } from 'lucide-vue-next';
import { onMounted, ref } from 'vue';
import { usePage } from '@inertiajs/vue3';

const props = defineProps<{
  ships: Array<any>;
}>();

const page = usePage();

const mapSettings = page.props.map as {
  default_latitude: number;
  default_longitude: number;
  default_zoom: number;
  default_bearing: number;
  default_style: string;
};

const map = ref<maplibregl.Map | null>(null);
const mapIsReady = ref(false);

onMounted(() => {
  if (!map.value) {
    const isDarkMode = localStorage.getItem('appearance') === 'dark';
    map.value = MapHelper.createMap('map', mapSettings, isDarkMode);

    map.value.on('load', () => {
      mapIsReady.value = true;
    });
  }
});
</script>

<template>
    <div id="map" class="rounded-br-lg rounded-bl-lg"></div>
  
    <div class="absolute top-0 right-0 z-10 p-4">
      <Badge>
        <Ship class="mr-2 h-4 w-4" />
        <span>{{ props.ships.length }} Ships</span>
      </Badge>
    </div>
  
    <AisLayer :mapInstance="map" :data="props.ships" v-if="mapIsReady" />
</template>

<style>
#map {
    height: 100%;
    width: 100%;
    background-color: #000;
}

.maplibregl-map {
    --ml-font-icons: maplibregl-icons-lucide;
}
</style>
