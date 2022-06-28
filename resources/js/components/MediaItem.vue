<template>
  <button
    class="o1-h-48 o1-w-48 o1-mx-2 o1-bg-slate-50 o1-shadow-sm hover:o1-shadow o1-border o1-border-slate-100 hover:o1-bg-slate-100 hover:o1-border-slate-200"
  >
    <img
      v-if="type === 'image'"
      :src="mediaItem.url"
      :alt="mediaItem.file_name"
      class="o1-object-contain o1-max-w-full o1-w-full o1-max-h-full o1-h-full"
    />

    <div v-else class="o1-h-full o1-w-full o1-flex o1-flex-col o1-items-center o1-justify-center">
      <AudioIcon v-if="type === 'audio'" />
      <VideoIcon v-if="type === 'video'" />
      <OtherIcon v-if="type === 'other'" />

      <span class="o1-mt-4">{{ mediaItem.file_name }}</span>
    </div>
  </button>
</template>

<script>
import AudioIcon from '../icons/AudioIcon';
import VideoIcon from '../icons/VideoIcon';
import OtherIcon from '../icons/OtherIcon';

export default {
  components: { AudioIcon, VideoIcon, OtherIcon },

  props: ['mediaItem'],

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
