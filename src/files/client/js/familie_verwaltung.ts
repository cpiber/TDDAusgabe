import $ from 'jquery';
import request, { apiData, getImageUrl, upload } from "./api";
import { familie } from "./familie";
import { ausgabeFam } from "./familie_ausgabe";
import { fam, famelems } from "./familie_interfaces";
import { clone, close_modal, open_modal, timeout } from "./helpers";
import { orte } from "./settings";

export class verwaltungFam extends familie {
  newFam = false;

  static elems: Required<famelems> = clone(fam);
  static $button_save: JQuery<HTMLInputElement>;
  static $button_delete: JQuery<HTMLInputElement>;
  static $button_updateProf1: JQuery<HTMLInputElement>;
  static $button_updateProf2: JQuery<HTMLInputElement>;
  static $button_deleteProf1: JQuery<HTMLInputElement>;
  static $button_deleteProf2: JQuery<HTMLInputElement>;
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

  static linkHtml($card: JQuery<HTMLElement>, $profile: JQuery<HTMLElement>) {
    const $inputs = $('#tab3 .familie-data :input, #tab3 .familie-data span, #tab3 .profile-pics img, #tab3 .profile-pics button') as JQuery<HTMLInputElement>;
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

    const doUpload = (prop: string) => {
      this.disable();
      ProfilePictureHelper.open()
        .then(blob => upload('familie/profile', 'Fehler beim upload', prop, blob))
        .then(response => {
          if (!this.current) return;
          this.current.data[prop] = response.data;
          this.current.dirty[prop] = true;
          this.elems[prop].attr('src', getImageUrl(this.current.data[prop]));
          this.enable();
        })
        .catch(() => {
          this.enable();
        });
    };
    this.$button_updateProf1 = $inputs.filter('.update[data-ref="ProfilePic"]').on('click', () => {
      doUpload('ProfilePic');
    });
    this.$button_updateProf2 = $inputs.filter('.update[data-ref="ProfilePic2"]').on('click', () => {
      doUpload('ProfilePic2');
    });
    this.$button_deleteProf1 = $inputs.filter('.remove[data-ref="ProfilePic"]').on('click', () => {
      this.current.data.ProfilePic = '';
      this.current.dirty.ProfilePic = true;
      this.elems.ProfilePic.attr('src', '');
    });
    this.$button_deleteProf2 = $inputs.filter('.remove[data-ref="ProfilePic2"]').on('click', () => {
      this.current.data.ProfilePic2 = '';
      this.current.dirty.ProfilePic2 = true;
      this.elems.ProfilePic2.attr('src', '');
    });

    ProfilePictureHelper.linkHtml($profile);
    super.linkHtml($card, $profile);
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
    [this.$button_save, this.$button_delete, this.$button_updateProf1, this.$button_updateProf2, this.$button_deleteProf1, this.$button_deleteProf2].forEach((el) => {
      el.prop('disabled', true);
    });
  }

  static enable() {
    super.enable();
    [this.$button_save, this.$button_delete, this.$button_updateProf1, this.$button_updateProf2, this.$button_deleteProf1, this.$button_deleteProf2].forEach((el) => {
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

class ProfilePictureHelper {
  static $modal: JQuery<HTMLElement>;
  static $video: JQuery<HTMLVideoElement>;
  static $canvas: JQuery<HTMLCanvasElement>;
  static $button: JQuery<HTMLButtonElement>;

  static linkHtml($profile: JQuery<HTMLElement>) {
    this.$modal = $profile;
    this.$video = $profile.find<HTMLVideoElement>('#profile-video');
    this.$canvas = $profile.find<HTMLCanvasElement>('#profile-canvas');
    this.$button = $profile.find<HTMLButtonElement>('#take-profile-pic');
  }

  static open(): JQuery.Promise<Blob> {
    const dfd = $.Deferred();

    open_modal(this.$modal);
    const close = () => dfd.reject('close');
    this.$modal.one('close', close);
    
    this.setStream().catch(err => dfd.reject(err));
    navigator.mediaDevices.removeEventListener('devicechange', this.setStream);
    navigator.mediaDevices.addEventListener('devicechange', this.setStream);

    const handleStartStream = () => {
      const width = 400;
      const v = this.$video.get(0);
      const height = v.videoHeight / v.videoWidth * width;
      this.$video.attr('width', width).attr('height', height);
      const takePic = () => this.takepicture(width, height, dfd);
      this.$button.one('click', takePic);
    };
    this.$video.one('canplay', handleStartStream);
    dfd.then(() => {
      this.$modal.off('close', close);
      close_modal(this.$modal);
      this.stopBothVideoAndAudio(this.$video.get(0).srcObject as MediaStream);
      this.$video.get(0).srcObject = null;
    });

    return dfd.promise();
  }

  static setStream(): Promise<any> {
    return navigator.mediaDevices.getUserMedia({ video: true, audio: false })
      .then(stream => {
        this.$video.get(0).srcObject = stream;
        this.$video.get(0).play();
      })
      .catch(err => {
        console.error(err);
        throw err;
      });
  }


  static takepicture(width: number, height: number, dfd: JQuery.Deferred<Blob>): void {
    const canvas = this.$canvas.get(0);
    const context = this.$canvas.get(0).getContext("2d");
    if (width && height) {
      canvas.width = width;
      canvas.height = height;
      context.drawImage(this.$video.get(0), 0, 0, width, height);
      canvas.toBlob(blob => dfd.resolve(blob));
    }
  }

  static stopBothVideoAndAudio(stream: MediaStream) {
    stream.getTracks().forEach((track) => {
      if (track.readyState == 'live') {
        track.stop();
      }
    });
  }
}
