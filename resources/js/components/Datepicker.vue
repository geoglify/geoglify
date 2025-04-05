<script setup lang="ts">
import { cn } from '@/utils';
import type { DateRange } from 'reka-ui';

import { Button } from '@/components/ui/button';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { RangeCalendar } from '@/components/ui/range-calendar';
import { CalendarDate, DateFormatter, getLocalTimeZone, parseDate } from '@internationalized/date';
import { CalendarIcon } from 'lucide-vue-next';
import { ref, watchEffect } from 'vue';

const df = new DateFormatter('en-US', {
    dateStyle: 'medium',
});

const props = defineProps<{
    startDate?: string;
    endDate?: string;
}>();

const emit = defineEmits<{
    'update:startDate': [value: string];
    'update:endDate': [value: string];
}>();

const parseDateString = (dateString?: string): CalendarDate | undefined => {
    if (!dateString) return undefined;
    try {
        return parseDate(dateString);
    } catch {
        return undefined;
    }
};

const value = ref<DateRange>({
    start: parseDateString(props.startDate) || new CalendarDate(getLocalTimeZone().now().year, 1, 1),
    end: parseDateString(props.endDate) || new CalendarDate(getLocalTimeZone().now().year, 1, 1).add({ days: 7 }),
});

watchEffect(() => {
    if (value.value.start) {
        emit('update:startDate', value.value.start.toString());
    }
    if (value.value.end) {
        emit('update:endDate', value.value.end.toString());
    }
});
</script>

<template>
    <Popover>
        <PopoverTrigger as-child>
            <Button variant="outline" :class="cn('w-[280px] justify-start text-left font-normal', !value && 'text-muted-foreground')">
                <CalendarIcon class="mr-2 h-4 w-4" />
                <template v-if="value.start">
                    <template v-if="value.end">
                        {{ df.format(value.start.toDate(getLocalTimeZone())) }} - {{ df.format(value.end.toDate(getLocalTimeZone())) }}
                    </template>
                    <template v-else>
                        {{ df.format(value.start.toDate(getLocalTimeZone())) }}
                    </template>
                </template>
                <template v-else> Pick a date </template>
            </Button>
        </PopoverTrigger>
        <PopoverContent class="w-auto p-0">
            <RangeCalendar
                v-model="value"
                initial-focus
                :number-of-months="2"
                @update:start-value="
                    (startDate) => {
                        value.start = startDate;
                    }
                "
                @update:end-value="
                    (endDate) => {
                        value.end = endDate;
                    }
                "
            />
        </PopoverContent>
    </Popover>
</template>
