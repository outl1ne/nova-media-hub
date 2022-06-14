<template>
  <LoadingView :loading="loading" :key="pageId">
    <div class="nml-flex nml-border nml-full nml-border-slate-200 nml-rounded nml-bg-white nml-shadow">
      <!-- Collections list -->
      <div class="nml-flex nml-flex-col nml-border-r nml-border-slate-200">
        <div class="nml-font-bold nml-border-b nml-border-slate-200 nml-px-6 nml-py-3 nml-text-center">Collections</div>

        <div class="nml-flex nml-flex-col">
          <div v-if="!collections.length" class="nml-text-sm nml-text-slate-400 nml-p-4 nml-whitespace-nowrap">
            No collections found
          </div>
        </div>
      </div>

      <!-- Media list -->
      <div class="nml-flex nml-p-4 nml-w-full" :class="{ 'nml-items-center nml-justify-center': !mediaItems.length }">
        <div v-if="!mediaItems.length" class="nml-text-sm nml-text-slate-400">No media items found</div>
      </div>
    </div>
  </LoadingView>
</template>

<script>
export default {
  data: () => ({
    loading: true,
    collections: [],
    mediaItems: [],
  }),

  mounted() {
    setTimeout(() => (this.loading = false), 200);
  },
};
</script>
