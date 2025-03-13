<script setup lang="ts">
import AisShipsList from '@/components/dashboard/AisShipsList.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import AisShipsCategoriesChart from '../../components/dashboard/AisShipsCategoriesChart.vue';
import AisShipsCountriesChart from '../../components/dashboard/AisShipsCountriesChart.vue';
import AisShipsMap from '../../components/dashboard/AisShipsMap.vue';
import AisShipsRealtimeChart from '../../components/dashboard/AisShipsRealtimeChart.vue';

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
        <div class="flex flex-1 flex-col gap-4 p-4 pt-4">
            <div class="grid auto-rows-min gap-4 md:grid-cols-3">
                <div class="aspect-video rounded-xl">
                    <Card>
                        <CardHeader>
                            <CardTitle>Realtime</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <AisShipsRealtimeChart></AisShipsRealtimeChart>
                        </CardContent>
                    </Card>
                </div>
                <div class="aspect-video rounded-xl">
                    <Card>
                        <CardHeader>
                            <CardTitle>Cargo Types</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <AisShipsCategoriesChart></AisShipsCategoriesChart>
                        </CardContent>
                    </Card>
                </div>
                <div class="aspect-video rounded-xl">
                    <Card>
                        <CardHeader>
                            <CardTitle>Top Countries</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <AisShipsCountriesChart></AisShipsCountriesChart>
                        </CardContent>
                    </Card>
                </div>
            </div>
            <div class="grid auto-rows-min gap-4 md:grid-cols-3">
                <div class="col-span-2 aspect-video rounded-xl">
                    <Card class="flex h-full flex-col">
                        <CardHeader>
                            <CardTitle>Map View</CardTitle>
                        </CardHeader>
                        <CardContent class="flex-1">
                            <AisShipsMap :ships="ships" />
                        </CardContent>
                    </Card>
                </div>
                <div class="aspect-video rounded-xl">
                    <Card>
                        <CardHeader>
                            <CardTitle>Ships List</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <AisShipsList :ships="ships" />
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
