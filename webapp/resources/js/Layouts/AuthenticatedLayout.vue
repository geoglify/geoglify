<script setup>
import { ref } from 'vue';
</script>

<template>
    <div class="bg-yellow min-h-screen flex items-center justify-center p-8">

        <v-card class="mx-auto pa-8" elevation="0" width="448" rounded="xs">
            <slot />
        </v-card>

        <div class="absolute bottom-2 w-full text-center px-8">
            <p class="text-xs">
                Powered by Geoglify v1.0.0 - {{ new Date().getFullYear() }}
            </p>
        </div>
    </div>
</template>