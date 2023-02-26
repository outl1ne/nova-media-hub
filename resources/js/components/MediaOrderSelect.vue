<template>
  <SelectControl v-bind="$attrs">
    <option value="">{{ __('novaMediaHub.orderBy.default') }}</option>
    <template v-for="column in columns" :key="column">
      <option v-for="direction in ['asc', 'desc']" :key="`${column}:${direction}`" :value="`${column}:${direction}`">
        {{ __(`novaMediaHub.orderBy.${column}`) }}
        {{ direction === 'asc' ? '&uarr;' : '&darr;' }}
      </option>
    </template>
  </SelectControl>
</template>

<script>
export default {
  props: ['columns'],
};
</script>
