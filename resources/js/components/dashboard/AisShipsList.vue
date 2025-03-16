<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { valueUpdater } from '@/utils.ts';
import type { ColumnDef, ColumnFiltersState, SortingState, VisibilityState } from '@tanstack/vue-table';
import { FlexRender, getCoreRowModel, getFilteredRowModel, getPaginationRowModel, getSortedRowModel, useVueTable } from '@tanstack/vue-table';
import { ArrowRight } from 'lucide-vue-next';
import { h, ref } from 'vue';

// Define the type for the ship data
export interface Ship {
    name: string;
    mmsi: string;
    last_updated: string;
    country_iso_code: string;
}

// Props passed to the component
const props = defineProps<{
    ships?: Ship[];
}>();

// Default ship data
const data: Ship[] = props.ships || [];

// Define the columns for the table
const columns: ColumnDef<Ship>[] = [
    {
        accessorKey: 'country_iso_code',
        header: 'Flag', // Column header
        cell: ({ row }) => row.getValue('country_iso_code'), // Render flag column data
    },
    {
        accessorKey: 'name',
        header: 'Name', // Column header
        cell: ({ row }) => h('div', { class: 'capitalize' }, row.getValue('name')), // Render name column data
    },
    // Details column for action (with a button to open the ship's page)
    {
        accessorKey: 'id', // Unique identifier for the ship
        header: '', // Column header
        cell: ({ row }) => {
            const shipId = row.getValue('id'); // Use the ship's id as identifier
            return h(
                Button,
                {
                    variant: 'outline',
                    size: 'sm',
                    class: 'ml-auto',
                    onClick: () => {
                        // Redirect to the ship details page
                        window.location.href = `/ships/${shipId}`;
                    },
                },
                () => [
                    h(ArrowRight, { class: 'h-4 w-4 text-right' }), // Button with an arrow icon
                ],
            );
        },
    },
];

// State management for table sorting, filters, visibility, and row selection
const sorting = ref<SortingState>([]);
const columnFilters = ref<ColumnFiltersState>([]);
const columnVisibility = ref<VisibilityState>({});
const rowSelection = ref({});

// Initialize the table using the Vue Table hooks
const table = useVueTable({
    data,
    columns,
    getCoreRowModel: getCoreRowModel(),
    getPaginationRowModel: getPaginationRowModel(),
    getSortedRowModel: getSortedRowModel(),
    getFilteredRowModel: getFilteredRowModel(),
    onSortingChange: (updaterOrValue) => valueUpdater(updaterOrValue, sorting),
    onColumnFiltersChange: (updaterOrValue) => valueUpdater(updaterOrValue, columnFilters),
    onColumnVisibilityChange: (updaterOrValue) => valueUpdater(updaterOrValue, columnVisibility),
    onRowSelectionChange: (updaterOrValue) => valueUpdater(updaterOrValue, rowSelection),
    state: {
        get sorting() {
            return sorting.value;
        },
        get columnFilters() {
            return columnFilters.value;
        },
        get columnVisibility() {
            return columnVisibility.value;
        },
        get rowSelection() {
            return rowSelection.value;
        },
    },
    initialState: {
        pagination: { pageSize: 8 },
    },
});
</script>

<template>
    <div class="w-full">
        <!-- Filter input for filtering ship names -->
        <div class="flex items-center pb-2">
            <Input
                class="max-w"
                placeholder="Filter names..."
                :model-value="table.getColumn('name')?.getFilterValue() as string"
                @update:model-value="table.getColumn('name')?.setFilterValue($event)"
            />
        </div>

        <!-- Table structure with header and body -->
        <div class="rounded-md border">
            <Table>
                <TableHeader>
                    <TableRow v-for="headerGroup in table.getHeaderGroups()" :key="headerGroup.id">
                        <TableHead v-for="header in headerGroup.headers" :key="header.id">
                            <!-- Render each column header dynamically -->
                            <FlexRender v-if="!header.isPlaceholder" :render="header.column.columnDef.header" :props="header.getContext()" />
                        </TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <!-- Render table rows if available -->
                    <template v-if="table.getRowModel().rows?.length">
                        <TableRow v-for="row in table.getRowModel().rows" :key="row.id" :data-state="row.getIsSelected() && 'selected'">
                            <TableCell v-for="cell in row.getVisibleCells()" :key="cell.id" class="truncate p-1">
                                <template v-if="cell.column.id === 'country_iso_code'">
                                    <!-- Render country flag based on country_iso_code -->
                                    <country-flag
                                        v-if="row.getValue('country_iso_code')"
                                        rounded
                                        :country="row.getValue('country_iso_code')"
                                        size="normal"
                                    />
                                </template>
                                <template v-else>
                                    <!-- Render other cell content -->
                                    <FlexRender :render="cell.column.columnDef.cell" :props="cell.getContext()" />
                                </template>
                            </TableCell>
                        </TableRow>
                    </template>

                    <!-- If no rows, show a message -->
                    <TableRow v-else>
                        <TableCell :colspan="columns.length" class="h-24 text-center"> No results. </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>

        <!-- Pagination controls -->
        <div class="flex items-center justify-end space-x-2 py-4">
            <div class="flex-1 text-sm text-muted-foreground">{{ table.getFilteredRowModel().rows.length }} results</div>
            <div class="space-x-2">
                <Button variant="outline" size="sm" :disabled="!table.getCanPreviousPage()" @click="table.previousPage()"> Previous </Button>
                <Button variant="outline" size="sm" :disabled="!table.getCanNextPage()" @click="table.nextPage()"> Next </Button>
            </div>
        </div>
    </div>
</template>

<style>
/* Add styling for truncating text in table cells */
.truncate {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
</style>
