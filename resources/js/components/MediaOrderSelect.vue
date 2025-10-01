<template>
  <SelectControl v-bind="$attrs" :options="options()" class="o1-w-[130px]" />
</template>

<script>
export default {
  props: ['columns'],

  methods: {
    options() {
      const orderColumns = this.columns.flatMap(column => [
        { value: `${column}:asc`, label: `↑ ${this.__(`novaMediaHub.orderBy.${column}`)}` },
        { value: `${column}:desc`, label: `↓ ${this.__(`novaMediaHub.orderBy.${column}`)}` },
      ]);

      return [{ value: '', label: this.__('novaMediaHub.orderBy.default') }].concat(orderColumns);
    },
  },
};
</script>

<style lang="scss">
.o1-w-\[130px\] {
  width: 130px;
}
</style>
