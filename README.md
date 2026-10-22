# mi-project-proxy

Schlanker PHP-Proxy-Endpunkt, der mehrere heterogene JSON-Quellen (z. B. GitLab APIs, Legacy-Systeme) abfragt, deren Daten über austauschbare Mapper-Klassen in ein einheitliches Normalformat transformiert und als konsolidiertes JSON ausliefert. Über Query-Parameter lässt sich die Ausgabe nach Quellen, Typ und Umfang filtern.

## Start (lokal)

```bash
php -S localhost:8080 -t public
```

Dann:

- `http://localhost:8080/index.php` (alle Sources)
- `http://localhost:8080/index.php?sources=gitlab-main,legacy-system&limit=25`
- `http://localhost:8080/index.php?type=gitlab&limit=50`
- `http://localhost:8080/index.php?raw=1` (inkl. `raw` Feld pro Projekt)

## Sources konfigurieren

Siehe [config/sources.php](config/sources.php).

Jede Source hat:

- `url`: JSON-Endpoint
- `mapper`: Mapper-Klasse
- `detail` (optional, Standard `auto`): wo die Detailseite eines Projekts gerendert wird
  - `auto`: Liefert der Mapper eine Detailseiten-URL, wird extern verlinkt, sonst intern gerendert
  - `internal`: immer auf dem Medieninformatik-Server rendern
  - `external`: immer auf die Quelle verlinken (fehlt die URL, wird intern gerendert)

### Detailseiten im Frontend

Jedes Projekt enthält ein aufgelöstes `detail`-Objekt. `auto` taucht dort nie auf, `mode` ist immer `internal` oder `external`:

```json
"detail": { "mode": "external", "url": "https://cnoss.github.io/thesis/…" }
"detail": { "mode": "internal", "url": null }
```

- `external`: auf `detail.url` verlinken (Detailseite liegt auf dem Source-Server)
- `internal`: eigene Detailseite rendern, identifiziert über `source` + `id`

## Mapper schreiben

Implementiere `Mi\\ProjectProxy\\Mapper\\MapperInterface` und trage die Klasse in [config/sources.php](config/sources.php) ein.

Der Mapper bekommt das bereits decodierte JSON (`mixed`) und gibt eine Liste normalisierter Projekte zurück.

Empfohlenes Normalformat pro Projekt:

- `id` (string|int|null)
- `name` (string)
- `type` (string|null)
- `url` (string|null)
- `detailUrl` (string|null): URL einer HTML-Detailseite auf dem Source-Server (keine PDF o. Ä.); wird vom Proxy in `detail` überführt
- `updatedAt` (string|null)
- `source` (string)
- optional `raw` (mixed/array)
