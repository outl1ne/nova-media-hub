<template>
  <!-- v-model:selected="orderBy" @change="selected => (orderBy = selected)" -->
  <SelectControl v-bind="$attrs">
    <option value="">{{ '> Show all' }}</option>
    <template v-for="column in columns" :key="column">
      <option v-for="direction in ['asc', 'desc']" :key="`${column}:${direction}`" :value="`${column}:${direction}`">
        <span class="o1-ml-auto">{{ direction === 'asc' ? '&uarr;' : '&darr;' }}</span>
        {{ __(`novaMediaHub.orderBy.${column}`) }}
      </option>
    </template>
  </SelectControl>
</template>

<script>
export default {
  props: ['columns'],
};
</script>
