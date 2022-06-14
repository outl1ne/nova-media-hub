<template>
  <LoadingView :loading="loading" :key="pageId">
    <div class="nml-flex nml-border nml-full nml-border-slate-800">
      <!-- Collections list -->
      <div class="nml-flex nml-flex-column">
        <span>Collection names</span>
      </div>

      <!-- Media list -->
      <div class="nml-flex">
        <span>Media items</span>
      </div>
    </div>
  </LoadingView>
</template>

<script>
export default {
  data: () => ({
    loading: true,
  }),

  mounted() {
    setTimeout(() => (this.loading = false), 200);
  },
};
</script>
