<script setup>
import GuestLayout from "@/Layouts/GuestLayout.vue";
</script>

<script>
import { Head, Link, useForm } from "@inertiajs/vue3";

export default {
    components: {
        Link,
    },
    props: {
        status: {
            type: String,
        },
    },
    data() {
        return {
            form: useForm({}),
        };
    },
    methods: {
        submit() {
            this.form.post(route("verification.send"));
        },
        logout() {
            this.form.post(route("logout"));
        },
    },
};
</script>

<template>
    <GuestLayout>

        <Head title="Email Verification" />

        <div class="mb-4 text-sm text-gray-600">
            Thanks for signing up! Before getting started, could you verify your email address by clicking on the link
            we just emailed to you? If you didn't receive the email, we will gladly send you another.
        </div>

        <div v-if="status === 'verification-link-sent'" class="mb-4 font-medium text-sm text-green-600">
            A new verification link has been sent to the email address you provided during registration.
        </div>

        <form>
            <v-btn :class="{ 'opacity-25': form.processing }" :readonly="form.processing" @click.prevent="submit"
                color="primary" size="large" elevation="0" block class="text-none mb-4">
                Resend Verification Email
            </v-btn>

             <v-btn color="primary" size="small" class="mt-2" variant="text" block @click.prevent="logout">
                Log Out
            </v-btn>

        </form>
    </GuestLayout>
</template>