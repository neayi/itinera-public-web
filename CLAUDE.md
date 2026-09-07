# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

The public marketing/landing site for Itinera (itinera.ag), a product by neayi. It is a small
multi-page static site (plain HTML) with a Sass build for CSS and a PHP endpoint for the contact
form. There is no JS bundler, no framework, and no client-side routing — each `.html` file is a
standalone page that duplicates the same `<head>`, nav, and footer markup.

## Commands

```bash
npm install              # install Sass (only dependency)
npm run build            # compile src/styles.scss -> dist/styles.css (with source map)
npm run watch            # rebuild on change while editing styles
npm run prod             # compressed, no source map — use before deploying CSS changes

composer install         # install PHP deps (PHPMailer, phpdotenv) for send-email.php
```

There is no test suite, linter, or type checker configured. `.prettierrc` defines formatting
(4-space indent, double quotes, semicolons, 120 print width) and VS Code is configured to format
HTML/JS/PHP/SCSS with Prettier on save — match that style when hand-editing.

### Deploy

`publish.sh` deploys by SSHing to the `neayi` host and running `git pull && composer install --no-dev`
in `itinera.ag`. There is no CI/build step server-side beyond that — `dist/styles.css` must already
be committed (run `npm run prod` before committing CSS changes).

## Architecture

**Pages**: `index.html`, `pricing.html`, `faq.html` are independent static pages, each with:
- The same `<head>` boilerplate (meta tags, Bootstrap 5.3.2 via CDN, `dist/styles.css`, Matomo
  analytics snippet pointing at `matomo.tripleperformance.fr` site ID 4).
- A `<nav class="sticky-menu" id="stickyMenu">` with a burger menu, and inline `<script>` at the
  bottom of the page handling menu toggle/scroll behavior (duplicated per-page, not shared).
- A HubSpot embed script (portal `5882962`) loaded at the end of `<body>`.

When editing shared elements (nav, footer, head boilerplate, HubSpot/Matomo snippets), the change
must be applied to all three HTML files individually — there is no templating/include mechanism.

**Styling**: `src/styles.scss` is the single Sass source (uses CSS custom properties for the color
palette — `--primary-color`, `--accent-color`, etc. — defined once in `:root` from Sass variables
at the top of the file). It layers custom styles on top of Bootstrap, which is pulled from a CDN
in each HTML file's `<head>` rather than bundled. Compiled output goes to `dist/styles.css` and
*is* checked into git (needed since there's no build step on the server).

**Contact form flow** (`js/contact-form.js` + `send-email.php`):
1. Client-side validation (firstname/lastname/email) in `contact-form.js`.
2. On valid submit, data is POSTed to two places in parallel:
   - HubSpot Forms API (`api.hsforms.com`) directly from the browser — controlled by
     `HUBSPOT_FORM_GUID` in `contact-form.js`; see `HUBSPOT-CONFIG.md` for how to obtain/rotate it.
     If left as `'YOUR_FORM_GUID'` this submission is skipped (logged, non-fatal).
   - `send-email.php` — sends the actual notification email via Brevo SMTP using PHPMailer.
     Config comes from environment variables loaded via `vlucas/phpdotenv` from `.env` (see
     `.env.example` for required keys: `BREVO_SMTP_*`, `CONTACT_EMAIL`). `.env` is gitignored;
     see `SETUP-EMAIL.md` for full setup steps.
3. HubSpot failure is non-blocking; PHP failure surfaces as an alert to the user.

`vendor/` (Composer) is committed-ignored but required at deploy time — `publish.sh` runs
`composer install --no-dev` on the server rather than shipping `vendor/` in git.

## Editing conventions specific to this repo

- French is the primary content language (`lang="fr"`), including copy inside HTML and PHP email
  templates/error messages. Keep new user-facing strings in French unless told otherwise.
- Images live under `images/` (top-level brand assets, plus `images/charte/` and
  `images/features/` subfolders); reference them with relative paths, matching existing usage.
