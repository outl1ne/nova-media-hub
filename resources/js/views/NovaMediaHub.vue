<template>
  <LoadingView :loading="loading" :key="collectionId" class="o1-flex o1-flex-col o1-m-2">
    <Head :title="__('novaMediaHub.navigationItemTitle')" />

    <!-- Header -->
    <div class="o1-flex o1-mb-4">
      <LoadingButton class="o1-ml-auto" @click="showMediaUploadModal = true">Upload media</LoadingButton>
    </div>

    <!-- Content wrapper -->
    <div
      class="o1-flex o1-border o1-full o1-border-slate-200 o1-rounded-lg o1-bg-white o1-shadow"
      style="min-height: 500px"
    >
      <!-- Collections list -->
      <div class="o1-flex o1-flex-col o1-border-r o1-border-slate-200" style="min-width: 160px">
        <div class="o1-font-bold o1-border-b o1-border-slate-200 o1-px-6 o1-py-3 o1-text-center">Collections</div>

        <div class="o1-flex o1-flex-col">
          <div v-if="!collections.length" class="o1-text-sm o1-text-slate-400 o1-p-4 o1-whitespace-nowrap">
            No collections found
          </div>

          <Link
            v-for="collectionName in collections"
            :key="collectionName"
            :href="`/media-hub/${collectionName}`"
            class="o1-p-4 o1-bg-slate-50 o1-border-b o1-border-slate-200 hover:o1-bg-slate-100"
            :class="{ 'font-bold text-primary-500 o1-bg-slate-100': collectionName === selectedCollection }"
          >
            {{ collectionName }}
          </Link>
        </div>
      </div>

      <div class="o1-flex o1-w-full o1-p-4 o1-items-center o1-justify-center" v-if="mediaLoading">
        <Loader class="text-gray-300" />
      </div>

      <!-- Media list -->
      <div
        v-else
        class="o1-flex o1-p-4 o1-w-full o1-flex-wrap"
        :class="{ 'o1-items-center o1-justify-center': !mediaItems.length }"
      >
        <div v-if="!mediaItems.length" class="o1-text-sm o1-text-slate-400">No media items found</div>

        <MediaItem
          v-for="mediaItem in mediaItems"
          :key="mediaItem.id"
          :mediaItem="mediaItem"
          @click.stop.prevent="openViewModal(mediaItem)"
          @contextmenu.stop.prevent="openContextMenu($event, mediaItem)"
        />
      </div>
    </div>

    <!-- Fake download button -->
    <a
      :href="targetMediaItem && targetMediaItem.url"
      download
      ref="downloadAnchor"
      target="_BLANK"
      rel="noopener noreferrer"
      class="o1-hidden"
    />

    <MediaUploadModal
      :show="showMediaUploadModal"
      @close="closeMediaUploadModal"
      :active-collection="selectedCollection"
    />

    <MediaViewModal :show="showMediaViewModal" :mediaItem="targetMediaItem" @close="showMediaViewModal = false" />

    <ConfirmDeleteModal :show="showConfirmDeleteModal" :mediaItem="targetMediaItem" @close="handleDeleteModalClose" />

    <VueSimpleContextMenu
      elementId="mediaItemContextMenu"
      :options="contextMenuOptions"
      ref="vueSimpleContextMenu"
      @option-clicked="onMediaItemContextMenuClick"
    />
  </LoadingView>
</template>

<script>
import API from '../api';
import MediaItem from '../components/MediaItem';
import MediaViewModal from '../modals/MediaViewModal';
import MediaUploadModal from '../modals/MediaUploadModal';
import ConfirmDeleteModal from '../modals/ConfirmDeleteModal';
import VueSimpleContextMenu from 'vue-simple-context-menu/src/vue-simple-context-menu';

export default {
  components: { MediaUploadModal, MediaItem, VueSimpleContextMenu, MediaViewModal, ConfirmDeleteModal },

  data: () => ({
    loading: true,
    mediaLoading: false,

    selectedCollection: void 0,
    collections: [],
    mediaItems: [],
    currentPage: 1,

    showMediaViewModal: false,
    showMediaUploadModal: false,
    showConfirmDeleteModal: false,

    contextMenuOptions: [],
    targetMediaItem: void 0,
  }),

  async created() {
    this.selectedCollection = this.$page.props.collectionId || 'default';

    this.contextMenuOptions = [
      { name: 'View / Edit', action: 'view', class: 'o1-text-slate-600' },
      { name: 'Download', action: 'download', class: 'o1-text-slate-600' },
      { type: 'divider' },
      { name: 'Delete', action: 'delete', class: 'o1-text-red-500' },
    ];
  },

  async mounted() {
    this.loading = true;
    await this.getCollections();
    await this.getCollectionMedia();
    this.loading = false;
  },

  methods: {
    async getCollections() {
      const { data } = await API.getCollections();
      this.collections = data || [];

      if (!this.selectedCollection) {
        this.selectedCollection = this.collections.length ? this.collections[0] : void 0;
      }
    },

    async getCollectionMedia() {
      this.mediaLoading = true;
      const { data } = await API.getCollectionMedia(this.selectedCollection);
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

    // Media item handlers
    openContextMenu(event, mediaItem) {
      this.$refs.vueSimpleContextMenu.showMenu(event, mediaItem);
    },

    onMediaItemContextMenuClick(event) {
      const action = event.option.action || void 0;
      this.targetMediaItem = event.item;

      if (action === 'view') {
        this.showMediaViewModal = true;
      }

      if (action === 'download') {
        this.$nextTick(() => {
          this.$refs.downloadAnchor.click();
        });
      }

      if (action === 'delete') {
        this.showConfirmDeleteModal = true;
      }
    },

    openViewModal(mediaItem) {
      this.$refs.vueSimpleContextMenu.hideContextMenu();
      this.targetMediaItem = mediaItem;
      this.showMediaViewModal = true;
    },

    handleDeleteModalClose(update = false) {
      this.showConfirmDeleteModal = false;

      if (update) {
        this.getCollectionMedia();
      }
    },
  },
};
</script>

<style lang="scss">
$light-grey: #ecf0f1;
$grey: darken($light-grey, 15%);
$blue: #007aff;
$white: #fff;
$black: #333;

.vue-simple-context-menu {
  background-color: #f1f5f9;
  border-bottom-width: 0px;
  border-radius: 2px;
  box-shadow: 0 3px 6px 0 rgba($black, 0.2);
  display: none;
  left: 0;
  list-style: none;
  margin: 0;
  padding: 0;

  position: fixed;
  top: 0;

  z-index: 1000000;

  &--active {
    display: block;
  }

  &__item {
    align-items: center;
    cursor: pointer;
    display: flex;
    padding: 5px 15px;
    font-weight: 700;

    &:hover {
      background-color: rgba(var(--colors-primary-500), 1);
      color: #fff;
    }
  }

  &__divider {
    background-clip: content-box;
    background-color: #cbd5e1;
    box-sizing: content-box;
    height: 2px;
    padding: 4px 0;
    pointer-events: none;
  }

  // Have to use the element so we can make use of `first-of-type` and `last-of-type`
  li {
    &:first-of-type {
      margin-top: 4px;
    }

    &:last-of-type {
      margin-bottom: 4px;
    }
  }
}
</style>
