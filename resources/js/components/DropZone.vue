<template>
  <div v-bind="$attrs">
    <input
      class="visually-hidden"
      :dusk="$attrs['input-dusk']"
      @change.prevent="handleChange"
      type="file"
      ref="fileInput"
      :multiple="multiple"
      :accept="acceptedTypes"
      :disabled="disabled"
    />

    <div class="space-y-4">
      <label
        @click="handleClick"
        class="block cursor-pointer p-4 bg-gray-50 dark:bg-gray-900 dark:hover:bg-gray-900 border-4 border-dashed hover:border-gray-300 dark:border-gray-700 dark:hover:border-gray-600 rounded-lg"
        :class="{ 'border-gray-300 dark:border-gray-600': startedDrag }"
        @dragenter.prevent="handleOnDragEnter"
        @dragleave.prevent="handleOnDragLeave"
        @dragover.prevent
        @drop.prevent="handleOnDrop"
      >
        <div class="flex items-center pointer-events-none" :class="[vertical ? 'flex-col space-y-2' : 'space-x-4']">
          <p class="text-center pointer-events-none">
            <DefaultButton component="div">
              {{ multiple ? __('novaMediaHub.dropZone.uploadFiles') : __('novaMediaHub.dropZone.uploadFile') }}
            </DefaultButton>
          </p>

          <p
            v-if="files && files.length"
            class="pointer-events-none text-center text-sm text-gray-500 dark:text-gray-400 font-semibold"
          >
            <template v-if="file.length > 1"
              >{{ files.length }} {{ __('novaMediaHub.dropZone.filesToUpload') }}</template
            >
            <template>{{ files.length }} {{ __('novaMediaHub.dropZone.fileToUpload') }}</template>
          </p>
          <p v-else class="pointer-events-none text-center text-sm text-gray-500 dark:text-gray-400 font-semibold">
            {{ multiple ? __('novaMediaHub.dropZone.dropFiles') : __('novaMediaHub.dropZone.dropFile') }}
          </p>
        </div>
      </label>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useDragAndDrop } from '../composables/useDragAndDrop';

const emit = defineEmits(['fileChanged', 'fileRemoved']);

const props = defineProps({
  files: { type: Array, default: [] },
  multiple: { type: Boolean, default: false },
  rounded: { type: Boolean, default: false },
  acceptedTypes: { type: String, default: null },
  disabled: { type: Boolean, default: false },
  vertical: { type: Boolean, default: false },
});

const { startedDrag, handleOnDragEnter, handleOnDragLeave } = useDragAndDrop(emit);

const demFiles = ref([]);
const fileInput = ref();

const handleClick = () => fileInput.value.click();

const handleOnDrop = e => {
  demFiles.value = props.multiple ? e.dataTransfer.files : [e.dataTransfer.files[0]];

  emit('fileChanged', demFiles.value);
};

const handleChange = () => {
  demFiles.value = props.multiple ? fileInput.value.files : [fileInput.value.files[0]];
  emit('fileChanged', demFiles.value);
  fileInput.value.files = null;
};

const handleRemove = index => {
  emit('fileRemoved', index);
  fileInput.value.files = null;
  fileInput.value.value = null;
};
</script>

<script>
export default {
  inheritAttrs: false,
};
</script>
