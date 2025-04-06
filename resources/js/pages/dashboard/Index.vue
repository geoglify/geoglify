<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import AisShipsMap from '../../components/dashboard/AisShipsMap.vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Map',
        href: '/',
    },
];

defineProps<{
    ships?: Array<any>;
}>();
</script>

<template>
    <Head title="Map" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <AisShipsMap :ships="ships" />
    </AppLayout>
</template>
