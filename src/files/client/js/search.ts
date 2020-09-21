import $ from 'jquery';
import request, { apiData } from './api';
import { familie } from './familie';
import { ausgabeFam } from './familie_ausgabe';
import { famdata } from './familie_interfaces';
import { verwaltungFam } from './familie_verwaltung';
import { timeout } from './helpers';
import { JPromise, orte } from './settings';

interface famlist extends Array<any> {
  page?: number,
  ort?: number,
  ortname?: string,
  gruppe?: number,
}

export default function generate($ausg_selects: JQuery<HTMLElement>, $forms: JQuery<HTMLElement>, loadOrte: () => JPromise<void>) {
  const ausgList: famlist = [];
  const verwList: famlist = [];
  const $ausgList = $forms.eq(1);
  const $ausgList2 = $('<ul>').insertAfter($ausgList);
  const $verwList = $forms.eq(3);
  const $verwList2 = $('<ul>').insertAfter($verwList);
  const ausgSearch: (next_page?: boolean) => void = search.bind(null, ausgList, $ausgList,
    $ausg_selects.add('#tab2 .search-header input:first'),
    $('<li>').addClass('loading').text('Loading...').hide().appendTo($ausgList2),
    $('<li>').addClass('more').text(`Mehr laden`).appendTo($ausgList2).on('click', function () { !$(this).is(':hidden') && ausgSearch(true) }),
    ausgabeFam, loadOrte);
  const verwSearch: (next_page?: boolean) => void = search.bind(null, verwList, $verwList,
    $('#tab3 .search-header input:first'),
    $('<li>').addClass('loading').text('Loading...').hide().appendTo($verwList2),
    $('<li>').addClass('more').text(`Mehr laden`).appendTo($verwList2).on('click', function () { !$(this).is(':hidden') && verwSearch(true) }),
    verwaltungFam, loadOrte);
  $ausg_selects.on('change', () => timeout().then(ausgSearch));
  $forms.eq(0).on('submit', () => ausgSearch());
  $ausgList.on('click', 'li', function () { select.call(this, ausgList, $ausgList, ausgabeFam); });
  $forms.eq(2).on('submit', () => verwSearch());
  $verwList.on('click', 'li', function () { select.call(this, verwList, $verwList, verwaltungFam); });

  return { ausgSearch, verwSearch };
}

function search(list: famlist, $list: JQuery<HTMLElement>, $inputs: JQuery<HTMLElement>, $loading: JQuery<HTMLElement>, $more: JQuery<HTMLElement>, fam: typeof familie, loadOrte: () => JPromise<void>, next_page = false) {
  const data: {
    search: string,
    ort?: number,
    gruppe?: number,
    page: number,
    pagesize: number,
  } = {
    search: $inputs.last().val().toString(),
    page: 1,
    pagesize: 300,
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
  $more.hide();
  if (!next_page) {
    $list.empty();
  } else {
    data.page = (list.page || 1) + 1;
  }
  const req = () => {
    request('familie', 'Suche fehlgeschlagen', data).then((data: apiData | { data: famdata[], pages: number }) => {
      if (data.data.constructor.name !== "Array") {
        data.data = [data.data]; // single fam
      }

      let ort = -1, ortname = "", grp = -1;
      if (!next_page) {
        $list.empty();
        list.length = 0;
        list.page = 1;
      } else {
        list.page += 1;
        ort = list.ort;
        ortname = list.ortname;
        grp = list.gruppe;
      }
      data.data.forEach((element: famdata, index: number) => {
        index = index + (list.page - 1) * 30; // pagesize
        if (element.Ort !== ort || element.Gruppe !== grp) {
          ort = element.Ort;
          grp = element.Gruppe;
          const i = orte.findIndex(val => val.ID == ort);
          ortname = orte[i] ? orte[i].Name : 'Unbekannt';
          $('<li>').addClass('title').val(-1).text(`${ortname}, Gruppe ${grp}`).appendTo($list);
        }
        let name = element.Name;
        if (!name) name = " - ";
        const $li = $('<li>').val(element.ID).data('idx', index).text(`${element.Num}/ ${name}`).appendTo($list);
        if (fam.current && fam.current.data.ID === element.ID) {
          $li.addClass('selected');
          if (fam.name === "ausgabeFam" && !(fam as typeof ausgabeFam).current.timeout) {
            Object.assign(fam.current.data, element); // update data
            fam.current.show(fam);
          }
        }
        list[element.ID] = element;
      });
      list.ort = ort;
      list.ortname = ortname;
      list.gruppe = grp;
      if (list.page < (+data.pages)) $more.show();
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

  if (fam.current && fam.current instanceof ausgabeFam && fam.current.data.ID == this.value) {
    fam.current._save();
    return;
  }
  const f = new fam(list[this.value]);
  list[this.value] = f.data;
}