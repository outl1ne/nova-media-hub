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
    async getMedia(collection = void 0) {
      this.loadingMedia = true;
      const { data } = await API.getMedia(collection);
      this.mediaItems = data.data || [];
      this.loadingMedia = false;
    },

    async getCollections() {
      this.loadingCollections = true;
      const { data } = await API.getCollections();
      this.collections = data || [];

      if (!this.collection) {
        this.collection = this.collections.length ? this.collections[0] : void 0;
      }
      this.loadingCollections = false;
    },
  },
};
