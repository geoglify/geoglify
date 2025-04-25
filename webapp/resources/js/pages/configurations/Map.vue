<script setup lang="ts">
import { TransitionRoot } from '@headlessui/vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, watch, onMounted, onUnmounted } from 'vue';
import maplibregl from 'maplibre-gl';
import 'maplibre-gl/dist/maplibre-gl.css';

import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import ConfigurationsLayout from '@/layouts/configurations/Layout.vue';
import { type BreadcrumbItem } from '@/types';

interface Props {
    default_latitude: number;
    default_longitude: number;
    default_zoom: number;
    default_bearing: number;
    default_style: string;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Map settings',
        href: '/configurations/map-settings',
    },
];

const form = useForm({
    default_latitude: props.default_latitude,
    default_longitude: props.default_longitude,
    default_zoom: props.default_zoom,
    default_bearing: props.default_bearing,
    default_style: props.default_style,
});

const submit = () => {
    form.patch(route('map.update'), {
        preserveScroll: true,
    });
};

// Map
const mapContainer = ref<HTMLElement | null>(null);
let map: maplibregl.Map | null = null;

// Init Map
const initializeMap = () => {
    if (mapContainer.value && !map) {
        map = new maplibregl.Map({
            container: mapContainer.value,
            style: form.default_style,
            center: [form.default_longitude, form.default_latitude],
            zoom: form.default_zoom,
            bearing: form.default_bearing,
            attributionControl: false,
        });
    }
};

// Update map when form values change
watch(() => [form.default_latitude, form.default_longitude, form.default_zoom, form.default_bearing, form.default_style], () => {
    if (map) {
        map.setCenter([form.default_longitude, form.default_latitude]);
        map.setZoom(form.default_zoom);
        map.setBearing(form.default_bearing);
        
        if (map.getStyle().name !== form.default_style) {
            map.setStyle(form.default_style);
        }
    }
}, { deep: true });

// Update map on window resize
const handleResize = () => {
    if (map) {
        map.resize();
    }
};

// Init map on component mount
onMounted(() => {
    initializeMap();
});

// Remove map on component unmount
onUnmounted(() => {
    if (map) {
        map.remove();
        map = null;
    }
    window.removeEventListener('resize', handleResize);
});

window.addEventListener('resize', handleResize);

</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Map settings" />

        <ConfigurationsLayout>
            <div class="flex flex-col lg:flex-row gap-6">
                <div class="w-full lg:w-1/2 space-y-6">
                    <HeadingSmall title="Map settings" description="Configure the default map settings for your application" />

                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="grid gap-2">
                            <Label for="default_latitude">Latitude</Label>
                            <Input id="default_latitude" class="mt-1 block w-full" v-model="form.default_latitude" required autocomplete="default_latitude" placeholder="Default Latitude" />
                            <InputError class="mt-2" :message="form.errors.default_latitude" />
                        </div>
                        
                        <div class="grid gap-2">
                            <Label for="default_longitude">Longitude</Label>
                            <Input id="default_longitude" class="mt-1 block w-full" v-model="form.default_longitude" required autocomplete="default_longitude" placeholder="Default Longitude" />
                            <InputError class="mt-2" :message="form.errors.default_longitude" />
                        </div>
                        
                        <div class="grid gap-2">
                            <Label for="default_zoom">Zoom</Label>
                            <Input id="default_zoom" type="number" class="mt-1 block w-full" v-model="form.default_zoom" required autocomplete="default_zoom" placeholder="Default Zoom" />
                            <InputError class="mt-2" :message="form.errors.default_zoom" />   
                        </div>
                        
                        <div class="grid gap-2">
                            <Label for="default_bearing">Bearing</Label>
                            <Input id="default_bearing" type="number" class="mt-1 block w-full" v-model="form.default_bearing" required autocomplete="default_bearing" placeholder="Default Bearing" />
                            <InputError class="mt-2" :message="form.errors.default_bearing" />
                        </div>
                        
                        <div class="grid gap-2">
                            <Label for="default_style">Style</Label>
                            <Input id="default_style" class="mt-1 block w-full" v-model="form.default_style" required autocomplete="default_style" placeholder="Default Style" />
                            <InputError class="mt-2" :message="form.errors.default_style" />
                        </div>

                        <div class="flex items-center gap-4">
                            <Button :disabled="form.processing">Save</Button>

                            <TransitionRoot
                                :show="form.recentlySuccessful"
                                enter="transition ease-in-out"
                                enter-from="opacity-0"
                                leave="transition ease-in-out"
                                leave-to="opacity-0"
                            >
                                <p class="text-sm text-neutral-600">Saved.</p>
                            </TransitionRoot>
                        </div>
                    </form>
                </div>

                <div class="w-full lg:w-1/2 h-96 lg:h-auto">
                    <div ref="mapContainer" class="w-full h-full rounded-lg border border-gray-200"></div>
                </div>
            </div>
        </ConfigurationsLayout>
    </AppLayout>
</template>

<style>
.map-container {
    width: 100%;
    height: 100%;
    min-height: 400px;
}

@media (max-width: 1023px) {
    .map-container {
        height: 400px;
    }
}
</style>
