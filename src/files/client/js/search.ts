import $ from 'jquery';
import { familie } from './familie';
import { alert, timeout } from './helpers';
import { JPromise, orte } from './settings';
import { ausgabeFam } from './familie_ausgabe';
import { verwaltungFam } from './familie_verwaltung';

export default function generate($ausg_selects: JQuery<HTMLElement>, $forms: JQuery<HTMLElement>, loadOrte: () => JPromise<void>) {
  const ausgList = [];
  const verwList = [];
  const $ausgList = $forms.eq(1);
  const $verwList = $forms.eq(3);
  const ausgSearch: () => void = search.bind(null, ausgList, $ausgList,
    $ausg_selects.add('#tab2 .search-header input:first'),
    $('<span>').text('Loading...').hide().insertBefore($ausgList), ausgabeFam, loadOrte);
  const verwSearch: () => void = search.bind(null, verwList, $verwList,
    $('#tab3 .search-header input:first'),
    $('<span>').text('Loading...').hide().insertBefore($verwList), verwaltungFam, loadOrte);
  $ausg_selects.on('change', () => timeout().then(ausgSearch));
  $forms.eq(0).on('submit', ausgSearch);
  $ausgList.on('click', 'li', function () { select.call(this, ausgList, $ausgList, ausgabeFam); });
  $forms.eq(2).on('submit', verwSearch);
  $verwList.on('click', 'li', function () { select.call(this, verwList, $verwList, verwaltungFam); });

  return { ausgSearch, verwSearch };
}

function search(list: any[], $list: JQuery<HTMLElement>, $inputs: JQuery<HTMLElement>, $loading: JQuery<HTMLElement>, fam: typeof familie, loadOrte: () => JPromise<void>) {
  const data: {
    search: string,
    ort?: number,
    gruppe?: number,
  } = {
    search: $inputs.last().val().toString()
  };
  if ($inputs.length === 3) {
    const ort = +$inputs.eq(0).val();
    if (ort > 0 && !isNaN(ort))
      data.ort = ort;
    const grp = +$inputs.eq(1).val();
    if (grp > 0 && !isNaN(grp))
      data.gruppe = grp;
  }
  $loading.show();
  $list.empty();
  const req = () => {
    $.post('?api=familie', data).then((data: any) => {
      if (data && data.status === "success") {
        if (data.data.constructor.name !== "Array") {
          data.data = [data.data]; // single fam
        }
        $list.empty();
        list.length = 0;
        let ort = -1, ortname = "", grp = -1;
        data.data.forEach((element: any, index: number) => {
          if (element.Ort !== ort || element.Gruppe !== grp) {
            ort = element.Ort;
            grp = element.Gruppe;
            const i = orte.findIndex(val => val.ID == ort);
            ortname = orte[i] ? orte[i].Name : 'Unbekannt';
            $('<li>').addClass('title').val(-1).text(`${ortname}, Gruppe ${grp}`).appendTo($list);
          }
          let name = element.Name;
          if (!name) name = " - ";
          const $li = $('<li>').val(element.ID).text(`${element.Num}/ ${name}`).appendTo($list);
          if (fam.current && fam.current.data.ID === element.ID) $li.addClass('selected');
          list[element.ID] = element;
        });
      } else {
        console.error(`Failed search: ${data.message}`);
        alert(`
            <p>Suche fehlgeschalgen:<br />${data.message}</p>
          `, "Fehler");
      }
    }).fail((xhr: JQueryXHR, status: string, error: string) => {
      const msg = xhr.responseJSON ? xhr.responseJSON.message : xhr.responseText;
      console.error(xhr.status, error, msg);
      alert(`
            <p>Suche fehlgeschalgen:<br />${xhr.status} ${error}</p>
            <p>${msg}</p>
          `, "Fehler");
    }).always(() => $loading.hide());
  };
  if (!orte.length) {
    loadOrte().then(req);
  } else {
    req();
  }
  return false;
}

function select(list: any[], $list: JQuery<HTMLElement>, fam: typeof familie) {
  if (this.value == -1) return false;
  if (!list[this.value]) return false;
  $list.find('.selected').removeClass('selected');
  $(this).addClass('selected');
  
  if (fam.current && fam.current.data.ID == this.value) {
    fam.current._save();
    return;
  }
  const f = new fam(list[this.value]);
  list[this.value] = f.data;
}