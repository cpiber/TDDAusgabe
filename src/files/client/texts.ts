
const preis_help = `
  <p>Der Preis kann über eine mathematische Formel angegeben werden.<br>
  Er wird für jede Familie jedes Mal neu berechnet.</p>
  <p>Um die Familienmitglieder hinein zu beziehen, kann <span class="code">e</span> für die Zahl der Erwachsenen und <span class="code">k</span> für die Zahl der Kinder verwendet werden.</p><br>
  <p>Kommas sind mit <span class="code">.</span> darzustellen!</p><br>
  <p>Erlaubt sind alle Grundrechenarten (<span class="code">+</span>, <span class="code">-</span>, <span class="code">*</span>, <span class="code">/</span>), Klammern werden beachtet.</p>
  <p>Ebenfalls möglich sind Vergleiche wie "größer als" (<span class="code">&gt;</span>) oder "kleiner als" (<span class="code">&lt;</span>), auch <span class="code">==</span> ("entspricht"), <span class="code">&lt;=</span>, <span class="code">&gt;=</span>.<br>
  Richtig wird als <span class="code">1</span> gewertet, falsch als <span class="code">0</span>.</p>
  <p>Zum Beispiel: <span class="code">e + k * 0.5</span> oder <span class="code">(e > 0) * 2 + (k > 0)</span>.
  <p>Ersteres berechnet 1€ pro Erwachsener und 50cent pro Kind. Zweiteres berechnet 2€ pauschal für alle Erwachsenen und 1€ für alle Kinder (jeweils sofern vorhanden).</p>`;

const karte_designs_help = `
  <p>Dieses Feld erlaubt das erstellen und bearbeiten von Kartendesigns für "Karte drucken".</p>
  <p>Designs werden im <span class="code"><a href="https://www.w3schools.com/js/js_json.asp">JSON</a></span>-Format gespeichert.</p><br>
  <p>Alle Designs finden sich in einem <i>Array</i>. Das heißt, das Feld beginnt mit <span class="code">[</span> und endet mit <span class="code">]</span>.<br>
  Alle Elemente zwischen <span class="code">[ ]</span> gehören zu einer Liste, alle Elemente einer Liste sind mit <span class="code">,</span> getrennt.</p>
  <p>In diesem Fall muss jedes dieser Elemente ein <i>Objekt</i> sein. Objekte gruppieren Unterelemente mit dem "Key-Value"-Prinzip. Jedes Element ("Property") hat einen Namen, anders als bei der Liste (numerisch zugeordnet).<br>
  Objekte werden mit <span class="code">{ }</span> angeschrieben. Unterelemente werden ebenfalls mit <span class="code">,</span> getrennt.</p>
  <p>Die Namen der Eigenschaften werden in Anführungszeichen (<span class="code">"</span> angeschrieben, dann mit Semicolon (<span class="code">:</span>) vom Inhalt getrennt.<br>
  Inhalt kann ein Array, ein Objekt, ein String (in Anführungszeichen) oder eine Zahl sein.</p>
  <p>Ein Design-Objekt muss die Eigenschaft <span class="code">name</span> haben. Die Eigenschaften <span class="code">format</span> und <span class="code">elements</span> sind optional. Achtung auf Groß-Kleinschreibung!</p>
  <ul><li><span class="code">name</span>: String (Text) zur Identifizierung</li>
  <li><span class="code">format</span> (opt): String, spezifiziert Kartengröße. Möglich: HxB (Höhe mal Breite in mm), Papierformate (A3, A4, ...), leer</li>
  <li><span class="code">elements</span> (opt): Array mit allen Elementen, die auf der Seite erwünscht sind. Elemente sind im Objekt-Format anzugeben (alle optional):
  <ul><li><span class="code">position</span>: String (HxB in mm), positioniert linke obere Ecke</li>
  <li><span class="code">html</span>: String, HTML-Code (oder reiner Text) für das Element</li>
  <li><span class="code">css</span>: String, CSS-Eigenschaften für style-Property</li></ul></li></ul>
  <br><p>Alle Elemente könnten im <span class="code">html</span> auf alle Eigenschaften der gewählten Familie zugreifen. Dazu einfach $ + Eigenschaft:
  <ul><li><span class="code">$ID</span>: Identifikationszahl, selbe wie Barcode</li>
  <li><span class="code">$Name</span></li>
  <li><span class="code">$Preis</span></li>
  <li><span class="code">$Erwachsene</span></li>
  <li><span class="code">$Kinder</span></li>
  <li><span class="code">$Ort</span></li>
  <li><span class="code">$Gruppe</span></li>
  <li><span class="code">$Schulden</span></li>
  <li><span class="code">$Karte</span>: Ablaufdatum</li>
  <li><span class="code">$lAnwesenheit</span>: Datum der letzen Anwesenheit</li>
  <li><span class="code">$Notizen</span></li>
  <li><span class="code">$img</span>: Barcode Bildelement</li>
  <li><span class="code">$isrc</span>: Barcode data:image/png</li></ul><br><br>
  <p>Kommentare beginnen mit <span class="code">//</span> und sind hier in grün dargestellt. Strings sind hier in rot.</p>
  <p class="code">[ <span style="color:green">//Beginn der Design-Liste</span></p>
  <p class="code">&nbsp;&nbsp;{ <span style="color:green">//Beginn Design-Objekt</span></p>
  <p class="code">&nbsp;&nbsp;&nbsp;&nbsp;"<span style="color:red">name</span>": "<span style="color:red">Design1</span>", <span style="color:green">//Name festlegen (Komma!)</span></p>
  <p class="code">&nbsp;&nbsp;&nbsp;&nbsp;"<span style="color:red">elements</span>": [ <span style="color:green">//Beginn der Element-Liste</span></p>
  <p class="code">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{ "<span style="color:red">html</span>": "<span style="color:red">&lt;p&gt;Zeile 1&lt;p&gt;</span>" }, <span style="color:green">//Ein Element mit HTML-Inhalt (Komma!)</span></p>
  <p class="code">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{ <span style="color:green">//Beginn Element 2</span></p>
  <p class="code">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"<span style="color:red">html</span>": "<span style="color:red">&lt;p&gt;Zeile 1&lt;p&gt;</span>", <span style="color:green">//HTML-Eigenschaft (Komma!)</span></p>
  <p class="code">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"<span style="color:red">css</span>": "<span style="color:red">color:red</span>" <span style="color:green">//CSS-Eigenschaft</span></p>
  <p class="code">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;} <span style="color:green">//Ende Element 2</span></p>
  <p class="code">&nbsp;&nbsp;&nbsp;&nbsp;] <span style="color:green">//Ende Element-Liste</span></p>
  <p class="code">&nbsp;&nbsp;} <span style="color:green">//Ende Design-Objekt</span></p>
  <p class="code">] <span style="color:green">//Ende Design-Liste</span></p><p></span></p>`;

export { preis_help, karte_designs_help };