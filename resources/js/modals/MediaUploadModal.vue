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
          <span class="o1-mb-2">{{ __('novaMediaHub.uploadModalSelectCollectionTitle') }}</span>
          <select className="w-full block form-control form-control-bordered form-input" v-model="selectedCollection">
            <option v-for="option in options" :key="option.value" :value="option.value" class="capitalize">
              {{ option.label }}
            </option>
          </select>

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

          <NMHDropZone class="mt-6" @fileChanged="onFilesChange" :files="selectedFiles" multiple />
        </ModalContent>
      </slot>

      <ModalFooter>
        <div class="ml-auto">
          <Button @click.prevent="$emit('close')" class="o1-mr-4">
            {{ __('novaMediaHub.closeButton') }}
          </Button>

          <Button @click.prevent="uploadFiles">
            {{ __('novaMediaHub.uploadFilesButton') }}
          </Button>
        </div>
      </ModalFooter>
    </LoadingCard>
  </Modal>
</template>

<script>
import API from '../api';
import HandlesMediaUpload from '../mixins/HandlesMediaUpload';
import { Button } from 'laravel-nova-ui';

export default {
  components: { Button },
  mixins: [HandlesMediaUpload],
  emits: ['close'],
  props: ['show', 'activeCollection'],

  data: () => ({
    loading: true,
    collectionName: '',
    selectedFiles: [],
    selectedCollection: 'default',
    collections: [],
  }),

  mounted() {
    Nova.$emit('close-dropdowns');
  },

  watch: {
    async show(newValue) {
      if (newValue) {
        await this.getCollections();
        this.selectedCollection = this.activeCollection || this.collections[0];
      }
    },
  },

  methods: {
    async uploadFiles() {
      this.loading = true;

      const { success, media, hadExisting } = await this.uploadFilesToCollection(
        this.selectedFiles,
        this.finalCollectionName
      );

      let goToCollection = this.finalCollectionName;

      if (hadExisting) {
        // Find possible new collection name
        const diffCollNameMedia = media.find(mi => mi.collection_name !== this.finalCollectionName);
        if (diffCollNameMedia) goToCollection = diffCollNameMedia.collection_name;
      }

      if (success) {
        this.$emit('close', true, goToCollection);
      }

      this.loading = false;
    },

    onFilesChange(selectedFiles) {
      this.selectedFiles = Array.from(selectedFiles);
    },

    async getCollections() {
      this.loading = true;
      const { data } = await API.getCollections();
      this.collections = data || [];

      if (!this.selectedCollection) {
        this.selectedCollection = this.collections.length ? this.collections[0] : void 0;
      }

      this.loading = false;
    },
  },

  computed: {
    options() {
      const options = this.collections.map(c => ({ label: c, value: c }));

      if (this.canCreateCollections) {
        options.unshift({
          label: this.__('novaMediaHub.uploadModalCreateNewOption'),
          value: 'media-hub-new-collection',
        });
      }

      return options;
    },

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
