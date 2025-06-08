<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Map from '@/Components/Map.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>

    <Head title="Map" />

    <AuthenticatedLayout>
        <Map/>
    </AuthenticatedLayout>
</template>
