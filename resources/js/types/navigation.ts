import type { InertiaLinkProps } from '@inertiajs/vue3';
import type { LucideIcon } from 'lucide-vue-next';

export type BreadcrumbItem = {
    title: string;
    href?: string;
};

// export type NavItem = {
//     title: string;
//     href: NonNullable<InertiaLinkProps['href']>;
//     icon?: LucideIcon;
//     isActive?: boolean;
// };

export type NavItem = {
    title: string;
    href?: string; // 親メニューが展開のみの場合は href が無いため、任意(?)にする
    icon?: LucideIcon;
    isActive?: boolean;
    items?: {      // 子メニューの構造を定義
        title: string;
        href: string;
    }[];
};
