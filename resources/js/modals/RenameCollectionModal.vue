<template>
  <Modal :show="show" @close-via-escape="$emit('close')" role="alertdialog" id="o1-nmh-rename-collection-modal">
    <div class="o1-bg-white dark:o1-bg-gray-800 o1-rounded-lg o1-shadow-lg o1-overflow-hidden" style="width: 460px">
      <ModalHeader v-text="__('novaMediaHub.renameCollectionTitle')" />

      <ModalContent class="o1-flex o1-flex-col">
        <p class="o1-leading-tight o1-mb-2">
          {{ __('novaMediaHub.renameCollectionText', { collection: collection }) }}
        </p>

        <input
          ref="nameInput"
          type="text"
          name="collection_name"
          v-model="newName"
          class="w-full form-control form-input form-input-bordered"
          :placeholder="__('novaMediaHub.enterNewCollectionName')"
          spellcheck="false"
          @keydown.enter.prevent="handleRename"
        />
      </ModalContent>

      <ModalFooter>
        <div class="o1-ml-auto">
          <Button variant="link" state="mellow" type="button" @click.prevent="$emit('close')" class="o1-mr-3">
            {{ __('novaMediaHub.closeButton') }}
          </Button>

          <Button @click.prevent="handleRename" :disabled="loading || !canSubmit">
            {{ __('novaMediaHub.renameButton') }}
          </Button>
        </div>
      </ModalFooter>
    </div>
  </Modal>
</template>

<script>
import API from '../api';
import { Button } from 'laravel-nova-ui';

export default {
  components: { Button },
  emits: ['close'],
  props: ['show', 'collection'],

  data: () => ({
    loading: false,
    newName: '',
  }),

  watch: {
    show(newValue) {
      if (!newValue) return;

      this.newName = this.collection || '';
      this.$nextTick(() => this.$refs.nameInput?.focus());
    },
  },

  methods: {
    async handleRename() {
      if (!this.canSubmit || this.loading) return;

      const newName = this.newName.trim();

      if (newName === this.collection) {
        this.$emit('close');
        return;
      }

      this.loading = true;

      try {
        const { data } = await API.renameCollection(this.collection, newName);

        Nova.$toasted.success(this.__('novaMediaHub.collectionSuccessfullyRenamed', { collection: data.collection }));

        this.$emit('close', data.collection);
      } catch (e) {
        Nova.$toasted.error(e?.response?.data?.error || e.message);
      } finally {
        this.loading = false;
      }
    },
  },

  computed: {
    canSubmit() {
      return !!this.newName?.trim();
    },
  },
};
</script>

<style lang="scss">
#o1-nmh-rename-collection-modal {
  z-index: 130;

  + .fixed {
    z-index: 129;
  }
}
</style>
