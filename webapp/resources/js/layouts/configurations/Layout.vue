<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';

const sidebarNavItems: NavItem[] = [
    {
        title: 'Map Settings',
        href: '/configurations/map-settings',
    },
    {
        title: 'AIS Server',
        href: '/configurations/ais-server',
    },
];

const currentPath = window.location.pathname;
</script>

<template>
    <div class="px-4 py-6">
        <Heading title="Configurations" description="Manage and configure application settings" />

        <div class="flex flex-col space-y-8 md:space-y-0 lg:flex-row lg:space-x-12">
            <aside class="w-full lg:w-48">
                <nav class="flex flex-col space-y-1">
                    <Button
                        v-for="item in sidebarNavItems"
                        :key="item.href"
                        variant="ghost"
                        :class="['w-full justify-start', { 'bg-muted': currentPath === item.href }]"
                        as-child
                    >
                        <Link :href="item.href">
                            {{ item.title }}
                        </Link>
                    </Button>
                </nav>
            </aside>

            <Separator class="my-6 md:hidden" />

            <div class="flex-1 w-full min-w-0">
                <section class="w-full space-y-12">
                    <slot />
                </section>
            </div>
        </div>
    </div>
</template>
