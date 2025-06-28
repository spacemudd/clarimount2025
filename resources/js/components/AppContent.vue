<script setup lang="ts">
import { SidebarInset } from '@/components/ui/sidebar';
import { computed } from 'vue';
import { cn } from '@/lib/utils';

interface Props {
    variant?: 'header' | 'sidebar';
    class?: string;
}

const props = defineProps<Props>();
const className = computed(() => cn(props.class));
const sidebarClassName = computed(() => cn('p-4 md:p-6', props.class));
</script>

<template>
    <SidebarInset v-if="props.variant === 'sidebar'" :class="sidebarClassName">
        <slot />
    </SidebarInset>
    <main v-else class="mx-auto flex h-full w-full max-w-7xl flex-1 flex-col gap-4 rounded-xl" :class="className">
        <slot />
    </main>
</template>
