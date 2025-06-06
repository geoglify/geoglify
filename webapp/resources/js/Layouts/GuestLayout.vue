<script setup>
import GuestLayout from "@/Layouts/GuestLayout.vue";
</script>

<script>
import { Head, useForm } from "@inertiajs/vue3";

export default {
    data() {
        return {
            form: useForm({
                locale: this.$page.props.locale,
            }),
            languages: [
                { code: "pt", flag: "PT" },
                { code: "en", flag: "GB" },
                { code: "es", flag: "ES" },
                { code: "fr", flag: "FR" },
            ],
        };
    },
    methods: {
        changeLanguage(code) {
            this.form.locale = code;
            this.form.post(route("locale.set"), {
                onSuccess: () => {
                    window.location.reload();
                },
            });
        },
    },
};
</script>
<template>
    <div class="bg-yellow min-h-screen flex items-center justify-center p-8">
        <v-card class="mx-auto pa-8" elevation="0" width="448" rounded="xs">
            <v-list-item class="pa-0 ma-0 my-3">
                <template v-slot:title>
                    <span class="font-weight-black text-uppercase pa-0 ma-0" style="font-size: 22px">Geoglify</span>{{
                    form.locale }}
                </template>
            </v-list-item>
            <slot />
        </v-card>

        <div class="absolute bottom-2 w-full text-center px-8">
            <div class="language-selector mb-1"
                style="display: flex; justify-content: center; gap: 12px; cursor: pointer;">
                <div v-for="lang in languages" :key="lang.code" @click="changeLanguage(lang.code)" :title="lang.label"
                    :style="{
                        opacity: form.locale === lang.code ? 1 : 0.3,
                    }">
                    <CountryFlag :country="lang.flag" rounded/>
                </div>
            </div>
            <p class="text-xs">
                Powered by Geoglify v1.0.0 - {{ new Date().getFullYear() }}
            </p>
        </div>
    </div>
</template>

<style>
input:-webkit-autofill,
textarea:-webkit-autofill,
select:-webkit-autofill {
    transition: background-color 5000s ease-in-out 0s !important;
    font-size: 14px !important;
    font-family: "Nunito", sans-serif !important;
    -webkit-text-fill-color: black !important;
    color: black;
}
</style>
