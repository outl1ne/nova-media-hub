<template>
  <LoadingView :loading="loading" :key="pageId">
    <div class="nml-flex nml-border nml-full nml-border-slate-200 nml-rounded nml-bg-white nml-shadow">
      <!-- Collections list -->
      <div class="nml-flex nml-flex-col nml-border-r nml-border-slate-200">
        <div class="nml-font-bold nml-border-b nml-border-slate-200 nml-px-6 nml-py-2">Collections</div>

        <div class="nml-flex nml-flex-col">
          <span>todo</span>
        </div>
      </div>

      <!-- Media list -->
      <div class="nml-flex nml-p-4">
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
