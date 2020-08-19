import $ from 'jquery';
import request, { apiData } from './api';

export default function init() {
  const $inp = $('#tab4 form, #tab4 :input');
  const $info = $('#tab4 .log-info span');
  const info = updateInfo.bind(null, $inp, $info);
  const logs = loadLogs.bind(null, $('#tab4 .log'), $inp.eq(7));

  const write = (date: Date, index: number) => {
    const str = date.toISOString().replace(/\.[0-9]{3}Z/, "");
    $inp.eq(index).val(str.substr(0, 10));
    $inp.eq(index + 1).val(str.substr(11, 5));
  }
  const writeDefault = () => {
    let d = new Date();
    d.setUTCDate(1); d.setUTCHours(0); d.setUTCMinutes(0); d.setUTCSeconds(0);
    write(d, 1);

    d = new Date();
    d.setUTCMonth(d.getUTCMonth() + 1); d.setUTCDate(0); d.setUTCHours(23); d.setUTCMinutes(59); d.setUTCSeconds(59);
    write(d, 3);
  };

  writeDefault();
  $inp.eq(0).on('submit', info);
  $inp.eq(6).on('click', () => { writeDefault(); info(); });
  $inp.eq(7).on('change', logs);
  $inp.eq(8).on('click', logs);

  return { info, logs };
}

function updateInfo($inp: JQuery<HTMLElement>, $info: JQuery<HTMLElement>) {
  const begin = `${$inp.eq(1).val()} ${$inp.eq(2).val()}`;
  const end = `${$inp.eq(3).val()} ${$inp.eq(4).val()}`;

  request('log/info', 'Fehler', {
    begin: begin,
    end: end
  }).then((data: apiData) => {
    const m = +(data.data.money);
    $info.eq(0).text((isNaN(m) ? 0 : m).toFixed(2));
    $info.eq(1).text(+data.data.adults);
    $info.eq(2).text(+data.data.children);
    $info.eq(3).text(+data.data.families);
  });

  return false;
}

function loadLogs($div: JQuery<HTMLElement>, $select: JQuery<HTMLSelectElement>) {
  const page = +$select.val() || -1;
  const pages = $select.get(0).length;

  const tr = (data: string[], $table: JQuery<HTMLElement>, heading = false) => {
    const $tr = $('<tr>').appendTo($table);
    data.forEach(element => {
      $(heading ? '<th>' : '<td>').appendTo($tr).text(element);
    });
    return $tr;
  }

  request('log', 'Fehler beim Abrufen der Logs', {
    page: page
  }).then((data: apiData) => {
    $div.empty();

    const $table = $('<table>').appendTo($div);
    const $tbody = $('<tbody>').appendTo($table);
    tr(['ID', 'Zeit', 'Typ', 'Wert'], $tbody, true);
    data.data.forEach((element: { [key: string]: string }) => {
      tr(Object.values(element), $tbody);
    });

    if (pages != data.pages) {
      let val = $select.val();
      $select.empty();
      for (let i = 1; i < data.pages; i++) {
        $select.append($('<option>').text(i));
      }
      $select.append($('<option>').text(data.pages || 1).val(-1));
      $select.val(val || -1);
    }
  });
}