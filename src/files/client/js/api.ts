import $ from 'jquery';
import { alert } from './helpers';

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
  return $.post(`?api=${encodeURIComponent(endpoint)}`, data)
    .then((data: apiData, status: JQuery.Ajax.SuccessTextStatus, jqXHR: JQueryXHR) => {
      if (data && data.status === "success") {
        dfd.resolve(data, jqXHR);
      } else {
        console.error(`${errorText} :: Failed request (API denied): ${data.message}`, data);
        alert(`
          <p>${errorText}:<br />${data.message}</p>
        `, "Fehler");
        dfd.reject(jqXHR, data.message, data);
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