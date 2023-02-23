<template>
  <Teleport to="body">
    <VueSimpleContextMenu
      :elementId="id || 'simple-ctx-menu'"
      :options="options"
      ref="vueSimpleContextMenu"
      @option-clicked="onOptionClicked"
    />

    <MediaViewModal :mediaItem="mediaItem" @close="closeViewModal" :show="showMediaViewModal" :readonly="readonly" />

    <a
      v-if="mediaItem"
      :href="mediaItem.url"
      download
      ref="downloadAnchor"
      target="_BLANK"
      rel="noopener noreferrer"
      class="o1-hidden"
    />
  </Teleport>
</template>

<script>
import MediaViewModal from '../modals/MediaViewModal';
import VueSimpleContextMenu from 'vue-simple-context-menu/src/vue-simple-context-menu';

export default {
  components: { VueSimpleContextMenu, MediaViewModal },

  props: ['id', 'showEvent', 'options', 'mediaItem', 'readonly'],

  emits: ['openModal', 'hideModal', 'optionClick'],

  data: () => ({
    showMediaViewModal: false,
  }),

  watch: {
    showEvent(newValue) {
      if (newValue) {
        this.$refs.vueSimpleContextMenu.showMenu(newValue);
      } else {
        this.$refs.vueSimpleContextMenu.hideContextMenu();
      }
    },
  },

  methods: {
    onOptionClicked(event) {
      const action = event.option.action || void 0;

      if (action === 'view') return this.openViewModal();

      if (action === 'download') {
        this.$nextTick(() => this.$refs.downloadAnchor.click());
        return;
      }

      this.$emit('optionClick', event);
    },

    openViewModal() {
      this.$emit('showModal');
      this.showMediaViewModal = true;
    },

    closeViewModal() {
      this.$emit('hideModal');
      this.showMediaViewModal = false;
    },
  },
};
</script>
