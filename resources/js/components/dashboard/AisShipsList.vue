<script lang="ts">
import { defineProps } from 'vue';

defineProps<{
    ships?: Array<any>;
}>();

export default {
    props: {
        ships: Array, // Array of ship objects
    },
};  
</script>

<template>
    <div>
        <h1>AisShipsList</h1>
    </div>
</template>
