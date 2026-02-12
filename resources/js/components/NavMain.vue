<script setup lang="ts">
import {
  Collapsible,
  CollapsibleContent,
  CollapsibleTrigger
} from '@/components/ui/collapsible';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
  SidebarMenuSub,
  SidebarMenuSubButton,
  SidebarMenuSubItem,
  useSidebar,
} from '@/components/ui/sidebar';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { ChevronRight } from 'lucide-vue-next';

defineProps<{
  items: NavItem[];
}>();

const { isCurrentUrl } = useCurrentUrl();
const { state, isMobile } = useSidebar();
</script>

<template>
  <SidebarMenu>
    <template v-for="item in items" :key="item.title">
      <template v-if="item.items && item.items.length > 0">
        
        <Collapsible 
          v-if="state !== 'collapsed' || isMobile"
          v-slot="{ open }" 
          as-child 
          :default-open="item.isActive"
          class="group/collapsible"
        >
          <SidebarMenuItem>
            <CollapsibleTrigger as-child>
              <SidebarMenuButton :tooltip="item.title" :is-active="item.href ? isCurrentUrl(item.href) : false">
                <component :is="item.icon" v-if="item.icon" :class="{ 'text-primary': open }" />
                <span>{{ item.title }}</span>
                <ChevronRight class="ml-auto transition-transform duration-200" :class="{ 'rotate-90': open }" />
              </SidebarMenuButton>
            </CollapsibleTrigger>
            <CollapsibleContent>
              <SidebarMenuSub>
                <SidebarMenuSubItem v-for="subItem in item.items" :key="subItem.title">
                  <SidebarMenuSubButton as-child :is-active="isCurrentUrl(subItem.href)">
                    <Link :href="subItem.href">
                      <span>{{ subItem.title }}</span>
                    </Link>
                  </SidebarMenuSubButton>
                </SidebarMenuSubItem>
              </SidebarMenuSub>
            </CollapsibleContent>
          </SidebarMenuItem>
        </Collapsible>

        <SidebarMenuItem v-else>
          <DropdownMenu>
            <DropdownMenuTrigger as-child>
              <SidebarMenuButton 
                :tooltip="item.title" 
                :is-active="item.isActive"
                class="w-full justify-center"
              >
                <component :is="item.icon" v-if="item.icon" class="mx-auto" />
              </SidebarMenuButton>
            </DropdownMenuTrigger>
            <DropdownMenuContent side="right" align="start" :side-offset="8" class="min-w-48">
              <DropdownMenuLabel>{{ item.title }}</DropdownMenuLabel>
              <DropdownMenuSeparator />
              <DropdownMenuItem v-for="subItem in item.items" :key="subItem.title" as-child>
                <Link :href="subItem.href" class="w-full cursor-pointer" :class="{ 'bg-accent text-accent-foreground': isCurrentUrl(subItem.href) }">
                  {{ subItem.title }}
                </Link>
              </DropdownMenuItem>
            </DropdownMenuContent>
          </DropdownMenu>
        </SidebarMenuItem>
      </template>

      <SidebarMenuItem v-else>
        <SidebarMenuButton 
          as-child 
          :tooltip="item.title" 
          :is-active="isCurrentUrl(item.href)"
          :class="{ 'justify-center': state === 'collapsed' }"
        >
          <Link :href="item.href">
            <component :is="item.icon" v-if="item.icon" :class="{ 'mx-auto': state === 'collapsed' }" />
            <span v-if="state !== 'collapsed'">{{ item.title }}</span>
          </Link>
        </SidebarMenuButton>
      </SidebarMenuItem>
    </template>
  </SidebarMenu>
</template>