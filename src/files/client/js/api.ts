import $ from 'jquery';
import { alert, close_modal, open_modal, timeout } from './helpers';

export interface apiData {
  status: string,
  message?: string,
  loggedin?: boolean,
  data?: { [key: string]: any },
  [key: string]: any,
}

export type apiRequest = JQuery.PromiseBase<apiData, JQueryXHR, never, JQueryXHR, string, never, never, apiData, never, never, never, never>;

export default function request(endpoint: string, errorText: string = '', data: { [key: string]: any } = undefined): apiRequest {
  const url = `?api=${encodeURIComponent(endpoint)}`;
  return _request(url, errorText, data);
}
function _request(url: string, errorText: string, data_in: { [key: string]: any }): apiRequest {
  if (loginpromise) return login(url, errorText, data_in);
  return $.post(url, data_in)
    .then((data: apiData, _: JQuery.Ajax.SuccessTextStatus, jqXHR: JQueryXHR) => {
      if (!data) {
        console.error(`${errorText} :: Failed request (NO DATA)`, data);
        alert(`
          <p>${errorText}</p>
        `, "Fehler");
        return $.Deferred().reject(jqXHR, "NO DATA", data).promise() as apiRequest;
      }
      if (data.status === "success") {
        return $.Deferred().resolve(data, jqXHR).promise() as apiRequest;
      }
      if (data.loggedin !== false) {
        console.error(`${errorText} :: Failed request (API denied): ${data.message}`, data);
        alert(`
          <p>${errorText}:<br />${data.status}: ${data.message}</p>
        `, "Fehler");
        return $.Deferred().reject(jqXHR, data.message, data).promise() as apiRequest;
      }
      console.debug(`${errorText} :: Failed request (API denied): ${data.message}`, data);
      console.debug(`Opening login dialog to retry...`);
      return login(url, errorText, data_in);
    }, (jqXHR: JQueryXHR, _: JQuery.Ajax.ErrorTextStatus, error: string) => {
      const msg = jqXHR.responseJSON ? jqXHR.responseJSON.message : jqXHR.responseText;
      console.error(`${errorText} :: Failed request (Network)`, jqXHR.status, error, msg);
      alert(`
        <p>${errorText}:<br />${jqXHR.status} ${jqXHR.status == 0 ? 'No internet / Blocked' : error}</p>
        <p>${msg}</p>
      `, "Fehler");
      return $.Deferred().reject(jqXHR, error).promise() as apiRequest;
    });
}

export function upload(endpoint: string, errorText: string, key: string, image: Blob): apiRequest {
  const url = `?api=${encodeURIComponent(endpoint)}&key=${encodeURIComponent(key)}`;
  console.log('Trying to send image to url', url);
  const data = new FormData();
  data.set('image', image, 'upload.png');
  return _upload(url, errorText, data);
}
function _upload(url: string, errorText: string, data: FormData): apiRequest {
  return $.post({
    url: url,
    data,
    processData: false,
    contentType: false,
  })
    .then((data: apiData, _: JQuery.Ajax.SuccessTextStatus, jqXHR: JQueryXHR) => {
      if (!data) {
        console.error(`${errorText} :: Failed request (NO DATA)`, data);
        alert(`
          <p>${errorText}</p>
        `, "Fehler");
        return $.Deferred().reject(jqXHR, "NO DATA", data).promise() as apiRequest;
      }
      if (data.status === "success") {
        return $.Deferred().resolve(data, jqXHR).promise() as apiRequest;
      }
      if (data.loggedin !== false) {
        console.error(`${errorText} :: Failed request (API denied): ${data.message}`, data);
        alert(`
          <p>${errorText}:<br />${data.status}: ${data.message}</p>
        `, "Fehler");
        return $.Deferred().reject(jqXHR, data.message, data).promise() as apiRequest;
      }
      console.debug(`${errorText} :: Failed request (API denied): ${data.message}`, data);
      return $.Deferred().reject(jqXHR, errorText, data).promise() as apiRequest;
    }, (jqXHR: JQueryXHR, _: JQuery.Ajax.ErrorTextStatus, error: string) => {
      const msg = jqXHR.responseJSON ? jqXHR.responseJSON.message : jqXHR.responseText;
      console.error(`${errorText} :: Failed request (Network)`, jqXHR.status, error, msg);
      alert(`
        <p>${errorText}:<br />${jqXHR.status} ${jqXHR.status == 0 ? 'No internet / Blocked' : error}</p>
        <p>${msg}</p>
      `, "Fehler");
      return $.Deferred().reject(jqXHR, error).promise() as apiRequest;
    });
}

export function getImageUrl(image: string, refresh = false): string {
  if (!image) return '';
  const p = { api: 'familie/profile', image };
  if (refresh) p['t'] = new Date().toString();
  return '?' + $.param(p);
}

let modal: JQuery<HTMLElement>;
let loginpromise: JQuery.Deferred<never, never, never>;
function login(url: string, errorText: string, data: { [key: string]: any }): apiRequest {
  if (!loginpromise) {
    loginpromise = $.Deferred();
    open_modal(modal);
  }
  return loginpromise.then(() => _request(url, errorText, data));
}
$(() => {
  modal = $('#login-modal').data('close', false);
  const $un = modal.find('[name="user"]');
  const $pw = modal.find('[name="pass"]');
  const $inputs = modal.find(':input');

  modal.find('form').on('submit', () => {
    if (!loginpromise) return false;
    $inputs.prop('disabled', true);
    $.post('?api=login', {
      username: $un.val(),
      password: $pw.val(),
    }).then((data: apiData, _: JQuery.Ajax.SuccessTextStatus, __: JQueryXHR) => {
      if (data && data.status === "success") {
        console.debug('Logged in');
        loginpromise.resolve();
        loginpromise = undefined;
        close_modal(modal);
      } else {
        console.error(`Login Failed (API denied): ${data.message}`, data);
        alert(`
          <p>Login fehlgeschlagen:<br />${data.message}</p>
        `, "Fehler");
      }
      $un.val('');
      $pw.val('');
      $inputs.prop('disabled', false);
    }, (jqXHR: JQueryXHR, _: JQuery.Ajax.ErrorTextStatus, error: string) => {
      const msg = jqXHR.responseJSON ? jqXHR.responseJSON.message : jqXHR.responseText;
      console.error(`Login Failed (Network)`, jqXHR.status, error, msg);
      console.log('Reload in 5 sec');
      alert(`
        <p>Login fehlgeschlagen:<br />${jqXHR.status} ${error}</p>
        <p>${msg}</p>
        <p>Lade neu in 5 sekunden...</p>
      `, "Fehler");
      timeout(5000).then(() => window.location.reload());
    });
    return false;
  });
});
