<script setup>
import GuestLayout from "@/Layouts/GuestLayout.vue";
</script>

<script>
import { Head, useForm } from "@inertiajs/vue3";

export default {
    data() {
        return {
            form: useForm({
                password: "",
            }),
            showPassword: false,
        };
    },
    methods: {
        submit() {
            this.form.post(route("password.confirm"), {
                onFinish: () => this.form.reset("password"),
            });
        },
    },
};
</script>

<template>
    <GuestLayout>

        <Head title="Confirm Password" />

        <div class="mb-4 text-sm text-gray-600">
            This is a secure area of the application. Please confirm your password before continuing.
        </div>

        <form>
            <div class="text-subtitle-1 text-medium-emphasis d-flex align-center justify-space-between">
                <span class="text-caption font-weight-bold">Password</span>
            </div>
            <v-text-field v-model="form.password" :append-inner-icon="showPassword ? 'mdi-eye-off' : 'mdi-eye'"
                :type="showPassword ? 'text' : 'password'" density="compact" placeholder="Enter your password"
                prepend-inner-icon="mdi-lock-outline" variant="outlined" required autofocus
                autocomplete="current-password" :error-messages="form.errors.password" hide-details="auto"
                @click:append-inner="() => (showPassword = !showPassword)">
            </v-text-field>

            <v-btn :class="{ 'opacity-25': form.processing }" :readonly="form.processing" @click.prevent="submit"
                color="primary" size="large" elevation="0" block class="text-none mt-6">
                Confirm
            </v-btn>
        </form>
    </GuestLayout>
</template>