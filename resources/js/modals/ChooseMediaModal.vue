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
              />
            </div>
            <div v-else class="o1-text-slate-400">No media items selected</div>
          </div>

          <div class="o1-flex">
            <!-- Choose collection -->
            <div class="o1-flex o1-flex-col o1-py-6 o1-pr-8">
              <div class="o1-leading-tight o1-text-teal-500 o1-font-bold o1-text-md o1-pb-4">Choose collection</div>
              <select
                v-model="collection"
                name="collection"
                class="block form-control form-select form-select-bordered"
              >
                <option v-for="c in collections" :key="c" :value="c">{{ c }}</option>
              </select>
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
  </Modal>
</template>

<script>
import MediaItem from '../components/MediaItem';
import HandlesMediaLists from '../mixins/HandlesMediaLists';
import MediaUploadModal from '../modals/MediaUploadModal';

export default {
  mixins: [HandlesMediaLists],
  components: { MediaItem, MediaUploadModal },

  emits: ['close', 'confirm'],
  props: ['show', 'activeCollection', 'initialSelectedMediaItems'],

  data: () => ({
    collectionName: '',
    selectedMediaItems: [],
    showMediaUploadModal: false,
  }),

  async mounted() {
    await this.getCollections();
    this.loading = false;
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
