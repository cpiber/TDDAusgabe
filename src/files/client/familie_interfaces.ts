
export interface famdata {
  ID?: number;
  Name?: string;
  Erwachsene?: number;
  Kinder?: number;
  Ort?: string;
  Gruppe?: number;
  Schulden?: number;
  Karte?: Date;
  lAnwesenheit?: Date;
  Notizen?: string;
  Num?: number;
  Adresse?: string;
  Telefonnummer?: string;
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
}

export interface famelems {
  ID?: any; //jQuery<HTMLInputElement>;
  Name?: any; //jQuery<HTMLInputElement>;
  Erwachsene?: any; //jQuery<HTMLInputElement>;
  Kinder?: any; //jQuery<HTMLInputElement>;
  Ort?: any; //jQuery<HTMLInputElement>;
  Gruppe?: any; //jQuery<HTMLInputElement>;
  Schulden?: any; //jQuery<HTMLInputElement>;
  Karte?: any; //jQuery<HTMLInputElement>;
  lAnwesenheit?: any; //jQuery<HTMLInputElement>;
  Notizen?: any; //jQuery<HTMLInputElement>;
  Num?: any; //jQuery<HTMLInputElement>;
  Adresse?: any; //jQuery<HTMLInputElement>;
  Telefonnummer?: any; //jQuery<HTMLInputElement>;
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
}
export { fam };