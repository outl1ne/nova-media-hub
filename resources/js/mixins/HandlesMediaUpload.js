import API from '../api';

export default {
  methods: {
    async uploadFilesToCollection(files, collectionName) {
      try {
        const formData = new FormData();
        for (const file of files) {
          formData.append('files[]', file);
        }

        const { data } = await API.saveMediaToCollection(collectionName, formData);

        if (data.hadExisting) {
          Nova.$toasted.info(this.__('novaMediaHub.existingMediaDetected'));
        }

        console.info(data.success_count);
        if (data.success_count > 0) {
          Nova.$toasted.success(this.__('novaMediaHub.successfullyUploadedNMedia', { count: data.success_count }));
        }

        return { success: true, media: data.media || [], hadExisting: data.hadExisting || false };
      } catch (e) {
        if (e && e.response && e.response.data) {
          const data = e.response.data;

          if (data.errors && data.errors.length) {
            data.errors.forEach(error => Nova.$toasted.error(error));
          }

          // Some succeeded, let the user know
          if (data.success_count > 0) {
            Nova.$toasted.success(this.__('novaMediaHub.successfullyUploadedNMedia', { count: data.success_count }));
          }

          return { success: data.success_count > 0, media: data.media || [] };
        } else {
          Nova.$toasted.error(e.message);
        }
      }

      return { success: false, media: [] };
    },
  },
};
