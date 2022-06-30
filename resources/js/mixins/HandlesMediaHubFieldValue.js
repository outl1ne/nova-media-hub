export default {
  data: () => ({
    value: void 0,
  }),

  created() {
    this.setInitialValue();
  },

  methods: {
    setInitialValue() {
      let value = this.field.value;
      const multiple = this.field.multiple;

      if (multiple && Array.isArray(value)) {
        this.value = value.map(id => this.field.media[id]).filter(Boolean);
      } else if (!!value) {
        if (Array.isArray(value)) value = value[0];
        this.value = this.field.media[value];
      }
    },
  },
};
