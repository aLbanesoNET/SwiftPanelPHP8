# Swift Panel

A game-server control panel (client area + admin back office), originally written for
PHP 5 / MySQL 5.0 in 2009 and modernised to run on **PHP 8** with `mysqli`.

- Client, game-server, box (node) and reseller-style client management
- Web SSH console + read-only file browser for every box, live system stats
- Passwords hashed with bcrypt (legacy SHA1/plain-text accounts upgrade on next login)
- Runs cleanly on PHP 8.0–8.4, MySQL 5.7+ / MariaDB 10.3+
- Three bundled themes — switch under **Configuration → General Settings**

---

## Screenshots

> The **Aurora** theme (dark). A **Bootstrap** and the classic **Default** theme also ship.

### Admin

| | |
|---|---|
| **Dashboard** — clients / servers / boxes at a glance, live activity feed | **Game servers** — status, ownership, start / stop / restart |
| [![Dashboard](docs/screenshots/admin-dashboard.png)](docs/screenshots/admin-dashboard.png) | [![Servers](docs/screenshots/admin-servers.png)](docs/screenshots/admin-servers.png) |
| **Boxes** — SSH / FTP reachability, CPU load & idle from cron | **Manage games** — per-game defaults, executables, query protocol |
| [![Boxes](docs/screenshots/admin-boxes.png)](docs/screenshots/admin-boxes.png) | [![Games](docs/screenshots/admin-games.png)](docs/screenshots/admin-games.png) |
| **Clients** — searchable, paginated, status-coloured | **Activity log** — every action, immutable, `#id` deep-links |
| [![Clients](docs/screenshots/admin-clients.png)](docs/screenshots/admin-clients.png) | [![Activity log](docs/screenshots/admin-activity-log.png)](docs/screenshots/admin-activity-log.png) |

**Box summary** — live system information, a read-only SSH file browser, and an
interactive `screen`-backed console, all on one page:

[![Box summary](docs/screenshots/admin-box-summary.png)](docs/screenshots/admin-box-summary.png)

### Client area

| | |
|---|---|
| **Dashboard** | **My servers** |
| [![Client dashboard](docs/screenshots/client-dashboard.png)](docs/screenshots/client-dashboard.png) | [![Client servers](docs/screenshots/client-servers.png)](docs/screenshots/client-servers.png) |

---

## Quick start

**1 — Install the stack** (Apache + MariaDB + PHP + extensions, enable `.htaccess`, start services):

```bash
sudo apt update && sudo apt install -y apache2 mariadb-server php libapache2-mod-php php-mysql php-ssh2 && sudo sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf && sudo a2enmod rewrite headers && sudo systemctl enable --now apache2 mariadb && sudo systemctl restart apache2
```

**2 — Create the database** (change `CHANGE_THIS`):

```bash
sudo mysql -e "CREATE DATABASE swiftpanel CHARACTER SET utf8mb4; CREATE USER 'swift'@'localhost' IDENTIFIED BY 'CHANGE_THIS'; GRANT ALL ON swiftpanel.* TO 'swift'@'localhost'; FLUSH PRIVILEGES;"
```

**3 — Put the files in the web root:**

```bash
sudo rm -f /var/www/html/index.html && sudo cp -r . /var/www/html/
```

**4 — Enter your DB password** into `configuration.php`:

```bash
sudo nano /var/www/html/configuration.php
```

**5 — Import the schema** (strict SQL mode is relaxed for the import only — the 2009 seed data needs it):

```bash
sudo mysql swiftpanel --init-command="SET SESSION sql_mode=''" < /var/www/html/full.sql
```

**6 — Fix ownership and restart:**

```bash
sudo chown -R www-data:www-data /var/www/html && sudo systemctl restart apache2
```

---

## Default logins

| Area | URL | Login |
|---|---|---|
| Admin | `/admin/` | `admin` / `password` |
| Client | `/` | created by an admin under *Clients → Add New Client* |

Change the admin password immediately under *My Account*.

---

## Themes

Set the active theme under **Configuration → General Settings → Panel Template**.
Each theme is a self-contained folder (with a matching one under `admin/templates/`):

| Folder | Look |
|---|---|
| `bootstrap` | Bootstrap 5.3 + Bootstrap Icons, light. **Shipped default.** |
| `aurora` | Dark, collapsible icon rail, glass panels, animated background (light-mode aware). |
| `default` | The original 2009 look. |

---

## Notes

- `full.sql` is the complete schema **and** seed data — one file, fresh installs only.
- `configuration.php` holds the DB credentials; the bundled `.htaccess` blocks it, every
  `*.sql` and `*.md` file, and `/includes/` from being served. On nginx, replicate those
  denies in the server block.
- Box/server management needs the `ssh2` PHP extension and key or password SSH access to
  each managed machine.
