import request, { apiData } from './api';
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
    request('action/delDate', 'Fehler beim Löschen', {
      date: str,
      col: column
    }).then((data: apiData) => {
      console.debug(`Removed ${data.entries}`);
      alert(`
        <p>${data.entries} gelöscht</p>
      `);
    });
  } else {
    console.debug(date, "is not a string");
  }
}

export function resetFam() {
  request('action/resetFam', 'Fehler beim Reset').then(() => {
    console.debug(`Reset`);
    alert(`
      <p>Erfolgreich zurückgesetzt</p>
    `);
  });
}