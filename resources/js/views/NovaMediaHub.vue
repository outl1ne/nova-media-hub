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
        <Button @click="showMediaUploadModal = true">
          {{ __('novaMediaHub.uploadMediaButton') }}
        </Button>
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
            class="o1-p-4 o1-bg-slate-50 o1-capitalize o1-border-b o1-border-slate-200 hover:o1-bg-slate-100 dark:o1-border-slate-600 dark:o1-bg-slate-700 dark:hover:o1-bg-slate-800"
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
      <div
        v-else
        class="o1-flex o1-flex-col o1-w-full o1-overflow-hidden o1-relative"
        @dragenter="toggleShowQuickUpload"
        @dragleave="toggleShowQuickUpload"
      >
        <!-- Dropzone -->
        <div
          v-show="showQuickUpload"
          class="o1-absolute o1-inset-0 o1-mx-auto o1-w-100 z-10 o1-bg-slate-900 o1-bg-opacity-90"
        >
          <div class="o1-dropzone-wrapper o1-py-32 o1-px-8 flex o1-items-center o1-justify-center o1-h-full">
            <NMHDropZone v-if="!quickUploadLoading" @fileChanged="uploadFiles" multiple />

            <Loader v-else class="text-gray-300" width="60" />
          </div>
        </div>

        <div
          id="media-items-list"
          class="o1-w-full o1-h-full flex flex-wrap o1-gap-6 o1-p-4 relative"
          :class="{ 'o1-flex o1-items-center o1-justify-center': !mediaItems.length }"
        >
          <Loader v-if="loadingMedia" class="text-gray-300 o1-absolute o1-inset-0 o1-m-auto" width="60" />
          <div v-else-if="!mediaItems.length" class="o1-text-sm o1-text-slate-400">
            {{ __('novaMediaHub.noMediaItemsFoundText') }}
          </div>

          <MediaItem
            v-show="!loadingMedia"
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
      @dataUpdated="getMedia"
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
import HandlesMediaUpload from '../mixins/HandlesMediaUpload';
import debounce from 'lodash.debounce';
import { Button } from 'laravel-nova-ui';

export default {
  mixins: [HandlesMediaLists, HandlesMediaUpload],

  components: {
    MediaItem,
    MediaViewModal,
    PaginationLinks,
    MediaUploadModal,
    ConfirmDeleteModal,
    MediaItemContextMenu,
    MoveToCollectionModal,
    MediaOrderSelect,
    Button,
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
    showQuickUpload: false,
    quickUploadLoading: false,
  }),

  async created() {
    this.collection = this.$page.props.collectionId || void 0;

    this.ctxOptions = [
      { name: this.__('novaMediaHub.contextViewEdit'), action: 'view' },
      { name: this.__('novaMediaHub.contextDownload'), action: 'download' },
      { name: this.__('novaMediaHub.contextMoveCollection'), action: 'move-collection' },
      { type: 'divider' },
      { name: this.__('novaMediaHub.contextReplace'), action: 'replace', class: 'warning' },
      { name: this.__('novaMediaHub.contextDelete'), action: 'delete', class: 'warning' },
    ];

    this.$watch(
      () => ({ search: this.search, orderBy: this.orderBy }),
      debounce(data => this.getMedia({ ...data, page: 1 }), 400)
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

    async uploadFiles(selectedFiles) {
      this.quickUploadLoading = true;

      const { success, hadExisting, media } = await this.uploadFilesToCollection(selectedFiles, this.collection);

      let goToCollection = this.collection;
      if (hadExisting) {
        // Find possible new collection name
        const diffCollNameMedia = media.find(mi => mi.collection_name !== this.finalCollectionName);
        if (diffCollNameMedia) goToCollection = diffCollNameMedia.collection_name;
      }

      if (success) {
        this.collection = goToCollection;
        await this.getMedia({ collection: goToCollection });
      }

      this.showQuickUpload = false;
      this.quickUploadLoading = false;
    },

    toggleShowQuickUpload() {
      this.showQuickUpload = !this.showQuickUpload;
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
.o1-dropzone-wrapper {
  > div {
    width: 100%;
  }

  label {
    height: 400px;
    display: flex;
    justify-content: center;
    align-items: center;
  }
}
.vue-simple-context-menu {
  display: none;
  position: absolute;
  top: 0;
  left: 0;
  z-index: 1000000;
  &--active {
    display: block;
  }
}
</style>
