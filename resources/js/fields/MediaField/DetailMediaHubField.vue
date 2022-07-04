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
              class="o1-mr-4 o1-mb-4"
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

      <MediaItemContextMenu
        id="detail-media-hub-field-ctx-menu"
        :showEvent="ctxShowEvent"
        :options="ctxOptions"
        @close="ctxShowEvent = void 0"
        :mediaItem="ctxMediaItem"
      />
    </template>
  </PanelItem>
</template>

<script>
import MediaItem from '../../components/MediaItem';
import MediaItemContextMenu from '../../components/MediaItemContextMenu';
import HandlesMediaHubFieldValue from '../../mixins/HandlesMediaHubFieldValue';

export default {
  mixins: [HandlesMediaHubFieldValue],
  components: { MediaItem, MediaItemContextMenu },

  props: ['index', 'resource', 'resourceName', 'resourceId', 'field'],

  data: () => ({
    showMediaViewModal: false,

    ctxOptions: void 0,
    ctxMediaItem: void 0,
    ctxShowEvent: void 0,
  }),

  created() {
    this.ctxOptions = [
      { name: 'View / Edit', action: 'view' },
      { name: 'Download', action: 'download' },
    ];
  },

  methods: {
    openContextMenu(event, mediaItem) {
      this.ctxShowEvent = event;
      this.ctxMediaItem = mediaItem;
    },
  },
};
</script>
