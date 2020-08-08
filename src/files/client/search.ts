import $ from 'jquery';
import { familie, ausgabeFam, verwaltungFam } from './familie';
import { orte } from './settings';
import { timeout, alert } from './helpers';

export default function generate($ausg_selects: JQuery<HTMLElement>, $forms: JQuery<HTMLElement>) {
  const ausgList = [];
  const verwList = [];
  const $ausgList = $forms.eq(1);
  const $verwList = $forms.eq(3);
  const ausgSearch: () => void = search.bind(null, ausgList, $ausgList, $ausg_selects.add('#tab2 .search-header input:first'), ausgabeFam);
  const verwSearch: () => void = search.bind(null, verwList, $verwList, $('#tab3 .search-header input:first'), verwaltungFam);
  $ausg_selects.on('change', () => timeout().then(ausgSearch));
  $forms.eq(0).on('submit', ausgSearch);
  $ausgList.on('click', 'li', function () { select.call(this, ausgList, $ausgList, ausgabeFam); });
  $forms.eq(2).on('submit', verwSearch);
  $verwList.on('click', 'li', function () { select.call(this, verwList, $verwList, verwaltungFam); });

  return { ausgSearch, verwSearch };
}

function search(list: any[], $list: JQuery<HTMLElement>, $inputs: JQuery<HTMLElement>, fam: typeof familie) {
  const data: {
    search: string,
    ort?: string,
    gruppe?: number,
  } = {
    search: $inputs.last().val().toString()
  };
  if ($inputs.length === 3) {
    const ort = +$inputs.eq(0).val();
    if (ort !== -1 && !isNaN(ort) && orte[ort])
      data.ort = orte[ort].Name;
    const grp = +$inputs.eq(1).val();
    if (grp > 0 && !isNaN(grp))
      data.gruppe = grp;
  }
  $.post('?api=familie', data).then((data: any) => {
    if (data && data.status === "success") {
      if (data.data.constructor.name !== "Array") {
        data.data = [data.data]; // single fam
      }
      list.length = 0;
      $list.empty();
      let ort = "", grp = -1;
      data.data.forEach((element: any, index: number) => {
        if (element.Ort !== ort || element.Gruppe !== grp) {
          ort = element.Ort;
          grp = element.Gruppe;
          $('<li>').addClass('title').val(-1).text(`${ort}, Gruppe ${grp}`).appendTo($list);
        }
        let name = element.Name;
        if (!name) name = " - ";
        const $li = $('<li>').val(index).text(`${element.Num}/ ${name}`).appendTo($list);
        if (fam.current && fam.current.data.ID === element.ID) $li.addClass('selected');
        list[index] = element;
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
  });
  return false;
}

function select(list: any[], $list: JQuery<HTMLElement>, fam: typeof familie) {
  if (this.value == -1) return false;
  if (!list[this.value]) return false;
  $list.find('.selected').removeClass('selected');
  $(this).addClass('selected');
  new fam(list[this.value]);
}