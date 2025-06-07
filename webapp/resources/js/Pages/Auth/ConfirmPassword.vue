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

        <Head :title="$t('custom.confirm_password_title')" />

        <div class="mb-4 text-sm text-gray-600">
            {{ $t('custom.confirm_password_instructions') }}
        </div>

        <form>
            <div class="text-subtitle-1 text-medium-emphasis d-flex align-center justify-space-between">
                <span class="text-caption font-weight-bold">{{ $t('custom.password') }}</span>
            </div>
            <v-text-field v-model="form.password" :type="showPassword ? 'text' : 'password'" density="compact"
                :placeholder="$t('custom.enter_password')" variant="outlined" required autofocus
                autocomplete="current-password" :error-messages="form.errors.password" hide-details="auto">
                <template v-slot:prepend-inner>
                    <Icon icon="mdi:lock-outline" style="font-size:24px;font-style:italic;height:24px;opacity:0.4" />
                </template>

                <template v-slot:append-inner>
                    <Icon icon="mdi:eye" v-if="showPassword" style="font-size:24px;opacity:0.4;cursor:pointer"
                        @click="showPassword = false" />
                    <Icon icon="mdi:eye-off" v-else style="font-size:24px;opacity:0.4;cursor:pointer"
                        @click="showPassword = true" />
                </template>
            </v-text-field>


            <v-btn :class="{ 'opacity-25': form.processing }" :readonly="form.processing" @click.prevent="submit"
                color="primary" size="large" elevation="0" block class="text-none mt-6">
                {{ $t('custom.confirm') }}
            </v-btn>
        </form>
    </GuestLayout>
</template>
