---
profile: medieninformatik
version: 1.0.0
---

# Projekte auf der MI-Website (mi-project-proxy)

Abschlussarbeiten und Praxisprojekte erscheinen gesammelt auf der Website der Medieninformatik. Gepflegt werden sie aber an verschiedenen Stellen: im **Kirby der Medieninformatik**, in GitHub-Repos der Medieninformatik oder auf den Seiten von Lehrenden.

Der **mi-project-proxy** sammelt diese Daten ein, bringt sie in ein einheitliches Format und stellt sie der Website als eine gemeinsame Liste bereit.

**Der Weg eines Projekts:**
Quelle (JSON) → mi-project-proxy → MI-Website → Detailseite

Die Detailseite liegt je nach Quelle an einer anderen Stelle: Hat die Quelle eine eigene Seite zum Projekt, verlinkt die Website dorthin. Sonst zeigt die MI-Website die Details selbst an.

**Wann sind Änderungen sichtbar?** Der Proxy holt jede Quelle höchstens alle 30 Minuten neu ab. Neue oder geänderte Arbeiten erscheinen auf der MI-Website also mit bis zu 30 Minuten Verzögerung. Ist eine Quelle kurz nicht erreichbar, zeigt die Website so lange den zuletzt abgerufenen Stand.

## Wie kommen meine Arbeiten auf die Website?

Es gibt vier Wege:

