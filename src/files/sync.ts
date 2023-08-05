import jQuery from 'jquery';
import request from './client/js/api';

// load window
jQuery(($) => {
  const createSuccess = () => $('.body').prepend($('<p>').text('Erfolgreich synchronisiert').addClass(['alert', 'success']));

  const $button = $<HTMLButtonElement>('#start');
  $button.on('click', () => {
    $(':input, button').attr('disabled', 'true');
    $('.alert').remove();

    request('sync', 'Synchronisieren fehlgeschlagen')
      .then(() => {
        createSuccess();
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
