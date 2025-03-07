<script setup lang="ts">
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
</script>

<template>
    <Head title="Ship" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-4 p-4 pt-0">
            <div class="grid gap-2 md:grid-cols-2">
                <div class="h-full rounded-xl">
                    <ShipDetails :ship="shipDetails" />
                </div>
                <div class="h-full rounded-xl">
                    <ShipAisInfo :shipRealtimePosition="shipRealtimePositionDetails" />
                </div>
            </div>

            <div class="flex-1 rounded-xl">
                <ShipHistoryMap :ships="ship" />
            </div>
        </div>
    </AppLayout>
</template>
