import $ from 'jquery';
import request, { apiData } from './api';
import { orte } from './settings';

export default function generate($selects: JQuery<HTMLSelectElement>) {
  const $A = [$selects.eq(0), $selects.eq(1)];
  const $V = [$selects.eq(2), $selects.eq(3)];

  const ausgabeOrtChange: () => void = ortChange.bind(null, $A[0], $A[1]);
  ($A[0] as JQuery<HTMLSelectElement>).on('change', ausgabeOrtChange);
  const verwaltungOrtChange: () => void = ortChange.bind(null, $V[0], $V[1]);
  ($V[0] as JQuery<HTMLSelectElement>).on('change', verwaltungOrtChange);

  function loadOrte() {
    if (orte.loading) {
      console.debug("'Orte' already loading");
      return orte.loading;
    }
    const promise = request('ort', 'Fehler beim Laden der Orte').then((data: apiData) => {
      orte.length = 0;
      updateOrte(data);
    }).always(() => {
      orte.loading = null;
    });
    return orte.loading = promise;
  }

  function updateOrte(data: any) {
    orte.maxGruppen = 0;
    [$A, $V].forEach(([$ort, $grp], index) => {
      const val = $ort.val();
      $ort.empty();

      if (index === 0) {
        // option to not restrict
        const el = $('<option>');
        el.val(0).text("Alle");
        $ort.append(el);
      }

      // load
      data.data.forEach((element: any, i: number) => {
        const el = $('<option>');
        el.val(element.ID).text(element.Name);
        $ort.append(el);
        if (index === 0) {
          orte[i] = element;
          orte.maxGruppen = Math.max(orte.maxGruppen, element.Gruppen);
        }
      });
      $ort.val(val || 0);

      // create group selectors
      if ($grp.get(0).length < orte.maxGruppen + 1) {
        const val = $grp.val() || 0;
        $grp.empty();

        // option to not restrict / auto decide
        const el = $('<option>');
        el.val(0).text(index === 0 ? "Alle" : "Auto");
        $grp.append(el);

        for (let i = 1; i <= orte.maxGruppen; i++) {
          const el = $('<option>');
          el.val(i).text(`Gruppe ${i}`);
          $grp.append(el);
        }
        $grp.val(val);
      }

      ortChange($ort, $grp);
    });
  }

  function ortChange($ort: JQuery<HTMLElement>, $grp: JQuery<HTMLElement>) {
    const o = $ort.data('val') || -1;
    const n = $ort.val();

    if (o == n) return;

    const index = orte.findIndex(val => val.ID === n);
    if (!orte[index]) {
      $ort.data('val', -1);
      $grp.children().slice(1).prop('disabled', true).prop('hidden', true);
      return;
    }
    const i = (+orte[index].Gruppen) + 1;
    const c = $grp.children();
    c.slice(0, i).prop('disabled', false).prop('hidden', false);
    c.slice(i).prop('disabled', true).prop('hidden', true);
    $grp.val(0);
    $ort.data('val', n);
  }

  return loadOrte;
}