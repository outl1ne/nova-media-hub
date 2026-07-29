<template>
  <LoadingView :loading="loading" class="o1-flex o1-flex-col o1-m-2">
    <Head :title="__('novaMediaHub.navigationItemTitle')" />

    <!-- Header -->
    <div class="o1-flex o1-mb-4">
      <input
        v-model="search"
        class="w-full md:w-1/3 md:shrink-0 bg-white dark:bg-gray-800 shadow dark:focus:bg-gray-800 appearance-none rounded-full h-8 px-4 w-full focus:bg-white focus:outline-none ring-[1px] ring-gray-50 focus:!ring-primary-400 dark:ring-gray-700"
        type="search"
        :placeholder="__('novaMediaHub.searchMediaTitle')"
        spellcheck="false"
      />

      <div class="o1-ml-auto o1-flex o1-gap-2">
        <MediaOrderSelect :columns="orderColumns" v-model:selected="orderBy" />
        <Button @click="showMediaUploadModal = true">
          {{ __('novaMediaHub.uploadMediaButton') }}
        </Button>
      </div>
    </div>

    <!-- Content wrapper -->
    <div
      class="o1-flex o1-border o1-full o1-border-gray-200 o1-rounded o1-bg-white o1-shadow dark:o1-bg-gray-800 dark:o1-border-gray-700 o1-min-h-[500px]"
    >
      <!-- Collections list -->
      <div class="o1-flex o1-flex-col o1-border-r o1-border-gray-200 dark:o1-border-gray-700 o1-min-w-[160px]">
        <div class="o1-font-bold o1-border-b o1-border-gray-200 o1-px-6 o1-py-3 o1-text-center dark:o1-border-gray-700">
          {{ __('novaMediaHub.collectionsTitle') }}
        </div>

        <div class="o1-flex o1-flex-col">
          <div v-if="!collections.length" class="o1-text-sm o1-text-gray-400 o1-p-4 o1-whitespace-nowrap">
            {{ __('novaMediaHub.noCollectionsFoundText') }}
          </div>

          <div
            v-for="collectionName in collections"
            :key="collectionName"
            class="o1-flex o1-items-stretch o1-bg-gray-50 o1-border-b o1-border-gray-200 hover:o1-bg-gray-100 dark:o1-border-gray-600 dark:o1-bg-gray-700 dark:hover:o1-bg-gray-800"
            :class="{ 'o1-bg-gray-100': collectionName === collection }"
          >
            <Link
              :href="`${basePath}/${collectionName}`"
              class="o1-p-4 o1-capitalize o1-flex-1 o1-whitespace-nowrap o1-overflow-hidden o1-text-ellipsis"
              :class="{ 'font-bold text-primary-500': collectionName === collection }"
            >
              {{ collectionName }}
            </Link>

            <button
              v-if="canRenameCollection(collectionName)"
              type="button"
              class="o1-px-3 o1-shrink-0 o1-text-gray-400 hover:o1-text-gray-700 dark:hover:o1-text-gray-200"
              :title="__('novaMediaHub.renameCollectionButton')"
              :aria-label="__('novaMediaHub.renameCollectionButton')"
              @click.stop.prevent="openRenameCollectionModal(collectionName)"
            >
              <PencilIcon />
            </button>
          </div>
        </div>
      </div>

      <!-- Media list -->
      <div
        class="o1-flex o1-flex-col o1-w-full o1-overflow-hidden o1-relative"
        @dragenter="toggleShowQuickUpload"
        @dragleave="toggleShowQuickUpload"
      >
        <!-- Dropzone -->
        <div
          v-show="showQuickUpload"
          class="o1-absolute o1-inset-0 o1-mx-auto o1-w-100 z-10 o1-bg-gray-900 o1-bg-opacity-90"
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
          <div v-else-if="!mediaItems.length" class="o1-text-sm o1-text-gray-400">
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
          class="o1-mt-auto o1-w-full o1-border-t o1-border-gray-200 dark:o1-border-gray-700"
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

    <RenameCollectionModal
      :show="showRenameCollectionModal"
      :collection="renamingCollection"
      @close="handleRenameCollectionModalClose"
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
import RenameCollectionModal from '../modals/RenameCollectionModal';
import HandlesMediaUpload from '../mixins/HandlesMediaUpload';
import PencilIcon from '../icons/PencilIcon';
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
    RenameCollectionModal,
    MediaOrderSelect,
    PencilIcon,
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
    showRenameCollectionModal: false,
    showQuickUpload: false,
    quickUploadLoading: false,
    renamingCollection: void 0,
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

    this.debouncedSearchRefresh = debounce(() => {
      this.getMedia({ search: this.search, orderBy: this.orderBy, page: 1 });
    }, 700);

    this.$watch(
      () => this.search,
      () => {
        this.debouncedSearchRefresh();
      }
    );

    this.$watch(
      () => this.orderBy,
      orderBy => {
        this.getMedia({ search: this.search, orderBy, page: 1 });
      }
    );
  },

  beforeUnmount() {
    this.debouncedSearchRefresh?.cancel();
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

    // Config defined collections always exist, so renaming them isn't possible.
    canRenameCollection(collectionName) {
      return this.canRenameCollections && !this.defaultCollections.includes(collectionName);
    },

    openRenameCollectionModal(collectionName) {
      this.renamingCollection = collectionName;
      this.showRenameCollectionModal = true;
    },

    async handleRenameCollectionModalClose(newCollectionName) {
      const renamedActiveCollection = this.renamingCollection === this.collection;

      this.showRenameCollectionModal = false;
      this.renamingCollection = void 0;

      if (!newCollectionName) return;

      // The active collection lives in the URL, so navigate instead of
      // leaving the page pointing at a collection that no longer exists.
      if (renamedActiveCollection) {
        Nova.visit(`${this.toolPath}/${newCollectionName}`);
        return;
      }

      await this.getCollections();
    },

    async switchToPage(page) {
      await this.goToMediaPage(page);
      Nova.$emit('resources-loaded');
    },
  },

  computed: {
    // Tool path relative to the Nova root, as expected by Nova.visit().
    toolPath() {
      const basePath = (Nova.appConfig.novaMediaHub.basePath || 'media-hub').replace(/^\/|\/$/g, '');
      return `/${basePath}`;
    },

    basePath() {
      const novaRoot = Nova.appConfig.base;

      if (['', '/'].includes(novaRoot)) return this.toolPath;
      return `${novaRoot}${this.toolPath}`;
    },

    canRenameCollections() {
      return Nova.appConfig.novaMediaHub.canCreateCollections;
    },

    defaultCollections() {
      return Nova.appConfig.novaMediaHub.defaultCollections || [];
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