| Deine Situation | Weg |
|---|---|
| Deine Arbeiten sind schon auf einer Website oder in einem Repo veröffentlicht. | [Bereits veröffentlichte Arbeiten einbinden](#bereits-veröffentlichte-arbeiten-einbinden) |
| Du arbeitest gern mit einem KI-Agenten (Claude Code, Codex, Cursor, Copilot …). | [Arbeiten mit dem eigenen KI-Agenten einpflegen](#arbeiten-mit-dem-eigenen-ki-agenten-einpflegen) |
| Du willst Arbeiten im Browser pflegen, ohne Git. | [Arbeiten im Kirby pflegen](#arbeiten-im-kirby-pflegen) |
| Es geht um ein Praxisprojekt, das im Praxisprojektseminar vorgestellt wurde. | [Praxisprojekte aus dem Praxisprojektseminar](#praxisprojekte-aus-dem-praxisprojektseminar) |

## Bereits veröffentlichte Arbeiten einbinden

Wenn deine Arbeiten schon auf einer Website oder in einem Repo stehen, können wir sie direkt übernehmen. Sie bleiben dort, wo sie sind, und du pflegst sie weiter wie gewohnt.

**Das wird gebraucht:** ein öffentlich erreichbarer Link zu einer JSON-Datei oder einem JSON-Endpunkt mit deinen Arbeiten. Eine einfache statische Datei reicht völlig aus.

**So läuft es ab:**

1. Du erzeugst die JSON-Datei oder den Endpunkt und schickst den Link an die Ansprechperson (siehe unten).
2. Wir tragen deine Quelle im Proxy ein.
3. Deine Arbeiten erscheinen auf der MI-Website. Hast du eigene Detailseiten, verlinkt die Website dorthin.

**Das Format muss nicht perfekt sein.** Am einfachsten ist es, wenn dein JSON ungefähr zu unserem [Schema](https://github.com/th-koeln/mi-project-proxy/blob/main/thesis-schema.json) passt. Weicht es ab, bauen wir im Proxy einen kleinen Adapter, der deine Felder übersetzt. Du musst deine Daten dafür nicht umbauen.

## Arbeiten mit dem eigenen KI-Agenten einpflegen

Im Repo [mi-project-agentic-feeding](https://github.com/th-koeln/mi-project-agentic-feeding) pflegst du Arbeiten zusammen mit deinem KI-Agenten ein. Du gibst ihm das Material, er zieht die Angaben heraus, fragt fehlende nach und veröffentlicht den Eintrag erst, wenn du ihn bestätigt hast.

**Zugang:** Damit dein Agent Arbeiten veröffentlichen kann, brauchst du Schreibrechte auf das Repo. Schick der Ansprechperson (siehe unten) eine kurze Mail mit deinem GitHub-Benutzernamen, dann bekommst du Zugriff. Alternativ kannst du einen Pull-Request stellen. Schick dann bitte trotzdem kurz eine Nachricht, sonst geht der Pull-Request unter.

**So läuft es ab:**

1. Das Repo klonen und in deiner IDE mit dem Agenten öffnen. 
2. Material zur Arbeit in den Ordner `_inbox/` legen, zum Beispiel PDF, Exposé, Bilder oder Notizen. Dieser Ordner wird nie veröffentlicht.
3. Dem Agenten sagen: **„Neue Arbeit eintragen.“** In Claude Code geht auch `/ingest-thesis`.
4. Fragen beantworten (gern per Diktat), die Zusammenfassung prüfen und bestätigen.
5. Der Agent prüft den Eintrag und pusht ihn. Nach etwa 1–2 Minuten steht die Arbeit im Feed des Repos, auf der MI-Website spätestens 30 Minuten später.

**Gut zu wissen:**

- Der Agent erfindet keine Inhalte. Was nicht im Material steht, fragt er nach.
- Ein Teaserbild ist Pflicht, und nach den Bildrechten fragt der Agent immer. Ohne geklärte Bildrechte wird die Arbeit nicht veröffentlicht.
- Die PDF der Arbeit wird nur veröffentlicht, wenn du das ausdrücklich erlaubst. Das PDF sollte, wenn es veröffentlicht wird, nicht größer als 10 MB sein. 
- Einmalig: Trag deine Git-E-Mail in `supervisors.json` bei deinem Eintrag unter `git_emails` ein. Dann warnt dich der Agent, wenn du eine Arbeit änderst, bei der du nicht als Prüfer*in eingetragen bist.
- Löschen macht der Agent nicht. Um eine Arbeit zu entfernen, löschst du den Ordner `works/<slug>/` selbst und pushst.

Die Detailseite zeigt die MI-Website selbst an. Mehr dazu steht in der [README des Repos](https://github.com/th-koeln/mi-project-agentic-feeding#readme).

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

## Praxisprojekte aus dem Praxisprojektseminar

Die Praxisprojekte, die im Praxisprojektseminar vorgestellt werden, liegen im GitHub-Repo [mi-bachelor-praxisprojektseminar](https://github.com/th-koeln/mi-bachelor-praxisprojektseminar). Von dort kommen sie auf die MI-Website, müssen aber **einzeln freigegeben** werden. Standardmäßig ist kein Projekt freigegeben.

Ein Projekt erscheint nur, wenn alle drei Bedingungen erfüllt sind:

1. **Freigabe:** Im Front Matter der Projektdatei unter `_abschlussvortraege/<datum>/` steht `mi-website: true`. Es muss genau `true` sein, ohne Anführungszeichen.
2. **Bild:** Ein Teaserbild ist eingetragen (`teaserimage`), und die Datei liegt im selben Ordner.
3. **Bildrechte:** Die Rechte am Bild sind angegeben (`quelle-bildrechte-teaser`).

Außerdem müssen Vor- und Nachname (`vorname`, `nachname`) eingetragen sein. Fehlt etwas, erscheint das Projekt trotz Freigabe nicht, und es gibt auch keine Fehlermeldung.

Die Detailseite bleibt auf der Seminar-Website, die MI-Website verlinkt dorthin. Die Freigabe wird wirksam, sobald die Änderung auf `master` liegt und GitHub Pages die Seite neu gebaut hat, auf der MI-Website mit bis zu 30 Minuten Verzögerung. Mehr dazu steht in der [README des Repos](https://github.com/th-koeln/mi-bachelor-praxisprojektseminar#readme).

## Aktuelle Quellen

| Quelle | Inhalt | Detailseite | Was erscheint? |
|---|---|---|---|
| [Kirby der Medieninformatik](https://www.medieninformatik.th-koeln.de/kirby/panel) | im Browser gepflegte Arbeiten | MI-Website | Einträge mit `visibility: published` |
| [mi-project-agentic-feeding](https://github.com/th-koeln/mi-project-agentic-feeding) | mit KI-Agenten eingepflegte Arbeiten | MI-Website | geprüfte Einträge mit `visibility: published` |
| [Praxisprojektseminar](https://github.com/th-koeln/mi-bachelor-praxisprojektseminar) | Praxisprojekte aus dem Seminar | Seminar-Website | nur mit `mi-website: true`, Bild und Bildrechten |
| [Thesis Works](https://cnoss.github.io/thesis/) | Arbeiten aus der Betreuung einer Lehrperson | Thesis Works | alle Einträge der Quelle |

## Doppelte Arbeiten

Der Proxy erkennt keine Dubletten. Steht eine Arbeit in zwei Quellen, muss sie in einer davon herausgenommen werden:

- **Kirby:** Sichtbarkeit zurücksetzen
- **mi-project-agentic-feeding:** den Ordner der Arbeit löschen
- **Praxisprojektseminar:** `mi-website: true` entfernen

## Weitere Informationen

- [mi-project-proxy auf GitHub](https://github.com/th-koeln/mi-project-proxy): Funktionsweise, Konfiguration und Adapter (README)
- [Schema für Arbeiten](https://github.com/th-koeln/mi-project-proxy/blob/main/thesis-schema.json): alle Felder und Pflichtangaben
- [README von mi-project-agentic-feeding](https://github.com/th-koeln/mi-project-agentic-feeding#readme): Arbeiten mit dem KI-Agenten einpflegen
- [README des Praxisprojektseminars](https://github.com/th-koeln/mi-bachelor-praxisprojektseminar#readme): Freigabe von Praxisprojekten

**Ansprechperson:** Christian Noss, christian.noss@th-koeln.de

## Alle Links auf einen Blick

| Was | Link |
|---|---|
| **Website** | |
| MI-Website | https://www.medieninformatik.th-koeln.de |
| **Pflegen** | |
| Kirby-Panel (Anmeldung mit Campus-ID) | https://www.medieninformatik.th-koeln.de/kirby/panel |
| Repo mi-project-agentic-feeding (Pflege mit KI-Agent) | https://github.com/th-koeln/mi-project-agentic-feeding |
| Repo Praxisprojektseminar (Freigabe mit `mi-website: true`) | https://github.com/th-koeln/mi-bachelor-praxisprojektseminar |
| Seminar-Website Praxisprojektseminar | https://th-koeln.github.io/mi-bachelor-praxisprojektseminar/ |
| Thesis Works | https://cnoss.github.io/thesis/ |
| **Proxy und Schema** | |
| Repo mi-project-proxy | https://github.com/th-koeln/mi-project-proxy |
| Schema für Arbeiten | https://github.com/th-koeln/mi-project-proxy/blob/main/thesis-schema.json |
| **JSON-Feeds der Quellen** (zum Nachsehen, was gerade veröffentlicht ist) | |
| Kirby | https://www.medieninformatik.th-koeln.de/kirby/abschlussarbeiten |
| mi-project-agentic-feeding | https://th-koeln.github.io/mi-project-agentic-feeding/index.json |
| Praxisprojektseminar | https://th-koeln.github.io/mi-bachelor-praxisprojektseminar/praxisprojekte.json |
| Thesis Works | https://cnoss.github.io/thesis/works.json |
| **Kontakt** | |
| Christian Noss | christian.noss@th-koeln.de |
