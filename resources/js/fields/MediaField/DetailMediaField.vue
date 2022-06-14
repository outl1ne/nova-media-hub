<template>
  <PanelItem :index="index" :field="field" class="nova-media-field detail-field">
    <template #value>
      <div>TODO</div>
    </template>
  </PanelItem>
</template>

<script>
export default {
  props: ['index', 'resource', 'resourceName', 'resourceId', 'field'],
};
</script>

<style lang="scss">
// ...
</style>
