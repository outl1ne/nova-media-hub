<template>
  <select
    className="o1-capitalize w-full block form-control form-control-bordered form-input o1-w-[130px]"
    v-model="collection"
  >
    <option v-for="option in options" :key="option.value" :value="option.value">{{ option.label }}</option>
  </select>
</template>

<script>
export default {
  props: ['columns'],

  computed: {
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
