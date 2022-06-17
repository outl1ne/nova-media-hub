<template>
  <LoadingView :loading="loading" :key="pageId" class="nml-flex nml-flex-col nml-m-2">
    <!-- Header -->
    <div class="nml-flex nml-mb-4">
      <LoadingButton class="nml-ml-auto" @click="showMediaUploadModal = true">Upload media</LoadingButton>
    </div>

    <!-- Content wrapper -->
    <div
      class="nml-flex nml-border nml-full nml-border-slate-200 nml-rounded-lg nml-bg-white nml-shadow"
      style="min-height: 500px"
    >
      <!-- Collections list -->
      <div class="nml-flex nml-flex-col nml-border-r nml-border-slate-200" style="min-width: 160px">
        <div class="nml-font-bold nml-border-b nml-border-slate-200 nml-px-6 nml-py-3 nml-text-center">Collections</div>

        <div class="nml-flex nml-flex-col">
          <div v-if="!collections.length" class="nml-text-sm nml-text-slate-400 nml-p-4 nml-whitespace-nowrap">
            No collections found
          </div>

          <Link
            v-for="collectionName in collections"
            :key="collectionName"
            :href="`/media-hub/${collectionName}`"
            class="nml-py-4 nml-bg-slate-50 nml-border-b nml-border-slate-200 hover:nml-bg-slate-100"
            :class="{ 'font-bold text-primary-500 nml-bg-slate-100': collectionName === selectedCollection }"
          >
            {{ collectionName }}
          </Link>
        </div>
      </div>

      <div class="nml-flex nml-w-full nml-p-4 nml-items-center nml-justify-center" v-if="mediaLoading">
        <Loader class="text-gray-300" />
      </div>

      <!-- Media list -->
      <div
        v-else
        class="nml-flex nml-p-4 nml-w-full nml-flex-wrap"
        :class="{ 'nml-items-center nml-justify-center': !mediaItems.length }"
      >
        <div v-if="!mediaItems.length" class="nml-text-sm nml-text-slate-400">No media items found</div>

        <button
          v-for="item in mediaItems"
          :key="item.id"
          class="nml-h-48 nml-w-48 nml-mx-2 nml-bg-slate-50 nml-shadow-sm hover:nml-shadow nml-border nml-border-slate-100 hover:nml-bg-slate-100 hover:nml-border-slate-200"
        >
          <img
            :src="item.url"
            :alt="item.id"
            class="nml-object-contain nml-max-w-full nml-w-full nml-max-h-full nml-h-full"
          />
        </button>
      </div>
    </div>
  </LoadingView>

  <!-- Modal -->
  <MediaUploadModal
    :show="showMediaUploadModal"
    @close="closeMediaUploadModal"
    :active-collection="selectedCollection"
  />
</template>

<script>
import MediaUploadModal from '../modals/MediaUploadModal';

export default {
  components: { MediaUploadModal },

  data: () => ({
    loading: true,
    mediaLoading: false,

    selectedCollection: void 0,
    collections: [],
    mediaItems: [],
    currentPage: 1,
    showMediaUploadModal: false,
  }),

  async mounted() {
    console.info(this.$attrs);
    this.loading = true;
    await this.getCollections();
    await this.getCollectionMedia();
    this.loading = false;
  },

  methods: {
    async getCollections() {
      const { data } = await Nova.request().get('/nova-vendor/media-hub/collections');
      this.collections = data || [];

      if (!this.selectedCollection) {
        this.selectedCollection = this.collections.length ? this.collections[0] : void 0;
      }
    },

    async getCollectionMedia() {
      this.mediaLoading = true;
      const { data } = await Nova.request().get(`/nova-vendor/media-hub/collections/${this.selectedCollection}/media`);
      this.mediaItems = data.data;
      this.mediaLoading = false;
    },

    async selectCollection(collectionName) {
      this.selectedCollection = collectionName;
      await this.getCollectionMedia();
    },

    async closeMediaUploadModal(updateData, collectionName) {
      if (updateData) {
        await this.getCollections();
        this.selectedCollection = collectionName;
        await this.getCollectionMedia();
      }
      this.showMediaUploadModal = false;
    },
  },
};
</script>
