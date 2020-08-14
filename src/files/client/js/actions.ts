import $ from 'jquery';
import { alert, formatDate } from './helpers';

export function delFamDate(date: number | Date = -1, column = 'lAnwesenheit') {
  if (date === -1) {
    date = new Date();
    //8 weeks
    date.setTime(date.getTime() - 8 * 7 * 24 * 1000 * 3600);
  }
  if (typeof (date) === "number") {
    let n = date;
    date = new Date();
    //n days
    date.setTime(date.getTime() - n * 24 * 1000 * 3600);
  }
  let str: string;
  if (typeof (date) === "object") {
    str = formatDate(date);
  }
  if (str) {
    $.post('?api=action/delDate', {
      date: str,
      col: column
    }).then((data: any) => {
      if (data && data.status === "success") {
        console.debug(`Removed ${data.entries}`);
        alert(`
          <p>${data.entries} gelöscht</p>
          `, "");
      } else {
        console.error(`Failed: ${data.message}`);
        alert(`
            <p>Fehler beim löschen:<br />${data.message}</p>
          `, "Fehler");
      }
    }).fail((xhr: JQueryXHR, status: string, error: string) => {
      const msg = xhr.responseJSON ? xhr.responseJSON.message : xhr.responseText;
      console.error(xhr.status, error, msg);
      alert(`
            <p>Fehler beim löschen:<br />${xhr.status} ${error}</p>
            <p>${msg}</p>
          `, "Fehler");
    });
  } else {
    console.debug(date, "is not a string");
  }
}

export function resetFam() {
  $.post('?api=action/resetFam').then((data: any) => {
    if (data && data.status === "success") {
      console.debug(`Reset`);
      alert(`
        <p>Erfolgreich zurückgesetzt</p>
        `, "");
    } else {
      console.error(`Failed: ${data.message}`);
      alert(`
            <p>Fehler beim reset:<br />${data.message}</p>
          `, "Fehler");
    }
  }).fail((xhr: JQueryXHR, status: string, error: string) => {
    const msg = xhr.responseJSON ? xhr.responseJSON.message : xhr.responseText;
    console.error(xhr.status, error, msg);
    alert(`
            <p>Fehler beim reset:<br />${xhr.status} ${error}</p>
            <p>${msg}</p>
          `, "Fehler");
  });
}