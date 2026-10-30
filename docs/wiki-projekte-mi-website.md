# Projekte auf der MI-Website (mi-project-proxy)

Abschlussarbeiten und Praxisprojekte erscheinen gesammelt auf der Website der Medieninformatik. Gepflegt werden sie aber an verschiedenen Stellen: im **Kirby der Medieninformatik**, auf den Seiten von Lehrenden oder in Projekt-Repos.

Der **mi-project-proxy** sammelt diese Daten ein, bringt sie in ein einheitliches Format und stellt sie der Website als eine gemeinsame Liste bereit.

**Der Weg eines Projekts:**
Quelle (JSON) → mi-project-proxy → MI-Website → Detailseite

Die Detailseite liegt je nach Quelle an einer anderen Stelle: Hat die Quelle eine eigene Seite zum Projekt, verlinkt die Website dorthin. Sonst zeigt die MI-Website die Details selbst an.

## Wie kommen meine Arbeiten auf die Website?

Es gibt zwei Wege:

- **Du hast deine Arbeiten schon irgendwo veröffentlicht?** Dann musst du nichts doppelt pflegen. Es reicht ein Link zu einer JSON-Datei (siehe „Bereits veröffentlichte Arbeiten einbinden“).
- **Du hast noch keinen Ort für deine Arbeiten?** Dann pflegst du sie im Kirby (siehe „Arbeiten im Kirby pflegen“).

## Bereits veröffentlichte Arbeiten einbinden

Wenn deine Arbeiten schon auf einer Website oder in einem Repo stehen, können wir sie direkt übernehmen. Sie bleiben dort, wo sie sind, und du pflegst sie weiter wie gewohnt.

**Das wird gebraucht:** ein öffentlich erreichbarer Link zu einer JSON-Datei oder einem JSON-Endpunkt mit deinen Arbeiten. Eine einfache statische Datei reicht völlig aus.

**So läuft es ab:**

1. Du erzeugst die JSON-Datei oder den Endpunkt und schickst den Link an die Ansprechperson (siehe unten).
2. Wir tragen deine Quelle im Proxy ein.
3. Deine Arbeiten erscheinen auf der MI-Website. Hast du eigene Detailseiten, verlinkt die Website dorthin.

**Das Format muss nicht perfekt sein.** Am einfachsten ist es, wenn dein JSON ungefähr zu unserem [Schema](https://github.com/th-koeln/mi-project-proxy/blob/main/thesis-schema.json) passt. Weicht es ab, bauen wir im Proxy einen kleinen Adapter, der deine Felder übersetzt. Du musst deine Daten dafür nicht umbauen.

## Arbeiten im Kirby pflegen

Für die Pflege von Inhalten gibt es eine eigene Kirby-Instanz. Kirby ist ein Content-Management-System: Einträge werden im Browser bearbeitet, im sogenannten Panel. Programmierkenntnisse oder ein Git-Zugang sind dafür nicht nötig.

- **Panel:** https://www.medieninformatik.th-koeln.de/kirby/panel
- **Anmeldung:** mit deiner Campus-ID
- **Heute:** Im Kirby werden Projekte gepflegt, also Abschlussarbeiten und Praxisprojekte.
- **Perspektivisch:** Auch andere Inhalte der Website, zum Beispiel Events und das Blog, könnten dort gepflegt werden.

**Ein Projekt veröffentlichen:**

1. Mit der Campus-ID im Panel anmelden und das Projekt anlegen oder öffnen.
2. Die Angaben ausfüllen, mindestens Titel, Art der Arbeit, Datum, Name, Prüfer*innen, Status und Teaserbild mit Bildrechten. Bei Praxisprojekten ist der Zweitprüfer optional.
3. Die Sichtbarkeit (`visibility`) auf `published` setzen. Erst dann erscheint das Projekt auf der MI-Website.

Die MI-Website zeigt Projekte aus dem Kirby selbst an, inklusive Detailseite.

## Aktuelle Quellen

| Quelle | Inhalt | Detailseite | Was erscheint? |
|---|---|---|---|
| [Kirby der Medieninformatik](https://www.medieninformatik.th-koeln.de/kirby/panel) | zentral gepflegte Arbeiten | MI-Website | Einträge mit `visibility: published` |
| [Thesis Works](https://cnoss.github.io/thesis/) | Arbeiten aus der Betreuung einer Lehrperson | Thesis Works | alle Einträge der Quelle |
| [mi-project-agentic-feeding](https://github.com/th-koeln/mi-project-agentic-feeding) | Feed aus einem Projekt-Repo | MI-Website | Einträge mit `visibility: published` |
| [Praxisprojektseminar](https://github.com/th-koeln/mi-bachelor-praxisprojektseminar) | Praxisprojekte aus dem Seminar | Seminar-Website | nur freigegebene Projekte |

## Gut zu wissen

**Praxisprojekt aus dem Seminar freigeben**

Im Repo des Praxisprojektseminars in der Datei des Projekts unter `_abschlussvortraege/<datum>/` ergänzen:

`mi-website: true`

Das Projekt braucht außerdem Vor- und Nachname, ein vorhandenes Teaserbild und eine Angabe zu den Bildrechten. Fehlt etwas, erscheint es nicht, und es gibt auch keine Fehlermeldung. Standardmäßig ist nichts freigegeben.

**Doppelte Arbeiten**

Der Proxy erkennt keine Dubletten. Steht eine Arbeit in zwei Quellen, wird sie in einer davon herausgenommen: im Kirby über die Sichtbarkeit, beim Praxisprojektseminar, indem man `mi-website: true` entfernt.

## Weitere Informationen

- [mi-project-proxy auf GitHub](https://github.com/th-koeln/mi-project-proxy): Funktionsweise, Konfiguration und Adapter (README)
- [Schema für Arbeiten](https://github.com/th-koeln/mi-project-proxy/blob/main/thesis-schema.json): alle Felder und Pflichtangaben
- [README des Praxisprojektseminars](https://github.com/th-koeln/mi-bachelor-praxisprojektseminar#readme): Freigabe von Praxisprojekten

**Ansprechperson:** Christian Noss, christian.noss@th-koeln.de
