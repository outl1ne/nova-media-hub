import API from '../api';

export default {
  data: () => ({
    collection: undefined,
    search: undefined,
    orderBy: undefined,

    collections: [],
    mediaItems: [],
    orderColumns: ['updated_at', 'created_at'],

    currentPage: 1,
    mediaResponse: {},

    loadingCollections: false,
    loadingMedia: false,
  }),

  methods: {
    async getMedia({
      collection = this.collection,
      search = this.search,
      orderBy = this.orderBy,
      orderDirection = this.orderDirection,
      page = this.currentPage,
    } = {}) {
      this.loadingMedia = true;

      await API.getMedia({ collection, page, search, orderBy, orderDirection })
        .then(({ data: res }) => {
          this.mediaResponse = res;
          this.mediaItems = res.data || [];
          if (this.currentPage !== page) this.currentPage = page;
        })
        .finally(() => {
          this.loadingMedia = false;
        });
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
