<script setup lang="ts">
import GenericTable from '@/components/GenericTable.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { router } from '@inertiajs/vue3';

const props = defineProps<{
    ships: PaginatedData<Ship>;
    filters?: TableFilters;
}>();

const columns = [
    { accessorKey: 'mmsi', header: 'MMSI' },
    { accessorKey: 'flag', header: 'Flag' },
    { accessorKey: 'name', header: 'Name' },
    { accessorKey: 'imo', header: 'IMO' },
    { accessorKey: 'callsign', header: 'Call Sign' },
    { accessorKey: 'cargo_category_name', header: 'Cargo Category' },
    { accessorKey: 'cargo_type_name', header: 'Cargo Type' },
    { accessorKey: 'status', header: 'Status' },
    { accessorKey: 'last_updated', header: 'Last Updated' },
    { accessorKey: 'id', header: '' },
];

const breadcrumbs = [{ title: 'Ships', href: '/' }];
</script>

<template>
    <GenericTable
        title="Ships"
        :breadcrumbs="breadcrumbs"
        :data="ships"
        :columns="columns"
        :filters="filters"
        routeName="ships.index"
        showSearch
        defaultSort="name"
    >
        <!-- Custom cell for country flag -->
        <template #cell-flag="{ row }">
            <div class="flex items-center space-x-2">
                <country-flag
                    :country="row.country_iso_code || ''"
                    :class="{ 'bg-primary': Boolean(row.country_iso_code) }"
                    rounded
                />
                <Label class="mt-2">{{ row.country_name ?? 'Unknown' }}</Label>
            </div>
        </template>

        <!-- Custom cell for status badge -->
        <template #cell-status="{ row }">
            <Badge variant="secondary" :class="row.status === 'LIVE' ? 'w-24 bg-green-500 text-white' : 'w-24 bg-red-500 text-white'">
                {{ row.status }}
            </Badge>
        </template>

        <!-- Custom cell for action button -->
        <template #cell-id="{ row }">
            <Button size="sm" @click="router.visit(route('ships.show', { id: row.id }))"> Info </Button>
        </template>
    </GenericTable>
</template>
