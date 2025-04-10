<script setup lang="ts">
import ShipHeatmap from '@/components/traffic/ShipHeatmap.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Traffic Analysis',
        href: '/',
    },
];
</script>

<template>
    <Head title="Traffic Analysis" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <ShipHeatmap />
    </AppLayout>
</template>
