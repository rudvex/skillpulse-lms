# SkillPulse LMS — Start Task

You are working on **SkillPulse LMS Pro**, a WordPress LMS plugin. Before starting any task, load this context.

## Plugin Architecture

```
skillpulse-lms-pro/
├── skillpulse-lms.php          → Plugin entry point
├── constants.php               → Constants (SKILLPULSE_LMS_DIR_PATH, SKILLPULSE_LMS_URL_PATH)
├── includes/
│   ├── class-main.php          → Main loader (setup_globals + load_classes)
│   ├── functions.php           → Global helper functions (splms_get_setting, etc.)
│   ├── admin/                  → Admin panel (menus, wizard, post types)
│   ├── frontend/               → Frontend (templates, shortcodes)
│   ├── modules/
│   │   ├── core/               → Database, settings, file manager, feature control
│   │   ├── courses/            → Course CRUD, progress, content info
│   │   ├── lessons/            → Lesson completion, progress tracking
│   │   ├── quizzes/            → Quiz system, attempts, questions
│   │   ├── certificates/       → Certificate generation, HTML builder, verification
│   │   ├── enrollment/         → Enrollment management, queries
│   │   ├── notifications/      → Email sender, templates, in-app notifications
│   │   ├── orders/             → Payments, invoices (uses dompdf)
│   │   ├── reviews/            → Course reviews system
│   │   └── license/            → License manager, config
│   └── rest-api/               → REST API controllers (namespace: skillpulse-lms/v1)
├── src/
│   ├── js/
│   │   ├── admin/              → Admin vanilla JS
│   │   ├── frontend/           → Frontend modules (checkout, lessons, quizzes, certificates)
│   │   └── react-core/         → React admin pages (license, settings, certificates, enrollments, etc.)
│   └── scss/                   → Admin + frontend styles
├── assets/                     → Compiled JS/CSS/vendor files
├── templates/                  → PHP templates (course, lesson, quiz, certificate, dashboard)
├── lib/vendor/                 → Strauss-prefixed packages (SkillPulseLMS\Vendor\*)
└── vendor/                     → Raw composer packages (dev only)
```

## Key Technical Details

- **Post Types:** `sp-course`, `sp-section`, `sp-lesson`, `sp-quiz`, `sp-certificate`
- **Taxonomies:** `sp-course-category`, `sp-course-tag`
- **REST Namespace:** `skillpulse-lms/v1`
- **Text Domain:** `skillpulse-lms`
- **Settings:** Nested array via `SkillPulse_LMS_Settings`, accessed with `splms_get_setting('key', default)`
- **Strauss:** Prefixes vendor packages to `SkillPulseLMS\Vendor\*` in `lib/vendor/`. Run via `composer strauss` (uses PHAR).
- **Build:** `npm run build` (JS + SCSS), `npm run js:build:prod` (minified)
- **PHP Standards:** WPCS — Yoda conditions, strict comparisons, single quotes, tabs, comments with periods
- **Certificate PDF:** Client-side via html2canvas + jsPDF (loaded from `assets/vendor/`)
- **Invoice PDF:** Server-side via dompdf (`lib/vendor/dompdf/`)

## WP-CLI Available

Test with: `wp eval "..."` — the dev environment is devilbox at `sp-lms.local.dev`

## Task: $ARGUMENTS

Read the relevant files before making changes. Use `wp eval` to verify backend changes. Run `npm run js:build` after JS changes. Check `wp-content/debug.log` for PHP errors.