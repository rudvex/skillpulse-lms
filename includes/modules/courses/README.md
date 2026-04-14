# Course Module - Complete System Documentation

**SkillPulse LMS - Course Management System**

**Last Updated:** January 3, 2026
**Module Status:** ✅ Production Ready
**Documentation Version:** 1.0.0

---

## Table of Contents

1. [Overview](#overview)
2. [Quick Start](#quick-start)
3. [Course Architecture](#course-architecture)
4. [Custom Post Types & Taxonomies](#custom-post-types--taxonomies)
5. [Course Settings System](#course-settings-system)
6. [Course Curriculum System](#course-curriculum-system)
7. [Course CRUD Operations](#course-crud-operations)
8. [Course Display & Templates](#course-display--templates)
9. [REST API Endpoints](#rest-api-endpoints)
10. [Access Control System](#access-control-system)
11. [Enrollment System](#enrollment-system)
12. [Progress Tracking](#progress-tracking)
13. [Database Structure](#database-structure)
14. [Hooks & Filters](#hooks--filters)
15. [Complete Workflows](#complete-workflows)
16. [Security Implementation](#security-implementation)
17. [Best Practices](#best-practices)
18. [Performance Considerations](#performance-considerations)
19. [Testing Checklist](#testing-checklist)

---

## Overview

The Course Module is the core of SkillPulse LMS. It manages the complete lifecycle of courses from creation to completion, including curriculum management, enrollment, access control, progress tracking, and payments.

### What This Module Covers

- **Course Post Type** - Custom post type registration and management
- **Course Settings** - Access, pricing, content, scheduling, completion settings
- **Curriculum** - Sections, lessons, quizzes hierarchical structure
- **Access Control** - Public free/paid, invitation-only, prerequisite-based
- **Enrollment** - User enrollment, capacity limits, expiration
- **Progress Tracking** - Lesson completion, quiz scores, course completion
- **REST APIs** - Public and private endpoints for frontend integration
- **Templates** - Archive listing, single course pages
- **Payments** - Integration with PayPal, Stripe, Razorpay

### Files in This Module

```
includes/modules/courses/
├── README.md                          # This file - complete documentation
├── class-courses.php                  # Main courses class
├── class-course-items-query.php       # Course items query handler
├── class-relationships-query.php      # Course relationships handler
└── general-functions.php              # Helper functions
```

---

## Quick Start

### Get Course Settings

```php
// Get settings for a specific course
$settings = splms_get_course_settings( $course_id );

// Settings structure:
// $settings = [
//     'course_access_settings' => [...],
//     'course_pricing_settings' => [...],
//     'course_content_settings' => [...],
//     'course_scheduling_settings' => [...],
//     'course_completion_settings' => [...]
// ]
```

### Update Course Settings

```php
// Prepare new settings (partial updates supported)
$new_settings = [
    'course_pricing_settings' => [
        'course_price' => 99.00,
        'course_discount' => 10
    ]
];

// Update settings (includes permission check)
$result = splms_update_course_settings( $course_id, $new_settings );

if ( is_wp_error( $result ) ) {
    // Handle error
    echo $result->get_error_message();
} else {
    // Success - $result contains updated settings
    $updated_settings = $result;
}
```

### Get Course Access Information

```php
$access_info = splms_get_course_access_info( $course_id, $user_id );
// Returns access type, price, enrollment dates, etc.
```

### Check Enrollment Status

```php
$is_enrolled = splms_is_user_enrolled( $course_id, $user_id );
```

---

## Course Architecture

### Component Map

```
Course Module Ecosystem
│
├── Post Types
│   ├── Course (sp-course)
│   ├── Section (sp-section)
│   ├── Lesson (sp-lesson)
│   └── Quiz (sp-quiz)
│
├── Taxonomies
│   ├── Course Categories (sp-course-category)
│   └── Course Tags (sp-course-tag)
│
├── Settings System
│   ├── Access Settings
│   ├── Pricing Settings
│   ├── Content Settings
│   ├── Scheduling Settings
│   └── Completion Settings
│
├── Curriculum System
│   ├── Course Items (splms_course_items table)
│   ├── Relationships (splms_relationships table)
│   └── Hierarchical Structure (Course → Section → Lesson/Quiz)
│
├── Access Control
│   ├── Guest Access Rules
│   ├── Enrollment Validation
│   ├── Membership Requirements
│   └── Prerequisite Validation
│
├── Enrollment System
│   ├── Enrollment Records (splms_enrollments table)
│   ├── Enrollment Dates
│   ├── Capacity Management
│   └── Auto-Expiration
│
├── Progress Tracking
│   ├── Lesson Progress (splms_lesson_progress table)
│   ├── Quiz Attempts (splms_quiz_attempts table)
│   └── Course Completion
│
├── Payment System
│   ├── Orders (splms_orders table)
│   ├── Payment Gateways (PayPal, Stripe, Razorpay)
│   └── Purchase Verification
│
└── REST APIs
    ├── Public APIs (courses list, single course)
    ├── Private APIs (settings, curriculum, actions)
    └── Enrollment & Purchase APIs
```

---

## Custom Post Types & Taxonomies

### Course Post Type (`sp-course`)

**File:** `includes/admin/manager/class-admin-post-types.php:109`

**Registration Details:**
```php
register_post_type('sp-course', [
    'public' => true,
    'has_archive' => 'courses',
    'rewrite' => ['slug' => 'course'],
    'supports' => ['title', 'editor', 'thumbnail', 'excerpt', 'comments'],
    'show_in_rest' => true,
    'taxonomies' => ['sp-course-category', 'sp-course-tag']
]);
```

**Key Features:**
- Public-facing with archive page
- REST API enabled
- Supports featured images, comments
- Hierarchical: No
- Archive slug: `/courses`
- Single slug: `/course/{slug}`

### Section Post Type (`sp-section`)

**Purpose:** Container for organizing lessons and quizzes within a course

**Key Features:**
- Public but not shown in menus
- Rewrite slug: `/section`
- Supports: title, editor, thumbnail, excerpt

### Lesson Post Type (`sp-lesson`)

**Purpose:** Individual learning content units

**Key Features:**
- Public with individual pages
- Supports comments
- Rewrite slug: `/lesson`
- Trackable for progress

### Quiz Post Type (`sp-quiz`)

**Purpose:** Assessments within courses

**Key Features:**
- Public with individual pages
- Supports comments
- Rewrite slug: `/quiz`
- Stores attempts and scores

### Course Categories (`sp-course-category`)

**Type:** Hierarchical taxonomy

**Features:**
- Hierarchical (like categories)
- Show in REST
- Archive pages: `/course-category/{slug}`
- Used for course filtering and organization

### Course Tags (`sp-course-tag`)

**Type:** Non-hierarchical taxonomy

**Features:**
- Non-hierarchical (like tags)
- Show in REST
- Archive pages: `/course-tag/{slug}`
- Used for course discovery

---

## Course Settings System

### Settings Architecture

Course settings are stored as post meta in 5 grouped arrays:

1. **`_splms_course_access_settings`** - Access control and pricing
2. **`_splms_course_pricing_settings`** - Price and discounts
3. **`_splms_course_content_settings`** - Content and difficulty
4. **`_splms_course_scheduling_settings`** - Dates and delivery
5. **`_splms_course_completion_settings`** - Completion and certificates

### Access Settings

```php
'course_access_settings' => [
    'course_access_type' => 'public_free', // public_free, public_paid, invitation_only, prerequisite_required
    'invited_users' => [],
    'prerequisite_course' => '',
    'required_memberships' => [],
    'restrict_course_for_guests' => false
]
```

**Access Types:**
- `public_free` - Anyone can enroll
- `public_paid` - Must purchase to enroll
- `invitation_only` - Only invited users can access
- `prerequisite_required` - Must complete prerequisite course first

### Pricing Settings

```php
'course_pricing_settings' => [
    'course_price' => 0,
    'course_discount_type' => 'percentage', // percentage, fixed
    'course_discount' => 0
]
```

### Content Settings

```php
'course_content_settings' => [
    'difficulty_level' => 'beginner', // beginner, intermediate, advanced
    'course_duration_value' => 1,
    'course_duration_unit' => 'months', // days, weeks, months, years
    'learning_outcomes' => [],
    'prerequisites_description' => '',
    'course_language' => 'en',
    'course_level' => 'beginner',
    'learning_method' => 'text' // text, video, audio, mixed
]
```

### Scheduling Settings

```php
'course_scheduling_settings' => [
    'enrollment_start_date' => '',
    'enrollment_end_date' => '',
    'course_delivery' => 'self_paced', // self_paced, live, cohort
    'live_class_schedule' => '',
    'course_start_date' => '',
    'max_enrollment' => 0,
    'enable_enrollment_expiration' => false,
    'enrollment_expiration_days' => 0,
    'auto_expire_enrollments' => true
]
```

### Completion Settings

```php
'course_completion_settings' => [
    'certificate_enabled' => false,
    'certificate_template_id' => '',
    'completion_criteria' => 'all_lessons', // all_lessons, passing_grade
    'passing_grade' => 70
]
```

### Helper Functions

**File:** `includes/modules/courses/general-functions.php`

```php
// Get all settings
$settings = splms_get_course_settings($course_id);

// Update settings (with permission check)
$result = splms_update_course_settings($course_id, $new_settings);

// Get access information
$access_info = splms_get_course_access_info($course_id, $user_id);

// Get delivery information
$delivery_info = splms_get_course_delivery_info($course_id);

// Get content information
$content_info = splms_get_course_content_info($course_id);

// Get enrollment dates
$enrollment_dates = splms_get_course_enrollment_dates($course_id);

// Format dates consistently
$formatted_date = splms_format_date($date, $format);
```

---

## Course Curriculum System

### Database Structure

**Table:** `{$wpdb->prefix}splms_course_items`

```sql
CREATE TABLE {$wpdb->prefix}splms_course_items (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    course_id bigint(20) NOT NULL,
    item_id bigint(20) NOT NULL,
    item_type varchar(50) NOT NULL,
    order_index int(11) NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    KEY course_id (course_id),
    KEY item_id (item_id)
)
```

**Table:** `{$wpdb->prefix}splms_relationships`

```sql
CREATE TABLE {$wpdb->prefix}splms_relationships (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    parent_id bigint(20) NOT NULL,
    child_id bigint(20) NOT NULL,
    child_type varchar(50) NOT NULL,
    order_index int(11) NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    KEY parent_id (parent_id),
    KEY child_id (child_id)
)
```

### Hierarchical Structure

```
Course (Post)
 ├── splms_course_items (links course to sections)
 │
 ├── Section 1 (Post)
 │   ├── splms_relationships (links section to lessons/quizzes)
 │   ├── Lesson 1-1 (Post)
 │   ├── Lesson 1-2 (Post)
 │   └── Quiz 1-1 (Post)
 │
 ├── Section 2 (Post)
 │   ├── Lesson 2-1 (Post)
 │   └── Lesson 2-2 (Post)
 │
 └── Section 3 (Post)
     └── Quiz 3-1 (Post)
```

### Curriculum Operations

**Get Course Items:**
```php
$course_items_query = SkillPulse_LMS_Course_Items_Query::get_instance();
$items = $course_items_query->get_items($course_id);
```

**Add Item to Course:**
```php
$course_items_query->add_item([
    'course_id' => $course_id,
    'item_id' => $section_id,
    'item_type' => 'section',
    'order_index' => 1
]);
```

**Get Section Children:**
```php
$relationships_query = SkillPulse_LMS_Relationships_Query::get_instance();
$children = $relationships_query->get_children($section_id);
```

---

## Course CRUD Operations

### Create Course

**REST API:**

**Endpoint:** `POST /splms/v1/courses`

**Request:**
```json
{
    "title": "New Course",
    "content": "Course description",
    "status": "draft",
    "excerpt": "Short description",
    "categories": [1, 2]
}
```

**Permission:** Requires `edit_posts` capability

### Read Course

**REST API:**

**Endpoint:** `GET /splms/v1/courses/{id}`

**Response Includes:**
- Course basic info (title, content, excerpt, featured image)
- Access information and pricing
- Content details (difficulty, duration, language)
- Enrollment status (for logged-in users)
- Curriculum statistics
- Author/instructor information
- Related courses data

### Update Course

**REST API:**

**Endpoint:** `PUT /splms/v1/courses/{id}`

**Request:**
```json
{
    "title": "Updated Course Title",
    "content": "Updated description",
    "status": "publish"
}
```

**Permission:** Requires `edit_posts` capability

### Delete Course

**REST API:**

**Endpoint:** `DELETE /splms/v1/courses/{id}`

**Request Parameters:**
- `force` (boolean) - Whether to bypass trash (default: true)

**Permission:** Requires `delete_posts` capability

### List Courses

**REST API:**

**Endpoint:** `GET /splms/v1/courses`

**Query Parameters:**
- `page` - Page number (default: 1)
- `per_page` - Results per page (default: 10, max: 100)
- `search` - Search query string
- `orderby` - Sort by (date, title)
- `order` - Sort order (asc, desc)
- `categories` - Array of category IDs

---

## Course Display & Templates

### Archive Page

**Template:** `templates/archive-course.php`

**URL:** `/courses` or custom page set in settings

**Key Features:**
- Dynamic filtering by category/tag
- Search functionality
- Grid/List layout toggle
- Responsive pagination
- Course count badge
- Mobile-responsive sidebar

### Single Course Page

**Template:** `templates/single-course.php`

**Template Parts:**
- `course/partials/course-hero.php` - Hero section
- `course/partials/course-navigation.php` - Tab navigation
- `course/partials/course-content.php` - Main content area
- `course/partials/course-sidebar.php` - Sidebar with enrollment
- `course/partials/course-related.php` - Related courses

---

## REST API Endpoints

### Public APIs (No Authentication Required)

#### List Courses
- **Endpoint:** `GET /splms/v1/courses`
- **Access:** Public (filtered by access type)
- **Purpose:** Get list of courses

#### Get Single Course
- **Endpoint:** `GET /splms/v1/courses/{id}`
- **Access:** Public (filtered by access type)
- **Purpose:** Get detailed course information

### Private APIs (Authentication Required)

#### Get Course Settings
- **Endpoint:** `GET /splms/v1/courses/{id}/settings`
- **Permission:** `edit_post` capability
- **Purpose:** Get all course settings

#### Update Course Settings
- **Endpoint:** `PUT /splms/v1/courses/{id}/settings`
- **Permission:** `edit_post` + nonce verification
- **Purpose:** Update course settings
- **Security:** Validates nonce, sanitizes input

#### Get Curriculum
- **Endpoint:** `GET /splms/v1/courses/{id}/curriculum`
- **Access:** Public (filterable via hook)
- **Purpose:** Get course curriculum structure

#### Update Curriculum
- **Endpoint:** `PUT /splms/v1/courses/{id}/curriculum`
- **Access:** Private (filterable via hook)
- **Purpose:** Update entire curriculum structure

### Enrollment & Actions APIs

#### Enroll in Course
- **Endpoint:** `POST /splms/v1/courses/{id}/enroll`
- **Access:** Requires authentication
- **Validation:** Checks dates, capacity, purchase, membership

#### Unenroll from Course
- **Endpoint:** `POST /splms/v1/courses/{id}/unenroll`
- **Access:** Requires authentication
- **Action:** Sets enrollment status to 'cancelled'

#### Get Course Progress
- **Endpoint:** `GET /splms/v1/courses/{id}/progress`
- **Access:** Requires authentication
- **Returns:** Completion percentage, completed lessons, quiz scores

#### Toggle Wishlist
- **Endpoint:** `POST /splms/v1/courses/{id}/wishlist`
- **Access:** Requires authentication
- **Storage:** User meta `_splms_course_wishlist`

---

## Access Control System

**File:** `includes/modules/core/class-access-control.php`

### Access Validation Methods

**Check Course Access:**
```php
$access_control = SkillPulse_LMS_Access_Control::get_instance();
$can_access = $access_control->user_can_access_course($user_id, $course_id);
```

**Check Lesson Access:**
```php
$can_access = $access_control->user_can_access_lesson($user_id, $lesson_id);
```

### Membership Integration

```php
// Check if user has required membership
$has_membership = splms_user_has_required_membership($user_id, $course_id);
```

### Prerequisite System

```php
$access_settings = [
    'course_access_type' => 'prerequisite_required',
    'prerequisite_course' => 123 // Course ID
];

// Validation includes:
// - Course exists and is published
// - Course is actually a course post type
// - User has completed prerequisite
```

---

## Enrollment System

### Enrollment Database

**Table:** `{$wpdb->prefix}splms_enrollments`

```sql
CREATE TABLE {$wpdb->prefix}splms_enrollments (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    user_id bigint(20) NOT NULL,
    course_id bigint(20) NOT NULL,
    status varchar(50) NOT NULL DEFAULT 'active',
    enrolled_at datetime DEFAULT CURRENT_TIMESTAMP,
    completed_at datetime DEFAULT NULL,
    expired_at datetime DEFAULT NULL,
    PRIMARY KEY (id),
    KEY user_id (user_id),
    KEY course_id (course_id),
    KEY status (status)
)
```

### Enrollment Operations

**Enroll User:**
```php
$enrollment = SkillPulse_LMS_Enrollment::get_instance();
$result = $enrollment->enroll_user_in_course($user_id, $course_id);
```

**Check if Enrolled:**
```php
$is_enrolled = splms_is_user_enrolled($course_id, $user_id);
```

**Get Enrollment Status:**
```php
$status = splms_get_user_enrollment_status($course_id, $user_id);
// Returns: 'active', 'completed', 'not_enrolled'
```

**Get User's Enrolled Courses:**
```php
$course_ids = splms_get_user_enrolled_courses($user_id);
```

### Enrollment Capacity

**Check Capacity:**
```php
$capacity_info = splms_get_course_max_enrollment_info($course_id);
// Returns: [
//     'max_enrollment' => 100,
//     'enrolled_count' => 45,
//     'is_full' => false,
//     'has_limit' => true
// ]
```

### Enrollment Dates

**Check Enrollment Window:**
```php
$dates_info = splms_get_course_enrollment_dates($course_id);
// Returns: [
//     'start_date' => '2026-01-01',
//     'end_date' => '2026-12-31',
//     'is_open' => true
// ]
```

---

## Progress Tracking

### Progress Database

**Lesson Progress Table:** `{$wpdb->prefix}splms_lesson_progress`

```sql
CREATE TABLE {$wpdb->prefix}splms_lesson_progress (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    user_id bigint(20) NOT NULL,
    lesson_id bigint(20) NOT NULL,
    course_id bigint(20) NOT NULL,
    status varchar(50) NOT NULL DEFAULT 'in_progress',
    started_at datetime DEFAULT CURRENT_TIMESTAMP,
    completed_at datetime DEFAULT NULL,
    PRIMARY KEY (id)
)
```

### Progress Calculation

```php
$lessons = SkillPulse_LMS_Lessons::get_instance();
$progress = $lessons->calculate_course_progress($user_id, $course_id);

// Returns:
[
    'percentage' => 75.5,
    'completed_lessons' => 15,
    'passed_quizzes' => 3,
    'total_items' => 20,
    'last_accessed' => '2026-01-03 10:30:00'
]
```

### Course Completion

**Check if Course Completed:**
```php
$enrollment = SkillPulse_LMS_Enrollment::get_instance();
$is_complete = $enrollment->has_user_completed_course($user_id, $course_id);
```

**Completion Triggers:**
- Updates enrollment status to 'completed'
- Sets `completed_at` timestamp
- Fires `splms_course_completed` action
- Generates certificate (if enabled)
- Sends completion email notification

---

## Database Structure

### Complete Schema

```sql
-- Courses (WordPress wp_posts)
-- post_type = 'sp-course'

-- Course Settings (WordPress wp_postmeta)
-- _splms_course_access_settings
-- _splms_course_pricing_settings
-- _splms_course_content_settings
-- _splms_course_scheduling_settings
-- _splms_course_completion_settings

-- Course Items
CREATE TABLE {$wpdb->prefix}splms_course_items (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    course_id bigint(20) NOT NULL,
    item_id bigint(20) NOT NULL,
    item_type varchar(50) NOT NULL,
    order_index int(11) NOT NULL DEFAULT 0,
    PRIMARY KEY (id)
);

-- Relationships (Section → Lessons/Quizzes)
CREATE TABLE {$wpdb->prefix}splms_relationships (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    parent_id bigint(20) NOT NULL,
    child_id bigint(20) NOT NULL,
    child_type varchar(50) NOT NULL,
    order_index int(11) NOT NULL DEFAULT 0,
    PRIMARY KEY (id)
);

-- Enrollments
CREATE TABLE {$wpdb->prefix}splms_enrollments (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    user_id bigint(20) NOT NULL,
    course_id bigint(20) NOT NULL,
    status varchar(50) NOT NULL DEFAULT 'active',
    enrolled_at datetime DEFAULT CURRENT_TIMESTAMP,
    completed_at datetime DEFAULT NULL,
    expired_at datetime DEFAULT NULL,
    PRIMARY KEY (id)
);

-- Lesson Progress
CREATE TABLE {$wpdb->prefix}splms_lesson_progress (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    user_id bigint(20) NOT NULL,
    lesson_id bigint(20) NOT NULL,
    course_id bigint(20) NOT NULL,
    status varchar(50) NOT NULL DEFAULT 'in_progress',
    started_at datetime DEFAULT CURRENT_TIMESTAMP,
    completed_at datetime DEFAULT NULL,
    PRIMARY KEY (id)
);

-- Orders (for paid courses)
CREATE TABLE {$wpdb->prefix}splms_orders (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    user_id bigint(20) NOT NULL,
    course_id bigint(20) NOT NULL,
    amount decimal(10,2) NOT NULL,
    status varchar(50) NOT NULL DEFAULT 'pending',
    payment_method varchar(50) NOT NULL,
    transaction_id varchar(255),
    created_at datetime DEFAULT CURRENT_TIMESTAMP,
    updated_at datetime DEFAULT NULL,
    PRIMARY KEY (id)
);
```

---

## Hooks & Filters

### Action Hooks

#### Course Actions

**`splms_course_settings_updated`**
```php
do_action('splms_course_settings_updated', $course_id, $settings);
// Fires after course settings are updated
```

**`splms_delete_course_item`**
```php
do_action('splms_delete_course_item', $item_id);
// Fires before a course item is deleted
```

#### Enrollment Actions

**`splms_user_enrolled`**
```php
do_action('splms_user_enrolled', $user_id, $course_id);
// Fires after user is enrolled in a course
```

**`splms_user_unenrolled`**
```php
do_action('splms_user_unenrolled', $user_id, $course_id);
// Fires after user is unenrolled from a course
```

**`splms_course_completed`**
```php
do_action('splms_course_completed', $user_id, $course_id);
// Fires when user completes a course
```

#### Template Actions

**`splms_before_archive_content`**
```php
do_action('splms_before_archive_content');
// Fires before course archive content
```

**`splms_before_single_course_content`**
```php
do_action('splms_before_single_course_content');
// Fires before single course content
```

### Filter Hooks

#### Settings Filters

**`splms_get_course_settings`**
```php
$settings = apply_filters('splms_get_course_settings', $settings, $course_id);
// Filter course settings before returning
```

**`splms_sanitize_course_settings`**
```php
$sanitized = apply_filters('splms_sanitize_course_settings', $sanitized, $raw_settings, $defaults);
// Filter settings during sanitization
```

**`splms_course_access_info`**
```php
$access_info = apply_filters('splms_course_access_info', $access_info, $course_id, $user_id, $args);
// Filter course access information
```

#### REST API Filters

**`splms_rest_prepare_course`**
```php
$response = apply_filters('splms_rest_prepare_course', $response, $post, $request);
// Filter course response data
```

**`splms_get_curriculum_permissions_check`**
```php
$retval = apply_filters('splms_get_curriculum_permissions_check', $retval, $request);
// Filter curriculum get permission
```

---

## Complete Workflows

### Course Settings Workflow

```
User/System Requests Course Settings
    ↓
splms_get_course_settings($course_id)
    ↓
SkillPulse_LMS_Courses::get_course_settings()
    ├─ Load Config Defaults from JSON
    ├─ Get All Post Meta
    ├─ Merge Defaults with Saved Values
    ├─ Auto-Select Default Certificate
    └─ Apply Filter: splms_get_course_settings
    ↓
Return Settings Array
```

### Update Settings Workflow

```
User Submits Settings Update
    ↓
Permission Check: current_user_can('edit_post')
    ↓
Validate and Sanitize Settings
    ├─ Type Detection (bool, int, float, array, string)
    ├─ Sanitization per type
    └─ Apply Filter: splms_sanitize_course_settings
    ↓
Auto-Select Certificate (if needed)
    ↓
Update Database (update_post_meta)
    ↓
Fire Action: splms_course_settings_updated
    ↓
Return Updated Settings
```

### Enrollment Workflow

```
User Clicks "Enroll" Button
    ↓
Validate Enrollment Dates → Open?
    ↓
Check Capacity → Full?
    ↓
Check Access Type → Paid/Invitation/Prerequisite?
    ↓
For Paid Courses → Payment Flow
    ↓
Create Enrollment Record
    - user_id, course_id
    - status: 'active'
    - enrolled_at: current timestamp
    ↓
Grant Course Access
    ↓
Fire Hooks & Notifications
    - Action: splms_user_enrolled
    - Send enrollment email
    - Log activity
    ↓
Student Can Access Course Content
```

---

## Security Implementation

### Input Validation
- ✅ Course ID validation
- ✅ Settings array type checking
- ✅ Required field validation

### Data Sanitization
- ✅ Type-based sanitization (bool, int, float, array, string)
- ✅ `sanitize_text_field()` for strings
- ✅ `array_map()` for arrays

### Permission Checks
- ✅ `current_user_can('edit_post', $course_id)`
- ✅ Applied in helper functions
- ✅ Applied in REST API

### CSRF Protection
- ✅ Nonce verification in REST API
- ✅ `X-WP-Nonce` header validation

### Data Type Enforcement
- ✅ Runtime type checking
- ✅ Default value-based type detection

---

## Best Practices

### ✅ Do's

1. **Always use helper functions**
   ```php
   // ✅ Good
   $settings = splms_get_course_settings( $course_id );

   // ❌ Bad
   $settings = SkillPulse_LMS_Courses::get_instance()->get_course_settings( $course_id );
   ```

2. **Check permissions before updating**
   ```php
   if ( current_user_can( 'edit_post', $course_id ) ) {
       splms_update_course_settings( $course_id, $settings );
   }
   ```

3. **Handle errors properly**
   ```php
   $result = splms_update_course_settings( $course_id, $settings );
   if ( is_wp_error( $result ) ) {
       error_log( $result->get_error_message() );
   }
   ```

4. **Use transients for caching**
   ```php
   $settings = get_transient( "course_settings_{$course_id}" );
   if ( false === $settings ) {
       $settings = splms_get_course_settings( $course_id );
       set_transient( "course_settings_{$course_id}", $settings, HOUR_IN_SECONDS );
   }
   ```

5. **Clear cache on update**
   ```php
   add_action( 'splms_course_settings_updated', function( $course_id ) {
       delete_transient( "course_settings_{$course_id}" );
   }, 10, 1 );
   ```

### ❌ Don'ts

1. **Don't bypass helper functions**
2. **Don't skip permission checks**
3. **Don't ignore error responses**
4. **Don't cache indefinitely**
5. **Don't sanitize already sanitized data**

---

## Performance Considerations

### Caching Strategy

1. **Transient Cache:** Use for frequently accessed settings
   ```php
   set_transient( "course_settings_{$course_id}", $settings, HOUR_IN_SECONDS );
   ```

2. **Cache Invalidation:** Clear on update via action hook
   ```php
   add_action( 'splms_course_settings_updated', 'clear_course_cache' );
   ```

3. **Selective Updates:** Only update changed fields
   ```php
   // Only update pricing
   $new_settings = [
       'course_pricing_settings' => [
           'course_price' => 99.00
       ]
   ];
   ```

---

## Testing Checklist

- [ ] Test get settings with valid course ID
- [ ] Test get settings with invalid course ID
- [ ] Test update with proper permissions
- [ ] Test update without permissions
- [ ] Test update with invalid data types
- [ ] Test sanitization with malicious input
- [ ] Test nonce verification in REST API
- [ ] Verify filter hooks execute
- [ ] Verify action hooks execute
- [ ] Test cache clearing on update
- [ ] Test certificate auto-selection
- [ ] Test transient caching behavior
- [ ] Test enrollment flow
- [ ] Test access control validation
- [ ] Test prerequisite validation
- [ ] Test capacity limits
- [ ] Test enrollment dates validation

---

## Common Errors & Solutions

| Error | Cause | Solution |
|-------|-------|----------|
| `splms_invalid_course_settings` | Empty course_id or invalid settings | Validate inputs before calling |
| `splms_permission_denied` | User lacks permission | Check `current_user_can('edit_post')` |
| `splms_rest_invalid_nonce` | Invalid/missing nonce | Ensure `X-WP-Nonce` header is set |
| `course_not_found` | Course doesn't exist | Verify course exists before operation |

---

## Changelog

### Version 1.0.0 (January 3, 2026)
- ✅ Added input validation and sanitization
- ✅ Added permission checks to helper functions
- ✅ Added nonce verification in REST API
- ✅ Added data type enforcement
- ✅ Added comprehensive action and filter hooks
- ✅ Added prerequisite course validation
- ✅ Added enrollment capacity checks
- ✅ Added enrollment date validation with three states
- ✅ Added dynamic access duration display
- ✅ Added course start date display for cohort courses
- ✅ Added date formatting helper function
- ✅ Security audit passed - Production ready

---

**Module Maintainer:** SkillPulse LMS Development Team
**Last Updated:** January 3, 2026
**Support:** Refer to main plugin documentation
