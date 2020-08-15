import { familie } from "./familie";
import { ausgabeFam } from "./familie_ausgabe";
import { fam, famelems } from "./familie_interfaces";
import { alert, clone, timeout } from "./helpers";
import { orte } from "./settings";

export class verwaltungFam extends familie {
  newFam = false;

  static elems: famelems = clone(fam);
  static $button_save: JQuery<HTMLInputElement>;
  static $button_delete: JQuery<HTMLInputElement>;
  static current: verwaltungFam = null;

  constructor(data: any = null) {
    super(data);
    verwaltungFam.current = this;
    if (ausgabeFam.current && ausgabeFam.current.data.ID === this.data.ID) {
      ausgabeFam.clear();
      ausgabeFam.current = null;
    }

    if (data !== undefined && data !== null) {
      this.newFam = false;
      verwaltungFam.editMode();
    } else {
      this.newFam = true;
      this.data.Ort = orte[0].ID || 0;
      verwaltungFam.createMode();
    }
    this.show();
    verwaltungFam.enable();
  }

  static linkHtml($card: JQuery<HTMLElement>) {
    const $inputs = $('#tab3 .familie-data :input, #tab3 .familie-data span') as JQuery<HTMLInputElement>;
    $inputs.eq(0).on('click', () => {
      if (!verwaltungFam.current) return;
      verwaltungFam.current.print();
    });
    this.elems.ID = $inputs.eq(1);
    this.elems.Name = $inputs.eq(2);
    this.elems.Ort = $inputs.eq(3).on('change', () => {
      const cur = this.current;
      if (!cur) return;
      if (cur.data.Ort == this.elems.Ort.val()) return;
      cur.data.Gruppe = 0;
      cur.data.Num = 0;
      cur.dirty.Gruppe = true;
      cur.dirty.Num = true;
      this.elems.Gruppe.val(0);
      this.elems.Num.val(0);
    });
    this.elems.Gruppe = $inputs.eq(4).on('change', () => {
      const cur = this.current;
      if (!cur) return;
      if (cur.data.Gruppe == this.elems.Gruppe.val()) return;
      cur.data.Num = 0;
      cur.dirty.Num = true;
      this.elems.Num.val(0);
    });
    this.elems.Num = $inputs.eq(5);
    this.elems.Erwachsene = $inputs.eq(6);
    this.elems.Kinder = $inputs.eq(7);
    this.elems.lAnwesenheit = $inputs.eq(8);
    this.elems.Karte = $inputs.eq(9);
    this.elems.Schulden = $inputs.eq(10);
    this.elems.Notizen = $inputs.eq(11);
    this.elems.Adresse = $inputs.eq(12);
    this.elems.Telefonnummer = $inputs.eq(13);
    this.$button_save = $inputs.eq(14).on('click', () => {
      if (!this.current) return;
      this.current.save();
    });
    this.$button_delete = $inputs.eq(15).on('click', () => {
      if (!this.current) return;
      this.current.delete();
    });
    const $list = $('#tab3 .select-list ul');
    $('#tab3 .button-add').on('click', () => {
      $list.find('.selected').removeClass('selected');
      new verwaltungFam();
    });

    super.linkHtml($card);
  }

  static clear() {
    super.clear();
    [this.$button_save, this.$button_delete].forEach((el) => {
      el.prop('checked', false);
    });
    this.editMode();
  }

  show() {
    super.show(verwaltungFam);

    verwaltungFam.elems.Ort.change();
    timeout().then(() => verwaltungFam.elems.Gruppe.val(this.data.Gruppe));
  }

  static disable() {
    super.disable();
    [this.$button_save, this.$button_delete].forEach((el) => {
      el.prop('disabled', true);
    });
  }

  static enable() {
    super.enable();
    [this.$button_save, this.$button_delete].forEach((el) => {
      el.prop('disabled', false);
    });
  }

  static editMode() {
    this.$button_save.text(this.$button_save.data('save'));
    this.$button_delete.show();
  }

  static createMode() {
    this.$button_save.text(this.$button_save.data('create'));
    this.$button_delete.hide();
  }

  save() {
    if (this.newFam) {
      verwaltungFam.disable();
      return $.post('?api=familie/insert', this.data).then((data: any) => {
        if (data && data.status === "success") {
          this.data.ID = data.new.ID;
          this.data.Gruppe = data.new.Gruppe || this.data.Gruppe;
          this.data.Num = data.new.Num || this.data.Num;
          this.show();
          verwaltungFam.editMode();
        } else {
          console.error(`Failed creating: ${data.message}`);
          alert(`
          <p>Fehler beim erstellen:<br />${data.message}</p>
        `, "Fehler");
        }
      }).fail((xhr: JQueryXHR, status: string, error: string) => {
        const msg = xhr.responseJSON ? xhr.responseJSON.message : xhr.responseText;
        console.error(xhr.status, error, msg);
        alert(`
        <p>Fehler beim erstellen:<br />${xhr.status} ${error}</p>
        <p>${msg}</p>
      `, "Fehler");
      }).always(() => {
        verwaltungFam.enable();
      });
    } else {
      const req = super.save(verwaltungFam);
      if (req) return req.then(() => this.show());
    }
  }

  _save() {
    this.save();
  }

  delete() {
    verwaltungFam.disable();
    $.post('?api=familie/delete', {
      ID: this.data.ID
    }).then((data: any) => {
      if (data && data.status === "success") {
        verwaltungFam.clear();
        verwaltungFam.current = null;
      } else {
        verwaltungFam.enable();
        console.error(`Failed deleting: ${data.message}`);
        alert(`
          <p>Fehler beim löschen:<br />${data.message}</p>
        `, "Fehler");
      }
    }).fail((xhr: JQueryXHR, status: string, error: string) => {
      verwaltungFam.enable();
      const msg = xhr.responseJSON ? xhr.responseJSON.message : xhr.responseText;
      console.error(xhr.status, error, msg);
      alert(`
        <p>Fehler beim löschen:<br />${xhr.status} ${error}</p>
        <p>${msg}</p>
      `, "Fehler");
    });
  }
}