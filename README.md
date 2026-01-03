# Simple Comic (Portable WordPress wp-content)

This repo contains the **portable** parts of the Simple Comic WordPress site — primarily `wp-content/`.
It intentionally **does not** include WordPress core or environment secrets.

## What’s in this repo

- `wp-content/themes/simple-comic/` (the theme)
- `wp-content/mu-plugins/` (optional; site-wide must-use tweaks)
- `wp-content/plugins/` (optional; only include if you want plugins tracked in Git)

## What is NOT in this repo (by design)

- WordPress core (`/wp-admin`, `/wp-includes`, etc.)
- `wp-config.php` and other environment-specific config
- uploads/media: `wp-content/uploads/`
- caches, logs, backups

This keeps the repo safe, light, and deployable to multiple environments.

---

## Requirements

- PHP: match production (HostGator shared). Use the same major version locally when possible.
- MySQL/MariaDB
- WordPress installed separately (local + production)
- This repo deployed into the site’s WordPress install as `wp-content/`

---

## Local setup (quick)

1) Create a local WordPress install:
- Any local stack works (LocalWP, MAMP, XAMPP, etc.)

2) Clone this repo and put `wp-content/` in place:
- Your local WP folder should end up like:
  - `public_html/` (or whatever your local docroot is)
    - `wp-admin/`
    - `wp-includes/`
    - `wp-content/`  <- from this repo

3) Database:
- Import a `.sql` backup of the site DB, or create a fresh DB and install WP.

4) Set the theme:
- WP Admin → Appearance → Themes → activate `Simple Comic`

5) Uploads:
- Copy `wp-content/uploads/` from a production backup if needed.
  - Uploads are intentionally not tracked in Git.

---

## Production deploy (HostGator shared)

### One-time server prep
- Make sure you have SSH/SFTP access.
- WordPress core must already be installed on the server.

### Deploy steps
1) On the server, go to the WordPress install root (the folder containing `wp-admin/`).
2) If this repo is the source of truth for `wp-content/`, either:
   - Replace the server’s `wp-content/` with the repo’s `wp-content/`, or
   - Keep `wp-content/` and only update the theme folder inside it.

Recommended approach (safer):
- Deploy only the theme:
  - `wp-content/themes/simple-comic/`

If you deploy the entire `wp-content/`, be careful not to overwrite:
- `wp-content/uploads/` (media)
- any host-specific cache configs

### Typical “theme-only” deploy
- Upload/rsync the theme folder:
  - `wp-content/themes/simple-comic/`

### Typical “repo is wp-content” deploy
- Upload/rsync:
  - `wp-content/themes/simple-comic/`
  - `wp-content/mu-plugins/` (if used)
  - `wp-content/plugins/` (only if you track plugins here)

Then clear caches (if any caching plugin is enabled).

---

## Config / secrets policy

Do not commit:
- database credentials
- salts
- API keys
- mail credentials
- any `.env` files

If you need environment portability, prefer one of these patterns:

1) Keep `wp-config.php` on each environment and do not track it in Git.
2) Optional: create a small “config loader” in `wp-config.php` that conditionally includes
   `wp-config.local.php` or `wp-config.prod.php` (also ignored by Git).

---

## Repo conventions

- `main` branch: production-ready
- `dev` branch: active development

Commit messages: short and specific, e.g.
- `Fix header layout on photo template`
- `Harden login redirect for staff role`
- `Add mu-plugin for forced HTTPS behind proxy`

---

## Backup policy (recommended)

- Database: export SQL before major changes
- Uploads: periodic zip of `wp-content/uploads/`
- Keep backups out of Git

---

## Notes / TODO

- Decide whether plugins are managed in Git or via WP Admin updates.
- Add a `mu-plugin` for small production hardening (optional).
