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
export function formatDate(date: number | Date) {
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
let modal: JQuery<HTMLElement>;
const messages: { text: string, title: string, footer: string }[] = [];
export function alert(text: string, title = "", footer = "") {
  const m = modal;
  messages.push({ text, title, footer });
  if (m.data('open') == true) return;

  _alert();
  open_modal(m);
}
function _alert() {
  const msg = messages[0];
  const m = modal;
  const h = m.find('.modal-head');
  const f = m.find('.modal-foot');
  const b = m.find('.modal-body');

  b.html(msg.text);
  h.html(msg.title);
  f.html(msg.footer);
}
$(() => {
  $('.modal').each(function () {
    let m = $(this);
    if (m.attr('id') === 'modal') modal = m;
    const close = () => {
      if (m.data('close') === false) return;
      close_modal(m);
    };
    m.on('click', function (e) {
      if (e.target === this) close(); // background
    }).on('click', '.close', () => close());
  });
});
export function open_modal(modal: JQuery<HTMLElement>) {
  modal.show().data('open', true);
  document.body.style.overflow = "hidden";
}
export function close_modal(modal: JQuery<HTMLElement>) {
  if (modal.attr('id') === 'modal') {
    messages.shift();
    if (messages.length) return _alert();
  }
  document.body.style.overflow = "";
  modal.hide().data('open', false);
  modal.trigger('close');
}


// calculate price
export function sandboxFn(fn: string, closure?: Parameters<typeof Object.defineProperties>[1]) {
  const code = `with (sandbox) {${fn}}`;
  const func = new Function('sandbox', code);
  const ctx = Object.create(null);
  Object.defineProperties(ctx, closure);
  return func(ctx);
};


export function preis(erwachsene = 0, kinder = 0) {
  var s = settings.preis;
  if (typeof (s) == "undefined") { return -1; }

  try {
    return sandboxFn(`return ${s};`, { k: { value: +kinder, writable: false }, e: { value: +erwachsene, writable: false } });
  } catch (e) {
    console.error(`Invalide Preis-Formel (${e}):`, settings.preis);
    alert(`<p>Fehler in der Preis-Formel!<br>${e} (${s})</p>`, "Fehler");
  }
  return 0;
}


// promise timeout
export function timeout(ms: number = 0) {
  const dfd = $.Deferred();
  setTimeout(() => { dfd.resolve(); }, ms);
  return dfd.promise();
}