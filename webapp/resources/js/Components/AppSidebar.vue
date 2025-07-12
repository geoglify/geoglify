<template>
    <v-navigation-drawer color="yellow" name="drawer" :model-value="modelValue"
        @update:model-value="$emit('update:modelValue', $event)" permanent app>
        <v-list density="comfortable" nav>
            <v-list-item class="mb-4">
                <template v-slot:title>
                    <span class="text-h5 font-weight-bold">GEOGLIFY</span>
                </template>
            </v-list-item>

            <v-list-item v-for="item in navigationItems" :key="item.value" :value="item.value" :disabled="item.disabled"
                :active="isActiveRoute(item.value)" :class="{
                    'v-list-item--disabled': item.disabled,
                    'v-list-item--active': isActiveRoute(item.value)
                }" @click="navigateTo(item)">
                <template v-slot:prepend>
                    <Icon :icon="item.icon" class="mr-2" style="font-size:26px; height:26px" :class="{
                        'opacity-40': item.disabled,
                        'text-primary': isActiveRoute(item.value)
                    }" />
                </template>
                <v-list-item-title class="py-2" style="font-size: 15px; font-weight: 500;" :class="{
                    'text-disabled': item.disabled,
                    'text-primary font-weight-bold': isActiveRoute(item.value)
                }">
                    {{ $t(item.title) }}
                </v-list-item-title>
            </v-list-item>
        </v-list>
    </v-navigation-drawer>
</template>

<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { router } from '@inertiajs/vue3'

const $page = usePage()

// Props
const props = defineProps({
    modelValue: {
        type: Boolean,
        default: false
    }
})

// Emits
const emit = defineEmits(['update:modelValue'])

// Navigation items
const navigationItems = computed(() => [
    {
        icon: 'material-symbols-light:map-outline',
        title: 'custom.map',
        value: 'map.index',
        disabled: !$page.props.auth?.can?.map_view
    },
    {
        icon: 'mdi-light:layers',
        title: 'custom.layers',
        value: 'layers.index',
        disabled: !$page.props.auth?.can?.layers_view
    },
    {
        icon: 'material-symbols-light:supervisor-account-outline',
        title: 'custom.users',
        value: 'users.index',
        disabled: !$page.props.auth?.can?.users_view
    },
    {
        icon: 'mdi-light:lock-open',
        title: 'custom.permissions',
        value: 'permissions.index',
        disabled: !$page.props.auth?.can?.permissions_view
    },
    {
        icon: 'mdi-light:cog',
        title: 'custom.settings',
        value: 'settings.index',
        disabled: !$page.props.auth?.can?.settings_view
    }
])

// Check if the current route is active
function isActiveRoute(routeName) {
    return $page.component.startsWith(routeName.replace('.index', '').charAt(0).toUpperCase() + routeName.replace('.index', '').slice(1))
}

// GoTo the specified route
function navigateTo(item) {
    if (!item.disabled) {
        router.visit(route(item.value))
    }
}
</script>

<style scoped>
.v-list-item--disabled {
    pointer-events: none;
}

.text-disabled {
    opacity: 0.6;
}

.v-navigation-drawer {
    border-right: 1px solid rgba(0, 0, 0, 0.12);
}

.v-list-item--active {
    background-color: rgba(var(--v-theme-primary), 0.1);
}

.v-list-item:not(.v-list-item--disabled):hover .v-icon {
    transform: scale(1.1);
    transition: transform 0.2s ease;
}

.v-list-item:not(.v-list-item--disabled):not(.v-list-item--active):hover {
    background-color: rgba(0, 0, 0, 0.04);
}
</style>
