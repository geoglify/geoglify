<script setup lang="ts">
import DatePicker from '@/components/Datepicker.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

// Import the components
import ShipAisInfo from '../../components/ship/ShipAisInfo.vue';
import ShipDetails from '../../components/ship/ShipDetails.vue';
import ShipHistoryMap from '../../components/ship/ShipHistoryMap.vue';

const props = defineProps<{
    ship?: Record<string, any>;
    lastKnownPosition?: Record<string, any>;
    translations: Record<string, string>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Ships / ' + props.ship.name,
        href: '/',
    },
];

// State
const imageError = ref(false);
const startDate = ref('2023-01-01');
const endDate = ref('2023-12-31');

// Compute the ship details
const shipDetails = computed(() => {
    if (!props.ship) return [];
    return Object.entries(props.ship).map(([key, value]) => ({
        label: props.translations[key] || key, // Use the translation from Laravel or the original key
        value: value ?? 'Unknown', // Replace null values with 'Unknown'
    }));
});

// Compute the ship realtime position
const lastKnownPositionDetails = computed(() => {
    if (!props.lastKnownPosition) return [];
    return Object.entries(props.lastKnownPosition).map(([key, value]) => ({
        label: props.translations[key] || key, // Use the translation from Laravel or the original key
        value: value ?? 'Unknown', // Replace null values with 'Unknown'
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
    <Head :title="props.ship.name" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-4 p-4 pt-4">
            <div class="grid gap-2 lg:grid-cols-3">
                <!-- Ship Photo by MarineTraffic -->
                <Card class="flex h-full flex-col">
                    <CardHeader>
                        <CardTitle>{{ $t('ship.card_photo_title') }}</CardTitle>
                    </CardHeader>
                    <CardContent class="flex-1">
                        <div class="flex max-h-[400px] flex-1 items-center justify-center overflow-hidden">
                            <img
                                v-if="!imageError && shipPhotoUrl"
                                :src="shipPhotoUrl"
                                alt="Ship Photo"
                                class="h-full w-full object-cover"
                                @error="handleImageError"
                            />
                            <div v-else class="flex min-h-[300px] items-center justify-center p-4 text-center text-gray-400">
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
                        <ShipAisInfo :lastKnownPosition="lastKnownPositionDetails" v-if="lastKnownPositionDetails.length" />
                        <div v-else class="flex min-h-[300px] items-center justify-center p-4 text-center text-gray-400">
                            {{ $t('ship.no_ais_data_available') }}
                        </div>
                    </CardContent>
                </Card>
            </div>

            <Card class="flex h-full flex-col">
                <CardHeader>
                    <CardTitle>Map Positions</CardTitle>

                    <DatePicker />
                </CardHeader>
                <CardContent class="flex-1">
                    <ShipHistoryMap :ship="ship" :startDate="startDate" :endDate="endDate" />
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
