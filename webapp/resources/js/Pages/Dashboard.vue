<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>

    <Head title="Dashboard" />

    <AuthenticatedLayout>

        <form>
            
            <div class="text-gray-900 text-center ">
               Welcome <b>{{ $page.props.auth.user.name }}</b> to your dashboard!
            </div>
            <!-- logout -->
            <v-btn class="mt-6" @click="$inertia.post(route('logout'))" size="large" color="primary" elevation="0"
                block>Logout</v-btn>
                
        </form>
    </AuthenticatedLayout>
</template>
