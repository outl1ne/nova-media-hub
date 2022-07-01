import API from '../api';

export default {
  data: () => ({
    collection: void 0,

    collections: [],
    mediaItems: [],

    currentPage: 1,
    mediaResponse: {},

    loadingCollections: false,
    loadingMedia: false,
  }),

  methods: {
    async getMedia(collection = void 0, pageNr = void 0) {
      this.loadingMedia = true;

      if (!collection) collection = this.collection;
      if (!pageNr) pageNr = this.currentPage;

      const { data } = await API.getMedia(collection, pageNr);
      this.mediaResponse = data;
      this.mediaItems = data.data || [];

      console.info(this.mediaResponse.current_page, this.mediaResponse.last_page);

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

    async goToMediaPage(pageNr) {
      this.currentPage = pageNr;
      await this.getMedia();
    },
  },
};
