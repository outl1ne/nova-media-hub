const PREFIX = '/nova-vendor/media-hub';

export default {
  async getMedia(collection, page) {
    return Nova.request().get(`${PREFIX}/media`, {
      params: { collection, page },
    });
  },

  async getCollections() {
    return Nova.request().get(`${PREFIX}/collections`);
  },

  async saveMediaToCollection(collectionName, formData) {
    return Nova.request().post(`${PREFIX}/media/save?collectionName=${collectionName}`, formData);
  },

  async deleteMedia(mediaId) {
    return Nova.request().delete(`${PREFIX}/media/${mediaId}`);
  },

  async moveMediaToCollection(mediaId, collection) {
    return Nova.request().post(`${PREFIX}/media/${mediaId}/move`, { collection });
  },

  async updateMediaData(mediaId, formData) {
    return Nova.request().post(`${PREFIX}/media/${mediaId}/data`, formData);
  },
};
