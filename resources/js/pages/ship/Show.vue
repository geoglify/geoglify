<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

// Import the components
import ShipAisInfo from '../../components/ship/ShipAisInfo.vue';
import ShipDetails from '../../components/ship/ShipDetails.vue';
import ShipHistoryMap from '../../components/ship/ShipHistoryMap.vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Ships',
        href: '/',
    },
];

const props = defineProps<{
    ship?: Record<string, any>;
    shipRealtimePosition?: Record<string, any>;
    translations: Record<string, string>;
}>();

// Compute the ship details
const shipDetails = computed(() => {
    if (!props.ship) return [];
    return Object.entries(props.ship).map(([key, value]) => ({
        label: props.translations[key] || key, // Use the translation from Laravel or the original key
        value: value ?? 'N/A', // Replace null values with 'N/A'
    }));
});

// Compute the ship realtime position
const shipRealtimePositionDetails = computed(() => {
    if (!props.shipRealtimePosition) return [];
    return Object.entries(props.shipRealtimePosition).map(([key, value]) => ({
        label: props.translations[key] || key, // Use the translation from Laravel or the original key
        value: value ?? 'N/A', // Replace null values with 'N/A'
    }));
});

// Compute the ship photo URL
const shipPhotoUrl = computed(() => {
    if (!props.ship || !props.ship.mmsi) return null;
    return `https://photos.marinetraffic.com/ais/showphoto.aspx?mmsi=${props.ship.mmsi}`;
});

// Handle image loading errors
const handleImageError = () => {
    imageError.value = true;
};
</script>

<template>
    <Head title="Ship" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-4 p-4 pt-4">
            <div class="grid gap-2 lg:grid-cols-3">
                <!-- Ship Photo by MarineTraffic -->
                <Card class="flex h-full flex-col">
                    <CardHeader>
                        <CardTitle>{{ $t('ship.card_photo_title') }}</CardTitle>
                    </CardHeader>
                    <CardContent class="flex flex-1 flex-col">
                        <div class="flex max-h-[300px] flex-1 items-center justify-center overflow-hidden">
                            <img v-if="shipPhotoUrl" :src="shipPhotoUrl" alt="Ship Photo" class="h-full w-full" />
                            <div v-else class="p-4 text-center text-gray-400">
                                {{ $t('ship.no_photo_available') }}
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card class="flex h-full flex-col">
                    <CardHeader>
                        <CardTitle>{{ $t('ship.card_details_title') }}</CardTitle>
                    </CardHeader>
                    <CardContent class="flex-1">
                        <ShipDetails :ship="shipDetails" />
                    </CardContent>
                </Card>

                <Card class="flex h-full flex-col">
                    <CardHeader>
                        <CardTitle>{{ $t('ship.card_ais_title') }}</CardTitle>
                    </CardHeader>
                    <CardContent class="flex-1">
                        <ShipAisInfo :shipRealtimePosition="shipRealtimePositionDetails" />
                    </CardContent>
                </Card>
            </div>

            <Card class="flex h-full flex-col">
                <CardHeader>
                    <CardTitle>Map Positions</CardTitle>
                </CardHeader>
                <CardContent class="flex-1">
                    <ShipHistoryMap :ship="ship" />
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
