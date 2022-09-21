<template>
  <LoadingView :loading="loading" :key="collectionId" class="o1-flex o1-flex-col o1-m-2">
    <Head :title="__('novaMediaHub.navigationItemTitle')" />

    <!-- Header -->
    <div class="o1-flex o1-mb-4">
      <IndexSearchInput class="o1-mb-0" v-model:keyword="search" @update:keyword="search = $event" />

      <div class="o1-ml-auto o1-flex o1-gap-2">
        <MediaOrderSelect
          :columns="orderColumns"
          v-model:selected="orderBy"
          @change="selected => (orderBy = selected)"
        />
        <LoadingButton @click="showMediaUploadModal = true">{{ __('novaMediaHub.uploadMediaButton') }}</LoadingButton>
      </div>
    </div>

    <!-- Content wrapper -->
    <div
      class="o1-flex o1-border o1-full o1-border-slate-200 o1-rounded o1-bg-white o1-shadow dark:o1-bg-slate-800 dark:o1-border-slate-700 o1-min-h-[500px]"
    >
      <!-- Collections list -->
      <div class="o1-flex o1-flex-col o1-border-r o1-border-slate-200 dark:o1-border-slate-700 o1-min-w-[160px]">
        <div
          class="o1-font-bold o1-border-b o1-border-slate-200 o1-px-6 o1-py-3 o1-text-center dark:o1-border-slate-700"
        >
          {{ __('novaMediaHub.collectionsTitle') }}
        </div>

        <div class="o1-flex o1-flex-col">
          <div v-if="!collections.length" class="o1-text-sm o1-text-slate-400 o1-p-4 o1-whitespace-nowrap">
            {{ __('novaMediaHub.noCollectionsFoundText') }}
          </div>

          <Link
            v-for="collectionName in collections"
            :key="collectionName"
            :href="`${basePath}/${collectionName}`"
            class="o1-p-4 o1-bg-slate-50 o1-border-b o1-border-slate-200 hover:o1-bg-slate-100 dark:o1-border-slate-600 dark:o1-bg-slate-700 dark:hover:o1-bg-slate-800"
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
          <div v-if="!mediaItems.length" class="o1-text-sm o1-text-slate-400">
            {{ __('novaMediaHub.noMediaItemsFoundText') }}
          </div>

          <MediaItem
            v-for="mediaItem in mediaItems"
            :key="mediaItem.id"
            :mediaItem="mediaItem"
            :showFileName="true"
            @click.stop.prevent="openViewModal(mediaItem)"
            @contextmenu.stop.prevent="openContextMenu($event, mediaItem)"
          />
        </div>

        <PaginationLinks
          class="o1-mt-auto o1-w-full o1-border-t o1-border-slate-200 dark:o1-border-slate-700"
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
import MediaOrderSelect from '../components/MediaOrderSelect';

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
    MediaOrderSelect,
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
      { name: this.__('novaMediaHub.contextViewEdit'), action: 'view' },
      { name: this.__('novaMediaHub.contextDownload'), action: 'download' },
      { name: this.__('novaMediaHub.contextMoveCollection'), action: 'move-collection' },
      { type: 'divider' },
      { name: this.__('novaMediaHub.contextDelete'), action: 'delete', class: 'warning' },
    ];

    this.$watch(
      () => ({ search: this.search, orderBy: this.orderBy }),
      data => this.getMedia({ ...data, page: 1 })
    );
  },

  async mounted() {
    this.loading = true;
    await this.getCollections();
    await this.getMedia();
    this.loading = false;
  },

  methods: {
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
      const novaRoot = Nova.appConfig.base;

      let basePath = Nova.appConfig.novaMediaHub.basePath || 'media-hub';
      basePath = basePath.replace(/^\/|\/$/g, '');

      if (['', '/'].includes(novaRoot)) return `/${basePath}`;
      return `${novaRoot}/${basePath}`;
    },
  },
};
</script>

<style lang="scss">
#media-items-list {
  grid-template-columns: repeat(auto-fill, minmax(192px, 1fr));
}

.vue-simple-context-menu {
  background-color: #fff;
  border-bottom-width: 0px;
  border-radius: 2px;
  box-shadow: 0 3px 6px 0 rgba(#000, 0.2);
  display: none;
  list-style: none;
  margin: 0;
  padding: 0;

  position: absolute;
  top: 0;
  left: 0;

  z-index: 1000000;

  &--active {
    display: block;
  }

  &__item {
    display: flex;
    align-items: center;
    cursor: pointer;
    padding: 5px 15px;
    font-weight: 700;
    color: #64748b;

    &.warning {
      color: #f43f5e;
    }

    &:hover {
      background-color: rgba(var(--colors-primary-500), 1);
      color: #fff;
    }
  }

  &__divider {
    background-clip: content-box;
    background-color: #e2e8f0;
    box-sizing: content-box;
    height: 1px;
    padding: 2px 0;
    pointer-events: none;
  }

  // Have to use the element so we can make use of `first-of-type` and `last-of-type`
  li {
    &:first-of-type {
      margin-top: 2px;
    }

    &:last-of-type {
      margin-bottom: 2px;
    }
  }
}
</style>
