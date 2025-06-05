<script setup>
import GuestLayout from "@/Layouts/GuestLayout.vue";
</script>

<script>
import { Head, useForm } from "@inertiajs/vue3";

export default {
    props: {
        status: {
            type: String,
        },
    },
    data() {
        return {
            form: useForm({
                email: "",
            }),
        };
    },
    methods: {
        submit() {
            this.form.post(route("password.email"));
        },
    },
};
</script>

<template>
    <GuestLayout>

        <Head title="Forgot Password" />

        <div class="mb-4 text-sm text-gray-600">
            Forgot your password? No problem. Just let us know your email address and we will email you a password reset
            link that will allow you to choose a new one.
        </div>

        <div v-if="status" class="mb-4 font-medium text-sm text-green-600">
            {{ status }}
        </div>

        <form>
            <div class="text-subtitle-1 text-medium-emphasis d-flex align-center justify-space-between">
                <span class="text-caption font-weight-bold">Email</span>
            </div>
            <v-text-field v-model="form.email" density="compact" placeholder="Email address"
                prepend-inner-icon="mdi-email-outline" variant="outlined" required autofocus autocomplete="username"
                :error-messages="form.errors.email" hide-details="auto">
            </v-text-field>

            <v-btn :class="{ 'opacity-25': form.processing }" :readonly="form.processing" @click.prevent="submit"
                color="primary" size="large" elevation="0" block class="text-none mt-6">
                Email Password Reset Link
            </v-btn>

            <v-btn :href="route('login')" color="primary" size="small" class="mt-2" variant="text" block>
                Back to Login
            </v-btn>
        </form>
    </GuestLayout>
</template>