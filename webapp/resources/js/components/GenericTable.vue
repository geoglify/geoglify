<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { type ColumnDef, FlexRender, getCoreRowModel, useVueTable } from '@tanstack/vue-table';
import { ref, watch } from 'vue';

interface PaginatedData<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    prev_page_url: string | null;
    next_page_url: string | null;
}

interface TableFilters {
    search?: string;
    sort?: string;
    direction?: string;
    [key: string]: any;
}

const props = defineProps<{
    title: string;
    breadcrumbs: BreadcrumbItem[];
    data: PaginatedData<any>;
    columns: ColumnDef<any>[];
    filters?: TableFilters;
    routeName: string;
    defaultSort?: string;
    defaultDirection?: string;
    showSearch?: boolean;
}>();

const search = ref(props.filters?.search || '');
const sortField = ref(props.filters?.sort || props.defaultSort || 'name');
const sortDirection = ref(props.filters?.direction || props.defaultDirection || 'asc');

const applyFilter = () => {
    console.log('Applying filter');
    // search.value, sortField.value, sortDirection.value
    console.log(search.value, sortField.value, sortDirection.value);
    // router replace
    router.get(
        '/ships',
        {
            search: search.value,
            sort: sortField.value,
            direction: sortDirection.value,
        },
        { replace: true },
    );
};

// Atualizar o filtro de pesquisa ao digitar
watch(search, () => {
    applyFilter();
});

const sort = (field: string) => {
    if (sortField.value === field) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortField.value = field;
        sortDirection.value = 'asc';
    }

    applyFilter();
};

// Initialize the table
const table = useVueTable({
    data: props.data.data,
    columns: props.columns,
    getCoreRowModel: getCoreRowModel(),
});
</script>

<template>
    <!-- Rest of the template remains the same -->
    <Head :title="title" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-4 p-4 pt-4">
            <!-- Search filter -->
            <div v-if="showSearch !== false" class="flex items-center pb-2">
                <Input class="max-w" placeholder="Search..." v-model="search" />
            </div>

            <!-- Table -->
            <div class="rounded-md border">
                <Table>
                    <TableHeader>
                        <TableRow v-for="headerGroup in table.getHeaderGroups()" :key="headerGroup.id">
                            <TableHead
                                v-for="header in headerGroup.headers"
                                :key="header.id"
                                @click.prevent="sort(header.column.id)"
                                class="cursor-pointer"
                            >
                                <FlexRender v-if="!header.isPlaceholder" :render="header.column.columnDef.header" :props="header.getContext()" />
                                <span v-if="sortField === header.column.id">
                                    {{ sortDirection === 'asc' ? '↑' : '↓' }}
                                </span>
                            </TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <template v-if="data.data.length">
                            <TableRow v-for="row in data.data" :key="row.id">
                                <TableCell v-for="column in columns" :key="column.accessorKey">
                                    <slot :name="`cell-${column.accessorKey}`" :row="row" :value="row[column.accessorKey as string]">
                                        {{ row[column.accessorKey as string] || 'Unknown' }}
                                    </slot>
                                </TableCell>
                            </TableRow>
                        </template>
                        <TableRow v-else>
                            <TableCell :colspan="columns.length" class="h-24 text-center"> No results found. </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <!-- Pagination -->
            <div class="flex items-center justify-end space-x-2">
                <div class="flex-1 text-sm text-muted-foreground">{{ data.total }} results</div>

                <div>
                    <span class="text-sm text-muted-foreground">Page</span>
                    <span class="px-1 text-sm font-medium">{{ data.current_page }}</span>
                    <span class="text-sm text-muted-foreground">of</span>
                    <span class="px-1 text-sm font-medium">{{ data.last_page }}</span>
                </div>

                <div class="space-x-2">
                    <Button variant="outline" size="sm" :disabled="data.current_page === 1" @click="router.get(data.prev_page_url)">
                        Previous
                    </Button>
                    <Button variant="outline" size="sm" :disabled="data.current_page === data.last_page" @click="router.get(data.next_page_url)">
                        Next
                    </Button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
