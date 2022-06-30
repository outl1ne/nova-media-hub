<template>
  <div>
    <VueSimpleContextMenu
      :elementId="id || 'simple-ctx-menu'"
      :options="options"
      ref="vueSimpleContextMenu"
      @option-clicked="onOptionClicked"
    />

    <!-- View media modal -->
    <MediaViewModal :show="showMediaViewModal" :mediaItem="mediaItem" @close="showMediaViewModal = false" />

    <!-- Fake download button -->
    <a
      v-if="mediaItem"
      :href="mediaItem.url"
      download
      ref="downloadAnchor"
      target="_BLANK"
      rel="noopener noreferrer"
      class="o1-hidden"
    />
  </div>
</template>

<script>
import MediaViewModal from '../modals/MediaViewModal';
import VueSimpleContextMenu from 'vue-simple-context-menu/src/vue-simple-context-menu';

export default {
  components: { VueSimpleContextMenu, MediaViewModal },

  props: ['id', 'showEvent', 'options', 'mediaItem'],

  emits: ['close', 'optionClick'],

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

      if (action === 'view') {
        this.showMediaViewModal = true;
      }

      if (action === 'download') {
        this.$nextTick(() => this.$refs.downloadAnchor.click());
      }
    },
  },
};
</script>
