<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { FlexRender, getCoreRowModel, getFilteredRowModel, getPaginationRowModel, getSortedRowModel, useVueTable } from '@tanstack/vue-table';
import { ref } from 'vue';

// Define the type for the ship data
export interface Ship {
    id: number;
    name: string;
    mmsi: string;
    imo: string;
    callsign: string;
    last_updated: string;
    cargo_type: { name: string };
    cargo_category: { name: string };
    country: { name: string; country_iso_code: string };
    status: string;
}

// Define the breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Ships',
        href: '/',
    },
];

// Props passed to the component
const props = defineProps<{
    ships?: {
        data: Ship[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    filters?: {
        search?: string;
        sort?: string;
        direction?: string;
    };
}>();

// Constants for the table
const search = ref(props.filters?.search || '');
const sortField = ref(props.filters?.sort || 'name');
const sortDirection = ref(props.filters?.direction || 'asc');

// Function to apply the filter
const applyFilter = () => {
    router.get('/ships', {
        search: search.value,
        sort: sortField.value,
        direction: sortDirection.value,
    });
};

// Function to sort the table
const sort = (field: string) => {
    if (sortField.value === field) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortField.value = field;
        sortDirection.value = 'asc';
    }
    applyFilter();
};

// Define the columns for the table
const columns: ColumnDef<Ship>[] = [
    {
        accessorKey: 'mmsi',
        header: 'MMSI',
    },
    {
        accessorKey: 'country_name', // Cargo Category
        header: 'Flag',
    },
    {
        accessorKey: 'name',
        header: 'Name',
    },
    {
        accessorKey: 'imo',
        header: 'IMO',
    },
    {
        accessorKey: 'callsign',
        header: 'Call Sign',
    },
    {
        accessorKey: 'cargo_category_name', // Cargo Category
        header: 'Cargo Category',
    },
    {
        accessorKey: 'status',
        header: 'Status',
    },
    {
        accessorKey: 'last_updated',
        header: 'Last Updated',
    },
    {
        accessorKey: 'id',
        header: '',
    },
];

// Initialize the table
const table = useVueTable({
    data: props.ships?.data || [],
    columns,
    getCoreRowModel: getCoreRowModel(),
    getPaginationRowModel: getPaginationRowModel(),
    getSortedRowModel: getSortedRowModel(),
    getFilteredRowModel: getFilteredRowModel(),
    state: {
        sorting: [],
        columnFilters: [],
        columnVisibility: {},
        rowSelection: {},
    },
});
</script>

<template>
    <Head title="Ships" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-4 p-4 pt-4">
            <!-- Filter names -->
            <div class="flex items-center pb-2">
                <Input class="max-w" placeholder="Filter names..." v-model="search" @update:model-value="applyFilter" />
            </div>

            <!-- Table -->
            <div class="rounded-md border">
                <Table>
                    <TableHeader>
                        <TableRow v-for="headerGroup in table.getHeaderGroups()" :key="headerGroup.id">
                            <TableHead v-for="header in headerGroup.headers" :key="header.id" @click="sort(header.column.id)" class="cursor-pointer">
                                <FlexRender v-if="!header.isPlaceholder" :render="header.column.columnDef.header" :props="header.getContext()" />
                                <!-- Sorting icon -->
                                <span v-if="sortField === header.column.id">
                                    {{ sortDirection === 'asc' ? '↑' : '↓' }}
                                </span>
                            </TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <template v-if="ships.data.length">
                            <TableRow v-for="ship in ships.data" :key="ship.id">
                                <TableCell>{{ ship.mmsi }}</TableCell>
                                <TableCell>
                                    <div className="flex items-center space-x-2">
                                        <country-flag :country="ship.country?.country_iso_code" rounded />
                                        <Label class="mt-2">{{ ship.country.name }}</Label>
                                    </div>
                                </TableCell>
                                <TableCell>{{ ship.name }}</TableCell>
                                <TableCell>{{ ship.imo }}</TableCell>
                                <TableCell>{{ ship.callsign }}</TableCell>
                                <TableCell>{{ ship.cargo_category?.name || 'N/A' }}</TableCell>
                                <TableCell>
                                    <Badge
                                        variant="secondary"
                                        :class="ship.status === 'LIVE' ? 'bg-green-500 text-white w-24' : 'bg-red-500 text-white w-24'"
                                    >
                                        {{ ship.status }}
                                    </Badge>
                                </TableCell>
                                <TableCell>{{ ship.last_updated }}</TableCell>
                                <TableCell>
                                    <Button size="sm" @click="router.route('ships.show', { id: ship.id })">
                                        Info
                                    </Button>
                                </TableCell>
                            </TableRow>
                        </template>
                        <TableRow v-else>
                            <TableCell :colspan="columns.length" class="h-24 text-center"> No results. </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <!-- Paginate -->
            <div class="flex items-center justify-end space-x-2">
                <!-- page of pages -->

                <div class="flex-1 text-sm text-muted-foreground">{{ ships.total }} results</div>

                <div>
                    <span class="text-sm text-muted-foreground">Page</span>
                    <span class="px-1 text-sm font-medium">{{ ships.current_page }}</span>
                    <span class="text-sm text-muted-foreground">of</span>
                    <span class="px-1 text-sm font-medium">{{ ships.last_page }}</span>
                </div>

                <div class="space-x-2">
                    <Button variant="outline" size="sm" :disabled="ships.current_page === 1" @click="router.get(ships.prev_page_url)">
                        Previous
                    </Button>
                    <Button variant="outline" size="sm" :disabled="ships.current_page === ships.last_page" @click="router.get(ships.next_page_url)">
                        Next
                    </Button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
