<template>
  <Modal
    :show="show"
    @close-via-escape="$emit('close')"
    role="alertdialog"
    maxWidth="2xl"
    id="o1-nmh-media-replace-modal"
  >
    <LoadingCard :loading="loading" class="mx-auto bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
      <slot>
        <ModalHeader class="flex items-center">{{ __('novaMediaHub.replaceMediaTitle') }}</ModalHeader>

        <ModalContent class="px-8 o1-flex o1-flex-col">
          <span class="o1-mb-2">{{ __('novaMediaHub.replaceModalActionDescription') }}</span>
          <NMHDropZone class="mt-6" @fileChanged="onFilesChange" :files="selectedFiles" />
        </ModalContent>
      </slot>

      <ModalFooter>
        <div class="ml-auto">
          <Button @click.prevent="$emit('close')" class="o1-mr-4">
            {{ __('novaMediaHub.closeButton') }}
          </Button>

          <Button @click.prevent="uploadFiles" state="danger" :disabled="loading || !canSubmit">
            {{ __('novaMediaHub.replaceFileButton') }}
          </Button>
        </div>
      </ModalFooter>
    </LoadingCard>
  </Modal>
</template>

<script>
import { Button } from 'laravel-nova-ui';
import HandlesMediaUpload from '../mixins/HandlesMediaUpload';

export default {
  components: { Button },
  mixins: [HandlesMediaUpload],
  emits: ['close'],
  props: ['show', 'existingMediaItem'],

  data: () => ({
    loading: false,
    selectedFiles: [],
    collections: [],
  }),

  mounted() {
    Nova.$emit('close-dropdowns');
  },

  methods: {
    async uploadFiles() {
      this.loading = true;

      const { success, media } = await this.replaceFileInPlace(this.existingMediaItem, this.selectedFiles[0]);

      if (success) {
        Nova.$toasted.success(this.__('novaMediaHub.mediaSuccessfullyReplaced', { id: media.id }));
        this.$emit('close', true);
      }

      this.loading = false;
    },

    onFilesChange(selectedFiles) {
      this.selectedFiles = Array.from(selectedFiles);
    },
  },

  computed: {
    canSubmit() {
      return this.selectedFiles.length === 1;
    },
  },
};
</script>

<style lang="scss">
#o1-nmh-media-replace-modal {
  z-index: 120;

  + .fixed {
    z-index: 119;
  }
}
</style>
