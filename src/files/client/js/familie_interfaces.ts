
export interface famdata {
  ID?: number;
  Name?: string;
  Erwachsene?: number;
  Kinder?: number;
  Ort?: number;
  Gruppe?: number;
  Schulden?: number;
  Karte?: string;
  lAnwesenheit?: string;
  Notizen?: string;
  Num?: number;
  Adresse?: string;
  Telefonnummer?: string;
  ProfilePic?: string;
  ProfilePic2?: string;
}

export interface famdirty {
  ID?: boolean;
  Name?: boolean;
  Erwachsene?: boolean;
  Kinder?: boolean;
  Ort?: boolean;
  Gruppe?: boolean;
  Schulden?: boolean;
  Karte?: boolean;
  lAnwesenheit?: boolean;
  Notizen?: boolean;
  Num?: boolean;
  Adresse?: boolean;
  Telefonnummer?: boolean;
  ProfilePic?: boolean;
  ProfilePic2?: boolean;
}

export interface famelems {
  ID?: JQuery<HTMLInputElement>;
  Name?: JQuery<HTMLInputElement>;
  Erwachsene?: JQuery<HTMLInputElement>;
  Kinder?: JQuery<HTMLInputElement>;
  Ort?: JQuery<HTMLInputElement>;
  Gruppe?: JQuery<HTMLInputElement>;
  Schulden?: JQuery<HTMLInputElement>;
  Karte?: JQuery<HTMLInputElement>;
  lAnwesenheit?: JQuery<HTMLInputElement>;
  Notizen?: JQuery<HTMLInputElement>;
  Num?: JQuery<HTMLInputElement>;
  Adresse?: JQuery<HTMLInputElement>;
  Telefonnummer?: JQuery<HTMLInputElement>;
  ProfilePic?: JQuery<HTMLImageElement>;
  ProfilePic2?: JQuery<HTMLImageElement>;
}

const fam: famelems = {
  ID: null,
  Name: null,
  Erwachsene: null,
  Kinder: null,
  Ort: null,
  Gruppe: null,
  Schulden: null,
  Karte: null,
  lAnwesenheit: null,
  Notizen: null,
  Num: null,
  Adresse: null,
  Telefonnummer: null,
  ProfilePic: null,
  ProfilePic2: null,
}
export { fam };
