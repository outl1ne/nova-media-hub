<template>
  <select
    class="o1-capitalize w-full block form-control form-control-bordered form-input o1-w-[130px]"
    :value="normalizedSelected"
    @change="handleChange"
  >
    <option v-for="option in options" :key="option.value" :value="option.value">{{ option.label }}</option>
  </select>
</template>

<script>
export default {
  props: {
    columns: {
      type: Array,
      default: () => [],
    },
    selected: {
      type: String,
      default: '',
    },
  },

  emits: ['update:selected', 'change'],

  computed: {
    normalizedSelected() {
      return typeof this.selected === 'string' ? this.selected : '';
    },

    options() {
      const orderColumns = this.columns.flatMap(column => [
        { value: `${column}:asc`, label: `↑ ${this.__(`novaMediaHub.orderBy.${column}`)}` },
        { value: `${column}:desc`, label: `↓ ${this.__(`novaMediaHub.orderBy.${column}`)}` },
      ]);

      return [{ value: '', label: this.__('novaMediaHub.orderBy.default') }].concat(orderColumns);
    },
  },

  methods: {
    handleChange(event) {
      const value = event?.target?.value ?? '';

      this.$emit('update:selected', value);
      this.$emit('change', value);
    },
  },
};
</script>

<style lang="scss">
.o1-w-\[130px\] {
  width: 130px;
}
</style>
