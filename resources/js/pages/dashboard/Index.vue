<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import AisShipsMap from '../../components/dashboard/AisShipsMap.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/',
    },
];

defineProps<{
    ships?: Array<any>;
}>();
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <AisShipsMap :ships="ships" />
    </AppLayout>
</template>
