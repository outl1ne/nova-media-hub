<template>
  <div class="o1-flex o1-flex-col o1-mb-2">
    <span class="o1-text-xs o1-leading-tight o1-text-teal-600 o1-font-semibold o1-ml-1">{{ label }}</span>
    <span
      class="o1-bg-slate-50 o1-border o1-border-slate-100 o1-rounded-md o1-px-2 o1-py-1 o1-whitespace-nowrap o1-text-slate-500"
    >
      {{ value }}
    </span>
  </div>
</template>

<script>
export default {
  props: ['label', 'value'],
};
</script>
