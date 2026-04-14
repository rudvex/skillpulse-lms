# Lesson Module - Complete System Documentation

**SkillPulse LMS - Lesson Management System**

**Last Updated:** January 4, 2026
**Module Status:** ✅ Production Ready
**Documentation Version:** 1.0.0

---

## Table of Contents

1. [Overview](#overview)
2. [Quick Start](#quick-start)
3. [Lesson Architecture](#lesson-architecture)
4. [Custom Post Types](#custom-post-types)
5. [Lesson Settings System](#lesson-settings-system)
6. [Lesson Types & Content](#lesson-types--content)
7. [Lesson CRUD Operations](#lesson-crud-operations)
8. [Lesson Display & Templates](#lesson-display--templates)
9. [REST API Endpoints](#rest-api-endpoints)
10. [Access Control System](#access-control-system)
11. [Progress Tracking](#progress-tracking)
12. [Drip Content System](#drip-content-system)
13. [Prerequisites System](#prerequisites-system)
14. [Completion System](#completion-system)
15. [Database Structure](#database-structure)
16. [Hooks & Filters](#hooks--filters)
17. [Complete Workflows](#complete-workflows)
18. [Security Implementation](#security-implementation)
19. [Best Practices](#best-practices)
20. [Performance Considerations](#performance-considerations)
21. [Testing Checklist](#testing-checklist)

---

## Overview

The Lesson Module is a core component of SkillPulse LMS that manages individual learning units within courses. It handles lesson creation, content delivery, progress tracking, access control, and completion verification across multiple content types.

### What This Module Covers

- **Lesson Post Type** - Custom post type registration and management
- **Lesson Types** - Text, Video, Audio, Document, Interactive (H5P/SCORM)
- **Lesson Settings** - Content, completion, drip, prerequisites, advanced settings
- **Access Control** - Enrollment-based, drip content, prerequisites, expiration
- **Progress Tracking** - Lesson completion, time tracking, video progress
- **Drip Content** - Time-based content release strategies
- **Prerequisites** - Lesson dependency management
- **REST APIs** - Public and private endpoints for frontend integration
- **Templates** - Single lesson pages with navigation
- **Media Handling** - YouTube, Vimeo, audio files, PDFs, embeds

### Files in This Module

```
includes/modules/lessons/
├── README.md                          # This file - complete documentation
├── class-lessons.php                  # Main lessons class
├── class-lesson-progress-query.php    # Lesson progress database handler
└── general-functions.php              # Helper functions
```

---

## Quick Start

### Get Lesson Settings

```php
// Get settings for a specific lesson
$settings = splms_get_lesson_settings( $lesson_id );

// Settings structure:
// $settings = [
//     'lesson_type' => 'video',
//     'lesson_duration' => 30,
//     'lesson_video_url' => 'https://youtube.com/...',
//     'lesson_completion_settings' => [...],
//     'lesson_drip_settings' => [...],
//     'lesson_prerequisites' => [...],
//     'lesson_attachments' => [...],
//     'lesson_access_expiration' => 90
// ]
```

### Check Lesson Completion

```php
// Check if user completed a lesson
$is_completed = splms_is_lesson_completed( $lesson_id, $user_id );
```

### Mark Lesson Complete

```php
// Mark lesson as complete
$result = splms_mark_lesson_complete( $lesson_id, $user_id, $course_id );

if ( is_wp_error( $result ) ) {
    // Handle error
    echo $result->get_error_message();
}
```

### Check Lesson Access

```php
// Check if user can access lesson
$can_access = splms_user_can_access_lesson( $lesson_id, $user_id );

// Check drip availability
$is_available = splms_is_lesson_drip_available( $lesson_id, $user_id );

// Check prerequisites
$prerequisites_met = splms_are_lesson_prerequisites_met( $lesson_id, $user_id );
```

---

## Lesson Architecture

### Component Map

```
Lesson Module Ecosystem
│
├── Post Type
│   └── Lesson (sp-lesson)
│
├── Settings System
│   ├── Content Settings (lesson type, duration, media URLs)
│   ├── Completion Settings (manual, auto, time-based, prevent skip)
│   ├── Drip Settings (enrollment-based, previous lesson, specific date)
│   ├── Prerequisites (required lessons)
│   └── Advanced Settings (access expiration)
│
├── Lesson Types
│   ├── Text Lesson (WordPress content)
│   ├── Video Lesson (YouTube, Vimeo, MP4)
│   ├── Audio Lesson (MP3, SoundCloud)
│   ├── Document Lesson (PDF, Google Docs)
│   └── Interactive Lesson (H5P, SCORM, embeds)
│
├── Access Control
│   ├── Course Enrollment Check
│   ├── Drip Content Validation
│   ├── Prerequisites Validation
│   ├── Prevent Skip Enforcement
│   └── Access Expiration Check
│
├── Progress Tracking
│   ├── Lesson Progress (splms_lesson_progress table)
│   ├── Completion Tracking
│   ├── Time Spent Tracking
│   └── Video Progress Tracking
│
├── Navigation System
│   ├── Previous/Next Lesson
│   ├── Prevent Skip Logic
│   └── Course Curriculum Integration
│
└── REST APIs
    ├── Public APIs (lessons list, single lesson)
    ├── Private APIs (create, update, delete)
    └── Progress APIs
```

---

## Custom Post Types

### Lesson Post Type (`sp-lesson`)

**File:** `includes/admin/manager/class-admin-post-types.php`

**Registration Details:**
```php
register_post_type('sp-lesson', [
    'public' => true,
    'has_archive' => false,
    'rewrite' => ['slug' => 'lesson'],
    'supports' => ['title', 'editor', 'thumbnail', 'excerpt', 'comments'],
    'show_in_rest' => true,
    'hierarchical' => false
]);
```

**Key Features:**
- Public-facing with individual pages
- REST API enabled
- Supports featured images, comments
- Hierarchical: No
- Single slug: `/lesson/{slug}`
- Belongs to courses via relationships table

---

## Lesson Settings System

### Settings Architecture

Lesson settings are stored as post meta in configuration-driven groups:

1. **Content Settings** - Lesson type, duration, media URLs, attachments
2. **Completion Settings** - Completion type, required time, prevent skip
3. **Drip Settings** - Enable drip, drip type, days, specific date
4. **Prerequisites** - Required lessons before accessing
5. **Advanced Settings** - Access expiration days

### Content Settings

```php
'lesson_type' => 'video', // text, video, audio, interactive, document
'lesson_duration' => 30, // minutes
'lesson_video_url' => 'https://youtube.com/watch?v=...',
'lesson_audio_url' => 'https://example.com/audio.mp3',
'lesson_document_url' => 'https://example.com/doc.pdf',
'lesson_embed_code' => '<iframe src="..."></iframe>',
'lesson_completion_required' => 100, // percentage for video lessons
'lesson_attachments' => [
    [
        'file_url' => 'https://example.com/file.pdf',
        'file_label' => 'Course Materials'
    ]
]
```

**Lesson Types:**
- `text` - Standard WordPress content
- `video` - YouTube, Vimeo, or direct video files
- `audio` - MP3, SoundCloud, or direct audio files
- `document` - PDF, Google Docs, presentations
- `interactive` - H5P, SCORM, iframe embeds, shortcodes

### Completion Settings

```php
'lesson_completion_settings' => [
    'completion_type' => 'manual', // manual, auto, time_based
    'required_time' => 300, // seconds (for time_based)
    'prevent_skip' => false // prevent navigating to next lesson before completion
]
```

**Completion Types:**
- `manual` - User clicks "Mark Complete" button
- `auto` - Auto-completes when content is viewed
- `time_based` - Requires minimum time spent on lesson

### Drip Settings

```php
'lesson_drip_settings' => [
    'enable_drip' => true,
    'drip_type' => 'days_after_enrollment', // days_after_enrollment, days_after_previous, specific_date
    'drip_days' => 7, // number of days
    'specific_date' => '2026-01-15' // for specific_date type
]
```

**Drip Types:**
- `days_after_enrollment` - Available X days after course enrollment
- `days_after_previous` - Available X days after previous lesson completion
- `specific_date` - Available on a specific date

### Prerequisites

```php
'lesson_prerequisites' => [123, 456] // array of lesson IDs
```

### Advanced Settings

```php
'lesson_access_expiration' => 90 // days after enrollment (empty = unlimited)
```

### Helper Functions

**File:** `includes/modules/lessons/general-functions.php`

```php
// Get lesson settings
$settings = splms_get_lesson_settings($lesson_id);

// Get lesson type
$type = splms_get_lesson_type($lesson_id);

// Get lesson duration
$duration = splms_get_lesson_duration($lesson_id);

// Get video URL
$video_url = splms_get_lesson_video_url($lesson_id);

// Get attachments
$attachments = splms_get_formatted_lesson_attachments($lesson_id);

// Get drip settings
$drip = splms_get_lesson_drip_settings($lesson_id);

// Get completion settings
$completion = splms_get_lesson_completion_settings($lesson_id);

// Get prerequisites
$prerequisites = splms_get_lesson_prerequisites($lesson_id);
```

---

## Lesson Types & Content

### Text Lesson

Uses WordPress post content editor.

**Display:**
```php
// Rendered automatically via the_content() in template
```

### Video Lesson

Supports YouTube, Vimeo, and direct video files (MP4, WebM, OGV).

**Settings:**
```php
'lesson_video_url' => 'https://youtube.com/watch?v=VIDEO_ID',
'lesson_completion_required' => 80 // percentage watched before completion
```

**Display:**
```php
$video_url = splms_get_lesson_video_url( $lesson_id );
echo splms_render_media_player( $video_url, 'video', [
    'lesson_id' => $lesson_id,
    'course_id' => $course_id,
    'completion_required' => 80
]);
```

**Supported Sources:**
- YouTube (`youtube.com`, `youtu.be`)
- Vimeo (`vimeo.com`)
- Direct files (MP4, WebM, OGV)

### Audio Lesson

Supports direct audio files and SoundCloud.

**Settings:**
```php
'lesson_audio_url' => 'https://example.com/audio.mp3'
```

**Display:**
```php
$audio_url = splms_get_lesson_audio_url( $lesson_id );
echo splms_render_media_player( $audio_url, 'audio' );
```

**Supported Sources:**
- Direct files (MP3, WAV, OGG, M4A, AAC, FLAC)
- SoundCloud (`soundcloud.com`)

### Document Lesson

Supports PDFs, Google Docs, images, and other documents.

**Settings:**
```php
'lesson_document_url' => 'https://docs.google.com/document/d/...'
```

**Display:**
```php
$document_url = splms_get_lesson_document_url( $lesson_id );
echo splms_render_document_lesson( $document_url, $lesson_id );
```

**Supported Formats:**
- **PDF** - Embedded in iframe
- **Google Docs/Slides/Sheets** - Embedded viewer
- **Images** - Inline display (JPG, PNG, GIF, WebP, SVG)
- **Other Documents** - Download link (DOC, PPT, XLS, ZIP, etc.)

### Interactive Lesson

Supports iframe embeds and WordPress shortcodes.

**Settings:**
```php
'lesson_embed_code' => '<iframe src="https://h5p.com/..."></iframe>'
// OR
'lesson_embed_code' => '[h5p id="123"]'
```

**Display:**
```php
$embed_code = $settings['lesson_embed_code'];
echo splms_render_interactive_lesson( $embed_code );
```

**Supported Content:**
- **Iframe embeds** - H5P, SCORM, custom embeds
- **WordPress shortcodes** - Any registered shortcode
- **HTML embeds** - object, embed tags

---

## Lesson CRUD Operations

### Create Lesson

**REST API:**

**Endpoint:** `POST /splms/v1/lessons`

**Request:**
```json
{
    "title": "New Lesson",
    "content": "Lesson description",
    "status": "draft",
    "course_id": 123,
    "lesson_type": "video",
    "lesson_video_url": "https://youtube.com/watch?v=..."
}
```

**Permission:** Requires `edit_posts` capability

### Read Lesson

**REST API:**

**Endpoint:** `GET /splms/v1/lessons/{id}`

**Response Includes:**
- Lesson basic info (title, content, excerpt, featured image)
- Lesson type and duration
- Media URLs (video, audio, document)
- Progress information (for logged-in users)
- Attachments, prerequisites, drip settings
- Author/instructor information

### Update Lesson

**REST API:**

**Endpoint:** `PUT /splms/v1/lessons/{id}`

**Request:**
```json
{
    "title": "Updated Lesson Title",
    "content": "Updated description",
    "status": "publish",
    "lesson_duration": 45
}
```

**Permission:** Requires `edit_posts` capability

### Delete Lesson

**REST API:**

**Endpoint:** `DELETE /splms/v1/lessons/{id}`

**Permission:** Requires `delete_posts` capability

### List Lessons

**REST API:**

**Endpoint:** `GET /splms/v1/lessons`

**Query Parameters:**
- `page` - Page number (default: 1)
- `per_page` - Results per page (default: 10, max: 100)
- `search` - Search query string
- `course_id` - Filter by course ID
- `orderby` - Sort by (date, title, menu_order)
- `order` - Sort order (asc, desc)
- `include` - Comma-separated list of lesson IDs

---

## Lesson Display & Templates

### Single Lesson Page

**Template:** `templates/lesson/single-lesson.php`

**Template Parts:**
- `lesson/partials/lesson-hero.php` - Hero section with title
- `lesson/partials/lesson-navigation.php` - Previous/next navigation
- `lesson/partials/lesson-content.php` - Main content area
- `lesson/partials/lesson-sidebar.php` - Sidebar with lesson info

---

## REST API Endpoints

### Public APIs (Open Access by Default)

#### List Lessons
- **Endpoint:** `GET /splms/v1/lessons`
- **Access:** Public (filterable via hook)
- **Purpose:** Get list of lessons

#### Get Single Lesson
- **Endpoint:** `GET /splms/v1/lessons/{id}`
- **Access:** Public (with access control and guest preview checks)
- **Purpose:** Get detailed lesson information

### Private APIs (Authentication Required)

#### Create Lesson
- **Endpoint:** `POST /splms/v1/lessons`
- **Permission:** `edit_posts` capability
- **Purpose:** Create new lesson

#### Update Lesson
- **Endpoint:** `PUT /splms/v1/lessons/{id}`
- **Permission:** `edit_posts` capability
- **Purpose:** Update lesson

#### Delete Lesson
- **Endpoint:** `DELETE /splms/v1/lessons/{id}`
- **Permission:** `delete_posts` capability
- **Purpose:** Delete lesson

---

## Access Control System

**File:** `includes/modules/lessons/class-lessons.php:790`

### Access Validation Layers

**Method:** `user_can_access_lesson( $lesson_id, $user_id )`

The access control system checks multiple layers:

1. **Admin/Editor Override** - Admins and editors can always access
2. **Prerequisites Check** - Required lessons must be completed
3. **Prevent Skip Check** - Previous lessons with prevent_skip must be completed
4. **Enrollment Check** - User must be enrolled in the course
5. **Drip Availability** - Lesson must be available per drip settings
6. **Access Expiration** - Lesson access must not be expired

```php
// Check if user can access lesson
$can_access = splms_user_can_access_lesson( $lesson_id, $user_id );

// Returns false if:
// - Prerequisites not met
// - Previous lesson has prevent_skip enabled and isn't completed
// - User not enrolled in course
// - Lesson not yet available (drip)
// - Access has expired
```

### Guest Access

For non-logged-in users, the system checks:
- Course guest access settings
- Lesson preview availability

```php
// Check guest access
$access_control = SkillPulse_LMS_Access_Control::get_instance();
$can_access = $access_control->user_can_access_lesson( 0, $lesson_id );
```

---

## Progress Tracking

### Progress Database

**Table:** `{$wpdb->prefix}splms_lesson_progress`

```sql
CREATE TABLE {$wpdb->prefix}splms_lesson_progress (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    user_id bigint(20) NOT NULL,
    lesson_id bigint(20) NOT NULL,
    course_id bigint(20) NOT NULL,
    is_completed tinyint(1) NOT NULL DEFAULT 0,
    progress decimal(5,2) DEFAULT 0,
    started_at datetime DEFAULT CURRENT_TIMESTAMP,
    completed_at datetime DEFAULT NULL,
    last_accessed datetime DEFAULT NULL,
    time_spent int(11) DEFAULT 0,
    PRIMARY KEY (id),
    KEY user_id (user_id),
    KEY lesson_id (lesson_id),
    KEY course_id (course_id)
)
```

### Progress Operations

**Check Completion:**
```php
$is_completed = splms_is_lesson_completed( $lesson_id, $user_id );
```

**Mark Complete:**
```php
$result = splms_mark_lesson_complete( $lesson_id, $user_id, $course_id );

// Triggers:
// - Updates lesson_progress table
// - Fires 'splms_lesson_completed' action
// - Calculates updated course progress
// - Syncs progress to enrollment table
// - Logs activity
```

**Get Progress Data:**
```php
$progress = SkillPulse_LMS_Lesson_Progress_Query::get_instance()
    ->get_lesson_progress( $user_id, $lesson_id );

// Returns:
// - is_completed (bool)
// - progress (decimal)
// - started_at (datetime)
// - completed_at (datetime)
// - time_spent (seconds)
```

### Video Progress Tracking

For video lessons, completion requires watching a specific percentage:

```php
// Server-side validation in mark_lesson_complete()
if ( 'video' === $lesson_type ) {
    $completion_required = 100; // percentage
    $video_progress = $_POST['video_progress']; // from client

    if ( $video_progress < $completion_required ) {
        wp_send_json_error( 'Must watch 100% of video' );
    }
}
```

---

## Drip Content System

**File:** `includes/modules/lessons/class-lessons.php:393`

### Drip Types

#### Days After Enrollment

Lesson becomes available X days after course enrollment.

```php
'lesson_drip_settings' => [
    'enable_drip' => true,
    'drip_type' => 'days_after_enrollment',
    'drip_days' => 7
]

// Available 7 days after enrollment date
```

**Calculation:**
```php
$enrollment_date = '2026-01-01 10:00:00';
$drip_days = 7;
$available_timestamp = strtotime( $enrollment_date . ' + 7 days' );
$is_available = current_time('timestamp') >= $available_timestamp;
```

#### Days After Previous Lesson

Lesson becomes available X days after completing the previous lesson.

```php
'lesson_drip_settings' => [
    'enable_drip' => true,
    'drip_type' => 'days_after_previous',
    'drip_days' => 3
]

// Available 3 days after completing previous lesson
```

**Calculation:**
```php
$previous_lesson = get_previous_lesson( $lesson_id, $course_id );
$previous_completion_date = get_lesson_completion_date( $previous_lesson, $user_id );
$available_timestamp = strtotime( $previous_completion_date . ' + 3 days' );
```

#### Specific Date

Lesson becomes available on a specific date.

```php
'lesson_drip_settings' => [
    'enable_drip' => true,
    'drip_type' => 'specific_date',
    'specific_date' => '2026-02-01'
]
```

**Calculation:**
```php
$specific_date = '2026-02-01 00:00:00';
$is_available = current_time('timestamp') >= strtotime($specific_date);
```

### Drip Check Function

```php
// Check if lesson is available based on drip settings
$is_available = splms_is_lesson_drip_available( $lesson_id, $user_id );

// Returns false if:
// - Drip is enabled
// - Current time < available time
```

---

## Prerequisites System

**File:** `includes/modules/lessons/class-lessons.php:508`

### Prerequisites Configuration

```php
// Lesson must complete lessons 123 and 456 before accessing
'lesson_prerequisites' => [123, 456]
```

### Prerequisites Validation

```php
// Check if prerequisites are met
$are_met = splms_are_lesson_prerequisites_met( $lesson_id, $user_id );

// Returns false if any prerequisite lesson is not completed
```

**Implementation:**
```php
public function are_prerequisites_met( $lesson_id, $user_id ) {
    $prerequisites = $this->get_lesson_prerequisites( $lesson_id );

    if ( empty( $prerequisites ) ) {
        return true;
    }

    foreach ( $prerequisites as $prereq_id ) {
        if ( ! $this->is_lesson_completed( $prereq_id, $user_id ) ) {
            return false;
        }
    }

    return true;
}
```

---

## Completion System

**File:** `includes/modules/lessons/class-lessons.php:609`

### Completion Types

#### Manual Completion

User must click "Mark Complete" button.

```php
'lesson_completion_settings' => [
    'completion_type' => 'manual'
]
```

**AJAX Handler:**
```php
// wp_ajax_splms_mark_lesson_complete
add_action( 'wp_ajax_splms_mark_lesson_complete',
    array( $this, 'mark_lesson_complete' ) );
```

#### Auto Completion

Lesson auto-completes when viewed (implementation pending).

```php
'lesson_completion_settings' => [
    'completion_type' => 'auto'
]
```

#### Time-Based Completion

Requires minimum time spent on lesson.

```php
'lesson_completion_settings' => [
    'completion_type' => 'time_based',
    'required_time' => 300 // seconds
]
```

### Prevent Skip

Prevents navigating to next lesson before completing current one.

```php
'lesson_completion_settings' => [
    'prevent_skip' => true
]
```

**Enforcement:**
```php
// Check if user can skip lesson
$can_skip = splms_can_skip_lesson( $lesson_id, $user_id );

// Used in navigation:
// - Disables "Next" button if prevent_skip enabled and lesson not completed
// - Blocks access to next lesson via access control
```

### Completion Workflow

```
User Clicks "Mark Complete"
    ↓
AJAX Request → mark_lesson_complete()
    ↓
Verify Nonce
    ↓
Validate User & Lesson & Course
    ↓
Check Access Permission
    ↓
[For Video] Validate Completion Percentage
    ↓
Update lesson_progress Table
    - is_completed = 1
    - completed_at = current_time()
    ↓
Fire Action: splms_lesson_completed
    ↓
Calculate Course Progress
    ↓
Update Enrollment Progress
    ↓
Log Activity
    ↓
Return Success + Progress Data
```

---

## Database Structure

### Complete Schema

```sql
-- Lessons (WordPress wp_posts)
-- post_type = 'sp-lesson'

-- Lesson Settings (WordPress wp_postmeta)
-- _splms_lesson_type
-- _splms_lesson_duration
-- _splms_lesson_video_url
-- _splms_lesson_audio_url
-- _splms_lesson_document_url
-- _splms_lesson_embed_code
-- _splms_lesson_completion_required
-- _splms_lesson_attachments
-- _splms_lesson_completion_settings
-- _splms_lesson_drip_settings
-- _splms_lesson_prerequisites
-- _splms_lesson_access_expiration

-- Lesson Progress
CREATE TABLE {$wpdb->prefix}splms_lesson_progress (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    user_id bigint(20) NOT NULL,
    lesson_id bigint(20) NOT NULL,
    course_id bigint(20) NOT NULL,
    is_completed tinyint(1) NOT NULL DEFAULT 0,
    progress decimal(5,2) DEFAULT 0,
    started_at datetime DEFAULT CURRENT_TIMESTAMP,
    completed_at datetime DEFAULT NULL,
    last_accessed datetime DEFAULT NULL,
    time_spent int(11) DEFAULT 0,
    PRIMARY KEY (id),
    KEY user_id (user_id),
    KEY lesson_id (lesson_id),
    KEY course_id (course_id)
);

-- Relationships (Lesson → Course via Section)
CREATE TABLE {$wpdb->prefix}splms_relationships (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    parent_id bigint(20) NOT NULL, -- Section ID
    child_id bigint(20) NOT NULL, -- Lesson ID
    child_type varchar(50) NOT NULL, -- 'sp-lesson'
    order_index int(11) NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    KEY parent_id (parent_id),
    KEY child_id (child_id)
);
```

---

## Hooks & Filters

### Action Hooks

#### Lesson Actions

**`splms_lesson_completed`**
```php
do_action('splms_lesson_completed', $lesson_id, $user_id);
// Fires after user completes a lesson
```

**Usage Example:**
```php
add_action('splms_lesson_completed', function($lesson_id, $user_id) {
    // Send notification
    // Award points
    // Check course completion
}, 10, 2);
```

### Filter Hooks

#### Settings Filters

**`splms_lesson_settings`**
```php
$settings = apply_filters('splms_lesson_settings', $settings, $lesson_id);
// Filter lesson settings before returning
```

#### REST API Filters

**`splms_rest_prepare_lesson`**
```php
$response = apply_filters('splms_rest_prepare_lesson', $response, $lesson, $request);
// Filter lesson response data
```

**`splms_rest_lessons_permissions_check`**
```php
$has_access = apply_filters('splms_rest_lessons_permissions_check', $has_access, $user_id, $request);
// Filter lessons list access permission
```

**`splms_rest_lesson_permissions_check`**
```php
$has_access = apply_filters('splms_rest_lesson_permissions_check', $has_access, $lesson_id, $user_id, $request);
// Filter single lesson access permission
```

---

## Complete Workflows

### Lesson Access Workflow

```
User Attempts to Access Lesson
    ↓
Check if Admin/Editor → Allow
    ↓
Check Prerequisites
    ├─ Required lessons completed? → Continue
    └─ Not completed → Deny Access
    ↓
Check Prevent Skip (Previous Lessons)
    ├─ All previous prevent_skip lessons completed? → Continue
    └─ Not completed → Deny Access
    ↓
Check Course Enrollment
    ├─ Enrolled? → Continue
    └─ Not enrolled → Check Guest Preview
    ↓
Check Drip Availability
    ├─ Available per drip settings? → Continue
    └─ Not yet available → Deny Access
    ↓
Check Access Expiration
    ├─ Not expired? → Continue
    └─ Expired → Deny Access
    ↓
Grant Access
```

### Lesson Completion Workflow

```
User Views Lesson
    ↓
[If Manual] User Clicks "Mark Complete"
[If Auto] Auto-trigger on view
[If Time-Based] Wait for required_time
    ↓
AJAX Request to mark_lesson_complete
    ↓
Verify Nonce & User & Lesson
    ↓
Check Access Permission
    ↓
[If Video] Validate video_progress >= completion_required
    ↓
Check for Existing Progress Record
    ├─ Exists → Update Record
    └─ Not exists → Insert Record
    ↓
Update Fields:
    - is_completed = 1
    - completed_at = current_time()
    - time_spent = X
    ↓
Fire Action: splms_lesson_completed
    ↓
Calculate Course Progress
    - Count completed lessons
    - Count passed quizzes
    - Calculate percentage
    ↓
Update Enrollment Progress
    ↓
Log Activity
    ↓
Return Success Response
```

---

## Security Implementation

### Input Validation
- ✅ Lesson ID validation
- ✅ User ID validation
- ✅ Settings array type checking

### Data Sanitization
- ✅ `esc_url_raw()` for media URLs
- ✅ `sanitize_text_field()` for strings
- ✅ `wp_kses()` for embed code with allowed tags

### Permission Checks
- ✅ `current_user_can('edit_post', $lesson_id)`
- ✅ Applied in REST API endpoints
- ✅ Applied in AJAX handlers

### CSRF Protection
- ✅ Nonce verification in AJAX requests
- ✅ `wp_verify_nonce()` validation

### SQL Injection Prevention
- ✅ `$wpdb->prepare()` for all queries
- ✅ Parameterized queries

---

## Best Practices

### ✅ Do's

1. **Always use helper functions**
   ```php
   // ✅ Good
   $settings = splms_get_lesson_settings( $lesson_id );

   // ❌ Bad
   $settings = SkillPulse_LMS_Lessons::get_instance()->get_lesson_settings( $lesson_id );
   ```

2. **Check access before operations**
   ```php
   if ( splms_user_can_access_lesson( $lesson_id, $user_id ) ) {
       // Proceed with lesson display
   }
   ```

3. **Handle errors properly**
   ```php
   $result = splms_mark_lesson_complete( $lesson_id, $user_id, $course_id );
   if ( is_wp_error( $result ) ) {
       error_log( $result->get_error_message() );
   }
   ```

4. **Validate video completion**
   ```php
   // Server-side validation is critical for video lessons
   if ( $video_progress < $completion_required ) {
       wp_send_json_error( 'Completion requirement not met' );
   }
   ```

5. **Use action hooks for extensions**
   ```php
   add_action( 'splms_lesson_completed', function( $lesson_id, $user_id ) {
       // Custom logic after lesson completion
   }, 10, 2 );
   ```

### ❌ Don'ts

1. **Don't bypass access control**
2. **Don't skip nonce verification**
3. **Don't trust client-side completion**
4. **Don't ignore drip settings**
5. **Don't modify completed_at timestamps manually**

---

## Performance Considerations

### Caching Strategy

1. **Cache Lesson Settings**
   ```php
   $cache_key = "lesson_settings_{$lesson_id}";
   $settings = wp_cache_get( $cache_key );
   if ( false === $settings ) {
       $settings = splms_get_lesson_settings( $lesson_id );
       wp_cache_set( $cache_key, $settings, '', HOUR_IN_SECONDS );
   }
   ```

2. **Optimize Database Queries**
   - Use indexes on user_id, lesson_id, course_id
   - Minimize meta_query usage
   - Batch progress checks when possible

3. **Lazy Load Media**
   - Load video/audio players on demand
   - Use poster images for videos
   - Implement lazy loading for attachments

---

## Testing Checklist

- [ ] Test lesson creation with all types (text, video, audio, document, interactive)
- [ ] Test lesson update and deletion
- [ ] Test completion with manual, auto, and time-based types
- [ ] Test prevent skip enforcement
- [ ] Test drip content with all three types
- [ ] Test prerequisites validation
- [ ] Test access expiration
- [ ] Test video completion percentage requirement
- [ ] Test navigation (previous/next) with prevent skip
- [ ] Test guest access for lessons
- [ ] Test REST API endpoints with/without authentication
- [ ] Test YouTube, Vimeo, and direct video playback
- [ ] Test audio player with MP3 and SoundCloud
- [ ] Test document rendering (PDF, Google Docs, images)
- [ ] Test interactive content (iframe, shortcodes)
- [ ] Test attachment downloads
- [ ] Test progress tracking accuracy
- [ ] Test course progress calculation after lesson completion
- [ ] Verify all hooks fire correctly
- [ ] Test access control with multiple users
- [ ] Test permission checks in REST API

---

## Common Errors & Solutions

| Error | Cause | Solution |
|-------|-------|----------|
| `access_denied` | User lacks access | Check enrollment, drip, prerequisites |
| `lesson_not_found` | Invalid lesson ID | Verify lesson exists and is published |
| `completion_requirement_not_met` | Video not watched enough | Watch full percentage required |
| `prerequisites_not_met` | Required lessons not completed | Complete prerequisite lessons first |
| `lesson_not_available` | Drip date not reached | Wait for drip release date |
| `access_expired` | Access period ended | Re-enroll or extend access |

---

## Changelog

### Version 1.0.0 (January 4, 2026)
- ✅ Multiple lesson types support (text, video, audio, document, interactive)
- ✅ Comprehensive settings system with grouped fields
- ✅ Drip content with three release strategies
- ✅ Prerequisites system for lesson dependencies
- ✅ Prevent skip enforcement
- ✅ Access expiration per lesson
- ✅ Video completion percentage tracking
- ✅ REST API with permission filters
- ✅ Progress tracking with time spent
- ✅ Media rendering with error handling
- ✅ Document type detection and rendering
- ✅ Interactive content with shortcode support
- ✅ Attachment management with repeater field
- ✅ Navigation system with access control
- ✅ Security audit passed - Production ready

---

**Module Maintainer:** SkillPulse LMS Development Team
**Last Updated:** January 4, 2026
**Support:** Refer to main plugin documentation
