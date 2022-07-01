<template>
  <Modal :show="show" @close-via-escape="$emit('close')" role="alertdialog" maxWidth="w-full" class="o1-px-8">
    <LoadingCard :loading="loading" class="mx-auto bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
      <slot>
        <ModalContent class="o1-px-8 o1-py-0 o1-flex o1-flex-col">
          <!-- Selected media -->
          <div class="o1-flex o1-flex-col o1-py-6 o1-border-b o1-border-slate-200">
            <div class="o1-leading-tight o1-text-teal-500 o1-font-bold o1-text-md o1-pb-4">Selected media</div>
            <div class="o1-flex o1-flex-wrap" v-if="!!selectedMediaItems.length">
              <Draggable v-model="selectedMediaItems" item-key="id" class="o1-flex o1-flex-wrap">
                <template #item="{ element: mediaItem }">
                  <MediaItem
                    :key="'selected-' + mediaItem.id"
                    :mediaItem="mediaItem"
                    @click="toggleMediaSelection(mediaItem)"
                    :selected="true"
                    :size="36"
                    @contextmenu.stop.prevent="openContextMenuFromSelected($event, mediaItem)"
                    class="o1-mr-4"
                  />
                </template>
              </Draggable>
            </div>
            <div v-else class="o1-text-slate-400">No media items selected</div>
          </div>

          <div class="o1-flex">
            <!-- Choose collection -->
            <div class="o1-flex o1-flex-col o1-py-6 o1-pr-8 o1-w-full o1-max-w-xs">
              <div class="o1-leading-tight o1-text-teal-500 o1-font-bold o1-text-md o1-pb-4">Choose collection</div>
              <SelectControl v-model:selected="collection" @change="c => (collection = c)">
                <option value="">{{ '> Show all' }}</option>
                <option v-for="c in collections" :key="c" :value="c">{{ c }}</option>
              </SelectControl>
            </div>

            <!-- Collection media -->
            <div class="o1-flex o1-flex-col o1-pt-6 o1-w-full">
              <div class="o1-leading-tight o1-text-teal-500 o1-font-bold o1-text-md o1-pb-4">Choose media</div>
              <div class="o1-w-full">
                <div
                  id="media-items-list"
                  class="o1-w-full o1-grid o1-gap-4 o1-justify-items-center"
                  v-show="!!filteredMediaItems.length"
                >
                  <MediaItem
                    v-for="mediaItem in filteredMediaItems"
                    :key="'media-' + mediaItem.id"
                    :mediaItem="mediaItem"
                    @click="toggleMediaSelection(mediaItem)"
                    @contextmenu.stop.prevent="openContextMenuFromChoose($event, mediaItem)"
                    class="o1-mb-4"
                  />
                </div>
              </div>

              <div v-show="allCollectionItemsSelected" class="o1-text-slate-400">
                All media items from collection selected
              </div>

              <div v-show="!allCollectionItemsSelected && !mediaItems.length" class="o1-text-slate-400">
                No media items found
              </div>

              <PaginationLinks
                v-if="!!filteredMediaItems.length"
                class="o1-mt-auto o1-w-full o1-border-t o1-border-slate-200 o1-border-l"
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
            {{ __('Close') }}
          </CancelButton>

          <LoadingButton @click.prevent="showMediaUploadModal = true" class="o1-mr-4">Upload media</LoadingButton>

          <LoadingButton @click.prevent="confirm">Confirm</LoadingButton>
        </div>
      </ModalFooter>
    </LoadingCard>

    <MediaUploadModal :activeCollection="collection" :show="showMediaUploadModal" @close="handleUploadModalClose" />

    <ConfirmDeleteModal :show="showConfirmDeleteModal" :mediaItem="ctxMediaItem" @close="handleDeleteModalClose" />

    <MediaItemContextMenu
      id="media-choose-modal-ctx-menu"
      :showEvent="ctxShowEvent"
      :options="ctxOptions"
      @close="ctxShowEvent = void 0"
      :mediaItem="ctxMediaItem"
      @optionClick="contextOptionClick"
    />
  </Modal>
</template>

<script>
import Draggable from 'vuedraggable';
import MediaItem from '../components/MediaItem';
import MediaUploadModal from '../modals/MediaUploadModal';
import PaginationLinks from '../components/PaginationLinks';
import HandlesMediaLists from '../mixins/HandlesMediaLists';
import ConfirmDeleteModal from '../modals/ConfirmDeleteModal';
import MediaItemContextMenu from '../components/MediaItemContextMenu';

export default {
  mixins: [HandlesMediaLists],
  components: { Draggable, MediaItem, MediaUploadModal, ConfirmDeleteModal, MediaItemContextMenu, PaginationLinks },

  emits: ['close', 'confirm'],
  props: ['show', 'field', 'activeCollection', 'initialSelectedMediaItems'],

  data: () => ({
    collectionName: '',
    selectedMediaItems: [],

    showMediaUploadModal: false,
    showConfirmDeleteModal: false,

    ctxOptions: [],
    ctxMediaItem: void 0,
    ctxShowEvent: void 0,
  }),

  async mounted() {
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

    async collection(newValue) {
      this.currentPage = 1;
      await this.getMedia(newValue);
    },
  },

  methods: {
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

    async handleUploadModalClose(updateData, collectionName) {
      this.showMediaUploadModal = false;

      if (updateData) {
        await this.getCollections();
        this.collection = collectionName;
        await this.getMedia(this.collection);
      }
    },

    openContextMenuFromSelected(event, mediaItem) {
      this.ctxMediaItem = mediaItem;
      this.ctxOptions = [
        { name: 'View / Edit', action: 'view', class: 'o1-text-slate-600' },
        { name: 'Download', action: 'download', class: 'o1-text-slate-600' },
        { name: 'Open collection', action: 'open-collection', class: 'o1-text-slate-600' },
        { type: 'divider' },
        { name: 'Deselect', action: 'deselect', class: 'o1-text-rose-600' },
        { name: 'Deselect others', action: 'deselect-others', class: 'o1-text-rose-600' },
      ];

      this.$nextTick(() => (this.ctxShowEvent = event));
    },

    openContextMenuFromChoose(event, mediaItem) {
      this.ctxMediaItem = mediaItem;
      this.ctxOptions = [
        { name: 'Select', action: 'select', class: 'o1-text-slate-600' },
        { name: 'View / Edit', action: 'view', class: 'o1-text-slate-600' },
        { name: 'Download', action: 'download', class: 'o1-text-slate-600' },
        { name: 'Delete', action: 'delete', class: 'o1-text-rose-600' },
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
      if (update) this.getMedia(this.collection);
    },

    async switchToPage(page) {
      await this.goToMediaPage(page);
      Nova.$emit('resources-loaded');
    },
  },

  computed: {
    allCollectionItemsSelected() {
      return !!this.mediaItems.length && !this.filteredMediaItems.length;
    },

    filteredMediaItems() {
      return this.mediaItems.filter(mi => !this.selectedMediaItems.find(m => m.id === mi.id));
    },

    hasFilteredMediaItems() {
      return !!this.filteredMediaItems.length;
    },
  },
};
</script>