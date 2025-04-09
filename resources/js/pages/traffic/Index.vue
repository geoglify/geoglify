<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Traffic Analysis',
        href: '/',
    },
];

defineProps<{
    ships?: Array<any>;
}>();
</script>

<template>
    <Head title="Traffic Analysis" />

    <AppLayout :breadcrumbs="breadcrumbs">
        
    </AppLayout>
</template>
