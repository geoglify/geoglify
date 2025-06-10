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

        <Head :title="$t('custom.forgot_password')" />

        <div class="mb-4 text-sm text-gray-600">
            {{ $t('custom.forgot_password_instructions') }}
        </div>

        <div v-if="status" class="mb-4 font-medium text-sm text-green-600">
            {{ status }}
        </div>

        <form>
            <div class="text-subtitle-1 text-medium-emphasis d-flex align-center justify-space-between">
                <span class="text-caption font-weight-bold">{{ $t('custom.email') }}</span>
            </div>
            <v-text-field v-model="form.email" density="compact" :placeholder="$t('custom.email_address')"
                variant="outlined" required autofocus autocomplete="username" :error-messages="form.errors.email"
                hide-details="auto" prepend-inner-icon="mdi:email-outline" />

            <v-btn :class="{ 'opacity-25': form.processing }" :readonly="form.processing" @click.prevent="submit"
                color="primary" size="large" elevation="0" block class="text-none mt-6">
                {{ $t('custom.email_password_reset_link') }}
            </v-btn>

            <v-btn :href="route('login')" color="primary" size="small" class="mt-2" variant="text" block>
                {{ $t('custom.back_to_login') }}
            </v-btn>
        </form>
    </GuestLayout>
</template>
