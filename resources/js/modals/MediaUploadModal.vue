<template>
  <Modal :show="show" @close-via-escape="$emit('close')" role="alertdialog" maxWidth="2xl">
    <LoadingCard :loading="loading" class="mx-auto bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
      <slot>
        <ModalHeader class="flex items-center"> Upload media </ModalHeader>

        <ModalContent class="px-8">
          <input type="text" name="collection_name" v-model="collectionName" />
          <input type="file" name="selected_media" ref="filesInput" @change="onFilesChange" multiple />
        </ModalContent>
      </slot>

      <ModalFooter>
        <div class="ml-auto">
          <LoadingButton @click.prevent="$emit('close')">
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
  props: ['show'],

  data: () => ({
    loading: false,
    collectionName: '',
    selectedFiles: '',
  }),

  async created() {},

  mounted() {
    Nova.$emit('close-dropdowns');
  },

  methods: {
    async uploadFiles() {
      this.loading = true;
      try {
        const formData = new FormData();
        for (const file of this.selectedFiles) {
          formData.append('files[]', file);
        }

        await Nova.request().post(`/nova-vendor/media-hub/media/save?collectionName=${this.collectionName}`, formData);
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
  },

  computed: {},
};
</script>
