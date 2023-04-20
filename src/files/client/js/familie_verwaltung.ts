import $ from 'jquery';
import request, { apiData } from "./api";
import { familie } from "./familie";
import { ausgabeFam } from "./familie_ausgabe";
import { fam, famelems } from "./familie_interfaces";
import { clone, timeout } from "./helpers";
import { orte } from "./settings";

export class verwaltungFam extends familie {
  newFam = false;

  static elems: Required<famelems> = clone(fam);
  static $button_save: JQuery<HTMLInputElement>;
  static $button_delete: JQuery<HTMLInputElement>;
  static current: verwaltungFam = null;
  static search: () => void = null;

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
      verwaltungFam.createMode();
    }
    this.show();
    if (this.newFam)
      verwaltungFam.elems.Ort.val(orte[0].ID || 0).trigger('change');
    verwaltungFam.enable();
  }

  static linkHtml($card: JQuery<HTMLElement>) {
    const $inputs = $('#tab3 .familie-data :input, #tab3 .familie-data span') as JQuery<HTMLInputElement>;
    $inputs.filter('.print').on('click', () => {
      if (!verwaltungFam.current) return;
      verwaltungFam.current.print();
    });
    for (const prop in this.elems) {
      this.elems[prop] = $inputs.filter(`.${prop}`);
    }
    this.elems.Ort.on('change', () => {
      const cur = this.current;
      if (!cur) return;
      if (!this.elems.Ort.val()) return;
      if (cur.data.Ort == this.elems.Ort.val()) return;
      cur.data.Gruppe = 0;
      cur.data.Num = 0;
      cur.dirty.Gruppe = true;
      cur.dirty.Num = true;
      this.elems.Gruppe.val(0);
      this.elems.Num.val(0);
    });
    this.elems.Gruppe.on('change', () => {
      const cur = this.current;
      if (!cur) return;
      if (cur.data.Gruppe == this.elems.Gruppe.val()) return;
      cur.data.Num = 0;
      cur.dirty.Num = true;
      this.elems.Num.val(0);
    });
    this.$button_save = $inputs.filter('.save').on('click', () => {
      if (!this.current) return;
      this.current.save();
    });
    this.$button_delete = $inputs.filter('.delete').on('click', () => {
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

  static setSearch(s: () => void) {
    this.search = s;
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

    verwaltungFam.elems.Ort.trigger('change');
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
      return request('familie/insert', 'Fehler beim Erstellen', {
        data: this.data
      }).then((data: apiData) => {
        this.data.ID = data.new.ID;
        this.data.Gruppe = data.new.Gruppe || this.data.Gruppe;
        this.data.Num = data.new.Num || this.data.Num;
        this.newFam = false;
        this.show();
        verwaltungFam.editMode();
        verwaltungFam.search();
        return data;
      }).always(() => {
        verwaltungFam.enable();
      });
    } else {
      if (this.data.Num == 0) this.dirty.Num = true;
      return super.save(verwaltungFam).then((data: apiData) => {
        this.show();
        verwaltungFam.search();
        return data;
      });
    }
  }

  _save() {
    this.save();
  }

  delete() {
    verwaltungFam.disable();
    request('familie/delete', 'Fehler beim Löschen', {
      ID: this.data.ID
    }).then(() => {
      verwaltungFam.clear();
      verwaltungFam.current = null;
      verwaltungFam.search();
    }).fail(() => {
      verwaltungFam.enable();
    });
  }
}