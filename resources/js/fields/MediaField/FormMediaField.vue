<template>
  <DefaultField :field="field" :errors="errors" :show-help-text="showHelpText" class="nova-media-field form-field">
    <template #field>
      <div class="nml-flex nml-flex-col">TODO</div>
    </template>
  </DefaultField>
</template>

<script>
import { FormField, HandlesValidationErrors } from 'laravel-nova';

export default {
  mixins: [FormField, HandlesValidationErrors],
  props: ['resourceName', 'resourceId', 'field'],
};
</script>

<style lang="scss">
// ...
</style>
