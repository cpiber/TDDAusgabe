/*
 README
 
 Wandelt eine XML-Datei in eine Text-Datei um.
 XML-Datei muss die Eigenschaften wie unten zu sehen beinhalten.
 Original zur Umwandlung der Programms von Martin.
 
 Komplette XML-Datei muss in der Variable xml gespeichert sein.
 Alle Zeilenumbrüche müssen zuvor entfernt werden (eine Zeile).
 
 Zur Ausführung im Browser in der Konsole gedacht.
 Auf jeder beliebigen Seite. Daten dann in der Konsole kopieren
 und in Textdatei einfügen.

*/


var parser = new DOMParser();
var xmlDoc = parser.parseFromString( xml, "text/xml" );

document.body.innerHTML = '<div id="xmloutput"></div>' + document.body;

var arr = xmlDoc.getElementsByTagName('ArrayOfFamilie')[0];
var out = document.getElementById('xmloutput');
for (var i = 0; i < arr.childElementCount; i++) {
	var f = arr.children[i];
	var id = escape( f.getElementsByTagName('ID')[0].innerHTML );
	var n = escape( f.getElementsByTagName('Nachname')[0].innerHTML + " " + f.getElementsByTagName('Vorname')[0].innerHTML );
	var er = escape( f.getElementsByTagName('Erwachsene')[0].innerHTML );
	var ki = escape( f.getElementsByTagName('Kinder')[0].innerHTML );
	var or = escape( f.getElementsByTagName('Ort')[0].innerHTML );
	var gr = escape( f.getElementsByTagName('Gruppe')[0].innerHTML );
	var sc = escape( f.getElementsByTagName('Schulden')[0].innerHTML );
	var ka = f.getElementsByTagName('Karte')[0].innerHTML;
	ka = ka.split('T')[0];
	var la = f.getElementsByTagName('letzteAnwesenheit')[0].innerHTML;
	la = la.split('T')[0];
	var no = escape( f.getElementsByTagName('Notizen')[0].innerHTML );
	
	var str = id + "\t" + n + "\t" + er + "\t" + ki + "\t" + or + "\t" + gr + "\t" + sc + "\t" + ka + "\t" + la + "\t" + no + "\n";
	
	out.innerHTML += str;
}