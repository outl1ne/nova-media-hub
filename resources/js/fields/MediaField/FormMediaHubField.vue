<template>
  <DefaultField :field="field" :errors="errors" :show-help-text="showHelpText">
    <template #field>
      <div class="o1-flex" v-if="hasValue">
        <div class="o1-flex o1-flex-wrap">
          <template v-if="field.multiple">
            <MediaItem v-for="mediaItem in value" :key="mediaItem.id" :mediaItem="mediaItem" :size="24" />
          </template>

          <MediaItem v-else :mediaItem="media" :size="24" />
        </div>
      </div>

      <LoadingButton type="button" @click.prevent.stop="showChooseModal = true" :class="[{ 'o1-mt-4': hasValue }]">
        Choose media
      </LoadingButton>

      <ChooseMediaModal
        :initialSelectedMediaItems="value"
        :show="showChooseModal"
        @close="showChooseModal = false"
        @confirm="mediaItemsSelected"
      />
    </template>
  </DefaultField>
</template>

<script>
import { FormField, HandlesValidationErrors } from 'laravel-nova';
import MediaItem from '../../components/MediaItem';
import ChooseMediaModal from '../../modals/ChooseMediaModal';

export default {
  components: { MediaItem, ChooseMediaModal },
  mixins: [FormField, HandlesValidationErrors],
  props: ['resourceName', 'resourceId', 'field'],

  data: () => ({
    showChooseModal: false,
    value: [],
  }),

  created() {
    this.setInitialValue();
    console.info(this.field.name);
  },

  methods: {
    setInitialValue() {
      const value = this.field.value;

      if (Array.isArray(value)) {
        this.value = value.map(id => this.field.media[id]).filter(Boolean);
      } else if (!!value) {
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
  },

  computed: {
    hasValue() {
      return this.field.multiple ? !!this.value.length : !!this.value;
    },
  },
};
</script>

<style lang="scss">
// ...
</style>
