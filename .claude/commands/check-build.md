# SkillPulse LMS — Check Build

Run a full build check and report any issues.

## Checks to Run

### 1. PHP Syntax Check
```bash
find includes/ templates/ -name "*.php" -exec php -l {} \; 2>&1 | grep -v "No syntax errors"
```

### 2. JavaScript Build
```bash
npm run js:build 2>&1 | tail -15
```

### 3. SCSS Build
```bash
npm run scss:build 2>&1 | tail -5
```

### 4. Plugin Loads Without Errors
```bash
> wp-content/debug.log
wp eval "echo 'Plugin: OK';" 2>&1
cat wp-content/debug.log | head -20
```
Note: debug.log path is `/Users/hardip/Workspace/devilbox/data/www/sp-lms/htdocs/wp-content/debug.log`

### 5. Strauss Vendor Check
```bash
wp eval "
echo class_exists('SkillPulseLMS\Vendor\Dompdf\Dompdf') ? 'Dompdf: OK' : 'Dompdf: FAIL';
echo PHP_EOL;
echo class_exists('SkillPulseLMS\Vendor\Firebase\JWT\JWT') ? 'JWT: OK' : 'JWT: FAIL';
echo PHP_EOL;
"
```

### 6. Key Post Types Registered
```bash
wp post-type list --format=table 2>&1 | grep sp-
```

### 7. Remaining console.log Check
```bash
grep -rc "console\.log(" src/js/ --include="*.js" | grep -v ":0$" | grep -v TinyMCE | grep -v registerPanel
```

Report a summary table: PASS/FAIL for each check with details on any failures.