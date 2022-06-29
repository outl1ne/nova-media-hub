<template>
  <Modal :show="show" role="alertdialog" id="o1-nmh-confirm-delete-modal">
    <div class="o1-bg-white dark:o1-bg-gray-800 o1-rounded-lg o1-shadow-lg o1-overflow-hidden" style="width: 460px">
      <ModalHeader v-text="__('Delete File')" />

      <ModalContent>
        <p class="o1-leading-tight">
          {{ __('Are you sure you want to delete this file?') }}
        </p>

        <p class="o1-leading-tight">
          {{ mediaItem.file_name }}
        </p>
      </ModalContent>

      <ModalFooter>
        <div class="o1-ml-auto">
          <LinkButton type="button" @click.prevent="$emit('close')" class="o1-mr-3">
            {{ __('Cancel') }}
          </LinkButton>

          <LoadingButton
            @click.prevent="handleDelete"
            :disabled="loading"
            :processing="loading"
            component="DangerButton"
          >
            {{ __('Delete') }}
          </LoadingButton>
        </div>
      </ModalFooter>
    </div>
  </Modal>
</template>

<script>
import API from '../api';

export default {
  emits: ['confirm', 'close'],

  props: ['show', 'mediaItem'],

  data: () => ({ loading: false }),

  methods: {
    async handleDelete() {
      this.loading = true;

      await API.deleteMedia(this.mediaItem.id);

      this.$emit('close', true);
    },
  },
};
</script>

<style lang="scss">
#o1-nmh-confirm-delete-modal {
  z-index: 130;

  + .fixed {
    z-index: 129;
  }
}
</style>
