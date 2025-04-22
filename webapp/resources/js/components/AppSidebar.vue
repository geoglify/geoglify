<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { Map, FolderClock, Ship, User, Lock, Pickaxe, Anchor, LandPlot, Antenna } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

const mainNavItems: NavItem[] = [
    {
        title: 'Realtime Map',
        href: '/',
        icon: Map,
    },
    {
        title: 'Traffic Analysis',
        href: '/traffic',
        icon: FolderClock ,
    },
    // Ships
    {
        title: 'Ships',
        href: '/ships',
        icon: Ship,
    },
    // Ports
    {
        title: 'Ports',
        href: '/ports',
        icon: Anchor,
    },
    // Terminals
    {
        title: 'Terminals',
        href: '/terminals',
        icon: LandPlot,
    },
];

const managementNavItems: NavItem[] = [
    // AIS
    {
        title: 'AIS',
        href: '/ais',
        icon: Antenna,
    },
    // Users
    {
        title: 'Users',
        href: '/users',
        icon: User,
    },
    // Roles
    {
        title: 'Roles',
        href: '/roles',
        icon: Pickaxe,
    },
    // Permissions
    {
        title: 'Permissions',
        href: '/permissions',
        icon: Lock,
    },
];

const footerNavItems: NavItem[] = [
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('ships.index')">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" title="Plaform" />
            <NavMain :items="managementNavItems" title="Management" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
