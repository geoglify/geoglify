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

        <Head title="Log in" />

        <form>
            <div class="mt-4">
                <v-text-field v-model="form.email" label="Email" outlined dense required autofocus variant="outlined"
                    autocomplete="username" :error-messages="form.errors.email" hide-details="auto"></v-text-field>
            </div>

            <div class="mt-4">
                <v-text-field v-model="form.password" label="Password" outlined dense required variant="outlined"
                    autocomplete="current-password" :type="showPassword ? 'text' : 'password'"
                    :error-messages="form.errors.password" hide-details="auto"
                    @click:append-inner="() => (showPassword = !showPassword)" :append-inner-icon="showPassword ? 'mdi-eye-off' : 'mdi-eye'
                        "></v-text-field>
            </div>

            <div class="flex items-center justify-end mt-4">
                <v-btn :class="{ 'opacity-25': form.processing }" :readonly="form.processing" @click.prevent="submit"
                    color="primary" elevation="0" block size="large" class="text-none">
                    Login
                </v-btn>
            </div>
        </form>
    </GuestLayout>
</template>
