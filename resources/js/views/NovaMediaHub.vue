<template>
  <LoadingView :loading="loading" :key="collectionId" class="o1-flex o1-flex-col o1-m-2">
    <Head :title="__('novaMediaHub.navigationItemTitle')" />

    <!-- Header -->
    <div class="o1-flex o1-mb-4">
      <LoadingButton class="o1-ml-auto" @click="showMediaUploadModal = true">Upload media</LoadingButton>
    </div>

    <!-- Content wrapper -->
    <div
      class="o1-flex o1-border o1-full o1-border-slate-200 o1-rounded o1-bg-white o1-shadow"
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
            :href="`/${basePath}/${collectionName}`"
            class="o1-p-4 o1-bg-slate-50 o1-border-b o1-border-slate-200 hover:o1-bg-slate-100"
            :class="{ 'font-bold text-primary-500 o1-bg-slate-100': collectionName === collection }"
          >
            {{ collectionName }}
          </Link>
        </div>
      </div>

      <div class="o1-flex o1-w-full o1-p-4 o1-items-center o1-justify-center" v-if="mediaLoading">
        <Loader class="text-gray-300" />
      </div>

      <!-- Media list -->
      <div v-else class="o1-flex o1-flex-col o1-w-full o1-overflow-hidden">
        <div
          id="media-items-list"
          class="o1-w-full o1-grid o1-gap-6 o1-p-4 o1-justify-items-center"
          :class="{ 'o1-flex o1-items-center o1-justify-center': !mediaItems.length }"
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

        <PaginationLinks
          class="o1-mt-auto o1-w-full o1-border-t o1-border-slate-200"
          :page="mediaResponse.current_page"
          :pages="mediaResponse.last_page"
          @page="switchToPage"
        />
      </div>
    </div>

    <MediaViewModal :show="showMediaViewModal" :mediaItem="ctxMediaItem" @close="showMediaViewModal = false" />

    <MediaUploadModal :show="showMediaUploadModal" @close="closeMediaUploadModal" :active-collection="collection" />

    <MediaItemContextMenu
      id="media-hub-ctx-menu"
      :showEvent="ctxShowEvent"
      :options="ctxOptions"
      @close="ctxShowEvent = void 0"
      :mediaItem="ctxMediaItem"
      @optionClick="contextOptionClick"
    />

    <ConfirmDeleteModal :show="showConfirmDeleteModal" :mediaItem="ctxMediaItem" @close="handleDeleteModalClose" />

    <MoveToCollectionModal
      :show="showMoveCollectionModal"
      :mediaItem="ctxMediaItem"
      @close="handleMoveCollectionModalClose"
    />
  </LoadingView>
</template>

<script>
import MediaItem from '../components/MediaItem';
import MediaViewModal from '../modals/MediaViewModal';
import MediaUploadModal from '../modals/MediaUploadModal';
import HandlesMediaLists from '../mixins/HandlesMediaLists';
import PaginationLinks from '../components/PaginationLinks';
import ConfirmDeleteModal from '../modals/ConfirmDeleteModal';
import MoveToCollectionModal from '../modals/MoveToCollectionModal';
import MediaItemContextMenu from '../components/MediaItemContextMenu';

export default {
  mixins: [HandlesMediaLists],

  components: {
    MediaItem,
    MediaViewModal,
    PaginationLinks,
    MediaUploadModal,
    ConfirmDeleteModal,
    MediaItemContextMenu,
    MoveToCollectionModal,
  },

  data: () => ({
    loading: true,

    ctxOptions: [],
    ctxShowEvent: false,
    ctxMediaItem: void 0,

    showMediaViewModal: false,
    showMediaUploadModal: false,
    showConfirmDeleteModal: false,
    showMoveCollectionModal: false,
  }),

  async created() {
    this.collection = this.$page.props.collectionId || 'default';

    this.ctxOptions = [
      { name: 'View / Edit', action: 'view', class: 'o1-text-slate-600' },
      { name: 'Download', action: 'download', class: 'o1-text-slate-600' },
      { name: 'Move to collection', action: 'move-collection', class: 'o1-text-slate-600' },
      { type: 'divider' },
      { name: 'Delete', action: 'delete', class: 'o1-text-red-500' },
    ];
  },

  async mounted() {
    this.loading = true;
    await this.getCollections();
    await this.getMedia();
    this.loading = false;
  },

  methods: {
    async selectCollection(collectionName) {
      this.collection = collectionName;
      await this.getMedia();
    },

    async closeMediaUploadModal(updateData, collectionName) {
      if (updateData) {
        await this.getCollections();
        this.collection = collectionName;
        await this.getMedia();
      }
      this.showMediaUploadModal = false;
    },

    // Media item handlers
    openContextMenu(event, mediaItem) {
      this.ctxShowEvent = event;
      this.ctxMediaItem = mediaItem;
    },

    contextOptionClick(event) {
      const action = event.option.action || void 0;

      if (action === 'delete') {
        this.showConfirmDeleteModal = true;
      }

      if (action === 'move-collection') {
        this.showMoveCollectionModal = true;
      }
    },

    openViewModal(mediaItem) {
      this.ctxShowEvent = void 0;
      this.ctxMediaItem = mediaItem;
      this.showMediaViewModal = true;
    },

    handleDeleteModalClose(update = false) {
      this.showConfirmDeleteModal = false;
      if (update) this.getMedia();
    },

    handleMoveCollectionModalClose(update = false) {
      this.showMoveCollectionModal = false;
      if (update) this.getMedia();
    },

    async switchToPage(page) {
      await this.goToMediaPage(page);
      Nova.$emit('resources-loaded');
    },
  },

  computed: {
    basePath() {
      return Nova.appConfig.novaMediaHub.basePath;
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

#media-items-list {
  grid-template-columns: repeat(auto-fill, minmax(192px, 1fr));
}

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
