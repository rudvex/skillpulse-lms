# SkillPulse LMS --- Investigate

Investigate a reported issue, question, or behavior in the SkillPulse LMS Pro plugin. This is a **read-only deep analysis** --- do NOT make any code changes. Your job is to trace, explain, and diagnose.

## Investigation Protocol

Follow ALL phases below. Do not skip any phase.

### Phase 1: Parse the Report

Break down the user's message into:
- **What is reported** --- the symptom, question, or unexpected behavior
- **Where it happens** --- which area (admin, frontend, REST API, specific page/flow)
- **When it happens** --- trigger condition, user action, or timing
- **Who is affected** --- user role (admin, student, instructor, guest)

State your understanding clearly before proceeding. If the report is ambiguous, list your assumptions.

### Phase 2: Locate the Code Path

Trace the **complete execution flow** from trigger to outcome:

1. **Entry point** --- which hook, AJAX handler, REST endpoint, shortcode, or template initiates the flow
2. **Core logic** --- the main function/method that processes the request, including:
   - Which class and method handles it
   - What data it reads (options, post meta, custom tables, transients)
   - What validations and permission checks exist
   - What conditionals/branches the code takes
3. **Data flow** --- trace variables from input to output, noting any transformations
4. **Output** --- how the result is returned (REST response, rendered template, redirect, wp_send_json)

For each file you read, note the **file path and line numbers** of relevant code.

### Phase 3: Identify the Validation Chain

List every validation, guard, and permission check in the flow:

- Nonce verification
- Capability checks (`current_user_can`)
- Data sanitization and type validation
- Business logic guards (enrollment status, course ownership, etc.)
- Early returns and their conditions

For each, state: **what it checks, what happens if it fails, and whether it covers the reported scenario.**

### Phase 4: Diagnose

Based on Phases 2--3, provide:

1. **What is actually happening** --- the exact code path being taken, with file:line references
2. **What is expected** --- the correct behavior based on the code's intent
3. **Root cause** --- if there is a bug, explain exactly what is wrong and why. If the behavior is correct, explain why it works this way
4. **Evidence** --- quote the specific code lines that prove your diagnosis

### Phase 5: Summary Report

Present findings in this format:

```
## Investigation: {brief title}

### Flow Traced
{entry point} -> {handler} -> {logic} -> {output}
Files: file1.php:L10-50, file2.php:L200-230, ...

### Validations Found
- [PASS/FAIL/MISSING] {description} @ file:line

### Diagnosis
{What is happening and why, with code references}

### Root Cause
{The specific issue, or "Behavior is correct because..."}

### Suggested Fix
{What needs to change --- be specific about file, method, and logic. Do NOT implement.}
```

## Key Files by Area

| Area | Key Files |
|------|-----------|
| Plugin loading | `skillpulse-lms.php`, `includes/class-main.php` |
| Settings | `includes/functions.php` (splms_get_setting), `includes/modules/core/class-settings.php` |
| Courses | `includes/modules/courses/`, `includes/rest-api/courses/` |
| Lessons | `includes/modules/lessons/`, `src/js/frontend/modules/lesson-viewer/` |
| Quizzes | `includes/modules/quizzes/`, `src/js/frontend/modules/quizzes/` |
| Certificates | `includes/modules/certificates/`, `templates/certificate/preview.php` |
| Enrollment | `includes/modules/enrollment/`, `includes/rest-api/features/enrollments/` |
| License | `includes/modules/license/`, `src/js/react-core/admin/pages/license/` |
| Notifications | `includes/modules/notifications/` |
| Orders/Payments | `includes/modules/orders/`, `src/js/frontend/modules/checkout/` |
| Reviews | `includes/modules/reviews/`, `includes/rest-api/reviews/` |
| User Management | `includes/modules/user-management/` |
| Admin React | `src/js/react-core/admin/pages/` |
| Frontend JS | `src/js/frontend/modules/` |
| Templates | `templates/` (certificate/, course/, lesson/, quiz/, dashboard/) |
| REST API | `includes/rest-api/` |
| Access Control | `includes/modules/core/class-access-control.php` |
| Database/Queries | `includes/modules/core/base/class-base-query.php`, module query classes |

## WP-CLI Available

Verify state with: `wp eval "..."` --- dev environment is devilbox at `sp-lms.local.dev`

## Investigate: $ARGUMENTS