import $ from 'jquery';
import { alert } from './helpers';

export interface OrtList extends Array<any> {
  loading ?: boolean;
}

export default function generate(orte: OrtList, ausgabe_sh: JQuery) {
  let ort_cur = -1;
  const $ort = ausgabe_sh.eq(0);
  const $grp = ausgabe_sh.eq(1);

  function loadOrte() {
    if (orte.loading) {
      console.debug("'Orte' already loading");
      return;
    }
    orte.loading = true;
    return $.post('?api=ort').then((data: any) => {
      if (data && data.status === "success") {
        orte.length = 0;

        const val = $ort.val();
        $ort.empty();

        // option to not restrict
        const el = $('<option>');
        el.val(-1).text("Alle");
        $ort.append(el);

        // load
        data.data.forEach((element: any, index: number) => {
          const el = $('<option>');
          el.val(index).text(element.Name);
          $ort.append(el);
          orte.push(element);
        });
        $ort.val(val || -1);
        ortChange(true);
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
      orte.loading = false;
    });
  }

  function ortChange(force = false) {
    const cur = ort_cur, n = +$ort.val(), val = $grp.val();
    if (!force && cur === n) return;

    $grp.empty();

    if (!orte[n]) {
      $grp.prop('disabled', true);
      return;
    }

    $grp.prop('disabled', false);

    // option to not restrict
    const el = $('<option>');
    el.val(-1).text("Alle");
    $grp.append(el);

    for (let i = 1; i <= orte[n].Gruppen; i++) {
      const el = $('<option>');
      el.val(i).text(`Gruppe ${i}`);
      $grp.append(el);
    }
    ort_cur = n;

    if (cur === n) $grp.val(val);
  }

  return { loadOrte, ortChange };
}