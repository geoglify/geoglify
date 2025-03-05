<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import AISLineChart from '../../components/AISLineChart.vue';
import AISShipCategoryChart from '../../components/AISShipCategoryChart.vue';
import Map from '../../components/Map.vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Ships',
        href: '/',
    },
];

defineProps<{
    ships?: Array<any>;
}>();
</script>

<template>
    <Head title="Ships" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-4 p-4">
            <div class="grid h-1/3 gap-2 md:grid-cols-2">
                <div class="h-full rounded-xl">
                    <AISLineChart></AISLineChart>
                </div>
                <div class="h-full rounded-xl">
                    <AISShipCategoryChart></AISShipCategoryChart>
                </div>
            </div>

            <div class="h-3/4 flex-1 rounded-xl">
                <Map :ships="ships" />
            </div>
        </div>
    </AppLayout>
</template>
