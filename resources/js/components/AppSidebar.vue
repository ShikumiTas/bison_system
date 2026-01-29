    <script setup lang="ts">
    import NavFooter from '@/components/NavFooter.vue';
    import NavMain from '@/components/NavMain.vue';
    import NavUser from '@/components/NavUser.vue';
    import {
        Sidebar,
        SidebarContent,
        SidebarFooter,
        SidebarHeader,
        SidebarMenu,
        SidebarMenuButton,
        SidebarMenuItem,
    } from '@/components/ui/sidebar';
    import { dashboard } from '@/routes';

    import { type NavItem } from '@/types';
    import { Link } from '@inertiajs/vue3';
    import { BookOpen, Folder, LayoutGrid } from 'lucide-vue-next';
    import AppLogo from './AppLogo.vue';
    
        const mainNavItems: NavItem[] = [
            {
                title: 'Dashboard',
                href: dashboard(),
                icon: LayoutGrid,
            },
            {
                title: '案件管理', 
                href: dashboard(),
                icon: LayoutGrid,
            },
            {
                title: '　入札案件登録',
                href: dashboard(),
                icon: LayoutGrid,
            },
            {
                title: '　案件一覧',
                href: dashboard(),
                icon: LayoutGrid,
            },
            {
                title: '　案件詳細',
                href: dashboard(),
                icon: LayoutGrid,
            },
            {
                title: '経審・企業管理',
                href: dashboard(),
                icon: LayoutGrid,
            },
            {
                title: '　経審・企業登録',
                href: dashboard(),
                icon: LayoutGrid,
            },
            {
                title: '　経審・企業一覧',
                href: dashboard(),
                icon: LayoutGrid,
            },
            {
                title: '　経審・企業情報',
                href: dashboard(),
                icon: LayoutGrid,
            },
    ];

const footerNavItems: NavItem[] = [
    {
        title: 'Github Repo',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: Folder,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: BookOpen,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
