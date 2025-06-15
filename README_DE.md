# Fotorama_XH

Fotorama_XH ermöglicht das Einbetten von [Fotorama](https://fotorama.io/)
Galerien auf CMSimple_XH Seiten.
Das Plugin bietet keinerlei Bild-Upload-Möglichkeit,
sondern verwendet statt dessen Bilder aus dem Bilderordner von CMSimple_XH
oder von irgendwo im World Wide Web (bislang wird nur JPEG unterstützt).
Jede Galerie kann individuell konfiguriert werden,
und jedes Bild kann eine zusätzliche Beschriftung erhalten.

- [Voraussetzungen](#voraussetzungen)
- [Installation](#installation)
- [Einstellungen](#einstellungen)
- [Verwendung](#verwendung)
    - [Vorbereiten einer Galerie](#vorbereiten-einer-galerie)
    - [Externe Bilder](#externe-bilder)
    - [Einbetten einer Galerie](#einbetten-einer-galerie)
- [Einschränkungen](#einschränkungen)
- [Fehlerbehebung](#fehlerbehebung)
- [Lizenz](#lizenz)
- [Danksagung](#danksagung)

## Voraussetzungen

Fotorama_XH ist ein Plugin für [CMSimple_XH](https://cmsimple-xh.org/de/).
Es benötigt PHP ≥ 7.4.0 mit den dom und gd Erweiterungen,
und CMSimple_XH ≥ 1.7.0.
Fotorama_XH benötigt weiterhin [Plib_XH](https://github.com/cmb69/plib_xh) ≥ 1.10;
ist dieses noch nicht installiert (siehe `Einstellungen` → `Info`),
laden Sie das [aktuelle Release](https://github.com/cmb69/plib_xh/releases/latest)
herunter, und installieren Sie es.

## Download

Das [aktuelle Release](https://github.com/cmb69/fotorama_xh/releases/latest)
kann von Github herunter geladen werden.

## Installation

Die Installation erfolgt wie bei vielen anderen CMSimple_XH-Plugins auch.

1. Sichern Sie die Daten auf Ihrem Server.
1. Entpacken Sie die ZIP-Datei auf Ihrem Rechner.
1. Laden Sie das ganze Verzeichnis `fotorama/` auf Ihren Server
   in das Plugin-Verzeichnis von CMSimple_XH hoch.
1. Vergeben Sie falls nötig Schreibrechte für die Unterverzeichnisse
   `cache/`, <!-- `config/`, --> `css/` und `languages/`.
1. Verschieben Sie `plugins/fotorama/editorhook.php` nach
   `plugins/filebrowser/editorhooks/fotorama/script.php`,
   so dass der Dateibrowser beim Bearbeiten der Galerien genutzt werden kann.
1. Prüfen Sie unter `Plugins` → `Fotorama` ob alle Voraussetzungen für den
   Betrieb erfüllt sind.

## Einstellungen

Die Plugin-Konfiguration erfolgt wie bei vielen anderen
CMSimple_XH-Plugins auch im Administrationsbereich der Website.
Gehen Sie zu `Plugins` → `Fotorama`.

<!--
Sie können die Voreinstellungen von Fotorama_XH unter `Konfiguration` ändern.
Beim Überfahren der Hilfe-Icons mit der Maus
werden Hinweise zu den Einstellungen angezeigt.
-->

Die Lokalisierung wird unter `Sprache` vorgenommen.
Sie können die Sprachtexte in Ihre eigene Sprache übersetzen,
falls keine entsprechende Sprachdatei zur Verfügung steht,
oder diese Ihren Wünschen gemäß anpassen.

Das Aussehen von Fotorama_XH kann unter `Stylesheet` angepasst werden.

## Verwendung

### Vorbereiten einer Galerie

Navigieren Sie zu `Plugins` → `Fotorama` → `Galerien`,
und verwenden Sie das Formular um eine erste Galerie
mit allen Bildern des gewählten Ordners zu erstellen.
Die Galerie wird im `content/` Ordner von CMSimple_XH gespeichert.
Jede Sprache hat ihren eigenen Satz von Galerie-Definitionen,
so dass Sie die Bildbeschriftungen übersetzen können.

Nachdem die Galerie erfolgreich erstellt wurde,
werden Sie zum Galerie-Editor weiter geleitet,
wo Sie die Feinabstimmung der Galerie vornehmen können.
Sie können Bilder hinzufügen und entfernen,
und deren Reihenfolge verändern.

- `width` und `ratio`:
  Werden diese Attribute ausgelassen,
  dann werden Breite und Seitenverhältnis durch das erste Bild bestimmt.
  Beachten Sie, dass die Größe der Bilder angepasst wird,
  so dass diese zu Breite/Seitenverhältnis passen,
  damit es möglich ist, Bilder im Hoch- und Querformat
  in derselben Galerie ohne Verzerrung zu mischen.
- `nav`:
  Die erforderlichen Vorschaubilder werden bei Bedarf automatisch erzeugt,
  und im `cache/` Ordner des Plugins gespeichert.
- `fullscreen`:
  Dies erlaubt dem Besucher in die Vollbildansicht zu wechseln.
  Wählen Sie entweder `true`,
  was die Vollbildansicht auf das Browserfenster beschränkt,
  aber auch in älteren Browsern funktioniert,
  oder `native`, was den gesamten Bildschirm verwendet,
  wenn vom Browser unterstützt.

Wird die Galerie gespeichert, wird sie automatisch gegen das RelaxNG Schema validiert
(`gallery.rng`).

### Externe Bilder

Es ist ebenfalls möglich externe Bilder
(d.h. Bilder außerhalb von Ihrem Bilderordner)
durch Angabe der vollständig qualifizierten URL des Bildes
anzuzeigen.
Wie in diesem Fall üblich ist zu beachten,
dass beispielsweise das Bild nicht verfügbar ist,
und unter Umständen rechtliche Einschränkungen gelten.
Beachten Sie, dass für externe Bilder keine Vorschaubilder generiert werden, 
sondern statt dessen ein Standard-Vorschaubild angezeigt wird,
das Sie durch Ersetzen von `plugins/fotorama/images/external.jpg`
mit einem Bild Ihrer Wahl ändern können.

Sie können externe Bilder und Bilder im Gallerieordner beliebig mischen.

### Einbetten einer Galerie

Um eine Galerie auf einer Seite einzubinden, schreiben Sie einfach:

    {{{fotorama('%NAME%')}}}

wobei `%NAME%` der Name der Galerie ist, z.B.

    {{{fotorama('urlaub')}}}

## Einschränkungen

Damit die Galerien *voll* funktionstüchtig sind,
muss JavaScript im Browser des Besuchers aktiviert sein.

## Fehlerbehebung

Melden Sie Programmfehler und stellen Sie Supportanfragen entweder auf
[Github](https://github.com/cmb69/fotorama_xh/issues)
oder im [CMSimple_XH Forum](https://cmsimpleforum.com/).

## Lizenz

Fotorama_XH ist freie Software. Sie können es unter den Bedingungen
der GNU General Public License, wie von der Free Software Foundation
veröffentlicht, weitergeben und/oder modifizieren, entweder gemäß
Version 3 der Lizenz oder (nach Ihrer Option) jeder späteren Version.

Die Veröffentlichung von Fotorama_XH erfolgt in der Hoffnung, daß es
Ihnen von Nutzen sein wird, aber *ohne irgendeine Garantie*, sogar ohne
die implizite Garantie der *Marktreife* oder der *Verwendbarkeit für einen
bestimmten Zweck*. Details finden Sie in der GNU General Public License.

Sie sollten ein Exemplar der GNU General Public License zusammen mit
Fotorama_XH erhalten haben. Falls nicht, siehe
<https://www.gnu.org/licenses/>.

Copyright 2015-2021 Christoph M. Becker

## Danksagung

Dieses Plugin verwendet [Fotorama](https://fotorama.io/)
zur Anzeige der Galerien.
Vielen Dank an Artem Polikarpov, dem Entwickler dieser Bibliothek,
für seine großartige Arbeit, und für die Veröffentlichung unter MIT-Lizenz.

Das Pluginlogo wurde von [Everaldo Coelho](https://www.everaldo.com/) gestaltet.
Vielen Dank für die Veröffentlichung unter LGPL.
Das Plugin verwendet ebenfalls Icons aus dem
[Oxygen Icon-Set](http://www.oxygen-icons.org/).
Vielen Dank für die Veröffentlichung dieses Icon-Sets unter GPL.

Vielen Dank an die Community im
[CMSimple_XH Forum](https://www.cmsimpleforum.com/)
für Hinweise, Anregungen und das Testen.
Besonders möchte ich *Traktorist* für das frühe und wertvolle
Feedback zur ersten Beta-Version danken.

Und zu guter letzt vielen Dank an
[Peter Harteg](https://www.harteg.dk/), den „Vater“ von CMSimple,
und allen Entwicklern von [CMSimple_XH](https://www.cmsimple-xh.org/de/)
ohne die es dieses phantastische CMS nicht gäbe.
