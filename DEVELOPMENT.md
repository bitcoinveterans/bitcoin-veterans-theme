# Developing this theme

## Requirements

- **Node 22** via nvm: `source ~/.nvm/nvm.sh && nvm use 22`
- Install dependencies (locked): `npm ci`

## Building

SCSS in `src/sass/` and JS in `src/js/custom/` are the source of truth.

```bash
npm run build        # = gulp styles adminStyles customJS
npm run build:css    # main.css + admin-styles.css only
npm run build:js     # theme.min.js only
npm run watch        # gulp watch while developing
```

**Commit the compiled assets** (`assets/css/main.css`, `assets/css/admin-styles.css`, `assets/js/theme.min.js`) together with their sources in the same commit. The deploy has no build step, so whatever is committed is what runs in production. Never hand-edit the compiled files.

## What NOT to run

**Never run:** `gulp build`, `gulp images`, `gulp fonts`, `gulp copy`, `gulp zip`, or the default `gulp` task. They rewrite `assets/img`/`assets/fonts` or create dist zips. `src/img` and `src/fonts` do not exist on purpose: `assets/img` and `assets/fonts` are the source of truth for binaries.

## Never edit theme files on the server

All changes come from this repo. Editing files in production (or saving anything that writes into the theme directory) creates drift that the next deploy silently overwrites. If production ever differs from `master`, fix it here, commit, and deploy — never the other way around.

## ACF field groups

Field groups are versioned as JSON in `acf-json/` (saved/loaded by `inc/acf.php`).

1. Edit the field group on **staging** (wp-admin there).
2. Export/download the group JSON (or copy the file ACF wrote into `acf-json/`).
3. Commit the updated JSON to this repo.

Never edit field groups in production wp-admin: it writes JSON into the live theme directory and creates drift.

## Deploys

- **Production:** push to `master` runs `.github/workflows/deploy.yml` → PHP lint → rsync to the live theme directory (no `--delete`). Merge to master = deploy. Treat merges accordingly.
- **Staging:** `.github/workflows/deploy-staging.yml` runs **manually only**: Actions → "Deploy branch to staging" → pick a ref (or `gh workflow run deploy-staging.yml -f ref=<branch>`). Mirrors one ref at a time to the staging theme directory **with `--delete`**.
- After any production deploy that changes CSS/JS: Hummingbird → Dashboard → **Clear cache**, and Asset Optimization → re-check files / regenerate Critical CSS, then reload the site and confirm the new `?ver=` on `main.css`.

## PHP

There is no local PHP. Lint changed files from the repo with the server one-off (see project docs), or rely on the PR lint workflow (PHP 8.1) and the deploy workflow lint.
