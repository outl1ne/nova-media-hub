<template>
  <select v-bind="$attrs" :value="modelValue" @input="$emit('update:modelValue', $event.target.value)">
    <option value="">{{ __('novaMediaHub.orderBy.default') }}</option>
    <template v-for="column in columns" :key="column">
      <option v-for="direction in ['asc', 'desc']" :key="`${column}:${direction}`" :value="`${column}:${direction}`">
        {{ direction === 'asc' ? '&uarr;' : '&darr;' }}
        {{ __(`novaMediaHub.orderBy.${column}`) }}
      </option>
    </template>
  </select>
</template>

<script>
export default {
  props: ['columns', 'modelValue'],
  emits: ['update:modelValue'],
};
</script>
