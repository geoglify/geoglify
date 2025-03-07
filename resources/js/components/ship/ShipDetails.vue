<script setup lang="ts">
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableRow } from '@/components/ui/table';

defineProps<{
    ship?: object;
}>();
</script>

<template>
    <Card>
        <CardHeader>
            <CardTitle>{{ $t('ship.card_details_title') }}</CardTitle>
            <CardDescription>{{ $t('ship.card_details_description') }}</CardDescription>
        </CardHeader>
        <CardContent>
            <Table>
                <TableBody>
                    <TableRow v-for="(attribute, key) in ship" :key="key">
                        <TableCell class="font-medium">{{ attribute.label }}</TableCell>
                        <TableCell>{{ attribute.value ?? 'N/A' }}</TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </CardContent>
    </Card>
</template>
