<template>
  <button
    class="nml-h-48 nml-w-48 nml-mx-2 nml-bg-slate-50 nml-shadow-sm hover:nml-shadow nml-border nml-border-slate-100 hover:nml-bg-slate-100 hover:nml-border-slate-200"
  >
    <!-- Content wrapper -->
    <div
      class="content-wrapper nml-h-full nml-w-full"
      @click.prevent.stop="openMediaViewModal"
      @contextmenu.prevent.stop="openMediaItemContextMenu($event)"
    >
      <img
        v-if="type === 'image'"
        :src="mediaItem.url"
        :alt="mediaItem.file_name"
        class="nml-object-contain nml-max-w-full nml-w-full nml-max-h-full nml-h-full"
      />

      <div v-else class="nml-h-full nml-w-full nml-flex nml-flex-col nml-items-center nml-justify-center">
        <AudioIcon v-if="type === 'audio'" />
        <VideoIcon v-if="type === 'video'" />
        <OtherIcon v-if="type === 'other'" />

        <span class="nml-mt-4">{{ mediaItem.file_name }}</span>
      </div>
    </div>

    <a
      :href="mediaItem.url"
      download
      ref="downloadAnchor"
      target="_BLANK"
      rel="noopener noreferrer"
      class="nml-hidden"
    />

    <MediaViewModal :show="showMediaViewModal" :mediaItem="mediaItem" @close="showMediaViewModal = false" />

    <VueSimpleContextMenu
      :elementId="'mediaItemContextMenu' + mediaItem.id"
      :options="contextMenuOptions"
      ref="vueSimpleContextMenu"
      @option-clicked="onMediaItemContextMenuClick"
    />
  </button>
</template>

<script>
import MediaViewModal from '../modals/MediaViewModal';
import VueSimpleContextMenu from 'vue-simple-context-menu/src/vue-simple-context-menu';
import AudioIcon from '../icons/AudioIcon';
import VideoIcon from '../icons/VideoIcon';
import OtherIcon from '../icons/OtherIcon';

export default {
  components: { VueSimpleContextMenu, MediaViewModal, AudioIcon, VideoIcon, OtherIcon },

  props: ['mediaItem'],

  data: () => ({
    contextMenuOptions: [],
    showMediaViewModal: false,
  }),

  async created() {
    this.contextMenuOptions = [
      { name: 'View', action: 'view' },
      { name: 'Edit' },
      { name: 'Download', action: 'download' },
      { type: 'divider' },
      { name: 'Delete' },
    ];
  },

  methods: {
    openMediaViewModal() {
      this.showMediaViewModal = true;
      this.$refs.vueSimpleContextMenu.hideContextMenu();
    },

    openMediaItemContextMenu(event) {
      this.$refs.vueSimpleContextMenu.showMenu(event);
    },

    onMediaItemContextMenuClick(event) {
      const action = event.option.action || void 0;

      if (action === 'view') {
        this.showMediaViewModal = true;
      }

      if (action === 'download') {
        console.info('download??');
        this.$refs.downloadAnchor.click();
      }
    },
  },

  computed: {
    type() {
      const mimeType = this.mediaItem.mime_type.split('/')[0];
      if (mimeType === 'image') return 'image';
      if (mimeType === 'audio') return 'audio';
      if (mimeType === 'video') return 'video';
      return 'other';
    },
  },
};
</script>
