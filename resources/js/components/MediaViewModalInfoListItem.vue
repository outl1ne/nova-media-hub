<template>
  <div class="o1-flex o1-flex-col o1-mb-2">
    <label class="o1-leading-tight">{{ label }}</label>
    <input
      type="text"
      class="o1-bg-slate-50 o1-border o1-border-slate-100 o1-rounded-md o1-px-2 o1-py-1 o1-whitespace-nowrap o1-text-slate-500 dark:o1-bg-slate-800 dark:o1-border-slate-700 dark:o1-text-slate-400 o1-overflow-x-auto"
      :value="value"
      :disabled="true"
    />
  </div>
</template>

<script>
export default {
  props: ['label', 'value'],
};
</script>
