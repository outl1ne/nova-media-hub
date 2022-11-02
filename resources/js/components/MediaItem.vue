<template>
  <button
    class="o1-relative o1-bg-slate-100 o1-rounded-sm dark:o1-bg-slate-900 o1-overflow-hidden o1-whitespace-no-wrap"
    :class="[
      { 'o1-ring-2 o1-ring-teal-200 hover:o1-ring-rose-300 dark:o1-ring-teal-800': selected },
      { 'o1-ring-1 o1-ring-slate-200 hover:o1-ring-2 hover:o1-ring-teal-200 dark:o1-ring-slate-700': !selected },
      sizeClasses,
    ]"
    type="button"
  >
    <img
      v-if="type === 'image'"
      :src="mediaItem.thumbnail_url || mediaItem.url"
      :alt="mediaItem.file_name"
      class="o1-object-contain o1-max-w-full o1-w-full o1-max-h-full o1-h-full"
    />

    <div v-else class="o1-h-full o1-w-full o1-flex o1-flex-col o1-items-center o1-justify-center">
      <AudioIcon v-if="type === 'audio'" />
      <VideoIcon v-if="type === 'video'" />
      <OtherIcon v-if="type === 'other'" />

      <span
        class="o1-mt-2 o1-whitespace-nowrap o1-text-ellipsis o1-overflow-hidden o1-text-xs o1-px-2 o1-text-center o1-w-full"
      >
        {{ mediaItem.file_name }}
      </span>
    </div>

    <div
      v-if="selected"
      class="o1-absolute o1-top-2 o1-left-2 o1-bg-teal-100 o1-rounded o1-w-6 o1-h-6 o1-flex o1-items-center o1-justify-center o1-shadow o1-p-1"
    >
      <CheckMarkIcon />
    </div>

    <div
      class="o1-absolute o1-top-0 o1-right-0 o1-bg-teal-100 o1-text-xs o1-py-0.5 o1-px-1 o1-rounded-bl o1-text-slate-700"
    >
      #{{ mediaItem.id }}
    </div>

    <div
      v-if="showFileName"
      class="o1-absolute o1-bottom-2 o1-left-2 o1-right-2 o1-bg-teal-100 o1-rounded o1-px-2 o1-py-1 o1-shadow o1-text-xs o1-text-slate-700 o1-whitespace-nowrap o1-overflow-hidden o1-text-ellipsis"
      @click.stop.prevent="$emit('nameClick')"
    >
      {{ mediaItem.file_name }}
    </div>

    <div
      v-if="showCollectionName"
      class="o1-absolute o1-bottom-2 o1-left-2 o1-bg-teal-100 o1-rounded o1-px-2 o1-py-1 o1-shadow o1-text-xs o1-text-slate-700"
    >
      {{ mediaItem.collection_name }}
    </div>
  </button>
</template>

<script>
import AudioIcon from '../icons/AudioIcon';
import VideoIcon from '../icons/VideoIcon';
import OtherIcon from '../icons/OtherIcon';
import CheckMarkIcon from '../icons/CheckMarkIcon';

export default {
  components: { AudioIcon, VideoIcon, OtherIcon, CheckMarkIcon },

  props: ['mediaItem', 'selected', 'showCollectionName', 'size', 'showCollectionName', 'showFileName'],

  computed: {
    type() {
      if (!this.mediaItem) return;

      const mimeType = this.mediaItem.mime_type.split('/')[0];
      if (mimeType === 'image') return 'image';
      if (mimeType === 'audio') return 'audio';
      if (mimeType === 'video') return 'video';
      return 'other';
    },

    sizeClasses() {
      const size = this.size || 48;
      return [`o1-h-${size}`, `o1-w-${size}`];
    },
  },
};
</script>
