<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>

    <Head title="Dashboard" />

    <AuthenticatedLayout>

        <form>

            <div class="text-center text-h6">
                Welcome <b>{{ $page.props.auth.user.name }}</b> to your dashboard!
            </div>

        </form>
    </AuthenticatedLayout>
</template>
