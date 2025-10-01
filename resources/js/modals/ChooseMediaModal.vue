<template>
  <Modal
    :show="show"
    @close-via-escape="closeViaEscape"
    role="alertdialog"
    maxWidth="w-full"
    size="custom"
    class="o1-px-8 overflow-hidden full-modal"
  >
    <LoadingCard
      :loading="loading"
      class="o1-flex o1-flex-col o1-h-full mx-auto bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden"
    >
      <slot>
        <ModalContent class="o1-min-h-[90%] o1-grow o1-px-8 o1-py-0 o1-flex o1-flex-col">
          <!-- Selected media -->
          <div class="o1-flex o1-flex-col o1-pt-6 o1-pb-1 o1-border-b o1-border-slate-200 dark:o1-border-slate-700">
            <div class="o1-leading-tight text-primary-500 o1-font-bold o1-text-md o1-mb-2">
              {{ __('novaMediaHub.selectedMediaTitle') + (selectedCount > 1 ? ` (${selectedCount})` : '') }}
            </div>
            <div class="o1-flex overflow-x-auto o1-pt-1 o1-px-1" v-show="!!selectedCount">
              <Draggable v-model="selectedMediaItems" item-key="id" class="o1-flex o1-flex-shrink-0">
                <template #item="{ element: mediaItem }">
                  <MediaItem
                    :key="'selected-' + mediaItem.id"
                    :mediaItem="mediaItem"
                    @click="toggleMediaSelection(mediaItem)"
                    :selected="true"
                    :showFileName="true"
                    :size="32"
                    @nameClick="openViewMediaModal(mediaItem)"
                    @contextmenu.stop.prevent="openContextMenuFromSelected($event, mediaItem)"
                    class="o1-mr-4 o1-mb-4"
                  />
                </template>
              </Draggable>
            </div>
            <div v-show="!selectedCount" class="o1-text-slate-400 o1-mb-4">
              {{ __('novaMediaHub.noMediaSelectedText') }}
            </div>
          </div>

          <div class="o1-flex o1-flex-col lg:o1-flex-row overflow-y-auto o1-min-h-[30%] o1-h-full o1-gap-8">
            <div class="o1-flex o1-flex-col o1-gap-5 o1-w-full lg:o1-max-w-xs overflow-y-auto o1-py-4">
              <!-- Choose collection -->
              <ModalFilterItem :title="__('novaMediaHub.chooseCollectionTitle')">
                <select
                  className="o1-capitalize w-full block form-control form-control-bordered form-input"
                  v-model="collection"
                >
                  <option value="">{{ __('novaMediaHub.showAll') }}</option>
                  <option v-for="c in collections" :key="c" :value="c">{{ c }}</option>
                </select>
              </ModalFilterItem>

              <Button v-show="someMediaItemsNotInCurrentCollection" @click.prevent="moveToCollection">
                {{ __('novaMediaHub.moveToCollectionTitle') }}
              </Button>

              <!-- Search -->
              <ModalFilterItem :title="__('novaMediaHub.searchMediaTitle')">
                <input
                  v-model="search"
                  ref="search"
                  class="w-full form-control form-input form-input-bordered"
                  tabindex="-1"
                  type="search"
                  :placeholder="__('novaMediaHub.searchMediaTitle')"
                  spellcheck="false"
                />
              </ModalFilterItem>

              <!-- Choose order -->
              <ModalFilterItem :title="__('novaMediaHub.chooseMediaOrder')">
                <MediaOrderSelect
                  :columns="orderColumns"
                  v-model:selected="orderBy"
                  @change="selected => (orderBy = selected)"
                />
              </ModalFilterItem>

              <div class="o1-flex o1-flex-col o1-w-full media-hub-dropzone">
                <div class="o1-leading-tight text-primary-500 o1-font-bold o1-text-md o1-mb-2">
                  {{ __('novaMediaHub.quickUpload') }}
                </div>
                <NMHDropZone @fileChanged="uploadFiles" multiple vertical />
              </div>
            </div>

            <!-- Collection media -->
            <div class="o1-flex o1-flex-col o1-w-full o1-py-4">
              <div class="o1-leading-tight text-primary-500 o1-font-bold o1-text-md o1-mb-2">
                {{ __('novaMediaHub.chooseMediaTitle') }}
              </div>
              <div class="o1-w-full overflow-y-auto">
                <div
                  id="media-items-list"
                  class="o1-w-full flex flex-wrap o1-gap-4 o1-justify-items-center o1-p-1"
                  v-show="!!mediaItems.length && !mediaLoading"
                >
                  <MediaItem
                    v-for="mediaItem in mediaItems"
                    :key="'media-' + mediaItem.id"
                    :mediaItem="mediaItem"
                    @click="toggleMediaSelection(mediaItem)"
                    @contextmenu.stop.prevent="openContextMenuFromChoose($event, mediaItem)"
                    @nameClick="openViewMediaModal(mediaItem)"
                    class="o1-mb-4"
                    :selected="selectedMediaItems.find(m => m.id === mediaItem.id)"
                    :showFileName="true"
                    :size="40"
                  />
                </div>
              </div>

              <div v-show="!mediaItems.length && !mediaLoading" class="o1-text-slate-400">
                {{ __('novaMediaHub.noMediaItemsFoundText') }}
              </div>

              <div v-if="mediaLoading">
                <Loader class="text-gray-300 o1-mt-5" width="30" />
              </div>

              <PaginationLinks
                v-show="mediaResponse.last_page > 1"
                class="o1-mt-auto o1-w-full o1-border o1-border-slate-200 o1-border-l dark:o1-border-gray-700 o1-rounded-b-lg"
                style="border-radius: 0px"
                :page="mediaResponse.current_page"
                :pages="mediaResponse.last_page"
                @page="switchToPage"
              />
            </div>
          </div>
        </ModalContent>
      </slot>

      <ModalFooter>
        <div class="ml-auto">
          <Button variant="link" state="mellow" @click.prevent="$emit('close')" class="o1-mr-4">
            {{ __('novaMediaHub.closeButton') }}
          </Button>

          <Button @click.prevent="confirm">{{ __('novaMediaHub.confirmButton') }}</Button>
        </div>
      </ModalFooter>
    </LoadingCard>

    <ConfirmDeleteModal :show="showConfirmDeleteModal" :mediaItem="ctxMediaItem" @close="handleDeleteModalClose" />
    <MediaViewModal
      :mediaItem="ctxMediaItem"
      @close="closeViewModal"
      :show="showMediaViewModal"
      :readonly="field.readonly"
    />

    <MediaItemContextMenu
      id="media-choose-modal-ctx-menu"
      :showEvent="ctxShowEvent"
      :options="ctxOptions"
      @showModal="ctxShowingModal = true"
      @hideModal="ctxShowingModal = false"
      :mediaItem="ctxMediaItem"
      @optionClick="contextOptionClick"
      :readonly="field.readonly"
      @dataUpdated="dataUpdated"
    />
  </Modal>
