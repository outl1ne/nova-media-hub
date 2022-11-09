<template>
  <Modal
    :show="show"
    @close-via-escape="$emit('close')"
    role="alertdialog"
    maxWidth="2xl"
    id="o1-nmh-media-upload-modal"
  >
    <LoadingCard :loading="loading" class="mx-auto bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
      <slot>
        <ModalHeader class="flex items-center">{{ __('novaMediaHub.uploadMediaTitle') }}</ModalHeader>

        <ModalContent class="px-8 o1-flex o1-flex-col">
          <!-- Select existing collection -->
          <span>{{ __('novaMediaHub.uploadModalSelectCollectionTitle') }}</span>
          <SelectControl v-model:selected="selectedCollection" @change="c => (selectedCollection = c)">
            <option value="media-hub-new-collection" v-if="canCreateCollections">
              {{ __('novaMediaHub.uploadModalCreateNewOption') }}
            </option>
            <option v-for="c in collections" :key="c" :value="c">{{ c }}</option>
          </SelectControl>

          <template v-if="newCollection">
            <span class="mt-6">{{ __('novaMediaHub.enterNewCollectionName') }}</span>
            <input
              type="text"
              name="collection_name"
              placeholder="Collection name"
              v-model="collectionName"
              class="form-control form-input form-input-bordered"
            />
          </template>

          <input
            class="form-control form-input form-input-bordered mt-6"
            type="file"
            name="selected_media"
            ref="filesInput"
            @change="onFilesChange"
            multiple
          />
        </ModalContent>
      </slot>

      <ModalFooter>
        <div class="ml-auto">
          <LoadingButton @click.prevent="$emit('close')" class="o1-mr-4">
            {{ __('novaMediaHub.closeButton') }}
          </LoadingButton>

          <LoadingButton @click.prevent="uploadFiles">{{ __('novaMediaHub.uploadFilesButton') }}</LoadingButton>
        </div>
      </ModalFooter>
    </LoadingCard>
  </Modal>
</template>

<script>
import API from '../api';

export default {
  emits: ['close'],
  props: ['show', 'activeCollection'],

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
    async uploadFiles() {
      this.loading = true;
      try {
        const formData = new FormData();
        for (const file of this.selectedFiles) {
          formData.append('files[]', file);
        }

        const { data } = await API.saveMediaToCollection(this.finalCollectionName, formData);

        let goToCollection = this.finalCollectionName;
        if (data.hadExisting) {
          Nova.$toasted.info(this.__('novaMediaHub.existingMediaDetected'));

          // Find possible new collection name
          const diffCollNameMedia = data.media.find(mi => mi.collection_name !== this.finalCollectionName);
          if (diffCollNameMedia) goToCollection = diffCollNameMedia.collection_name;
        }

        this.$emit('close', true, goToCollection);
      } catch (e) {
        if (e && e.response && e.response.data) {
          const data = e.response.data;

          if (data.errors && data.errors.length) {
            data.errors.forEach(error => Nova.$toasted.error(error));
          }

          // Some succeeded, let the user know
          if (data.success_count > 0) {
            Nova.$toasted.success(this.__('novaMediaHub.successfullyUploadedNMedia', { count: data.success_count }));
            this.$emit('close', true, this.finalCollectionName);
          }
        } else {
          Nova.$toasted.error(e.message);
        }
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

    canCreateCollections() {
      return Nova.appConfig.novaMediaHub.canCreateCollections;
    },
  },
};
</script>

<style lang="scss">
#o1-nmh-media-upload-modal {
  z-index: 120;

  + .fixed {
    z-index: 119;
  }
}
</style>
