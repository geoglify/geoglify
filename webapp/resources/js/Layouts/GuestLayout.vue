<template>
    <div class="bg-gray-100 min-h-screen flex items-center justify-center p-8">

        <v-card class="w-full sm:max-w-md px-6 py-4 pb-10">
            <v-card-text class="mx-0 px-0 py-1">
                <v-list-item class="pa-0 ma-0 my-3">
                    <template v-slot:title>
                        <span class="font-weight-black pa-0 ma-0" style="font-size: 22px">Geoglify</span>
                    </template>

                    <template v-slot:subtitle>
                        <span style="font-size: 12px">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</span>
                    </template>
                </v-list-item>
            </v-card-text>
            <slot />
        </v-card>

        <div class="absolute bottom-2 w-full text-center text-white px-8">
            <p class="text-xs">
                Powered by Geoglify v1.0.0 - {{ new Date().getFullYear() }}
            </p>
        </div>
    </div>
</template>

<script>
export default {
    data() {
        return {
            isMobile: false,
        };
    },
    mounted() {
        this.checkViewport();
        window.addEventListener("resize", this.checkViewport);
    },
    methods: {
        checkViewport() {
            this.isMobile = window.innerWidth < 640;
        },
    },
    beforeDestroy() {
        window.removeEventListener("resize", this.checkViewport);
    },
};
</script>
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
