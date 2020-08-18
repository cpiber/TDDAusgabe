import $ from 'jquery';
import { alert, open_modal, timeout, close_modal } from './helpers';

export interface apiData {
  status: string,
  message?: string,
  loggedin?: boolean,
  data?: { [key: string]: any },
  [key: string]: any,
}

export type apiRequest = JQuery.PromiseBase<apiData, JQueryXHR, never, JQueryXHR, string, never, never, apiData, never, never, never, never>;

export default function request(endpoint: string, errorText: string = '', data: { [key: string]: any } = undefined): apiRequest {
  const dfd = $.Deferred();
  const url = `?api=${encodeURIComponent(endpoint)}`;
  return _request(dfd, url, errorText, data);
}
function _request(dfd: JQuery.Deferred<any, any, any>, url: string, errorText: string, data: { [key: string]: any }): apiRequest {
  if (loginpromise) return login(dfd, url, errorText, data);
  return $.post(url, data)
    .then((data: apiData, status: JQuery.Ajax.SuccessTextStatus, jqXHR: JQueryXHR) => {
      if (data) {
        if (data.status === "success") {
          dfd.resolve(data, jqXHR);
        } else {
          if (data.loggedin === false) {
            console.debug(`${errorText} :: Failed request (API denied): ${data.message}`, data);
            console.debug(`Opening login dialog to retry...`);
            login(dfd, url, errorText, data);
          } else {
            console.error(`${errorText} :: Failed request (API denied): ${data.message}`, data);
            alert(`
              <p>${errorText}:<br />${data.message}</p>
            `, "Fehler");
            dfd.reject(jqXHR, data.message, data);
          }
        }
      } else {
        console.error(`${errorText} :: Failed request (NO DATA)`, data);
        alert(`
          <p>${errorText}</p>
        `, "Fehler");
        dfd.reject(jqXHR, "NO DATA", data);
      }
      return dfd.promise() as apiRequest;
    }, (jqXHR: JQueryXHR, status: JQuery.Ajax.ErrorTextStatus, error: string) => {
      const msg = jqXHR.responseJSON ? jqXHR.responseJSON.message : jqXHR.responseText;
      console.error(`${errorText} :: Failed request (Network)`, jqXHR.status, error, msg);
      alert(`
        <p>${errorText}:<br />${jqXHR.status} ${error}</p>
        <p>${msg}</p>
      `, "Fehler");
      dfd.reject(jqXHR, error);
      return dfd.promise() as apiRequest;
    });
}

let modal: JQuery<HTMLElement>;
let loginpromise: JQuery.Deferred<never, never, never>;
function login(dfd: JQuery.Deferred<any, any, any>, url: string, errorText: string, data: { [key: string]: any }): apiRequest {
  if (!loginpromise) {
    loginpromise = $.Deferred();
    open_modal(modal);
  }
  return loginpromise.then(() => _request(dfd, url, errorText, data));
}
$(() => {
  modal = $('#login-modal').data('close', false);
  const $un = modal.find('[name="user"]');
  const $pw = modal.find('[name="pass"]');
  modal.find('form').on('submit', () => {
    if (!loginpromise) return false;
    $.post('?api=login', {
      username: $un.val(),
      password: $pw.val(),
    }).then((data: apiData, status: JQuery.Ajax.SuccessTextStatus, jqXHR: JQueryXHR) => {
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
    }, (jqXHR: JQueryXHR, status: JQuery.Ajax.ErrorTextStatus, error: string) => {
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