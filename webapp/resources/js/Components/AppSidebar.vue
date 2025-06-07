<script setup>
defineProps({
    modelValue: {
        type: Boolean,
        default: true
    }
})

defineEmits(['update:modelValue'])

const navigationItems = [
  { icon: 'material-symbols-light:map-outline', title: 'custom.map', value: 'map' },
  { icon: 'mdi-light:layers', title: 'custom.layers', value: 'layers' },
  { icon: 'mdi-light:map-marker', title: 'custom.assets', value: 'assets' },
  { icon: 'material-symbols-light:crisis-alert', title: 'custom.alerts', value: 'alerts' },
  { icon: 'material-symbols-light:manage-history', title: 'custom.history', value: 'history' },
  { icon: 'material-symbols-light:simulation-outline', title: 'custom.simulations', value: 'simulations' },
  { icon: 'material-symbols-light:analytics-outline', title: 'custom.analytics', value: 'analytics' },
  { icon: 'material-symbols-light:integration-instructions-outline', title: 'custom.integrations', value: 'integrations' },
  { icon: 'material-symbols-light:supervisor-account-outline', title: 'custom.users', value: 'users' },
  { icon: 'mdi-light:lock-open', title: 'custom.permissions', value: 'permissions' },
  { icon: 'mdi-light:cog', title: 'custom.settings', value: 'settings' }
];

</script>

<template>
    <v-navigation-drawer color="yellow" name="drawer" :model-value="modelValue"
        @update:model-value="$emit('update:modelValue', $event)" permanent app>
        <v-list density="comfortable" nav>
            <v-list-item class="mb-4">
                <template v-slot:title>
                    <span class="text-h5 font-weight-bold">GEOGLIFY</span>
                </template>
            </v-list-item>

            <v-list-item v-for="item in navigationItems" :key="item.value" :value="item.value">
                <template v-slot:prepend>
                    <Icon :icon="item.icon" class="mr-2" style="font-size:26px;font-style:italic;height:26px" />
                </template>
                <v-list-item-title class="py-2" style="font-size: 15px; font-weight: 500;">
                    {{ $t(item.title) }}
                </v-list-item-title>
            </v-list-item>
        </v-list>
    </v-navigation-drawer>
</template>