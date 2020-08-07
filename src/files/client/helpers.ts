import $ from 'jquery';
import settings from './settings';

// clone object
export function clone(obj: any) {
  if (null == obj || "object" != typeof obj) return obj;
  var copy = obj.constructor();
  for (var attr in obj) {
    if (obj.hasOwnProperty(attr)) copy[attr] = obj[attr];
  }
  return copy;
}


// minimum-height for tabs
export function tabH() {
  var b = document.getElementById('tab-body'),
    w = window.outerWidth,
    h = document.getElementById('tab-head').offsetHeight;
  if (w >= 1160) {
    b.style.minHeight = h + 'px';
  } else {
    b.style.minHeight = '';
  }
}


// pad number
export function numPad(num: number, size: number) {
  var s = num + "";
  while (s.length < size) s = "0" + s;
  return s;
}


// format date string
export function formatDate(date: number|Date) {
  let d: Date;
  if (date instanceof Date) {
    d = date;
  } else {
    d = new Date(date);
  }
  let month = '' + (d.getMonth() + 1),
    day = '' + d.getDate(),
    year = d.getFullYear();

  if (month.length < 2) month = '0' + month;
  if (day.length < 2) day = '0' + day;

  return [year, month, day].join('-');
}


// short highlight
export function highlightElement(el: JQuery<HTMLElement>) {
  el.addClass('highlight');
  setTimeout(function () {
    el.removeClass('highlight');
  }, 400);
}


// alert using modal
let modal: JQuery<HTMLElement> = null;
export function alert(text: string, title = "", footer = "") {
  var m = modal;
  var h = m.find('.modal-head');
  var f = m.find('.modal-foot');
  var b = m.find('.modal-body');

  b.html(text);
  h.html(title);
  f.html(footer);

  modal.show();
  document.body.style.overflow = "hidden"
}
$(() => {
  const close = () => {
    document.body.style.overflow = "";
    modal.hide();
  }
  modal = $('#modal').on('click', (e) => {
    if (e.target.id === 'modal') close(); // backgroud
  }).on('click', '.close', () => close());
});


// calculate price
export function preis(erwachsene = 0, kinder = 0) {
  var s = settings.preis;
  if (typeof (s) == "undefined") { return -1; }
  s = s.replace(/e/g, ""+erwachsene);
  s = s.replace(/k/g, ""+kinder);
  s = s.replace(/[^0-9\+\-\*\/\(\)\.><=]/g, '');
  try {
    return eval(s);
  } catch (e) {
    console.debug(`<code>${settings.preis}</code> invalide Preis-Formel`, e);
    alert(`<p>Fehler in der Preis-Formel!<br>${e}</p>`, "Fehler");
  }
}