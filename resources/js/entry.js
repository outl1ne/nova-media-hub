import DetailMediaField from './fields/MediaField/DetailMediaField';
import FormMediaField from './fields/MediaField/FormMediaField';

let mediaLibraryDarkModeObserver = null;

Nova.booting((Vue, router, store) => {
  mediaLibraryDarkModeObserver = new MutationObserver(() => {
    const cls = document.documentElement.classList;
    const isDarkMode = cls.contains('dark');

    if (isDarkMode && !cls.contains('nml-dark')) {
      cls.add('nml-dark');
    } else if (!isDarkMode && cls.contains('nml-dark')) {
      cls.remove('nml-dark');
    }
  }).observe(document.documentElement, {
    attributes: true,
    attributeOldValue: true,
    attributeFilter: ['class'],
  });

  Nova.inertia('NovaMediaLibrary', require('./views/NovaMediaLibrary').default);

  Vue.component('detail-media-field', DetailMediaField);
  Vue.component('form-media-field', FormMediaField);
});
