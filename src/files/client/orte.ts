import $ from 'jquery';
import { alert } from './helpers';
import { orte } from './settings';

export default function generate($selects: JQuery) {
  const $A = [ $selects.eq(0), $selects.eq(1) ];
  const $V = [ $selects.eq(2), $selects.eq(3) ];

  const ausgabeOrtChange: () => void = ortChange.bind(null, $A[0], $A[1], true);
  $A[0].on('change', ausgabeOrtChange);
  const verwaltungOrtChange: () => void = ortChange.bind(null, $V[0], $V[1], false);
  $V[0].on('change', verwaltungOrtChange);

  function loadOrte() {
    if (orte.loading) {
      console.debug("'Orte' already loading");
      return orte.loading;
    }
    const promise = $.post('?api=ort').then((data: any) => {
      if (data && data.status === "success") {
        orte.length = 0;
        updateOrte(data);
      } else {
        console.error(`Failed getting 'Orte': ${data.message}`);
        alert(`
          <p>Fehler beim laden der Orte:<br />${data.message}</p>
        `, "Fehler");
      }
    }).fail((xhr: JQueryXHR, status: string, error: string) => {
      const msg = xhr.responseJSON ? xhr.responseJSON.message : xhr.responseText;
      console.error(xhr.status, error, msg);
      alert(`
        <p>Fehler beim laden der Orte:<br />${xhr.status} ${error}</p>
        <p>${msg}</p>
      `, "Fehler");
    }).always(() => {
      orte.loading = null;
    });
    return orte.loading = promise;
  }

  function updateOrte(data: any) {
    [$A, $V].forEach(([$ort, $grp], index) => {
      const val = $ort.val();
      $ort.empty();

      if (index === 0) {
        // option to not restrict
        const el = $('<option>');
        el.val(-1).text("Alle");
        $ort.append(el);
      }

      // load
      data.data.forEach((element: any, i: number) => {
        const el = $('<option>');
        el.val(i).text(element.Name);
        $ort.append(el);
        if (index === 0)
          orte[i] = element;
      });
      $ort.val(val || -1);
      ortChange($ort, $grp, index === 0);
    });
  }

  function ortChange($ort: JQuery<HTMLElement>, $grp: JQuery<HTMLElement>, all: boolean) {
    let n = $ort.val();
    if (n === null) {
      n = -1;
    } else {
      n = +n;
    }
    $grp.empty();

    if (all) {
      // option to not restrict
      const el = $('<option>');
      el.val(-1).text("Alle");
      $grp.append(el);
    }

    if (!orte[n]) {
      return;
    }

    for (let i = 1; i <= orte[n].Gruppen; i++) {
      const el = $('<option>');
      el.val(i).text(`Gruppe ${i}`);
      $grp.append(el);
    }
    $grp.val(-1);
  }

  return loadOrte;
}