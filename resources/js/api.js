const PREFIX = '/nova-vendor/media-hub';

export default {
  async getMedia(params) {
    return Nova.request().get(`${PREFIX}/media`, { params });
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

  async moveMediaItemToCollection(mediaId, collection) {
    return Nova.request().post(`${PREFIX}/media/${mediaId}/move`, { collection });
  },

  async moveMediaToCollection(mediaIds, collection) {
    return Nova.request().post(`${PREFIX}/media/move`, { collection, mediaIds });
  },

  async updateMediaData(mediaId, formData) {
    return Nova.request().post(`${PREFIX}/media/${mediaId}/data`, formData);
  },

  async replaceMediaItem(mediaId, formData) {
    return Nova.request().post(`${PREFIX}/media/${mediaId}/replace`, formData);
  },
};
