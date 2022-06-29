<template>
  <Modal :show="show" @close-via-escape="$emit('close')" role="alertdialog" maxWidth="w-full" class="p-8">
    <LoadingCard :loading="loading" class="mx-auto bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
      <slot>
        <ModalContent class="o1-px-8 o1-flex o1-flex-col">
          <!-- Selected media -->
          <div class="o1-flex o1-flex-col o1-py-6 o1-border-b o1-border-slate-200">
            <div class="o1-leading-tight o1-text-teal-500 o1-font-bold o1-text-md o1-pb-4">Selected media</div>
            <div class="o1-flex o1-flex-wrap" v-if="!!selectedMediaItems.length">
              <MediaItem
                v-for="mediaItem in selectedMediaItems"
                :key="'selected-' + mediaItem.id"
                :mediaItem="mediaItem"
                @click="toggleMediaSelection(mediaItem)"
                :selected="true"
                :size="36"
                @contextmenu.stop.prevent="openContextMenuFromSelected($event, mediaItem)"
              />
            </div>
            <div v-else class="o1-text-slate-400">No media items selected</div>
          </div>

          <div class="o1-flex">
            <!-- Choose collection -->
            <div class="o1-flex o1-flex-col o1-py-6 o1-pr-8 o1-w-64">
              <div class="o1-leading-tight o1-text-teal-500 o1-font-bold o1-text-md o1-pb-4">Choose collection</div>
              <SelectControl v-model:selected="collection" @change="c => (collection = c)">
                <option v-for="c in collections" :key="c" :value="c">{{ c }}</option>
              </SelectControl>
            </div>

            <!-- Collection media -->
            <div class="o1-flex o1-flex-col o1-py-6">
              <div class="o1-leading-tight o1-text-teal-500 o1-font-bold o1-text-md o1-pb-4">Choose media</div>
              <div class="o1-flex o1-flex-wrap" v-show="!!filteredMediaItems.length">
                <MediaItem
                  v-for="mediaItem in filteredMediaItems"
                  :key="'media-' + mediaItem.id"
                  :mediaItem="mediaItem"
                  @click="toggleMediaSelection(mediaItem)"
                  @contextmenu.stop.prevent="openContextMenuFromChoose($event, mediaItem)"
                />
              </div>

              <div v-show="allCollectionItemsSelected" class="o1-text-slate-400">
                All media items from collection selected
              </div>

              <div v-show="!allCollectionItemsSelected && !mediaItems.length" class="o1-text-slate-400">
                No media items found
              </div>
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

    <MediaViewModal :show="showMediaViewModal" :mediaItem="targetMediaItem" @close="showMediaViewModal = false" />

    <ConfirmDeleteModal :show="showConfirmDeleteModal" :mediaItem="targetMediaItem" @close="handleDeleteModalClose" />

    <ChooseMediaModal
      :initialSelectedMediaItems="value"
      :show="showChooseModal"
      @close="showChooseModal = false"
      @confirm="mediaItemsSelected"
    />

    <VueSimpleContextMenu
      elementId="mediaItemContextMenuChooseModal"
      :options="contextMenuOptions"
      ref="vueSimpleContextMenu"
      @option-clicked="onMediaItemContextMenuClick"
    />

    <!-- Fake download button -->
    <a
      :href="targetMediaItem && targetMediaItem.url"
      download
      ref="downloadAnchor"
      target="_BLANK"
      rel="noopener noreferrer"
      class="o1-hidden"
    />
  </Modal>
</template>

<script>
import MediaItem from '../components/MediaItem';
import MediaViewModal from '../modals/MediaViewModal';
import MediaUploadModal from '../modals/MediaUploadModal';
import HandlesMediaLists from '../mixins/HandlesMediaLists';
import ConfirmDeleteModal from '../modals/ConfirmDeleteModal';
import VueSimpleContextMenu from 'vue-simple-context-menu/src/vue-simple-context-menu';

export default {
  mixins: [HandlesMediaLists],
  components: { MediaItem, MediaUploadModal, MediaViewModal, ConfirmDeleteModal, VueSimpleContextMenu },

  emits: ['close', 'confirm'],
  props: ['show', 'activeCollection', 'initialSelectedMediaItems'],

  data: () => ({
    collectionName: '',
    selectedMediaItems: [],

    showMediaUploadModal: false,
    showMediaViewModal: false,
    showConfirmDeleteModal: false,

    targetMediaItem: void 0,
    contextMenuOptions: [],
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
        this.selectedMediaItems = [...this.initialSelectedMediaItems];
      }
    },

    async collection() {
      await this.getCollectionMedia(this.collection);
    },
  },

  methods: {
    toggleMediaSelection(mediaItem) {
      if (this.selectedMediaItems.find(mi => mi.id === mediaItem.id)) {
        this.selectedMediaItems = this.selectedMediaItems.filter(mi => mi.id !== mediaItem.id);
      } else {
        this.selectedMediaItems.push(mediaItem);
      }
    },

    confirm() {
      this.$emit('confirm', this.selectedMediaItems);
    },

    async handleUploadModalClose(updateData, collectionName) {
      this.showMediaUploadModal = false;

      if (updateData) {
        await this.getCollections();
        this.collection = collectionName;
        await this.getCollectionMedia(this.collection);
      }
    },

    openContextMenuFromSelected(event, mediaItem) {
      this.contextMenuOptions = [
        { name: 'Deselect', action: 'deselect', class: 'o1-text-rose-600' },
        { name: 'View / Edit', action: 'view', class: 'o1-text-slate-600' },
        { name: 'Download', action: 'download', class: 'o1-text-slate-600' },
      ];

      this.$nextTick(() => {
        this.$refs.vueSimpleContextMenu.showMenu(event, mediaItem);
      });
    },

    openContextMenuFromChoose(event, mediaItem) {
      this.contextMenuOptions = [
        { name: 'Select', action: 'select', class: 'o1-text-slate-600' },
        { name: 'View / Edit', action: 'view', class: 'o1-text-slate-600' },
        { name: 'Download', action: 'download', class: 'o1-text-slate-600' },
        { name: 'Delete', action: 'delete', class: 'o1-text-rose-600' },
      ];

      this.$nextTick(() => {
        this.$refs.vueSimpleContextMenu.showMenu(event, mediaItem);
      });
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

      if (action === 'select' || action === 'deselect') {
        this.toggleMediaSelection(this.targetMediaItem);
      }
    },

    handleDeleteModalClose(update = false) {
      this.showConfirmDeleteModal = false;
      if (update) this.getCollectionMedia();
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
