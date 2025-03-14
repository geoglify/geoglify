<script setup lang="ts">
import { Table, TableBody, TableCell, TableRow } from '@/components/ui/table';

defineProps<{
    ship?: object;
}>();
</script>

<template>
    <Table>
        <TableBody>
            <TableRow v-for="(attribute, key) in ship" :key="key">
                <TableCell class="font-medium">{{ attribute.label }}</TableCell>
                <TableCell>{{ attribute.value ?? 'N/A' }}</TableCell>
            </TableRow>
        </TableBody>
    </Table>
</template>
