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

        <Head :title="$t('custom.email_verification')" />

        <div class="mb-4 text-sm text-gray-600">
            {{ $t('custom.email_verification_instructions') }}
        </div>

        <div v-if="status === 'verification-link-sent'" class="mb-4 font-medium text-sm text-green-600">
            {{ $t('custom.verification_link_sent') }}
        </div>

        <form>
            <v-btn :class="{ 'opacity-25': form.processing }" :readonly="form.processing" @click.prevent="submit"
                color="primary" size="large" elevation="0" block class="text-none mb-4">
                {{ $t('custom.resend_verification_email') }}
            </v-btn>

            <v-btn color="primary" size="small" class="mt-2" variant="text" block @click.prevent="logout">
                {{ $t('custom.log_out') }}
            </v-btn>
        </form>
    </GuestLayout>
</template>
