# Fotorama_XH

Fotorama_XH ermöglicht die Präsentation von Bildergalerien auf einer Website.
Die Galerien sind vollständig responsiv, Vorschaubilder werden automatisch
generiert, und die Galerieverwaltung im Backend bietet eine vollwertige
Nutzerschnittstelle.

- [Voraussetzungen](#voraussetzungen)
- [Installation](#installation)
- [Einstellungen](#einstellungen)
- [Verwendung](#verwendung)
  - [Galerie-Verwaltung](#galerie-verwaltung)
  - [Vorschaubilder](#vorschaubilder)
  - [Externe Bilder](#externe-bilder)
  - [Manuelle Bearbeitung der Galeriedateien](#manuelle-bearbeitung-der-galeriedateien)
- [Einschränkungen](#einschränkungen)
- [Fehlerbehebung](#fehlerbehebung)
- [Lizenz](#lizenz)
- [Danksagung](#danksagung)

## Voraussetzungen

Fotorama_XH ist ein Plugin für [CMSimple_XH](https://cmsimple-xh.org/de/).
Es benötigt PHP ≥ 7.4.0 mit der dom Erweiterung, und CMSimple_XH ≥ 1.7.0.
Um Vorschaubilder zu erzeugen, werden die PHP Erweiterungen gd und exif empfohlen.
Fotorama_XH benötigt weiterhin [Plib_XH](https://github.com/cmb69/plib_xh) ≥ 1.12;
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
   `cache/`, `config/`, `css/` und `languages/`.
1. Prüfen Sie unter `Plugins` → `Fotorama` ob alle Voraussetzungen für den
   Betrieb erfüllt sind.

## Einstellungen

Die Plugin-Konfiguration erfolgt wie bei vielen anderen
CMSimple_XH-Plugins auch im Administrationsbereich der Website.
Gehen Sie zu `Plugins` → `Fotorama`.

Sie können die Voreinstellungen von Fotorama_XH unter `Konfiguration` ändern.
Beim Überfahren der Hilfe-Icons mit der Maus
werden Hinweise zu den Einstellungen angezeigt.

Die Lokalisierung wird unter `Sprache` vorgenommen.
Sie können die Sprachtexte in Ihre eigene Sprache übersetzen,
falls keine entsprechende Sprachdatei zur Verfügung steht,
oder diese Ihren Wünschen gemäß anpassen.

Das Aussehen von Fotorama_XH kann unter `Stylesheet` angepasst werden.
Zu Beginn der Datei befindet sich ein `simple customization` Abschnitt,
der ein paar einfache Anpassungen ermöglicht. Alles darunter benötigt
fortgeschrittene CSS Kenntnisse zur Anpassung.

## Verwendung

Um eine Galerie auf einer Seite einzubinden, schreiben Sie:

    {{{fotorama('name')}}}

wobei `name` durch den den Namen einer Galerie zu ersetzen ist, die zuvor in der
[Galerie-Verwaltung](#galerie-verwaltung) angelegt wurde.

Es können mehrere Galerien auf einer Seite eingebunden werden.

### Galerie-Verwaltung

Die Galerien werden unter `Plugins` → `Fotorama` → `Galerien` verwaltet.
Die Nutzerschnittstelle ist weitgehend selbsterklärend; es gilt: Probieren geht
über Studieren. Ein paar Hinweise sind dennoch angebracht:

* Fotorama ist für mäßig große Bilder (etwa ein paar Mega-Pixel) optimiert.
  Das Laden von sehr großen Bildern kann für Besucher mit einer eher langsamen
  Internetverbindung zu lange dauern; das Betrachten kleiner Bilder auf großen
  Bildschirmen oder Retina-Geräten ist unangenehm.
  Unterstützt Ihr Server WebP oder gar AVIF (das wird in der Systemprüfung
  angezeigt), sollten sie erwägen diese Formate zu verwenden, da sie bessere
  Kompression als JPEG bieten, und heutzutage weithin unterstützt werden
  (allerdings nicht so universell wie JPEG).

* Nur Bilder innerhalb des Bilderordners von CMSimple_XH werden voll unterstützt;
  während im Backend Vorschaubilder für Bilder ausserhalb des Bilderordners
  angezeigt werden, werden die Bilder in den eigentlichen Galerien nicht angezeigt.

* Das Ordnen der Bilder kann per Tastatur oder Drag and Drop erfolgen; die
  Vorschaubilder können fokusiert werden, und dann mit den Pfeiltasten verschoben
  werden; oder sie können an die gewünschte Stelle gezogen werden.

* `Details ausblenden` zeigt die Bilder in einer kompakten Darstellung,
  die besonders zum Ordnen und dem Hinzufügen mehrerer Bilder geeignet ist.

* Während die `Beschriftung`en angezeigt werden, dienen die `Beschreibung`en als
  alt Attribute der Bilder, was eine wichtige Information für sehbehinderte
  Nutzer ist. Daher sollten Sie erwägen `Beschreibung`en hinzuzufügen, die die
  Bilder tatsächlich beschreiben (so als würden sie das Bild am Telefon
  beschreiben).

### Vorschaubilder

Das Plugin erzeugt Vorschaubilder der Bilder, immer wenn die Galerie im Backend
gespeichert wird. Diese werden unter `plugins/fotorama/cache/` abgelegt; die
Ordnerstruktur spiegelt die Ordnerstruktur des Bilderordners von CMSimple_XH.
Die Vorschaubilder werden in mehreren Größen erzeugt, so dass zeitgemäße Browser
die geeignetste Größe auswählen können. Normalerweise müssen Sie sich nicht um
den Vorschaubilder-Cache kümmern; nur wenn sie mit vielen Galerien/Bilder
experimentieren, aber später entscheiden, diese nicht zu veröffentlichen,
können Sie den Cache löschen (entweder im Backend oder per FTP) um etwas
Speicherplatz zu sparen. Danach sollten sie alle noch exisitierenden Galerien
speichern, damit die nötigen Vorschaubilder erzeugt werden.

Es ist zu beachten, dass die Vorschaubilddateien für jedermann zugänglich sind.
Sind die Originalbilder im Bilderordner von CMSimple_XH geschützt, dann sollten
sie den selben Schutz auch auf den Vorschaubilder-Cache anwenden.

Es ist weiterhin zu beachten, dass die Vorschaubilder *keine* Bild-Metadaten
enthalten (außer ICC Farbprofilen), die möglicherweise in den Originalbildern
gespeichert sind.

### Externe Bilder

Es ist ebenfalls möglich externe Bilder (d.h. Bilder außerhalb von Ihrem
Bilderordner) durch Angabe der vollständig qualifizierten URL des Bildes
zu verwenden.
Wie in diesem Fall üblich ist zu beachten, dass möglicherweise das Bild nicht
verfügbar ist, und dass das Bild vom Browser des Besucher geladen wird, was
möglicherweise datenschutzrechtlich bedenklich ist.
Es ist zu beachten, dass aus rechtlichen Gründen für externe Bilder keine
Vorschaubilder generiert werden, sondern statt dessen ein Standard-Vorschaubild
angezeigt wird, das Sie durch Ersetzen von `plugins/fotorama/images/external.svg`
mit einem Bild Ihrer Wahl ändern können.

Sie können externe und interne Bilder in einer Galerie beliebig mischen.

Es ist allerdings zu beachten, dass externe Bilder aus den oben genannten
Gründen am besten vermieden werden.

### Manuelle Bearbeitung der Galeriedateien

Die Galerien werden im `content/` Ordner von CMSimple_XH gespeichert.

Werden Galeriedateien manuell bearbeitet, wird empfohlen einen Editor mit
Unterstützung von RelaxNG-Schemata zu verwenden, und gegen `gallery.rng` im Wurzelordner
des Plugins zu validieren. Wird das nicht getan, kann es passieren, dass die
Galerien nicht geladen werden können. In diesem Fall kann der `Prüfen` Schalter
in der Pluginverwaltung genutzt werden, um herauszufinden wo der Fehler liegt.

Es ist zu beachten, dass es nach der manuellen Bearbeitung einer Galeriedatei
sinnvoll ist, die Galerie erneut im Backend zu speichern, da dann die Bildgrößen
aktualisiert werden, und ebenso die benötigten Vorschaubilder erzeugt werden.

## Einschränkungen

Damit die Galerien *voll* funktionstüchtig sind, wird ein zeitgemäßer Browser
mit JavaScript-Unterstützung benötigt.

Ist die PHP exif Erweiterung nicht verfügbar, werden Vorschaubilder von Bildern
mit Exif `Orientation` Markern nicht korrekt angezeigt (sie sind dann rotiert
oder seitenverkehrt).

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

Dieses Plugin verwendet [SimpleLightbox](https://simplelightbox.js.org/) zur Anzeige der Galerien.
Vielen Dank an Andre Rinas, dem Entwickler dieser Bibliothek,
für seine großartige Arbeit, und für die Veröffentlichung unter MIT-Lizenz.

Dieses Plugin verwendet [Fotorama](https://fotorama.io/) zur Anzeige der Galerien.
Vielen Dank an Artem Polikarpov, dem Entwickler dieser Bibliothek,
für seine großartige Arbeit, und für die Veröffentlichung unter MIT-Lizenz.

Das Pluginlogo wurde von [Everaldo Coelho](https://www.everaldo.com/) gestaltet.
Vielen Dank für die Veröffentlichung unter LGPL.

[`external.svg`](https://commons.wikimedia.org/w/index.php?curid=112311856)
wurde von Pigeon43 gestaltet. Vielen Dank für die Veröffentlichung unter CC BY-SA 4.0.

Vielen Dank an [Jeffrey Friedl](https://regex.info/blog/photo-tech/color-spaces-page2)
für die schöne Demonstration der Wirkung von eingebetten ICC-Farbprofilen.

Vielen Dank an die Community im
[CMSimple_XH Forum](https://www.cmsimpleforum.com/)
für Hinweise, Anregungen und das Testen.
Besonders möchte ich *Traktorist* für das frühe und wertvolle
Feedback zur ersten Beta-Version danken.

Und zu guter letzt vielen Dank an
[Peter Harteg](https://www.harteg.dk/), den „Vater“ von CMSimple,
und allen Entwicklern von [CMSimple_XH](https://www.cmsimple-xh.org/de/)
ohne die es dieses phantastische CMS nicht gäbe.
