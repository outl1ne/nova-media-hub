<template>
  <LoadingView :loading="loading" :key="pageId">
    TODO
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
