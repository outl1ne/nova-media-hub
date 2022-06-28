const PREFIX = '/nova-vendor/media-hub';

export default {
  async getCollections() {
    return Nova.request().get(`${PREFIX}/collections`);
  },

  async getCollectionMedia(collectionName) {
    return Nova.request().get(`${PREFIX}/collections/${collectionName}/media`);
  },

  async saveMediaToCollection(collectionName, formData) {
    return Nova.request().post(`${PREFIX}/media/save?collectionName=${collectionName}`, formData);
  },

  async deleteMedia(mediaId) {
    return Nova.request().delete(`${PREFIX}/media/${mediaId}`);
  },
};
