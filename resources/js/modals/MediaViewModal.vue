<template>
  <Modal :show="show" @close-via-escape="$emit('close')" role="alertdialog" maxWidth="2xl">
    <LoadingCard :loading="loading" class="mx-auto bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
      <slot>
        <ModalHeader class="flex items-center">Media ({{ mediaItem.file_name }})</ModalHeader>

        <ModalContent class="o1-px-8 o1-flex o1-flex-col">
          <div class="o1-flex o1-flex-col o1-m-auto o1-h-full o1-w-full" style="max-height: 50vh">
            <img
              v-if="type === 'image'"
              class="o1-object-contain o1-max-w-full o1-w-full o1-max-h-full o1-shadow o1-border o1-border-slate-100 o1-bg-slate-50 o1-min-h-0"
              :src="mediaItem.url"
              :alt="mediaItem.file_name"
            />

            <video v-else-if="type === 'video'" controls autoplay class="o1-ml-auto o1-h-full o1-w-full o1-min-h-0">
              <source :src="mediaItem.url" :type="mediaItem.mime_type" />
            </video>

            <audio v-else-if="type === 'audio'" :src="mediaItem.url" controls autoplay>
              <source :src="mediaItem.url" :type="mediaItem.mime_type" />
            </audio>

            <a v-else :href="mediaItem.url">
              {{ mediaItem.url }}
            </a>
          </div>

          <!-- TODO: Inputs -->
        </ModalContent>
      </slot>

      <ModalFooter>
        <div class="ml-auto">
          <LoadingButton @click.prevent="$emit('close')" class="o1-mr-4">
            {{ __('Close') }}
          </LoadingButton>

          <LoadingButton @click.prevent="saveAndExit">Save and exit</LoadingButton>
        </div>
      </ModalFooter>
    </LoadingCard>
  </Modal>
</template>

<script>
import API from '../api';

export default {
  emits: ['close'],
  props: ['show', 'mediaItem'],

  data: () => ({
    loading: false,
    collectionName: '',
    selectedFiles: '',
    selectedCollection: void 0,
    collections: [],
  }),

  mounted() {
    Nova.$emit('close-dropdowns');
  },

  watch: {
    async show(newValue) {
      if (newValue) {
        await this.getCollections();
        this.selectedCollection = this.activeCollection;
      }
    },
  },

  methods: {
    async saveAndExit() {
      this.loading = true;
      try {
        c;

        this.$emit('close');
      } catch (e) {
        Nova.$toasted.success(e.message);
      }
      this.loading = false;
    },

    onFilesChange(e) {
      if (this.$refs.filesInput) {
        this.selectedFiles = Array.from(this.$refs.filesInput.files);
      }
    },

    async getCollections() {
      const { data } = await API.getCollections();
      this.collections = data || [];

      if (!this.selectedCollection) {
        this.selectedCollection = this.collections.length ? this.collections[0] : void 0;
      }
    },
  },

  computed: {
    newCollection() {
      return this.selectedCollection === 'media-hub-new-collection';
    },

    finalCollectionName() {
      return this.newCollection ? this.collectionName : this.selectedCollection;
    },

    type() {
      const mimeType = this.mediaItem.mime_type.split('/')[0];
      if (mimeType === 'image') return 'image';
      if (mimeType === 'audio') return 'audio';
      if (mimeType === 'video') return 'video';
      return 'other';
    },
  },
};
</script>
