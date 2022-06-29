<template>
  <DefaultField :field="field" :errors="errors" :show-help-text="showHelpText">
    <template #field>
      <div class="o1-flex" v-if="hasValue">
        <div class="o1-flex o1-flex-wrap">
          <template v-if="field.multiple">
            <MediaItem
              v-for="mediaItem in value"
              :key="mediaItem.id"
              :mediaItem="mediaItem"
              :size="24"
              class="o1-mb-4"
              @contextmenu.stop.prevent="openContextMenu($event, mediaItem)"
            />
          </template>

          <MediaItem v-else :mediaItem="value" :size="24" />
        </div>
      </div>

      <LoadingButton type="button" @click.prevent.stop="showChooseModal = true">Choose media</LoadingButton>

      <MediaViewModal :show="showMediaViewModal" :mediaItem="targetMediaItem" @close="showMediaViewModal = false" />

      <ChooseMediaModal
        :field="field"
        :initialSelectedMediaItems="value"
        :show="showChooseModal"
        @close="showChooseModal = false"
        @confirm="mediaItemsSelected"
      />

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
  </DefaultField>
</template>

<script>
import MediaItem from '../../components/MediaItem';
import MediaViewModal from '../../modals/MediaViewModal';
import ChooseMediaModal from '../../modals/ChooseMediaModal';
import { FormField, HandlesValidationErrors } from 'laravel-nova';
import VueSimpleContextMenu from 'vue-simple-context-menu/src/vue-simple-context-menu';

export default {
  components: { MediaItem, ChooseMediaModal, MediaViewModal, VueSimpleContextMenu },
  mixins: [FormField, HandlesValidationErrors],
  props: ['resourceName', 'resourceId', 'field'],

  data: () => ({
    showChooseModal: false,
    showMediaViewModal: false,

    value: [],
    contextMenuOptions: [],
    targetMediaItem: void 0,
  }),

  created() {
    this.setInitialValue();

    this.contextMenuOptions = [
      { name: 'View / Edit', action: 'view', class: 'o1-text-slate-600' },
      { name: 'Download', action: 'download', class: 'o1-text-slate-600' },
    ];
  },

  methods: {
    setInitialValue() {
      let value = this.field.value;
      const multiple = this.field.multiple;

      if (multiple && Array.isArray(value)) {
        this.value = value.map(id => this.field.media[id]).filter(Boolean);
      } else if (!!value) {
        if (Array.isArray(value)) value = value[0];
        this.value = this.field.media[value];
      }
    },

    mediaItemsSelected(mediaItems) {
      this.value = mediaItems;
      this.showChooseModal = false;
    },

    fill(formData) {
      if (!this.value || !this.value.length) {
        formData.append(this.field.attribute, '');
      } else if (this.value.length) {
        this.value.map(mediaItem => {
          formData.append(`${this.field.attribute}[]`, mediaItem.id);
        });
      } else {
        formData.append(this.field.attribute, this.value.id);
      }
    },

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

  computed: {
    hasValue() {
      return this.field.multiple ? !!this.value.length : !!this.value;
    },
  },
};
</script>
