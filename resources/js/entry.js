import DetailMediaHubField from './fields/MediaField/DetailMediaHubField';
import FormMediaHubField from './fields/MediaField/FormMediaHubField';

let mediaHubDarkModeObserver = null;

Nova.booting((Vue, router, store) => {
  mediaHubDarkModeObserver = new MutationObserver(() => {
    const cls = document.documentElement.classList;
    const isDarkMode = cls.contains('dark');

    if (isDarkMode && !cls.contains('o1-dark')) {
      cls.add('o1-dark');
    } else if (!isDarkMode && cls.contains('o1-dark')) {
      cls.remove('o1-dark');
    }
  }).observe(document.documentElement, {
    attributes: true,
    attributeOldValue: true,
    attributeFilter: ['class'],
  });

  Vue.component('detail-media-hub-field', DetailMediaHubField);
  Vue.component('form-media-hub-field', FormMediaHubField);

  Nova.inertia('NovaMediaHub', require('./views/NovaMediaHub').default);
});
