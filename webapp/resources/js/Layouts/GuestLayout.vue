<template>

    <div class="bg-yellow min-h-screen flex items-center justify-center p-8">

        <v-card class="mx-auto pa-8" elevation="0" width="448" rounded="xs">

            <v-list-item class="pa-0 ma-0 my-3">
                <template v-slot:title>
                    <span class="font-weight-black text-uppercase pa-0 ma-0" style="font-size: 22px">Geoglify</span>
                </template>
            </v-list-item>
            <slot />
        </v-card>

        <div class="absolute bottom-2 w-full text-center px-8">
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
