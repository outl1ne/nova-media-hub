<template>
  <div class="o1-flex o1-flex-col o1-w-full">
    <div class="o1-leading-tight o1-text-primary-500 o1-font-bold o1-text-md o1-mb-2">
      {{ title }}
    </div>
    <slot />
  </div>
</template>

<script>
export default {
  props: ['title'],
};
</script>
