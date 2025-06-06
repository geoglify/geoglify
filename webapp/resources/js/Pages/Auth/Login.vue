<script setup>
import GuestLayout from "@/Layouts/GuestLayout.vue";
</script>

<script>
import { Head, useForm } from "@inertiajs/vue3";

export default {
    props: {
        canResetPassword: {
            type: Boolean,
        },
        status: {
            type: String,
        },
    },
    data() {
        return {
            form: useForm({
                email: "",
                password: "",
                remember: false,
            }),
            showPassword: false,
        };
    },
    methods: {
        submit() {
            this.form.post(route("login"), {
                onFinish: () => this.form.reset("password"),
            });
        },
    },
};
</script>

<template>
    <GuestLayout>

        <Head :title="$t('custom.log_in')" />

        <form>

            <div class="text-subtitle-1 text-medium-emphasis d-flex align-center justify-space-between">
                <span class="text-caption font-weight-bold">{{ $t('custom.email') }}</span>
            </div>

            <v-text-field v-model="form.email" density="compact" :placeholder="$t('custom.email_address')"
                prepend-inner-icon="mdi-email-outline" variant="outlined" required autofocus autocomplete="username"
                :error-messages="form.errors.email" hide-details="auto" />

            <div class="text-subtitle-1 text-medium-emphasis d-flex align-center justify-space-between mt-4">
                <span class="text-caption font-weight-bold">{{ $t('custom.password') }}</span>
                <a v-if="canResetPassword" class="text-caption text-decoration-none text-blue" rel="noopener noreferrer"
                    :href="route('password.request')">
                    {{ $t('custom.forgot_login_password') }}
                </a>
            </div>

            <v-text-field v-model="form.password" :append-inner-icon="showPassword ? 'mdi-eye-off' : 'mdi-eye'"
                :type="showPassword ? 'text' : 'password'" density="compact"
                :placeholder="$t('custom.enter_your_password')" prepend-inner-icon="mdi-lock-outline" variant="outlined"
                required autocomplete="current-password" :error-messages="form.errors.password" hide-details="auto"
                @click:append-inner="() => (showPassword = !showPassword)" />

            <v-card class="mb-4 mt-4" color="surface-variant" variant="tonal">
                <v-card-text class="text-medium-emphasis text-caption">
                    {{ $t('custom.login_attempts_warning') }}
                </v-card-text>
            </v-card>

            <v-btn :class="{ 'opacity-25': form.processing }" :readonly="form.processing" @click.prevent="submit"
                color="primary" size="large" elevation="0" block class="text-none">
                {{ $t('custom.log_in') }}
            </v-btn>

            <v-btn color="primary" size="small" class="mt-2" variant="text" block :href="route('register')">
                {{ $t('custom.sign_up') }}
            </v-btn>
        </form>
    </GuestLayout>
</template>
