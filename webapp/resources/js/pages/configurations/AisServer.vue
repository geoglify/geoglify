<script setup lang="ts">
import { TransitionRoot } from '@headlessui/vue';
import { Head,  useForm } from '@inertiajs/vue3';

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
    ais_port: number;
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
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="AIS Server" />

        <ConfigurationsLayout>
            <div class="flex flex-col space-y-6">
                <HeadingSmall title="AIS Server" description="Configure the AIS server settings for your application" />

                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid gap-2">
                        <Label for="ais_host">Host</Label>
                        <Input id="ais_host" class="mt-1 block w-full" v-model="form.ais_host" required autocomplete="ais_host" placeholder="Host / IP" />
                        <InputError class="mt-2" :message="form.errors.ais_host" />
                    </div>
                    
                    <div class="grid gap-2">
                        <Label for="ais_port">Port</Label>
                        <Input id="ais_port" type="number" class="mt-1 block w-full" v-model="form.ais_port" required autocomplete="port" placeholder="Port" />
                        <InputError class="mt-2" :message="form.errors.ais_port" />
                    </div>

                    <div class="flex items-center gap-4">
                        <Button :disabled="form.processing">Save</Button>

                        <TransitionRoot
                            :show="form.recentlySuccessful"
                            enter="transition ease-in-out"
                            enter-from="opacity-0"
                            leave="transition ease-in-out"
                            leave-to="opacity-0"
                        >
                            <p class="text-sm text-neutral-600">Saved.</p>
                        </TransitionRoot>
                    </div>
                </form>
            </div>
            
        </ConfigurationsLayout>
    </AppLayout>
</template>
