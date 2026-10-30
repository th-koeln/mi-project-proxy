# AGENTS.md

Der Proxy sammelt Projekte aus mehreren JSON-Quellen und liefert sie der MI-Website als eine Liste im Normalformat. Das Frontend liegt nicht in diesem Repo.

Architektur, Parameter und aktuelle Quellen: [README.md](README.md). Text für Lehrende: [docs/wiki-projekte-mi-website.md](docs/wiki-projekte-mi-website.md).

## Leitlinien

**Die Quelle bleibt, wie sie ist.** Weicht ein Format ab, übersetzt ein Adapter (im Code: Mapper). Quellen liefern nur einen öffentlich erreichbaren JSON-Link, Lehrende sollen dafür nichts umbauen. `ThesisSchemaMapper` deckt alles ab, was ungefähr [thesis-schema.json](thesis-schema.json) entspricht; neue Mapper nur für wirklich andere Formate.

**Kuratiert wird in der Quelle.** Was erscheint, entscheidet die Redaktion dort: `visibility` im Kirby, `mi-website: true` im Praxisprojektseminar. Auch Dubletten werden dort bereinigt, der Proxy bleibt frei von Abgleichslogik. Ein Abgleich über das Datum würde ohnehin scheitern, weil Quellen für dieselbe Arbeit verschiedene Daten führen (z. B. Vortragstermin und Abgabe).

**Rechte vor Vollständigkeit.** Praxisprojekte ohne Rechteangabe zum Bild, ohne nutzbares Bild oder ohne Namen werden gar nicht gezeigt, auch nicht ohne Bild. Das ist eine Entscheidung der Projektleitung, das Seminar-Template setzt sie um.

**Der Detail-Modus wird an genau einer Stelle aufgelöst.** Mapper liefern nur `detailUrl`, und zwar ausschließlich echte HTML-Detailseiten (nie PDF, Repo oder Demo). `ProxyService::withDetail()` macht daraus `detail: {mode, url}`. In der Ausgabe ist `mode` immer `internal` oder `external`, damit das Frontend keine Regeln pro Quelle braucht. `auto` ist der Standard in der Config.

**Lieber kein Link als ein falscher.** Relative Pfade ohne `baseUrl` werden zu `null`, `external` ohne URL wird zu `internal`.

**Das Schema bleibt klein.** Es hat `additionalProperties: false`. Felder, die ein Feed wirklich liefert, kommen als optional dazu, Pflichtfelder nur nach Rücksprache. Der Zweitprüfer ist nur bei Bachelor- und Masterarbeiten Pflicht.

## Neue Quelle anbinden

1. Feed abrufen und mit dem Schema vergleichen: Hülle, Felder, Detailseiten. Fertig, wenn du weißt, ob ein vorhandener Mapper passt und ob die Quelle eigene Detailseiten hat.
2. Mapper wählen oder schreiben, ggf. eine neue Hülle im `ThesisSchemaMapper` ergänzen.
3. Eintrag in `config/sources.php`, aber erst, wenn der Feed live ist. Sonst meldet der Proxy `errors` und `meta.ok: false`.
4. Gegen den echten Feed prüfen, das Repo hat keine Testsuite: `php -S localhost:8080 -t public`, dann `?sources=<name>&limit=500`. Fertig, wenn `meta.ok` `true` ist, `count` der erwarteten Zahl entspricht und bei jedem Projekt `detail.mode` stimmt und Bild- und Detail-URLs existieren.
5. Quellentabellen in README und Wiki-Text aktualisieren.

## Fallstricke

- **Stille Leere:** Kennt `ThesisSchemaMapper` die Hülle nicht (bekannt: `works`, `projects`, `data`), liefert er 0 Projekte ohne Fehler. Deshalb `count` prüfen.
- **Keine Validierung zur Laufzeit:** Mapper prüfen das Schema nicht. Feeds gegen `thesis-schema.json` mit einem Validator für Draft 2020-12 prüfen.
- **Limit:** Ohne Parameter kommen höchstens 50 Projekte (Maximum 500), unsortiert und abgeschnitten nach dem Zusammenführen. Spätere Sources fallen also zuerst weg.
- **IDs:** Kirby liefert keine `id`, der Mapper baut sie aus Name und Datum. Eine Korrektur daran ändert die Adresse interner Detailseiten. Für `internal` fehlt außerdem ein Abruf einzelner Projekte, das Frontend sucht in der Liste.
- **`type`:** Die Werte sind nicht einheitlich (`Bachelorarbeit` im Schema, Fallback `thesis` bei Thesis Works).
- **GitHub Pages baut mit Jekyll 3.10:** Feeds aus Jekyll-Repos mit dieser Version testen. Dort fehlen z. B. Collection-Dateien in `site.static_files`, unter Jekyll 4 nicht.

## Zusammenarbeit

- Rückfragen einzeln stellen, jeweils mit Empfehlung.
- Commit und Push nur auf Anfrage. 
- Doku, Kommentare und Commit-Nachrichten auf deutsch.
