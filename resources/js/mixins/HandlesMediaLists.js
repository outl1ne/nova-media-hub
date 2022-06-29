import API from '../api';

export default {
  data: () => ({
    collection: void 0,

    collections: [],
    mediaItems: [],

    loadingCollections: false,
    loadingMedia: false,
  }),

  methods: {
    async getCollections() {
      this.loadingCollections = true;
      const { data } = await API.getCollections();
      this.collections = data || [];

      if (!this.collection) {
        this.collection = this.collections.length ? this.collections[0] : void 0;
      }
      this.loadingCollections = false;
    },

    async getCollectionMedia(collection = void 0) {
      this.loadingMedia = true;
      if (!collection) collection = this.collection;
      const { data } = await API.getCollectionMedia(collection);
      this.mediaItems = data.data || [];
      this.loadingMedia = false;
    },
  },
};
