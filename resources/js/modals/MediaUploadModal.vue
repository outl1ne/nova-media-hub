<template>
  <Modal :show="show" @close-via-escape="$emit('close')" role="alertdialog" maxWidth="2xl">
    <LoadingCard :loading="loading" class="mx-auto bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
      <slot>
        <ModalHeader class="flex items-center">Upload media</ModalHeader>

        <ModalContent class="px-8 nml-flex nml-flex-col">
          <!-- Select existing collection -->
          <span>Select collection to add media to:</span>
          <select v-model="selectedCollection" class="form-control form-input form-input-bordered">
            <option value="media-hub-new-collection">Create new collection</option>
            <option v-for="c in collections" :key="c" :value="c">{{ c }}</option>
          </select>

          <template v-if="newCollection">
            <span class="mt-6"> Enter new collection name: </span>
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
          <LoadingButton @click.prevent="$emit('close')" class="nml-mr-4">
            {{ __('Close') }}
          </LoadingButton>

          <LoadingButton @click.prevent="uploadFiles"> Upload files </LoadingButton>
        </div>
      </ModalFooter>
    </LoadingCard>
  </Modal>
</template>

<script>
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

        await Nova.request().post(
          `/nova-vendor/media-hub/media/save?collectionName=${this.finalCollectionName}`,
          formData
        );

        this.$emit('close', true, this.finalCollectionName);
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
      const { data } = await Nova.request().get('/nova-vendor/media-hub/collections');
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
  },
};
</script>
