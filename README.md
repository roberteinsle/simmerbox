# Simmerbox

Self-Hosted Rezeptverwaltungs-Webapp. Rezepte speichern, Wochenplaene erstellen, Einkaufslisten generieren und innerhalb eines Haushalts teilen.

## Features

- **Rezepte** - CRUD mit Bild-Upload, Kategorien, Tags, Zutaten und Zubereitungsschritten
- **JSON-Import** - Rezepte via JSON-Struktur importieren
- **Wochenplan** - Ein Rezept pro Tag zuweisen, Wochennavigation, Druckansicht
- **Einkaufslisten** - Auto-Generierung aus dem Wochenplan, manuelle Eintraege, Checkbox-UI
- **Wiederkehrende Einkaeufe** - NLP-basierte Eingabe ("woechentlich am Montag", "alle 2 Wochen")
- **Volltextsuche** - SQLite FTS5 mit BM25-Ranking und Filtern
- **Haushalt-System** - Daten teilen via Einladungscode oder E-Mail
- **Realtime-Sync** - Live-Updates via WebSockets (Laravel Reverb)
- **Admin-Bereich** - Einstellungen, Benutzerverwaltung, Kategorien
- **Berechtigungen** - Konfigurierbar (Alle / Haushalt / Ersteller)
- **Dark Mode** - Umschaltbar, wird im Browser gespeichert
- **Druckansicht** - Print-CSS fuer Wochenplan und Einkaufslisten
- **Komplett auf Deutsch**

## Tech Stack

- **Backend:** Laravel 11 + PHP 8.2
- **Datenbank:** SQLite
- **Frontend:** Blade + Tailwind CSS + Alpine.js
- **Auth:** Laravel Breeze
- **Realtime:** Laravel Reverb (WebSockets)
- **Bilder:** Intervention Image v3 (GD)
- **Deployment:** Docker / Coolify

## Installation (Lokal)

```bash
# Repository klonen
git clone https://github.com/roberteinsle/simmerbox.git
cd simmerbox

# Abhaengigkeiten installieren
composer install
npm install && npm run build

# Umgebung einrichten
cp .env.example .env
php artisan key:generate

# Datenbank erstellen
touch database/database.sqlite
php artisan migrate --seed

# Storage verlinken
php artisan storage:link

# Volltextsuche indexieren
php artisan search:reindex

# Server starten
php artisan serve
```

Die App ist dann unter `http://localhost:8000` erreichbar.

Der erste registrierte Benutzer kann ueber die Datenbank zum Admin gemacht werden:

```bash
php artisan tinker
> App\Models\User::first()->update(['is_admin' => true]);
```

## Installation (Docker)

```bash
# Repository klonen
git clone https://github.com/roberteinsle/simmerbox.git
cd simmerbox

# App-Key generieren (einmalig)
echo "APP_KEY=base64:$(openssl rand -base64 32)" > .env

# Container starten
docker compose up -d --build
```

Die App laeuft unter `http://localhost:8000`.

### Umgebungsvariablen

| Variable | Default | Beschreibung |
|---|---|---|
| `APP_KEY` | - | Laravel App-Key (erforderlich) |
| `APP_URL` | `http://localhost:8000` | Oeffentliche URL |
| `APP_PORT` | `8000` | HTTP-Port |
| `REVERB_PORT` | `8080` | WebSocket-Port |
| `REVERB_HOST` | `localhost` | WebSocket-Host |

### Volumes

- `db-data` - SQLite-Datenbank (persistiert Daten)
- `storage-data` - Uploads (Rezeptbilder)

## Konfiguration

Die meisten Einstellungen koennen ueber den **Admin-Bereich** (Zahnrad-Icon > Admin-Bereich) konfiguriert werden:

- **Allgemein** - App-Name, Beschreibung
- **Authentifizierung** - Registrierung offen oder nur per Einladung
- **Berechtigungen** - Wer darf Rezepte ansehen/bearbeiten/loeschen
- **Wochenplan** - Standard-Portionen, Wochenstart
- **Upload** - Max. Bildgroesse
- **E-Mail** - Absender-Konfiguration

## Wiederkehrende Einkaeufe

Das System versteht deutsche Wiederholungsmuster:

| Eingabe | Ergebnis |
|---|---|
| `Milch, 1L, taeglich` | Jeden Tag |
| `Brot, 1, woechentlich am Montag` | Jeden Montag |
| `Kaese, 200g, alle 2 Wochen am Freitag` | Alle 2 Wochen freitags |
| `Waschmittel, 1, monatlich am 1.` | Am 1. jedes Monats |

## Scheduled Commands

Fuer wiederkehrende Einkaeufe muss der Laravel Scheduler laufen:

```bash
# Crontab (lokal)
* * * * * cd /pfad/zu/simmerbox && php artisan schedule:run >> /dev/null 2>&1
```

Im Docker-Container laeuft der Scheduler automatisch via Supervisor.

## Lizenz

MIT
