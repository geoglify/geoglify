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

        <Head :title="$t('custom.reset_password')" />

        <form>
            <div class="text-subtitle-1 text-medium-emphasis d-flex align-center justify-space-between">
                <span class="text-caption font-weight-bold">{{ $t('custom.email') }}</span>
            </div>
            <v-text-field v-model="form.email" density="compact" :placeholder="$t('custom.email_address')"
                variant="outlined" required autofocus autocomplete="username" :error-messages="form.errors.email"
                hide-details="auto" prepend-inner-icon="mdi:email-outline">
            </v-text-field>

            <div class="text-subtitle-1 text-medium-emphasis d-flex align-center justify-space-between mt-4">
                <span class="text-caption font-weight-bold">{{ $t('custom.new_password') }}</span>
            </div>
            <v-text-field v-model="form.password" :type="showPassword ? 'text' : 'password'" density="compact"
                :placeholder="$t('custom.enter_your_new_password')" variant="outlined" required
                autocomplete="new-password" :error-messages="form.errors.password" hide-details="auto" 
                prepend-inner-icon="mdi:lock-outline" :append-inner-icon="showPassword ? 'mdi:eye' : 'mdi:eye-off'"
                @click:append-inner="showPassword = !showPassword">
            </v-text-field>

            <div class="text-subtitle-1 text-medium-emphasis d-flex align-center justify-space-between mt-4">
                <span class="text-caption font-weight-bold">{{ $t('custom.confirm_password') }}</span>
            </div>
            <v-text-field v-model="form.password_confirmation" :type="showPasswordConfirmation ? 'text' : 'password'"
                density="compact" :placeholder="$t('custom.confirm_your_new_password')" variant="outlined" required
                autocomplete="new-password" :error-messages="form.errors.password_confirmation" hide-details="auto"
                prepend-inner-icon="mdi:lock-outline" :append-inner-icon="showPasswordConfirmation ? 'mdi:eye' : 'mdi:eye-off'"
                @click:append-inner="showPasswordConfirmation = !showPasswordConfirmation">
            </v-text-field>

            <v-btn :class="{ 'opacity-25': form.processing }" :readonly="form.processing" @click.prevent="submit"
                color="primary" size="large" elevation="0" block class="text-none mt-6">
                {{ $t('custom.reset_password') }}
            </v-btn>
        </form>
    </GuestLayout>
</template>
