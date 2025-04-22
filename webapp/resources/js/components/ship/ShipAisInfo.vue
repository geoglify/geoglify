<script setup lang="ts">
import { Table, TableBody, TableCell, TableRow } from '@/components/ui/table';

defineProps<{
    lastKnownPosition?: object;
}>();
</script>

<template>
    <Table>
        <TableBody>
            <TableRow v-for="(attribute, key) in lastKnownPosition" :key="key">
                <TableCell class="font-medium">{{ attribute.label }}</TableCell>
                <TableCell>{{ attribute.value ?? 'Unknown' }}</TableCell>
            </TableRow>
        </TableBody>
    </Table>
</template>
