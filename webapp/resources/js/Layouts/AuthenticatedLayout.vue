<script setup>
import { ref } from 'vue'
import AppHeader from '../Components/AppHeader.vue'
import AppSidebar from '../Components/AppSidebar.vue'
import AppMain from '../Components/AppMain.vue'
import AppFooter from '../Components/AppFooter.vue'

const sidebarOpen = ref(true)

const toggleSidebar = () => {
    sidebarOpen.value = !sidebarOpen.value
}
</script>

<template>
    <v-app>

        <AppHeader @toggle-sidebar="toggleSidebar" />

        <AppSidebar v-model="sidebarOpen" />

        <AppMain>
            <slot />
        </AppMain>

        <AppFooter />
    </v-app>
</template>

<style>
html,
body {
    height: 100%;
    margin: 0;
    overflow: hidden;
}
</style>