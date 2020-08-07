<!DOCTYPE html>
<html class="main">

<head>
  <title>Tischlein Deck Dich</title>
  <meta charset="UTF-8">
  <link href="?file=favicon" rel="icon" type="image/x-icon" />
  <link href="?file=css" rel="stylesheet" defer />
  <script src="?file=js" defer></script>
</head>

<body>

  <div id="header" class="header">
    <div><span><a href=""><img src="?file=logo" style="max-height:120px;max-width:100%" /></a></span></div>
  </div>

  <p style="text-align: right; padding: 0 10px; margin: 2px 0;"><a class="button" href="?page=print"
      target="_blank">Druckversion</a> <a class="button" href="?login">Log Out</a></p>
  <img id="barcode" style="display:none" />

  &nbsp;


  <div id="tabs">
    <div id="tab-head">
      <ul class="tab">
        <li><a href="#tab1">Start</a></li>
        <li><a href="#tab2">Ausgabe</a></li>
        <li><a href="#tab3">Verwaltung</a></li>
        <li><a href="#tab4">Logs</a></li>
        <li><a href="#tab5">Einstellungen</a></li>
        <li><a href="#tab6">Hilfe</a></li>
      </ul>
    </div>

    <div id="tab-body">
      <div id="tab1">
        <h1>Willkommen</h1>
        <div style="display: inline-table;">
          <div class="cols3">
            <h2>Das neue TDD-Programm</h2>
            <p>Willkommen zum neuen "Tischlein Deck Dich"-Lebensmittel&shy;ausgabe&shy;programm.</p>
            <p>Über die Tabs können Sie die verschiedenen Sektionen des Programms erreichen, links bzw. unten finden Sie
              weitere Informationen zu selbigen.
          </div>
          <div class="cols3">
            <h2>Ausgabe</h2>
            <p>Im Tab "Lebensmittel&shy;ausgabe" wird verwaltet, wer anwesend ist, für wieviele Kinder und Erwachsene
              diese Person Essen abholt und welche Nummer sie bekommt.</p>
            <p>Familien sind sortiert nach Ort und Gruppe. Jede Familie darf nur an einem Ausgabeort erscheinen
              (Ausnahmen bei Feiertagen), für jeden dieser Orte können mehrere Gruppen angelegt werden.</p>
            <p>Bitte immer die Daten der Familien überprüfen!</p><br>
            <p>Das Programm speichert automatisch sobald eine neue Familie geöffnet wird. Alternativ wird auch nach 20
              Sekunden automatisch gespeichert.</p>
          </div>
          <div class="cols3">
            <h2>Verwaltung</h2>
            <p>Die Familien&shy;verwaltung ist dazu da, neue Familien anzulegen oder die Daten vorhandener Familien zu
              bearbeiten.</p>
            <p>Hier können auch vorhandene Familien gelöscht werden.</p><br>
            <p>Vor dem anlegen neuer Familien nach dem Namen suchen, um Doppel-Einträge zu verhindern!</p>
          </div>
        </div>
        <p style="text-align: right;">2018 by Constantin, Version <?php echo VERSION; ?></p>
      </div>
      <div id="tab2">
        <h1>Lebensmittelausgabe</h1>
        <div>
          <div class="cols2 search-header" style="margin-bottom: 10px">
            <div class="cols2 cw100p">
              <select id="ort-select"></select><br />
              <select id="gruppe-select"></select>
              <button id="fam-reload">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18">
                  <path d="M9 13.5c-2.49 0-4.5-2.01-4.5-4.5S6.51 4.5 9 4.5c1.24 0 2.36.52 3.17 1.33L10 8h5V3l-1.76 1.76C12.15 3.68 10.66 3 9 3 5.69 3 3.01 5.69 3.01 9S5.69 15 9 15c2.97 0 5.43-2.16 5.9-5h-1.52c-.46 2-2.24 3.5-4.38 3.5z" />
                </svg>
              </button>
            </div>
            <div class="cols2 cw100p" style="vertical-align: bottom">
              <input id="familie-search" type="text" placeholder="Suche" />
              <input type="submit" value="Suchen" />
            </div>
          </div>
        </div>

        <div>
          <div class="cols2 select-list">
            <ul id="familie-list"></ul>
          </div>
          <div class="cols2 familie-data">
            <p style="font-weight: bolder;" class="w100pm400px"><span></span></p>
            <p class="w100pm400px">
              <b>Familien-Nummer: <span>0</span></b>
              <button class="fam-count o">-</button>
              <button class="fam-count o">0</button>
              <button class="fam-count o">+</button>
            </p>
            <p class="w100pm400px">
              <span class="link">Karte drucken</span> &nbsp; <span></span>
            </p>
            <p class="w100pm400px">Ort: <span></span> | Gruppe: <span></span> | Nummer: <span></span></p>
            <p class="w100pm400px">Letzte Anwesenheit: <span></span></p>
            <p class="w100pm400px">Karte gültig bis: <input type="date" /></p>
            <p class="w100pm400px">Erwachsene / Kinder: <span></span> / <span></span></p>
            <p class="w100pm400px">zu zahlen: <span></span>€</p>
            <p class="w100pm400px">Schulden: <input type="number" style="width:60px" step="0.01" />€</p>
            <p class="w100pm400px">Notizen:<br><textarea class="w100pm400px"></textarea></p>
            <p class="no-space">Zusatzinfo:
              <button onclick="var s=this.parentElement.nextElementSibling;s.style.display=s.style.display=='none'?'':'none'">Umschalten</button>
            </p>
            <p style="display:none;" class="w100pm400px">
              Adresse:<br /><span ></span><br /><br />
              Telefonnummer:<br /><span></span>
            </p>
            <br />
            <div class="w100pm400px">
              <div class="cols3">
                <label><input type="checkbox" /> Anwesend</label>
              </div>
              <div class="cols3">
                <label><input type="checkbox" title="Fügt gesamten Preis zu den Schulden hinzu" /> Geld vergessen</label>
              </div>
              <div class="cols3">
                <label><input type="checkbox" title="Setzt Schulden auf Null&#xA;&#013;Nur wenn ALLE Schulden bezahlt wurden" /> Schulden beglichen</label>
              </div>
              <div class="clear"></div>
            </div>
            <p class="w100pm400px">&nbsp;</p>
            <span class="w100pm400px msg-box"></span>
            <p class="w100pm400px">&nbsp;</p>
            <button class="w100pm400px">Familie bearbeiten</button>
          </div>
        </div>
      </div>
      <div id="tab3">
        <h1>Familienverwaltung</h1>
        <div>
          <div class="cols2 cw100p">
            <div class="cw100p search-header" style="margin-bottom: 10px">
              <input id="verwaltung-search" type="text" placeholder="Suche" />
              <input type="submit" value="Suchen" />
            </div>
            <div class="select-list">
              <ul id="verwaltung-list"></ul>
            </div>
            <div class="cw100p">
              <button class="button-add">+</button>
            </div>
          </div>
          <div class="cols2 familie-data">
            <p class="w100pm400px">
              <span class="link">Karte drucken</span> &nbsp;&nbsp;&nbsp; ID: <span></span>
            </p>
            <p>Name:<br><input class="w100pm400px" type="text" placeholder="Name" /></p>
            <p>Ort: <select></select></p>
            <p>Gruppe: <select></select></p>
            <p>Nummer: <input type="number" style="width:60px;" /></p>
            <p>Erwachsene: <input type="number" style="width:60px;" /></p>
            <p>Kinder: <input type="number" style="width:60px;" /></p>
            <p>Letzte Anwesenkeit: <input type="date" /></p>
            <p>Ablaufdatum Karte: <input type="date" /></p>
            <p>Schulden: <input type="number" style="width:60px;" step="0.01" />€</p>
            <p>Notizen:<br><textarea class="w100pm400px"></textarea></p>
            <p>Adresse:<br><textarea class="w100pm400px"></textarea></p>
            <p>Telefonnummer:<br><input class="w100pm400px" type="text" placeholder="Telefon" /></p>
            <br>
            <p><button class="w100pm400px" data-save="Speichern" data-create="Anlegen"></button></p>
            <p><button class="w100pm400px">Löschen</button></p>
          </div>
        </div>
      </div>
      <div id="tab4">
        <h1>Logs</h1>
        <h2>Einnahmen</h2>
        <p><form>
          <input type="date" /><input type="time" /> - <input type="date" /><input type="time" />
          <button type="submit">Go</button>
          &nbsp; &nbsp;
          <button type="button">Monat</button>
        </form></p>
        <p class="log-info">
          Einnahmen im angegebenen Bereich: <span></span>€<br />
          Personen im angegebenen Bereich: <span></span> Erwachsene(r), <span></span> Kind(er) / <span></span> Familie(n)
        </p>
        <p>&nbsp;</p>
        <h2>Kompletter Log</h2>
        <div class="log"></div>
        <p>
          Seite <select></select>
          &nbsp; &nbsp;
          <button type="button">Aktualisieren</button>
        </p>
      </div>
      <div id="tab5">
        <h1>Einstellungen</h1>
        <div>
          <div class="cols3" id="orte">
            <h2>Orte</h2>
            <div class="select-list w100pm400px">
              <ul style="max-height:180px"></ul>
            </div>
          </div>
          <div class="cols3" id="actions">
            <h2>Aktionen</h2>
            <p>
              <button class="w100pm400px" title="Löscht alle Familien, die seit 8 Wochen nicht mehr anwesend waren.">
                8 Wochen nicht anwesend löschen
              </button><br />
              <button class="w100pm400px" title="Löscht alle Familien, deren Karte seit 8 Wochen abgelaufen ist.">
                Karte 8 Wochen abgelaufen löschen
              </button>
            </p>
            <p>
              <button class="w100pm400px" title="Setzt alle Nummern der Familien zurück, d.h. alle Familien werden neu durchnummeriert.">
                Nummern zurücksetzen
              </button>
            </p>
            <!--<p>
              <button class="w100pm400px" title="Backup aller Daten (Einstellungen, Familien, ...) als Datenbank erstellen">
                Datenbank Backup
              </button>
            </p>-->
            <p>
              <button class="w100pm400px" title="Backup aller Daten (Einstellungen, Familien, ...) herunterladen">
                Backup herunterladen
              </button><br />
              <button class="w100pm400px" title="Backup aller Daten (Einstellungen, Familien, ...) laden">
                Backup laden
              </button>
            </p>
          </div>
          <div class="cols3" id="settings">
            <h2>Allgemein</h2>
            <p>
              <label class="heading" style="display: inline;">Preis Formel:
                <span class="help" title="e ... Anzahl Erwachsene, k ... Anzahl Kinder&#xA;&#013;z.B.: e + k * 0.5&#xA;&#013;oder: (e > 0) * 2 + (k > 0)">(?)</span>
                <input class="w100pm400px" type="text" data-name="Preis" placeholder="Preisformel" />
              </label>
            </p>
            <p>
              <label class="heading" style="display: inline;">Karten-Designs:
                <span class="help">(?)</span>
                <textarea class="w100pm400px" data-name="Kartendesigns" style="height: 120px;"></textarea>
              </label>
            </p>
            <p>
              <button class="w100pm400px" title="Felder speichern automatisch">Alle Speichern</button>
            </p>
          </div>
        </div>
      </div>
      <div id="tab6">
        <h1>Hilfe</h1>
        <h2>Ausgabe</h2>
        <p>Im Tab "Ausgabe" wird die Hauptarbeit gemacht.</p>
        <p>Links oben kann der aktuelle Ort ausgewählt werden und darunter die aktuelle Gruppe. Daneben findet sich
          alternativ das Suchfeld.</p>
        <p>Mit dem Barcodescanner kann ganz einfach gesucht werden: Das Suchfeld auswählen, scannen, fertig. Die
          gescannte Person wird automatisch gewählt und als anwesend eingetragen.</p><br>
        <p>Rechts oben befindet sich ein Zähler; diese Zahl ist dazu da, die anwesenden Personen zu sortieren.</p>
        <p>Jede anwesende Person bekommt Erwachsene / Kinder auf die Hand geschrieben ( z.B. 2 / 1 ) und darunter die
          eben genannte Nummer.</p>
        <p>Vor dem Schreiben sollte immer überprüft werden, ob die Anzahl der Personen auf der Karte mit denen im
          Computer übereinstimmt, damit der richtige Preis kalkuliert werden kann. Diese können sich ändern, wenn die
          anwesende Person nach Änderungen in der Familie eine neue Karte beantragt hat.</p><br>
        <p>Sollte eine Familie Schulden haben, so lassen sich diese direkt bearbeiten. Alternativ können auch alle
          Schulden beglichen werden (=0) oder der komplette Betrag hinzugefügt werden mit der jeweiligen Checkbox.
          Sollte der Betrag nur teilweise fehlen/beglichen werden, muss NUR das Textfeld verwendet werden.</p>
        <p>Wenn eine Person Schulden in Höhe des dreifachen des jeweiligen Preises hat (oder höher) muss diese Person
          erst ALLE Schulden zurückzahlen um wieder Essen holen zu drüfen. Dazu kann das Feld manuell auf 0 gesetzt
          werden oder "Schulden beglichen" gedrückt werden.</p><br>
        <h4>Barcode drucken</h4>
        <p>Sollte eine Person noch keinen Barcode auf der Karte haben (etwa neue Karte), so lässt sich dieser mit dem
          Befehl "Karte drucken" (rechts oben in Ausgabe und Verwaltung) ausdrucken.</p>
        <p>Die Darstellung ist optimiert für den Brother QL-500, in den Druckeinstellungen beachten, dass KEINE RÄNDER
          mitgedruckt werden dürfen! Der Code sollte im Querformat gedruckt werden.</p><br>
        <p>Mit dem Dropdown-Menü unten lassen sich auch andere Designs auswählen. Standard ist ein unformatiertes Papier
          (kann etwa auf A4 gedruckt werden) mit allen wichtigen Informationen, etwa um einen Bescheid zu drucken, sowie
          auch ein Visitenkartenformat mit dem Barcode in der Mitte.</p>
        <p>Weitere Designs lassen sich in den Einstellungen anlegen (mehr unten bzw. in Einstellungen).</p><br>
        <h5>Drucker (Brother QL-500)</h5>
        <p>Design 1 (nur Barcode) ist dafür ausgelegt, mit dem <a
            href="https://www.amazon.de/Brother-P-Touch-QL-500-BW-Etikettendrucker/dp/B002V4I8TI" target="_blank"
            class="link">Brother QL-500 Etikettendrucker</a> auf einen Streifen Klebe-Etiketten gedruckt zu werden
          (ähnliche Drucker sollten ebenfalls kompatibel sein). Die Größe des Barcodes ist optimiert für ein 12mm
          Endlos-Band DK-22214 (<a
            href="https://www.amazon.de/Brother-DK-22214-Endlosetiketten-Papier-QL-Etikettendrucker/dp/B0006HIQPS"
            target="_blank" class="link">Original</a>/<a
            href="https://www.amazon.de/Bubprint-Etiketten-kompatibel-Brother-DK-22214/dp/B00UN2CQB6" target="_blank"
            class="link">Alternative</a>).</p>
        <p>In den Druckeinstellungen (Systemeinstellungen "Geräte und Drucker"; Druck, nicht Drucker!) muss außerdem
          noch die Länge des Etiketts festgelegt werden, hier sind 25mm optimal.</p><br>
        <h4>Navigation über Tasten</h4>
        <p>Dieses Programm lässt sich in der Lebensmittel&shy;ausgabe auch nur über Tasten benutzen:<br>
        <ul>
          <li><b>Alt + Pfeil Ab/Auf</b>: Nächste/Vorige Familie</li>
          <li><b>Alt + n</b>: Ort wechseln, <b>Alt + m</b>: Gruppe wechseln (Je mit Pfeiltasten Auf/Ab), <b>Alt + ,</b>:
            Suchfeld, <b>Alt + .</b>: Gruppe neu laden</li>
          <li><b>Alt + j</b>: Ablaufdatum der Karte, <b>Alt + k</b>: Schulden, <b>Alt + l</b>: Notizen</li>
          <li><b>Alt + u</b>: Anwesend, <b>Alt + i</b>: Geld vergessen, <b>Alt + o</b>: Schulden beglichen</li>
        </ul>
        </p><br>

        <h2>Verwaltung</h2>
        <p>Um neue Personen anzulegen oder existierende Personen zu bearbeiten, muss man in diesen Tab wechseln.</p>
        <p>Existierende Personen können direkt vom Ausgabe-Tab unten mit "Familie bearbeiten" aufgerufen werden oder
          mithilfe der Suche.</p>
        <p>Um eine neue Familie anzulegen, erst den "+"-Knopf unter der Liste drücken, nach Eingabe der Daten mit "Neu
          anlegen" speichern.</p><br>
        <p>Das Programm unterstützt in diesem Modus die Bearbeitung aller Felder. Alle Daten können nun über die
          Textfelder verändert werden. Hier sollte vor allem darauf geachtet werden, das richtige Feld zu wählen.</p>
        <p>Beim Neuanlegen wird außerdem automatisch die Gruppe mit den wenigsten Personen ausgewählt.</p>
        <p>Die Daten der Familie müssen manuell mit dem Knopf unten gespeichert werden.</p><br>
        <p>Bitte vor dem Anlegen immer überprüfen, ob diese Familie bereits eingetragen ist (möglicherweise vertippt)!
        </p><br>

        <h2>Suche</h2>
        <p>Sowohl im Tab "Ausgabe" als auch im Tab "Verwaltung" findet sich ein Suchfeld.</p>
        <p>Der Inhalt wird bei der Suche bei den Leerzeichen aufgebrochen und als mehrere Parameter verwendet. Das
          heißt, Begriffe in der Suche müssen nicht in dieser Reihenfolge im Ergebnis erscheinen.</p>
        <p>Die Suche erstreckt sich über die Felder ID, Name und Ort.</p><br>
        <p>Standardmäßig wird nach "Wildcard" gesucht; zu Deutsch, es können auch Buchstaben (und Zahlen) vor und nach
          dem Begriff sein.</p>
        <p>Um nach einem Begriff inklusive Leerzeichen zu suchen, kann der gesamte Begriff in Anführungszeichen <span
            class="code">"</span> oder <span class="code">'</span> gegeben werden. Zum Beispiel: <span
            class="code">"Vorname Nachname"</span> sucht nach "Vorname Nachname", wobei davor und danach Buchstaben (und
          Zahlen) sein dürfen, allerdings nicht dazwischen.</p>
        <p>Um nach genau nach einem Begriff zu suchen (das gesamte Feld muss dem Begriff entsprechen, ohne Wildcard)
          kann ein Gleichheitszeichen <span class="code">=</span> vor dem Begriff (obiges auch möglich) angebracht
          werden. Beispiel: <span class="code">=Name</span> oder <span class="code">="Vorname Nachname"</span>.</p>
        <p>Um einen Begriff aus der Suche auszuschließen, also dass der Begriff in keinem der Felder erscheinen darf,
          kann ein Ausrufezeicen <span class="code">!</span> vor dem Begriff angebracht werden. Beispiel: <span
            class="code">!Feld</span> schließt alle mit "Feld" in Name und Ort aus (Begriffe mit Anführungszeichen
          erlaubt).</p>
        <p>Eine Kombination des obigen ist ebenfalls möglich: <span class="code">!=</span> schließt alle Familien aus,
          bei denen ein gesamtes Feld dem Begriff entspricht (Begriffe mit Anführungszeichen erlaubt).</p><br>
        <p>Wenn nur eine Zahl eingegeben wird (gesamtes Feld), so wird dieses als ID interpretiert (etwa vom Barcode),
          somit wird nur in diesem Feld nach genau diesem Wert gesucht.</p><br>

        <h2>Logs</h2>
        <p>Dieser Tab ermöglicht das Abrufen der Einnahmen in jedem beliebigen Zeitraum mittels der zwei Felder oben.
        </p>
        <p>Bei den Einnahmen werden sowohl der Preis als auch die Änderungen in Schulden zusammengerechnet.</p><br>
        <p>Darunter findet sich außerdem eine Liste mit allen Aktionen, die über das Programm getätigt worden sind. Die
          Darstellung ist etwas kompliziert, enthält jedoch alle Informationen.</p><br>

        <h2>Einstellungen</h2>
        <p>In den Einstellungen lassen sich alle administrativen Operationen betätigen.</p>
        <p>Wenn genügend Platz ist, ist dieser Tab in drei Spalten aufgeteilt: Orte, Aktionen und allgemeine
          Einstellungen. Bei kleineren Bildschirmen werden diese Spalten untereinander (je nach Platz) angeordnet.</p>
        <br>
        <h4>Orte</h4>
        <p>Dieses Menü ermöglicht das Anlegen und Bearbeiten (und Löschen) aller Ausgabeorte. Mit dem "+"-Knopf können
          Orte angelegt werden, per Klick lassen sich alle Daten bearbeiten. Das Programm speichert nur bei Knopfdruck!
        </p>
        <p>Orte besitzen zwei Felder: Name und Gruppen. Zweiters definiert die Anzahl der auswählbaren Gruppen pro Ort.
        </p><br>
        <h4>Aktionen</h4>
        <p>Hier finden sich Knöpfe für Massenoperationen oder allgemeine Aktionen.</p>
        <p>Weitere Informationen lassen sich mit Hovering (Maus über den Knopf halten) anzeigen.</p><br>
        <h4>Allgemeines</h4>
        <p>Diese Spalte beinhalten Einstellungen im wahren Sinne des Wortes; hier lassen sich Eigenschaften über Inputs
          festlegen.</p>
        <p>Textfelder mit nur einer Zeile speichern automatisch mit "Enter", mehrzeilige Textareas lassen sich nur mit
          dem Knopf "Alle speichern" unten festsetzen. Dieser Knopf speichert alle Felder in dieser Spalte, es werden
          also Änderungen in jedem Feld aufgenommen, auch die einzeiligen.</p><br>
        <p>Per Hovering über oder klicken auf (?) werden weitere Informationen angezeigt.</p><br>

        <h2>Druckversion</h2>
        <p>In der Kopfzeile finden sich zwei weitere Knöpfe: Druckversion und Logout.</p>
        <p>Druckversion öffnet eine neue Seite, mit welcher Sektionen des Programms gedruckt werden können.</p>
        <p>Die Seite ermöglicht es, sowohl Ort als auch Gruppe auszuwählen (alternativ auch Alle), mit "OK" wird dann
          eine Tabelle generiert, welche die gewünschten Daten enthält. Ebenfalls wird ein Rechteck hinter dem Name
          eingefügt, um Personen als Anwesend abzuhacken.</p>
        <p>Um eine einzelne Gruppe zu wählen, muss zuerst der gewünschte Ort gesetzt werden und danach mit "OK"
          bestätigt werden. Erst dann werden die verschiedenen Gruppen angezeigt.</p>
      </div>
    </div>
  </div>

  <div id="modal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <span class="close">&times;</span>
        <h2 class="modal-head">Modal Header</h2>
      </div>
      <div class="modal-body">Modal Body</div>
      <div class="modal-footer">
        <h3 class="modal-foot">Modal Footer</h3>
      </div>
    </div>
  </div>

  <div class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <span class="close">&times;</span>
        <h2 class="modal-head">Karte drucken</h2>
      </div>
      <div class="modal-body"><iframe src="?page=card"></iframe></div>
      <div class="modal-footer">
        <h3 class="modal-foot"></h3>
      </div>
    </div>
  </div>

</body>

</html>