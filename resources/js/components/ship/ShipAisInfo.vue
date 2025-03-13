<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableRow } from '@/components/ui/table';

defineProps<{
    shipRealtimePosition?: object;
}>();
</script>

<template>
    <Card>
        <CardHeader>
            <CardTitle>{{ $t('ship.card_ais_title') }}</CardTitle>
        </CardHeader>
        <CardContent>
            <Table>
                <TableBody>
                    <TableRow v-for="(attribute, key) in shipRealtimePosition" :key="key">
                        <TableCell class="font-medium">{{ attribute.label }}</TableCell>
                        <TableCell>{{ attribute.value ?? 'N/A' }}</TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </CardContent>
    </Card>
</template>
