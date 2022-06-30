<template>
  <PanelItem :index="index" :field="field" class="nova-media-field detail-field">
    <template #value>
      <div class="o1-flex">
        <div class="o1-flex o1-flex-wrap">
          <template v-if="field.multiple">
            <MediaItem
              v-for="mediaItem in value"
              :key="mediaItem.id"
              :mediaItem="mediaItem"
              :size="24"
              @contextmenu.stop.prevent="openContextMenu($event, mediaItem)"
            />
          </template>

          <MediaItem
            v-else-if="value"
            :mediaItem="value"
            :size="36"
            @contextmenu.stop.prevent="openContextMenu($event, value)"
          />

          <div v-else>&mdash;</div>
        </div>
      </div>

      <MediaViewModal :show="showMediaViewModal" :mediaItem="targetMediaItem" @close="showMediaViewModal = false" />

      <VueSimpleContextMenu
        elementId="mediaItemContextMenu"
        :options="contextMenuOptions"
        ref="vueSimpleContextMenu"
        @option-clicked="onMediaItemContextMenuClick"
      />

      <!-- Fake download button -->
      <a
        :href="targetMediaItem && targetMediaItem.url"
        download
        ref="downloadAnchor"
        target="_BLANK"
        rel="noopener noreferrer"
        class="o1-hidden"
      />
    </template>
  </PanelItem>
</template>

<script>
import MediaItem from '../../components/MediaItem';
import MediaViewModal from '../../modals/MediaViewModal';
import HandlesMediaHubFieldValue from '../../mixins/HandlesMediaHubFieldValue';
import VueSimpleContextMenu from 'vue-simple-context-menu/src/vue-simple-context-menu';

export default {
  mixins: [HandlesMediaHubFieldValue],
  components: { MediaItem, VueSimpleContextMenu, MediaViewModal },

  props: ['index', 'resource', 'resourceName', 'resourceId', 'field'],

  data: () => ({
    showMediaViewModal: false,

    contextMenuOptions: void 0,
    targetMediaItem: void 0,
  }),

  created() {
    this.contextMenuOptions = [
      { name: 'View / Edit', action: 'view', class: 'o1-text-slate-600' },
      { name: 'Download', action: 'download', class: 'o1-text-slate-600' },
    ];
  },

  methods: {
    openContextMenu(event, mediaItem) {
      this.$refs.vueSimpleContextMenu.showMenu(event, mediaItem);
    },

    onMediaItemContextMenuClick(event) {
      const action = event.option.action || void 0;
      this.targetMediaItem = event.item;

      if (action === 'view') {
        this.showMediaViewModal = true;
      }

      if (action === 'download') {
        this.$nextTick(() => {
          this.$refs.downloadAnchor.click();
        });
      }
    },
  },
};
</script>
