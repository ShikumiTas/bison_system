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
    
    //     const mainNavItems: NavItem[] = [
    //         {
    //             title: 'Dashboard',
    //             href: dashboard(),
    //             icon: LayoutGrid,
    //         },

    //         {
    //             title: '案件管理',
    //             icon: Folder,
    //             isActive: true, // 最初から展開しておくフラグ
    //             items: [        // 子メニューを配列で持たせる
    //                 {
    //                     title: '入札案件登録',
    //                     href: dashboard(),
    //                 },

    //                 {
    //                     title: '案件一覧',
    //                     href: '/project/index',
    //                 },
    //                 {
    //                     title: '案件詳細',
    //                     href: '/project/edit/0',
    //                 },
    //             ],
    //         },            
    //         // {
    //         //     title: '案件管理', 
    //         //     href: dashboard(),
    //         //     icon: LayoutGrid,
    //         // },
    //         // {
    //         //     title: '　入札案件登録',
    //         //     href: dashboard(),
    //         //     icon: LayoutGrid,
    //         // },
    //         // {
    //         //     title: '　案件一覧',
    //         //     href: '/project/index',
    //         //     icon: LayoutGrid,
    //         // },
    //         // {
    //         //     title: '　案件詳細',
    //         //     href: '/project/edit/0',
    //         //     icon: LayoutGrid,
    //         // },
    //         // {
    //         //     title: '経審・企業管理',
    //         //     href: dashboard(),
    //         //     icon: LayoutGrid,
    //         // },
    //         // {
    //         //     title: '　経審・企業登録',
    //         //     href: dashboard(),
    //         //     icon: LayoutGrid,
    //         // },
    //         // {
    //         //     title: '　経審・企業一覧',
    //         //     href: '/biz/index',
    //         //     icon: LayoutGrid,
    //         // },
    //         // {
    //         //     title: '　経審・企業情報',
    //         //     href: '/biz/edit/0',
    //         //     icon: LayoutGrid,
    //         // },
    // ];

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: 'dashboard',
        icon: LayoutGrid,
    },
    {
        title: '案件管理',
        icon: Folder,
        isActive: false, // 初回レンダリング時に展開しておくフラグ
        items: [
            {
                title: '案件一覧',
                href: '/project/index',
            },
        ],
    },
    {
        title: '経審・企業管理',
        icon: Folder,
        isActive: false,
        items: [
            {
                title: '経審・企業一覧',
                href: '/biz/index',
            },
        ],
    },
    {
        title: '外部データの取り込み',
        icon: Folder,
        isActive: false,
        items: [
            {
                title: '外部データの取り込み',
                href: '/import',
            },
        ],
    },
];
const footerNavItems: NavItem[] = [
    {
        title: 'NJSS入札情報速報サービス',
        href: 'https://www2.njss.info/',
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
