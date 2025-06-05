<script setup>
import GuestLayout from "@/Layouts/GuestLayout.vue";
</script>

<script>
import { Head, useForm } from "@inertiajs/vue3";

export default {
    props: {
        email: {
            type: String,
            required: true,
        },
        token: {
            type: String,
            required: true,
        },
    },
    data() {
        return {
            form: useForm({
                token: this.token,
                email: this.email,
                password: "",
                password_confirmation: "",
            }),
            showPassword: false,
            showPasswordConfirmation: false,
        };
    },
    methods: {
        submit() {
            this.form.post(route("password.store"), {
                onFinish: () => this.form.reset("password", "password_confirmation"),
            });
        },
    },
};
</script>

<template>
    <GuestLayout>

        <Head title="Reset Password" />

        <form>
            <div class="text-subtitle-1 text-medium-emphasis d-flex align-center justify-space-between">
                <span class="text-caption font-weight-bold">Email</span>
            </div>
            <v-text-field v-model="form.email" density="compact" placeholder="Email address"
                prepend-inner-icon="mdi-email-outline" variant="outlined" required autofocus autocomplete="username"
                :error-messages="form.errors.email" hide-details="auto">
            </v-text-field>

            <div class="text-subtitle-1 text-medium-emphasis d-flex align-center justify-space-between mt-4">
                <span class="text-caption font-weight-bold">New Password</span>
            </div>
            <v-text-field v-model="form.password" :append-inner-icon="showPassword ? 'mdi-eye-off' : 'mdi-eye'"
                :type="showPassword ? 'text' : 'password'" density="compact" placeholder="Enter your new password"
                prepend-inner-icon="mdi-lock-outline" variant="outlined" required autocomplete="new-password"
                :error-messages="form.errors.password" hide-details="auto"
                @click:append-inner="() => (showPassword = !showPassword)">
            </v-text-field>

            <div class="text-subtitle-1 text-medium-emphasis d-flex align-center justify-space-between mt-4">
                <span class="text-caption font-weight-bold">Confirm Password</span>
            </div>
            <v-text-field v-model="form.password_confirmation"
                :append-inner-icon="showPasswordConfirmation ? 'mdi-eye-off' : 'mdi-eye'"
                :type="showPasswordConfirmation ? 'text' : 'password'" density="compact"
                placeholder="Confirm your new password" prepend-inner-icon="mdi-lock-check-outline" variant="outlined"
                required autocomplete="new-password" :error-messages="form.errors.password_confirmation"
                hide-details="auto" @click:append-inner="() => (showPasswordConfirmation = !showPasswordConfirmation)">
            </v-text-field>

            <v-btn :class="{ 'opacity-25': form.processing }" :readonly="form.processing" @click.prevent="submit"
                color="primary" size="large" elevation="0" block class="text-none mt-6">
                Reset Password
            </v-btn>
        </form>
    </GuestLayout>
</template>