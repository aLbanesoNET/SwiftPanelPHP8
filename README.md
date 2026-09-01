# Swift Panel

A game-server control panel (client area + admin back office), originally written for
PHP 5 / MySQL 5.0 in 2009 and modernised to run on **PHP 8** with `mysqli`.

- Passwords are hashed with bcrypt (legacy SHA1/plain-text accounts upgrade on next login).
- Runs cleanly on PHP 8.0–8.4, MySQL 5.7+ / MariaDB 10.3+.
- Ships with two themes (`default`, `aurora`); switch under **Configuration → General Settings**.

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

## Notes

- `full.sql` is the complete schema **and** seed data — one file, fresh installs only.
- `configuration.php` holds the DB credentials; the bundled `.htaccess` blocks it, every
  `*.sql` and `*.md` file, and `/includes/` from being served. On nginx, replicate those
  denies in the server block.
- Box/server management needs the `ssh2` PHP extension and key or password SSH access to
  each managed machine.
