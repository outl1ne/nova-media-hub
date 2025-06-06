<template>
  <Modal
    :show="show"
    @close-via-escape="$emit('close')"
    role="alertdialog"
    maxWidth="2xl"
    id="o1-nmh-media-upload-modal"
  >
    <LoadingCard :loading="loading" class="mx-auto overflow-hidden bg-white rounded-lg shadow-lg dark:bg-gray-800">
      <slot>
        <ModalHeader class="flex items-center">{{ __('novaMediaHub.uploadMediaTitle') }}</ModalHeader>

        <ModalContent class="px-8 o1-flex o1-flex-col">
          <!-- Select existing collection -->
          <span class="o1-mb-2">{{ __('novaMediaHub.uploadModalSelectCollectionTitle') }}</span>
          <select 
            v-model="selectedCollection" 
            class="w-full form-control form-select form-select-bordered"
          >
            <option value="media-hub-new-collection" v-if="canCreateCollections">
              {{ __('novaMediaHub.uploadModalCreateNewOption') }}
            </option>
            <option v-for="c in collections" :key="c" :value="c">{{ c }}</option>
          </select>

          <template v-if="newCollection">
            <span class="mt-6">{{ __('novaMediaHub.enterNewCollectionName') }}</span>
            <input
              type="text"
              name="collection_name"
              placeholder="Collection name"
              v-model="collectionName"
              class="form-control form-input form-input-bordered"
            />
          </template>

          <NMHDropZone class="mt-6" @fileChanged="onFilesChange" :files="selectedFiles" multiple />
        </ModalContent>
      </slot>

      <ModalFooter>
        <div class="ml-auto">
          <Button @click.prevent="$emit('close')" class="o1-mr-4">
            {{ __('novaMediaHub.closeButton') }}
          </Button>

          <Button @click.prevent="uploadFiles">
            {{ __('novaMediaHub.uploadFilesButton') }}
          </Button>
        </div>
      </ModalFooter>
    </LoadingCard>
  </Modal>
</template>

<script>
import API from '../api';
import HandlesMediaUpload from '../mixins/HandlesMediaUpload';
import { Button } from 'laravel-nova-ui';

export default {
  components: { Button },
  mixins: [HandlesMediaUpload],
  emits: ['close'],
  props: ['show', 'activeCollection'],

  data: () => ({
    loading: false,
    collectionName: '',
    selectedFiles: [],
    selectedCollection: void 0,
    collections: [],
  }),

  mounted() {
    Nova.$emit('close-dropdowns');
  },

  watch: {
    async show(newValue) {
      if (newValue) {
        await this.getCollections();
      }
    },

    newCollection(newValue) {
      // Reset del nome della collezione quando si passa da "crea nuova" a una collezione esistente
      if (!newValue) {
        this.collectionName = '';
      }
    },
  },

  methods: {
    async uploadFiles() {
      this.loading = true;

      const { success, media, hadExisting } = await this.uploadFilesToCollection(
        this.selectedFiles,
        this.finalCollectionName
      );

      let goToCollection = this.finalCollectionName;

      if (hadExisting) {
        // Find possible new collection name
        const diffCollNameMedia = media.find(mi => mi.collection_name !== this.finalCollectionName);
        if (diffCollNameMedia) goToCollection = diffCollNameMedia.collection_name;
      }

      if (success) {
        this.$emit('close', true, goToCollection);
      }

      this.loading = false;
    },

    onFilesChange(selectedFiles) {
      this.selectedFiles = Array.from(selectedFiles);
    },

    async getCollections() {
      const { data } = await API.getCollections();
      this.collections = data || [];

      // Filtriamo le collezioni per rimuovere eventuali oggetti malformati
      this.collections = this.collections.filter(c => typeof c === 'string' && c !== '[object event]');

      // Se non abbiamo una selectedCollection selezionata, impostiamo un default ragionevole
      if (!this.selectedCollection) {
        // Se possiamo creare collezioni, inizializziamo con "crea nuova"
        if (this.canCreateCollections) {
          this.selectedCollection = 'media-hub-new-collection';
        }
        // Altrimenti usa l'activeCollection se esiste
        else if (this.activeCollection && this.collections.includes(this.activeCollection)) {
          this.selectedCollection = this.activeCollection;
        }
        // Come ultimo fallback, usa la prima collezione disponibile
        else if (this.collections.length) {
          this.selectedCollection = this.collections[0];
        }
      }
    },
  },

  computed: {
    newCollection() {
      return this.selectedCollection === 'media-hub-new-collection';
    },

    finalCollectionName() {
      return this.newCollection ? this.collectionName : this.selectedCollection;
    },

    canCreateCollections() {
      return Nova.appConfig.novaMediaHub.canCreateCollections;
    },
  },
};
</script>

<style lang="scss">
#o1-nmh-media-upload-modal {
  z-index: 120;

  + .fixed {
    z-index: 119;
  }
}
</style>
