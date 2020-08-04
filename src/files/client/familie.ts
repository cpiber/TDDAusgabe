
import jQuery from 'jquery';
import { famdata, famdirty, famelems, fam } from './familie_interfaces';
import { clone } from './helpers';


class familie {
  data: famdata = {};
  dirty: famdirty = {};

  static elems: famelems;
  static linkHtml: () => void;
  static clear() {
    for (const prop in this.elems) {
      const el: JQuery<HTMLInputElement> = this.elems[prop];
      if (!el) continue;
      if (typeof (el.html) === "function") el.html('');
      if (typeof (el.val) === "function") el.val('');
      el.prop('checked', false);
    }
  }
  static disable() {
    for (const prop in this.elems) {
      if (this.elems[prop])
        this.elems[prop].prop('disabled', true);
    }
  }
  static enable() {
    for (const prop in this.elems) {
      if (this.elems[prop])
        this.elems[prop].prop('disabled', false);
    }
  }
}


export class ausgabeFam extends familie {
  static elems: famelems = clone(fam);
  static anwesend: JQuery<HTMLInputElement>;
  static geldverg: JQuery<HTMLInputElement>;
  static schuldbeg: JQuery<HTMLInputElement>;

  static linkHtml() {
    const inputs = jQuery('#tab2 .familie-data :input') as JQuery<HTMLInputElement>;
    this.elems.Karte = inputs.eq(3);
    this.elems.Schulden = inputs.eq(4);
    this.elems.Notizen = inputs.eq(5);
    this.anwesend = inputs.eq(7);
    this.geldverg = inputs.eq(8);
    this.schuldbeg = inputs.eq(9);
  }

  static clear() {
    super.clear();
    [this.anwesend, this.geldverg, this.schuldbeg].forEach((el) => {
      el.prop('checked', false);
    });
  }

  static disable() {
    super.disable();
    [this.anwesend, this.geldverg, this.schuldbeg].forEach((el) => {
      el.prop('disabled', true);
    });
  }

  static enable() {
    super.enable();
    [this.anwesend, this.geldverg, this.schuldbeg].forEach((el) => {
      el.prop('disabled', false);
    });
  }
}


export class verwaltungFam extends familie {
  static elems: famelems = clone(fam);
  static button_save: JQuery<HTMLInputElement>;
  static button_delete: JQuery<HTMLInputElement>;

  static linkHtml() {
    const inputs = jQuery('#tab3 .familie-data :input') as JQuery<HTMLInputElement>;
    this.elems.Name = inputs.eq(0);
    this.elems.Ort = inputs.eq(1);
    this.elems.Gruppe = inputs.eq(2);
    this.elems.Num = inputs.eq(3);
    this.elems.Erwachsene = inputs.eq(4);
    this.elems.Kinder = inputs.eq(5);
    this.elems.lAnwesenheit = inputs.eq(6);
    this.elems.Karte = inputs.eq(7);
    this.elems.Schulden = inputs.eq(8);
    this.elems.Notizen = inputs.eq(9);
    this.elems.Adresse = inputs.eq(10);
    this.elems.Telefonnummer = inputs.eq(11);
    this.button_save = inputs.eq(12);
    this.button_delete = inputs.eq(13);
  }

  static clear() {
    super.clear();
    [this.button_save, this.button_delete].forEach((el) => {
      el.prop('checked', false);
    });
    this.editMode();
  }

  static disable() {
    super.disable();
    [this.button_save, this.button_delete].forEach((el) => {
      el.prop('disabled', true);
    });
  }

  static enable() {
    super.enable();
    [this.button_save, this.button_delete].forEach((el) => {
      el.prop('disabled', false);
    });
  }

  static editMode() {
    this.button_save.text(this.button_save.data('save'));
    this.button_delete.show();
  }

  static createMode() {
    this.button_save.text(this.button_save.data('create'));
    this.button_delete.hide();
  }
}