</template>

<script>
import API from '../api';
import Draggable from 'vuedraggable';
import MediaItem from '../components/MediaItem';
import PaginationLinks from '../components/PaginationLinks';
import HandlesMediaLists from '../mixins/HandlesMediaLists';
import ConfirmDeleteModal from '../modals/ConfirmDeleteModal';
import MediaItemContextMenu from '../components/MediaItemContextMenu';
import ModalFilterItem from '../components/ModalFilterItem';
import MediaOrderSelect from '../components/MediaOrderSelect';
import MediaViewModal from '../modals/MediaViewModal';
import HandlesMediaUpload from '../mixins/HandlesMediaUpload';
import { Button } from 'laravel-nova-ui';

export default {
  mixins: [HandlesMediaLists, HandlesMediaUpload],
  components: {
    Draggable,
    MediaItem,
    ConfirmDeleteModal,
    MediaItemContextMenu,
    PaginationLinks,
    ModalFilterItem,
    MediaOrderSelect,
    MediaViewModal,
    Button,
  },

  emits: ['close', 'confirm'],
  props: ['show', 'field', 'activeCollection', 'initialSelectedMediaItems'],

  data: self => ({
    selectedMediaItems: [],

    loading: false,
    mediaLoading: false,
    showConfirmDeleteModal: false,
    showMediaViewModal: false,
    collection: self.$props.field?.defaultCollectionName || void 0,

    ctxOptions: [],
    ctxMediaItem: void 0,
    ctxShowEvent: void 0,
    ctxShowingModal: false,
  }),

  created() {
    this.$watch(
      () => ({ collection: this.collection, search: this.search, orderBy: this.orderBy, show: this.show }),
      async data => {
        if (!data.show) return;
        await this.refresh(1);
      }
    );
  },

  watch: {
    async show(newValue) {
      if (newValue) {
        // Let it be async
        this.getCollections();

        this.selectedCollection = this.activeCollection;

        const iniVal = this.initialSelectedMediaItems;
        if (Array.isArray(iniVal)) {
          this.selectedMediaItems = [...iniVal];
        } else if (!!iniVal && !!iniVal.id) {
          this.selectedMediaItems = [iniVal];
        } else {
          this.selectedMediaItems = [];
        }
      }
    },
  },

  methods: {
    async refresh(page = null) {
      this.mediaLoading = true;
      await this.getMedia({
        collection: this.collection,
        search: this.search,
        orderBy: this.orderBy,
        page: page || this.currentPage,
      });
      this.mediaLoading = false;
    },

    async uploadFiles(selectedFiles) {
      this.loading = true;

      const { success, media } = await this.uploadFilesToCollection(selectedFiles, this.collection);

      if (success) {
        await this.getMedia({ collection: this.collection });
        media.map(this.toggleMediaSelection);
      }

      this.loading = false;
    },

    async moveToCollection() {
      await API.moveMediaToCollection(
        this.selectedMediaItems.map(mi => mi.id),
        this.collection
      );
      this.selectedMediaItems.forEach(mi => (mi.collection_name = this.collection));

      Nova.$toasted.success(this.__('novaMediaHub.successfullyMovedToCollection', { collection: this.collection }));
      await this.getMedia({ collection: this.collection });
    },

    toggleMediaSelection(mediaItem) {
      if (this.selectedMediaItems.find(mi => mi.id === mediaItem.id)) {
        this.selectedMediaItems = this.selectedMediaItems.filter(mi => mi.id !== mediaItem.id);
      } else {
        if (this.field.multiple) {
          this.selectedMediaItems.push(mediaItem);
        } else {
          this.selectedMediaItems = [mediaItem];
        }
      }
    },

    confirm() {
      this.$emit('confirm', this.field.multiple ? this.selectedMediaItems : this.selectedMediaItems[0]);
    },

    openContextMenuFromSelected(event, mediaItem) {
      this.ctxMediaItem = mediaItem;
      this.ctxOptions = [
        { name: this.__('novaMediaHub.contextViewEdit'), action: 'view' },
        { name: this.__('novaMediaHub.contextDownload'), action: 'download' },
        { name: this.__('novaMediaHub.contextOpenCollection'), action: 'open-collection' },
        { type: 'divider' },
        { name: this.__('novaMediaHub.contextDeselect'), action: 'deselect', class: 'warning' },
        { name: this.__('novaMediaHub.contextDeselectOthers'), action: 'deselect-others', class: 'warning' },
        { name: this.__('novaMediaHub.contextReplace'), action: 'replace', class: 'warning' },
      ];

      this.$nextTick(() => (this.ctxShowEvent = event));
    },

    openContextMenuFromChoose(event, mediaItem) {
      this.ctxMediaItem = mediaItem;
      this.ctxOptions = [
        { name: this.__('novaMediaHub.contextSelect'), action: 'select' },
        { name: this.__('novaMediaHub.contextViewEdit'), action: 'view' },
        { name: this.__('novaMediaHub.contextDownload'), action: 'download' },
        { type: 'divider' },
        { name: this.__('novaMediaHub.contextReplace'), action: 'replace', class: 'warning' },
        { name: this.__('novaMediaHub.contextDelete'), action: 'delete', class: 'warning' },
      ];

      this.$nextTick(() => (this.ctxShowEvent = event));
    },

    contextOptionClick(event) {
      const action = event.option.action || void 0;

      if (action === 'delete') {
        this.showConfirmDeleteModal = true;
      }

      if (action === 'select' || action === 'deselect') {
        this.toggleMediaSelection(this.ctxMediaItem);
      }

      if (action === 'open-collection') {
        this.collection = this.ctxMediaItem.collection_name;
      }

      if (action === 'deselect-others') {
        this.selectedMediaItems = this.selectedMediaItems.filter(mi => mi.id === this.ctxMediaItem.id);
      }
    },

    handleDeleteModalClose(update = false) {
      this.showConfirmDeleteModal = false;
      if (update) this.getMedia({ collection: this.collection });
    },

    openViewMediaModal(mediaItem) {
      this.ctxMediaItem = mediaItem;
      this.showMediaViewModal = true;
    },

    closeViewModal() {
      this.showMediaViewModal = false;
    },

    async switchToPage(page) {
      await this.goToMediaPage(page);
      Nova.$emit('resources-loaded');
    },

    closeViaEscape() {
      // Close only if context isn't showing anything
      if (!this.ctxShowingModal && !this.showConfirmDeleteModal && !this.showMediaViewModal) {
        this.$emit('close');
      }
    },

    dataUpdated(media) {
      if (media) {
        const i1 = this.selectedMediaItems.findIndex(m => m.id === media.id);
        this.selectedMediaItems[i1] = media;

        const i2 = this.mediaItems.findIndex(m => m.id === media.id);
        this.mediaItems[i2] = media;
      }
    },
  },

  computed: {
    selectedCount() {
      return this.selectedMediaItems.length;
    },
    someMediaItemsNotInCurrentCollection() {
      return this.collection?.length > 0 && this.selectedMediaItems.some(mi => mi.collection_name !== this.collection);
    },
  },
};
</script>

<style lang="scss">
.full-modal {
  height: calc(100vh - 3rem);
}

.media-hub-dropzone {
  label > div > p > div.rounded {
    white-space: nowrap;
  }
}
</style>
