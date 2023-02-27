<template>
  <div class="o1-flex o1-flex-col o1-mb-5">
    <label>{{ label }}</label>
    <input type="text" class="w-full form-control form-input form-input-bordered" :value="value" :disabled="true" />
  </div>
</template>

<script>
export default {
  props: ['label', 'value'],
};
</script>
