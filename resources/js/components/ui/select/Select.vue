<template>
  <div class="relative">
    <select
      :id="id"
      :value="modelValue"
      @input="$emit('update:modelValue', ($event.target as HTMLSelectElement).value)"
      :class="cn(selectVariants({ variant, size }), 'appearance-none', props.class)"
      :disabled="disabled"
      v-bind="$attrs"
    >
      <slot />
    </select>
    <div class="pointer-events-none absolute inset-y-0 ltr:right-0 rtl:left-0 flex items-center ltr:pr-3 rtl:pl-3">
      <ChevronDownIcon class="h-4 w-4 text-gray-400" />
    </div>
  </div>
</template>

<script setup lang="ts">
import { ChevronDown as ChevronDownIcon } from 'lucide-vue-next';
import { cva } from 'class-variance-authority';
import { cn } from '@/lib/utils';

const selectVariants = cva(
  'flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-base shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50 md:text-sm',
  {
    variants: {
      variant: {
        default: 'border-input',
        destructive: 'border-destructive text-destructive focus-visible:ring-destructive',
      },
      size: {
        default: 'h-9 px-3',
        sm: 'h-8 px-2 text-xs',
        lg: 'h-10 px-4',
      },
    },
    defaultVariants: {
      variant: 'default',
      size: 'default',
    },
  }
);

interface Props {
  modelValue?: string;
  id?: string;
  disabled?: boolean;
  class?: string;
  variant?: 'default' | 'destructive';
  size?: 'default' | 'sm' | 'lg';
}

const props = withDefaults(defineProps<Props>(), {
  variant: 'default',
  size: 'default',
});

defineEmits<{
  'update:modelValue': [value: string];
}>();
</script> 