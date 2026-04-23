# SkillPulse LMS — Debug

Debug an issue in the SkillPulse LMS Pro plugin. Follow this systematic process.

## Step 1: Collect Evidence

Run these commands to gather the current error state:

```bash
# Check PHP errors
tail -50 /Users/hardip/Workspace/devilbox/data/www/sp-lms/htdocs/wp-content/debug.log

# Check plugin is active and loading
wp plugin list --status=active --fields=name,version

# Check PHP syntax of recently modified files
find includes/ templates/ -name "*.php" -newer CLAUDE.md -exec php -l {} \;

# Check JS build status
npm run js:build 2>&1 | tail -5
```

## Step 2: Identify the Error Type

### PHP Fatal / Warning / Notice
- Read the file and line from the error
- Check the stack trace to understand the call chain
- Look for: missing classes, undefined functions, type mismatches, null access

### JavaScript Build Error
- Check webpack output for the exact file and line
- Look for: missing imports, deleted files still imported, syntax errors

### REST API Error
- Test the endpoint: `wp eval "var_dump(rest_do_request(new WP_REST_Request('GET', '/skillpulse-lms/v1/endpoint')));"` 
- Check permission callbacks and nonce verification

### Frontend Display Issue
- Check if the template is loading: `wp eval "echo splms_is_certificate_page() ? 'YES' : 'NO';"`
- Check template loader in `includes/frontend/core/class-template.php`
- Verify enqueued scripts: check the page source for `splms` scripts

### Strauss / Vendor Issue
- Check prefixed class exists: `wp eval "echo class_exists('SkillPulseLMS\Vendor\Dompdf\Dompdf') ? 'OK' : 'FAIL';"`
- Run `composer strauss` to regenerate `lib/vendor/`
- Check `lib/vendor/autoload.php` exists and is loaded

## Step 3: Key Files by Area

| Area | Key Files |
|------|-----------|
| Plugin loading | `skillpulse-lms.php`, `includes/class-main.php` |
| Settings | `includes/functions.php` (splms_get_setting), settings store |
| Courses | `includes/modules/courses/`, `includes/rest-api/courses/` |
| Lessons | `includes/modules/lessons/`, `src/js/frontend/modules/lesson-viewer/` |
| Quizzes | `includes/modules/quizzes/`, `src/js/frontend/modules/quizzes/` |
| Certificates | `includes/modules/certificates/`, `templates/certificate/preview.php` |
| Enrollment | `includes/modules/enrollment/`, `includes/rest-api/features/enrollments/` |
| License | `includes/modules/license/`, `src/js/react-core/admin/pages/license/` |
| Notifications | `includes/modules/notifications/` |
| Orders/Payments | `includes/modules/orders/`, `src/js/frontend/modules/checkout/` |
| Admin React | `src/js/react-core/admin/pages/` (each page has store/, components/) |
| Frontend JS | `src/js/frontend/modules/` |
| Templates | `templates/` (certificate/, course/, lesson/, quiz/, dashboard/) |
| REST API | `includes/rest-api/` (each resource has its own controller) |
| Build | `webpack.config.js`, `package.json`, `composer.json` |
| Strauss | `composer.json` (extra.strauss), `lib/vendor/`, `bin/strauss.phar` |

## Step 4: Common Fixes

### "Class not found" in lib/vendor
```bash
composer strauss  # Regenerates lib/vendor/ from vendor/ via PHAR
```

### "foreach() argument must be of type array"
Check `includes/functions.php` splms_get_setting — settings can have scalar values mixed with arrays.

### JS import error after file deletion
Search for imports of the deleted file: `grep -rn "deleted-file-name" src/js/`

### Certificate PDF not generating
Certificate PDF is client-side (html2canvas + jsPDF in `assets/vendor/`). Check browser console, not PHP logs.

### Rewrite rules not working (404 on certificate/quiz URLs)
```bash
wp rewrite flush
```

## Issue: $ARGUMENTS

Start by reading the debug log, then trace the error to its source file.