import jQuery from 'jquery';
import request from './client/js/api';

// load window
jQuery(($) => {
  const $button = $<HTMLButtonElement>('#start');
  $button.on('click', () => {
    $(':input, button').attr('disabled', 'true');

    request('sync', 'Synchronisieren fehlgeschlagen')
      .then(() => {
        const href = window.location.href;
        const url = href + (href.indexOf('?') >= 0 ? '&' : '?') + $.param({synced:true});
        window.location.href = url;
      });
  });
});
