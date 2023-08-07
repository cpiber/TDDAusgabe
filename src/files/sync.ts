import jQuery from 'jquery';
import request from './client/js/api';

// load window
jQuery(($) => {
  const createSuccess = (more?: string) => {
    $('.alert').remove();
    $('.body').prepend($('<p>').text('Erfolgreich synchronisiert' + (more ? ` (${more})` : '')).addClass(['alert', 'success']));
  };

  const upload_file = (name: string) => request('sync_upload', 'Upload fehlgeschlagen', { file: name });
  const download_file = (name: string) => request('sync_download', 'Download fehlgeschlagen', { file: name });

  const $button = $<HTMLButtonElement>('#start');
  $button.on('click', () => {
    $(':input, button').attr('disabled', 'true');
    $('.alert').remove();

    request('sync', 'Synchronisieren fehlgeschlagen')
      .then((data) => {
        let prom = $.Deferred().resolve().promise();
        const toupload = data['static_upload'] as string[];
        const todownload = data['static_download'] as string[];
        let hasFailed = false;
        createSuccess(`Upload: 0/${toupload.length}, Download: 0/${todownload.length}`);
        for (let i = 0; i < toupload.length; i++) {
          const j = i;
          prom = prom.then(() => upload_file(toupload[i]))
            .then(() => createSuccess(`Upload: ${j+1}/${toupload.length}, Download: 0/${todownload.length}`));
        }
        for (let i = 0; i < todownload.length; i++) {
          const j = i;
          prom = prom.then(() => download_file(todownload[i]))
            .catch(() => hasFailed = true) // ignore failed downloads. there will be an alert, continue with the rest, but don't reload
            .then(() => createSuccess(`Upload: ${toupload.length}/${toupload.length}, Download: ${j+1}/${todownload.length}`));
        }
        return prom.then(() => (hasFailed ? $.Deferred().reject() : $.Deferred().resolve()).promise());
      }).then(() => {
        createSuccess('Werte werden neu geladen...');
        const href = window.location.href.replace(/[&?]synced=true(&|$)/,'$1');
        const url = href + (href.indexOf('?') >= 0 ? '&' : '?') + $.param({synced:true});
        window.location.href = url;
      })
      .catch(() => {
        $(':input, button').removeAttr('disabled');
      });
  });

  $('#close').on('click', () => window.close());

  try {
    if (!window.performance.getEntriesByType('navigation').map((nav) => (nav as PerformanceNavigationTiming).type).includes('reload') &&
        location.search.match(/[&?]synced=true(?:&|$)/)) {
      createSuccess();
    }
  } catch (e) {
    console.error(e);
  }
});
