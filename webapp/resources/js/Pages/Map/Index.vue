<script setup>
import { ref, onMounted, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Map from '@/Components/Map.vue';

// Props
const props = defineProps({
    layers: {
        type: Array,
        default: () => []
    }
});

// Reactive data
const search = ref('');
const loading = ref(false);
const serverLayers = ref(props.layers || []);
const selectedLayers = ref([]);
const treeViewOpen = ref([]);

// Computed properties
const filteredLayers = computed(() => {
    if (!search.value) return serverLayers.value;

    const searchTerm = search.value.toLowerCase();
    return filterLayersRecursive(serverLayers.value, searchTerm);
});

// Methods
const filterLayersRecursive = (layers, searchTerm) => {
    return layers.filter(layer => {
        const matchesSearch = layer.name.toLowerCase().includes(searchTerm) ||
            (layer.description && layer.description.toLowerCase().includes(searchTerm));

        if (layer.children) {
            const filteredChildren = filterLayersRecursive(layer.children, searchTerm);
            if (filteredChildren.length > 0) {
                return {
                    ...layer,
                    children: filteredChildren
                };
            }
        }

        return matchesSearch;
    });
};

const loadLayers = async () => {
    try {
        loading.value = true;
        const response = await fetch(route('layers.list'), {
            method: 'POST',
            body: JSON.stringify({
                search: search.value
            })
        });

        const data = await response.json();
        serverLayers.value = data.items;
    } catch (error) {
        console.error('Error loading layers:', error);
    } finally {
        loading.value = false;
    }
};

const updateLayerOpacity = (layer, opacity) => {
    layer.opacity = opacity;
    emitLayerChange(layer);
};

const emitLayerChange = (layer) => {
    window.dispatchEvent(new CustomEvent('layer-changed', {
        detail: { layer }
    }));
};

const getTypeIcon = (type) => {
    const icons = {
        'vector': 'mdi-vector-polygon',
        'raster': 'mdi-image',
        'point': 'mdi-circle-small',
        'line': 'mdi-minus',
        'polygon': 'mdi-square-rounded-outline',
        'realtime': 'eos-icons:iot',
        'group': 'mdi-folder-outline',
    };
    return icons[type] || 'mdi-layers';
};

const getCategoryColor = (category) => {
    const colors = {
        'realtime': 'green darken-2',
        'road': 'blue-grey darken-2',
        'warehouse': 'deep-orange darken-2',
        'wrench': 'amber darken-2',
    };
    return colors[category] || 'grey';
};

const getItemIcon = (item) => {
    // Use custom icon if provided, otherwise fall back to type-based icon
    return item.icon;
};

// Initialize selected layers with visible ones
const initializeSelectedLayers = () => {
    const getVisibleLayers = (layers) => {
        let visible = [];
        layers.forEach(layer => {
            if (layer.visible) {
                visible.push(layer.id);
            }
            if (layer.children) {
                visible.push(...getVisibleLayers(layer.children));
            }
        });
        return visible;
    };

    selectedLayers.value = getVisibleLayers(serverLayers.value);
    treeViewOpen.value = serverLayers.value.map(layer => layer.id);
};

onMounted(() => {
    loadLayers();
    initializeSelectedLayers();
});
</script>

<template>

    <Head title="Digital Twin Platform" />

    <AuthenticatedLayout>
        <template #breadcrumbs>
            <v-breadcrumbs :items="[
                { title: 'Home', disabled: false, href: '/' },
                { title: 'Digital Twin', disabled: true },
            ]" divider="/" />
        </template>

        <div class="fill-height">
            <Map />
        </div>

    </AuthenticatedLayout>
</template>

<style scoped>
.fill-height {
    height: calc(100vh - 200px);
}

</style>