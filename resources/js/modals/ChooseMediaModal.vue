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
            <div class="o1-leading-tight o1-text-teal-500 o1-font-bold o1-text-md o1-pb-4">
              {{ __('novaMediaHub.selectedMediaTitle') + (selectedCount > 1 ? ` (${selectedCount})` : '') }}
            </div>
            <div class="o1-flex overflow-x-auto o1-pt-1 o1-px-1" v-if="!!selectedCount">
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
            <div v-else-if="!selectedCount" class="o1-text-slate-400">{{ __('novaMediaHub.noMediaSelectedText') }}</div>
          </div>

          <div class="o1-flex o1-pt-4 o1-min-h-[30%] o1-h-full">
            <div class="o1-flex o1-flex-col o1-gap-5 o1-w-full o1-max-w-xs o1-pr-8 overflow-y-auto">
              <!-- Choose collection -->
              <ModalFilterItem :title="__('novaMediaHub.chooseCollectionTitle')">
                <SelectControl v-model:selected="collection" @change="c => (collection = c)">
                  <option value="">{{ '> Show all' }}</option>
                  <option v-for="c in collections" :key="c" :value="c">{{ c }}</option>
                </SelectControl>
              </ModalFilterItem>

              <!-- Search -->
              <ModalFilterItem :title="__('novaMediaHub.searchMediaTitle')">
                <input
                  v-model="search"
                  ref="search"
                  class="w-full form-control form-input form-input-bordered"
                  tabindex="-1"
                  type="search"
                  :placeholder="__('Search')"
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
                <div class="o1-leading-tight o1-text-teal-500 o1-font-bold o1-text-md o1-mb-2">
                  {{ __('novaMediaHub.quickUpload') }}
                </div>
                <NMHDropZone @fileChanged="uploadFiles" :multiple="true" />
              </div>
            </div>

            <!-- Collection media -->
            <div class="o1-flex o1-flex-col o1-w-full">
              <div class="o1-leading-tight o1-text-teal-500 o1-font-bold o1-text-md o1-pb-4">
                {{ __('novaMediaHub.chooseMediaTitle') }}
              </div>
              <div class="o1-w-full overflow-y-auto">
                <div
                  id="media-items-list"
                  class="o1-w-full o1-grid o1-gap-4 o1-justify-items-center o1-p-1"
                  v-show="!!mediaItems.length"
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

              <div v-show="!mediaItems.length" class="o1-text-slate-400">
                {{ __('novaMediaHub.noMediaItemsFoundText') }}
              </div>

              <PaginationLinks
                v-show="mediaResponse.last_page > 1"
                class="o1-mt-auto o1-w-full o1-border-t o1-border-slate-200 o1-border-l dark:o1-border-gray-700"
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
          <CancelButton @click.prevent="$emit('close')" class="o1-mr-4">
            {{ __('novaMediaHub.closeButton') }}
          </CancelButton>

          <LoadingButton @click.prevent="confirm">{{ __('novaMediaHub.confirmButton') }}</LoadingButton>
        </div>
      </ModalFooter>
    </LoadingCard>

    <ConfirmDeleteModal :show="showConfirmDeleteModal" :mediaItem="ctxMediaItem" @close="handleDeleteModalClose" />
    <MediaViewModal :mediaItem="ctxMediaItem" @close="closeViewModal" :show="showMediaViewModal" />

    <MediaItemContextMenu
      id="media-choose-modal-ctx-menu"
      :showEvent="ctxShowEvent"
      :options="ctxOptions"
      @showModal="ctxShowingModal = true"
      @hideModal="ctxShowingModal = false"
      :mediaItem="ctxMediaItem"
      @optionClick="contextOptionClick"
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
  },

  emits: ['close', 'confirm'],
  props: ['show', 'field', 'activeCollection', 'initialSelectedMediaItems'],

  data: () => ({
    selectedMediaItems: [],

    loading: false,
    showConfirmDeleteModal: false,
    showMediaViewModal: false,

    ctxOptions: [],
    ctxMediaItem: void 0,
    ctxShowEvent: void 0,
    ctxShowingModal: false,
  }),

  created() {
    this.$watch(
      () => ({ collection: this.collection, search: this.search, orderBy: this.orderBy }),
      data => this.getMedia({ ...data, page: 1 })
    );
  },

  async mounted() {
    if (this.field.defaultCollectionName) this.collection = this.field.defaultCollectionName;
    await this.getCollections();
    this.$nextTick(() => (this.loading = false));
  },

  watch: {
    async show(newValue) {
      if (newValue) {
        await this.getCollections();
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
    async uploadFiles(selectedFiles) {
      this.loading = true;

      const { success, media } = await this.uploadFilesToCollection(selectedFiles, this.collection);

      if (success) {
        await this.getMedia({ collection: this.collection });
        media.map(this.toggleMediaSelection);
      }

      this.loading = false;
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
  },

  computed: {
    selectedCount() {
      return this.selectedMediaItems.length;
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
