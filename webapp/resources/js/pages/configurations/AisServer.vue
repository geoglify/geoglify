<script setup lang="ts">
import { ref, onBeforeUnmount, watch } from 'vue';
import { TransitionRoot } from '@headlessui/vue';
import { Head, useForm } from '@inertiajs/vue3';
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import ConfigurationsLayout from '@/layouts/configurations/Layout.vue';
import { type BreadcrumbItem } from '@/types';

interface Props {
    ais_host: string;
    ais_port: string;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'AIS Server',
        href: '/configurations/ais-server',
    },
];

const form = useForm({
    ais_host: props.ais_host,
    ais_port: props.ais_port,
});

const submit = () => {
    form.patch(route('ais-server.update'), {
        preserveScroll: true,
    });
};

// Terminal state
const connectionState = ref<'connecting' | 'connected' | 'disconnected'>('disconnected');
const consoleData = ref('');
let eventSource: EventSource | null = null;
const interval = ref<ReturnType<typeof setInterval> | null>(null); 

const connect = () => {
    // Prevent multiple connections if already connected or connecting
    if (connectionState.value === 'connected' || connectionState.value === 'connecting') return;

    connectionState.value = 'connecting';
    const url = `http://localhost:9002/stream`;

    eventSource = new EventSource(url);

    // Print the connection URL to the console
    printConsole(`📡 Connecting to AIS server...`);

    // Handle connection open event
    eventSource.onopen = () => {
        connectionState.value = 'connected';
        printConsole(`✔️ Connected to AIS server!`);
        clearIntervalIfNeeded(); // Ensure no retries are happening when connected
    };

    // Handle incoming messages from the EventSource
    eventSource.onmessage = (event) => {
        printConsole(`Received message: ${event.data}`);
    };

    // Handle connection errors
    eventSource.onerror = () => {
        printConsole(`❌ Connection error. Retrying...`);
        disconnect(); // Stop the previous connection attempt

        // Start retrying every second after disconnecting
        interval.value = setInterval(() => {
            connect(); // Try reconnecting
        }, 2000); // Retry every 2 seconds
    };
};

const disconnect = () => {
    if (eventSource) {
        eventSource.close();
        eventSource = null;
    }
    connectionState.value = 'disconnected';

    // Clear the retry interval when disconnecting
    clearIntervalIfNeeded();
};

// Function to clear the retry interval if it's set
const clearIntervalIfNeeded = () => {
    if (interval.value) {
        clearInterval(interval.value);
        interval.value = null;
    }
};

const toggleConnection = () => {
    if (connectionState.value === 'connected' || connectionState.value === 'connecting') {
        disconnect();
    } else {
        connect();
    }
};

const clearConsole = () => {
    consoleData.value = '';
}

const printConsole = (message: string) => {
    const timestamp = new Date().toLocaleTimeString();
    consoleData.value += `[${timestamp}] ${message}\n`;

    // Limit the console to a maximum number of lines
    const lines = consoleData.value.split('\n');
    if (lines.length > 100) {
        consoleData.value = lines.slice(lines.length - 100).join('\n');
    }
};

const consoleTextarea = ref<HTMLElement | null>(null);

// Function to automatically update scroll position
watch(consoleData, () => {
    if (consoleTextarea.value) {
        consoleTextarea.value.scrollTop = consoleTextarea.value.scrollHeight;
    }
});

onBeforeUnmount(() => {
    disconnect();
});

</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">

        <Head title="AIS Server" />

        <ConfigurationsLayout>
            <div class="grid grid-cols-1 gap-6">

                <div class="flex flex-col space-y-6">
                    <HeadingSmall title="AIS Server"
                        description="Configure the AIS server settings for your application" />

                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="grid gap-2">
                            <Label for="ais_host">Host</Label>
                            <Input id="ais_host" v-model="form.ais_host" required placeholder="Host / IP" />
                            <InputError class="mt-2" :message="form.errors.ais_host" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="ais_port">Port</Label>
                            <Input id="ais_port" type="number" v-model="form.ais_port" required placeholder="Port" />
                            <InputError class="mt-2" :message="form.errors.ais_port" />
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <Button type="submit" :disabled="form.processing">Save</Button>
                        </div>

                        <TransitionRoot :show="form.recentlySuccessful" enter="transition ease-in-out"
                            enter-from="opacity-0" leave="transition ease-in-out" leave-to="opacity-0">
                            <p class="text-sm text-neutral-600">Saved.</p>
                        </TransitionRoot>
                    </form>
                </div>
                
                <div class="space-y-2 mt-0">
                    <div class="flex justify-between items-center">
                        <Label class="block"><b>AIS Console</b></Label>
                        <p class="text-sm text-neutral-600">
                            <span :class="{
                                'text-green-500': connectionState === 'connected',
                                'text-yellow-500 animate-pulse': connectionState === 'connecting',
                                'text-red-500': connectionState === 'disconnected'
                            }">
                                {{ connectionState === 'connected' ? '🟢 Connected' : connectionState === 'connecting' ?
                                '🟡 Connecting' : '🔴 Disconnected' }}
                            </span>
                        </p>
                    </div>

                    <textarea ref="consoleTextarea" readonly
                        class="w-full h-[450px] p-2 gap-0 font-mono text-sm border rounded-md resize-none bg-neutral-900 text-white"
                        :value="consoleData"></textarea>

                    <div class="flex flex-wrap gap-2">
                        <Button type="button" variant="outline" @click="toggleConnection">{{ connectionState ===
                            'connected' ? 'Disconnect' : 'Connect' }}</Button>
                        <Button type="button" variant="ghost" @click="clearConsole">Clear Console</Button>
                    </div>
                </div>
            </div>
        </ConfigurationsLayout>
    </AppLayout>
</template>
