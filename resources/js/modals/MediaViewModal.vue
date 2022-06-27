<template>
  <Modal :show="show" @close-via-escape="$emit('close')" role="alertdialog" maxWidth="2xl">
    <LoadingCard :loading="loading" class="mx-auto bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
      <slot>
        <ModalHeader class="flex items-center">View media</ModalHeader>

        <ModalContent class="px-8 nml-flex nml-flex-col">
          <div>
            <img :src="mediaItem.url" :alt="mediaItem.file_name" />
          </div>
        </ModalContent>
      </slot>

      <ModalFooter>
        <div class="ml-auto">
          <LoadingButton @click.prevent="$emit('close')" class="nml-mr-4">
            {{ __('Close') }}
          </LoadingButton>

          <LoadingButton @click.prevent="saveAndExit">Save and exit</LoadingButton>
        </div>
      </ModalFooter>
    </LoadingCard>
  </Modal>
</template>

<script>
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
