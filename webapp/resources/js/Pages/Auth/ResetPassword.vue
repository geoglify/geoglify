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
                hide-details="auto">
                <template v-slot:prepend-inner>
                    <Icon icon="mdi:email-outline" style="font-size:24px;font-style:italic;height:24px;opacity:0.4" />
                </template>
            </v-text-field>

            <div class="text-subtitle-1 text-medium-emphasis d-flex align-center justify-space-between mt-4">
                <span class="text-caption font-weight-bold">{{ $t('custom.new_password') }}</span>
            </div>
            <v-text-field v-model="form.password" :type="showPassword ? 'text' : 'password'" density="compact"
                :placeholder="$t('custom.enter_your_new_password')" variant="outlined" required
                autocomplete="new-password" :error-messages="form.errors.password" hide-details="auto">
                <template v-slot:append-inner>
                    <Icon icon="mdi:eye" v-if="showPassword" style="font-size:24px;opacity:0.4;cursor:pointer"
                        @click="showPassword = false" />
                    <Icon icon="mdi:eye-off" v-else style="font-size:24px;opacity:0.4;cursor:pointer"
                        @click="showPassword = true" />
                </template>
                <template v-slot:prepend-inner>
                    <Icon icon="mdi:lock-outline" style="font-size:24px;font-style:italic;height:24px;opacity:0.4" />
                </template>
            </v-text-field>

            <div class="text-subtitle-1 text-medium-emphasis d-flex align-center justify-space-between mt-4">
                <span class="text-caption font-weight-bold">{{ $t('custom.confirm_password') }}</span>
            </div>
            <v-text-field v-model="form.password_confirmation" :type="showPasswordConfirmation ? 'text' : 'password'"
                density="compact" :placeholder="$t('custom.confirm_your_new_password')" variant="outlined" required
                autocomplete="new-password" :error-messages="form.errors.password_confirmation" hide-details="auto">
                <template v-slot:append-inner>
                    <Icon icon="mdi:eye" v-if="showPasswordConfirmation"
                        style="font-size:24px;opacity:0.4;cursor:pointer" @click="showPasswordConfirmation = false" />
                    <Icon icon="mdi:eye-off" v-else style="font-size:24px;opacity:0.4;cursor:pointer"
                        @click="showPasswordConfirmation = true" />
                </template>
                <template v-slot:prepend-inner>
                    <Icon icon="mdi:lock-check-outline"
                        style="font-size:24px;font-style:italic;height:24px;opacity:0.4" />
                </template>
            </v-text-field>

            <v-btn :class="{ 'opacity-25': form.processing }" :readonly="form.processing" @click.prevent="submit"
                color="primary" size="large" elevation="0" block class="text-none mt-6">
                {{ $t('custom.reset_password') }}
            </v-btn>
        </form>
    </GuestLayout>
</template>
