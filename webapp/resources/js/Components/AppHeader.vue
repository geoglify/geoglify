<script setup>
import { ref } from 'vue'
defineEmits(['toggle-sidebar'])

const menu = ref(false)
</script>

<template>
    <v-app-bar color="white" name="app-bar" order="1" elevation="0">
        <v-btn icon @click="$emit('toggle-sidebar')">
            <Icon icon="mdi-light:menu" style="font-size:22px;" />
        </v-btn>

        <v-spacer></v-spacer>

        <v-list-item prepend-avatar="https://pbs.twimg.com/profile_images/1929263634816856064/I2ZtcV0K_400x400.jpg"
            :title="$page.props.auth.user.name" :subtitle="$page.props.auth.role.toUpperCase()">
            <template v-slot:append>
                <v-menu v-model="menu" location="bottom end">
                    <template v-slot:activator="{ props }">
                        <v-btn icon v-bind="props">
                            <Icon icon="mdi-menu-down" style="font-size:22px;" />
                        </v-btn>
                    </template>

                    <v-list>
                        <v-list-item @click="() => $inertia.post('/logout')">
                            <v-list-item-title>Logout</v-list-item-title>
                        </v-list-item>
                    </v-list>
                </v-menu>
            </template>
        </v-list-item>
    </v-app-bar>
</template>
