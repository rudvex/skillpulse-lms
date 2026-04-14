# Quiz Module - Complete System Documentation

> **Version**: 1.0
> **Last Updated**: 2026-01-04
> **Status**: Production Ready

## Table of Contents
1. [Overview](#overview)
2. [Quick Start](#quick-start)
3. [Architecture](#architecture)
4. [Quiz Types](#quiz-types)
5. [Question Types](#question-types)
6. [Settings System](#settings-system)
7. [Quiz Attempts & Progress](#quiz-attempts--progress)
8. [Quiz Evaluation & Grading](#quiz-evaluation--grading)
9. [CRUD Operations](#crud-operations)
10. [Access Control](#access-control)
11. [Database Structure](#database-structure)
12. [REST API Endpoints](#rest-api-endpoints)
13. [Hooks & Filters](#hooks--filters)
14. [Complete Workflows](#complete-workflows)
15. [Security](#security)
16. [Best Practices](#best-practices)
17. [Testing Checklist](#testing-checklist)
18. [Common Errors & Solutions](#common-errors--solutions)
19. [Performance Optimization](#performance-optimization)
20. [Migration Guide](#migration-guide)

---

## Overview

The Quiz Module is a comprehensive assessment system for the SkillPulse LMS plugin. It provides:

- **3 Quiz Types**: Graded, Practice, Survey
- **12 Question Types**: Multiple choice, multiple select, true/false, short answer, fill blank, matching, ordering, essay, long answer, file upload, and more
- **Advanced Evaluation Engine**: Automatic grading with partial credit support
- **Attempt Management**: Track unlimited quiz attempts with detailed analytics
- **Time Limits**: Optional time restrictions with countdown timers
- **Randomization**: Randomize questions and answer options
- **Manual Review**: Essay and file upload questions require instructor grading
- **Password Protection**: Secure quiz access
- **Results Control**: Fine-grained control over when and how results are shown

### Key Features

✅ **Quiz Types**: Graded quizzes, practice quizzes, and surveys
✅ **12 Question Types**: Support for all common question formats
✅ **Auto-Grading**: Intelligent evaluation engine with partial credit
✅ **Manual Grading**: Essays and file uploads need instructor review
✅ **Attempt Tracking**: Unlimited attempts with best score tracking
✅ **Time Limits**: Optional countdown timers
✅ **Randomization**: Questions and answer options
✅ **Password Protection**: Quiz security
✅ **Feedback System**: Customizable feedback messages
✅ **Results Control**: Control when students see results and answers
✅ **Progress Analytics**: Detailed attempt history and statistics

---

## Quick Start

### Creating a Quiz

```php
// Create a new quiz
$quiz_id = wp_insert_post( array(
    'post_title'   => 'Final Exam',
    'post_content' => 'This quiz covers all course material.',
    'post_type'    => SPLMS_POST_TYPES['quiz'],
    'post_status'  => 'publish',
) );

// Configure quiz settings
$quizzes = SkillPulse_LMS_Quizzes::get_instance();
$quizzes->update_quiz_settings( $quiz_id, array(
    'quiz_type'          => 'graded',
    'passing_grade'      => 70,
    'max_attempts'       => 3,
    'time_limit_enabled' => true,
    'time_limit'         => 60, // minutes
) );

// Add questions
$questions_query = SkillPulse_LMS_Quiz_Questions_Query::get_instance();
$question_id = $questions_query->add_question( array(
    'quiz_id'     => $quiz_id,
    'title'       => 'What is PHP?',
    'type'        => 'multiple_choice',
    'points'      => 5,
    'options_json' => json_encode( array(
        array( 'id' => 'a', 'text' => 'A programming language' ),
        array( 'id' => 'b', 'text' => 'A database' ),
        array( 'id' => 'c', 'text' => 'An operating system' ),
    ) ),
    'correct_answer_json' => json_encode( array( 'answers' => array( 'A programming language' ) ) ),
) );
```

### Starting a Quiz Attempt

```php
$user_id   = get_current_user_id();
$quiz_id   = 123;
$course_id = 456;

// Start new attempt
$attempts_query = SkillPulse_LMS_Quiz_Attempts_Query::get_instance();
$attempt_id = $attempts_query->start_attempt( $user_id, $quiz_id, $course_id );

if ( $attempt_id ) {
    echo "Attempt started! ID: " . $attempt_id;
}
```

### Submitting and Grading a Quiz

```php
// Get quiz questions
$questions = splms_get_quiz_questions( $quiz_id );

// Student's submitted answers
$submitted_answers = array(
    1 => 'A programming language',  // question_id => answer
    2 => array( 'PHP', 'Python' ),  // multiple select
    3 => 'True',                     // true/false
);

// Evaluate the attempt
$evaluator = SkillPulse_LMS_Quiz_Evaluator::get_instance();
$results = $evaluator::evaluate_attempt(
    $quiz_id,
    $questions,
    $submitted_answers,
    70  // passing grade
);

// Complete the attempt
$attempts_query->complete_attempt(
    $attempt_id,
    $submitted_answers,
    $results['points_earned'],
    $results['total_points'],
    $results['passed'],
    120  // time taken in seconds
);
```

---

## Architecture

### Core Components

```
includes/modules/quizzes/
├── class-quizzes.php                  # Main quiz class (Singleton)
├── class-quiz-questions-query.php     # Questions database operations
├── class-quiz-attempts-query.php      # Attempts database operations
├── class-quiz-evaluator.php           # Grading engine
└── general-functions.php               # Helper functions

includes/rest-api/quizzes/
├── class-rest-quiz-controller.php           # Quiz CRUD API
├── class-rest-quiz-actions-controller.php    # Quiz actions (start, submit)
├── class-rest-quiz-attempts-controller.php   # Attempts management API
└── admin/
    ├── class-rest-quiz-settings-controller.php  # Settings API
    └── class-rest-quiz-questions-controller.php # Questions API

config/
└── quiz-settings-config.json          # Settings configuration

templates/quiz/
├── single-quiz.php                     # Quiz template
└── partials/
    ├── quiz-interface.php              # Quiz taking interface
    ├── quiz-results.php                # Results display
    ├── quiz-navigation.php             # Question navigation
    └── quiz-attempts-list.php          # Attempts history
```

### Class Relationships

```
SkillPulse_LMS_Quizzes (Singleton)
    ├── Uses: SkillPulse_LMS_Quiz_Questions_Query
    ├── Uses: SkillPulse_LMS_Quiz_Attempts_Query
    └── Uses: SkillPulse_LMS_Quiz_Evaluator

SkillPulse_LMS_Quiz_Evaluator (Singleton)
    ├── evaluate_attempt()      # Grade complete quiz
    ├── evaluate_question()     # Grade single question
    └── 12 judge_* methods      # Type-specific grading

SkillPulse_LMS_Quiz_Questions_Query extends SkillPulse_LMS_Base_Query
    ├── add_question()
    ├── update_question()
    ├── get_questions()
    └── delete_question()

SkillPulse_LMS_Quiz_Attempts_Query extends SkillPulse_LMS_Base_Query
    ├── start_attempt()
    ├── complete_attempt()
    ├── get_completed_attempts()
    └── has_user_passed()
```

---

## Quiz Types

The system supports 3 quiz types, each with different behavior:

### 1. Graded Quiz (`graded`)

**Purpose**: Formal assessments that count toward course completion
**Behavior**:
- Requires passing grade
- Attempts are limited (configurable)
- Can block course progress until passed
- Counts toward course completion percentage
- Results affect course grade

**Use Cases**:
- Final exams
- Chapter tests
- Certification quizzes
- Graded assignments

```php
$settings = array(
    'quiz_type'     => 'graded',
    'passing_grade' => 70,
    'max_attempts'  => 3,
);
```

### 2. Practice Quiz (`practice`)

**Purpose**: Self-assessment without grade consequences
**Behavior**:
- No pass/fail requirement
- Unlimited attempts (typically)
- Does NOT count toward course completion
- Students can review correct answers immediately
- No grade impact

**Use Cases**:
- Practice exams
- Study guides
- Self-assessment tools
- Drill exercises

```php
$settings = array(
    'quiz_type'          => 'practice',
    'max_attempts'       => 0, // unlimited
    'show_correct_answers' => true,
    'show_correct_answers_timing' => 'after_completion',
);
```

### 3. Survey (`survey`)

**Purpose**: Collect feedback or opinions without grading
**Behavior**:
- No correct/incorrect answers
- No pass/fail
- No grade calculation
- All submissions are "correct"
- Used for data collection

**Use Cases**:
- Course feedback
- Student surveys
- Opinion polls
- Needs assessments

```php
$settings = array(
    'quiz_type'     => 'survey',
    'passing_grade' => 0, // not applicable
);
```

---

## Question Types

The system supports 12 question types with specialized evaluation logic:

### 1. Multiple Choice (`multiple_choice`)

**Description**: Single correct answer from multiple options
**Auto-graded**: Yes
**Partial Credit**: No

**Structure**:
```php
array(
    'type'    => 'multiple_choice',
    'title'   => 'What is the capital of France?',
    'points'  => 1,
    'options' => array(
        array( 'id' => 'a', 'text' => 'London' ),
        array( 'id' => 'b', 'text' => 'Paris' ),
        array( 'id' => 'c', 'text' => 'Berlin' ),
        array( 'id' => 'd', 'text' => 'Madrid' ),
    ),
    'correct_answer' => array( 'answers' => array( 'Paris' ) ),
)
```

**Grading**: Exact text match (case-sensitive)

---

### 2. Multiple Select (`multiple_select`)

**Description**: Multiple correct answers
**Auto-graded**: Yes
**Partial Credit**: Yes (optional)

**Structure**:
```php
array(
    'type'    => 'multiple_select',
    'title'   => 'Which are programming languages?',
    'points'  => 2,
    'options' => array(
        array( 'id' => 'a', 'text' => 'PHP' ),
        array( 'id' => 'b', 'text' => 'HTML' ),
        array( 'id' => 'c', 'text' => 'Python' ),
        array( 'id' => 'd', 'text' => 'CSS' ),
    ),
    'correct_answer' => array( 'answers' => array( 'PHP', 'Python' ) ),
    'settings' => array( 'partial_credit' => true ),
)
```

**Grading**:
- All-or-nothing: Must select ALL correct and NO incorrect
- Partial credit: `(correct_selected / total_correct) - (wrong_selected × 0.1)`

---

### 3. True/False (`true_false`)

**Description**: Binary choice question
**Auto-graded**: Yes
**Partial Credit**: No

**Structure**:
```php
array(
    'type'    => 'true_false',
    'title'   => 'PHP is a server-side language.',
    'points'  => 1,
    'options' => array(
        array( 'id' => 'true', 'text' => 'True' ),
        array( 'id' => 'false', 'text' => 'False' ),
    ),
    'correct_answer' => array( 'answers' => array( 'True' ) ),
)
```

**Grading**: Exact match, case-insensitive ("true"/"false" normalized to "True"/"False")

---

### 4. Short Answer (`short_answer`)

**Description**: Brief text response
**Auto-graded**: Yes
**Partial Credit**: No

**Structure**:
```php
array(
    'type'    => 'short_answer',
    'title'   => 'What does PHP stand for?',
    'points'  => 1,
    'correct_answer' => array( 'answers' => array( 'php hypertext preprocessor' ) ),
)
```

**Grading**: Case-insensitive exact match after trimming

---

### 5. Fill in the Blank (`fill_blank`)

**Description**: Complete a sentence with missing word(s)
**Auto-graded**: Yes
**Partial Credit**: No

**Structure**:
```php
array(
    'type'    => 'fill_blank',
    'title'   => 'WordPress is built with _____ and MySQL.',
    'points'  => 1,
    'correct_answer' => array( 'answers' => array( 'php' ) ),
)
```

**Grading**: Same as short answer

---

### 6. Matching (`matching`)

**Description**: Match items from two columns
**Auto-graded**: Yes
**Partial Credit**: Yes (optional)

**Structure**:
```php
array(
    'type'    => 'matching',
    'title'   => 'Match the language to its type',
    'points'  => 3,
    'options' => array(
        array( 'id' => '1', 'option_data' => array( 'left' => 'PHP', 'right' => 'Server-side' ) ),
        array( 'id' => '2', 'option_data' => array( 'left' => 'JavaScript', 'right' => 'Client-side' ) ),
        array( 'id' => '3', 'option_data' => array( 'left' => 'SQL', 'right' => 'Database' ) ),
    ),
    'correct_answer' => array(
        'answers' => array(
            'PHP'        => 'Server-side',
            'JavaScript' => 'Client-side',
            'SQL'        => 'Database',
        ),
    ),
    'settings' => array( 'partial_credit' => true ),
)
```

**Grading**:
- All-or-nothing: All pairs must match
- Partial credit: `correct_pairs / total_pairs`

---

### 7. Ordering (`ordering`)

**Description**: Arrange items in correct sequence
**Auto-graded**: Yes
**Partial Credit**: Yes (optional)

**Structure**:
```php
array(
    'type'    => 'ordering',
    'title'   => 'Order the web development process',
    'points'  => 2,
    'options' => array(
        array( 'id' => '1', 'text' => 'Design' ),
        array( 'id' => '2', 'text' => 'Development' ),
        array( 'id' => '3', 'text' => 'Testing' ),
        array( 'id' => '4', 'text' => 'Deployment' ),
    ),
    'correct_answer' => array( 'answers' => array( 'Design', 'Development', 'Testing', 'Deployment' ) ),
    'settings' => array( 'partial_credit' => true ),
)
```

**Grading**:
- All-or-nothing: Exact sequence match
- Partial credit: `correct_positions / total_items`

---

### 8. Essay (`essay`)

**Description**: Long-form text response
**Auto-graded**: No (requires manual review)
**Partial Credit**: Manual

**Structure**:
```php
array(
    'type'    => 'essay',
    'title'   => 'Explain the MVC pattern in web development.',
    'points'  => 10,
)
```

**Grading**: Instructor must manually grade. Initial score is 0 until reviewed.

**Workflow**:
1. Student submits essay
2. Quiz marked as "pending review"
3. Instructor grades manually
4. Final score updated

---

### 9. Long Answer (`long_answer`)

**Description**: Extended text response (alias for essay)
**Auto-graded**: No
**Partial Credit**: Manual

Same as Essay type.

---

### 10. File Upload (`file_upload`)

**Description**: Upload document, image, or other file
**Auto-graded**: No
**Partial Credit**: Manual

**Structure**:
```php
array(
    'type'    => 'file_upload',
    'title'   => 'Upload your project documentation',
    'points'  => 15,
    'settings' => array(
        'allowed_types' => array( 'pdf', 'doc', 'docx' ),
        'max_size'      => 5 * 1024 * 1024, // 5MB
    ),
)
```

**Grading**: Instructor must review and grade manually.

---

### Question Type Summary

| Type | Auto-Graded | Partial Credit | Use Case |
|------|-------------|----------------|----------|
| Multiple Choice | ✅ | ❌ | Single answer from options |
| Multiple Select | ✅ | ✅ | Multiple correct answers |
| True/False | ✅ | ❌ | Binary questions |
| Short Answer | ✅ | ❌ | Brief text response |
| Fill Blank | ✅ | ❌ | Complete sentence |
| Matching | ✅ | ✅ | Pair related items |
| Ordering | ✅ | ✅ | Sequence items |
| Essay | ❌ | ✅ (Manual) | Long-form writing |
| Long Answer | ❌ | ✅ (Manual) | Extended response |
| File Upload | ❌ | ✅ (Manual) | Document submission |

---

## Settings System

### Settings Groups

Quiz settings are organized into 4 sections:

#### 1. Basic Settings (`quiz_basic_settings`)

```php
array(
    'quiz_type'     => 'graded',  // graded | practice | survey
    'passing_grade' => 70,         // 0-100
    'max_attempts'  => 3,          // 0 = unlimited
)
```

#### 2. Timing Settings (`quiz_timing_settings`)

```php
array(
    'time_limit_enabled' => true,
    'time_limit'         => 60,  // minutes
)
```

#### 3. Grading Settings (`quiz_grading_settings`)

```php
array(
    'passing_grade' => 70,  // minimum percentage to pass
)
```

#### 4. Attempts Settings (`quiz_attempts_settings`)

```php
array(
    'max_attempts' => 3,  // 0 = unlimited
)
```

#### 5. Display Settings (`quiz_display_settings`)

```php
array(
    'show_correct_answers'        => true,
    'show_correct_answers_timing' => 'after_completion', // after_completion | after_passing | after_all_attempts
)
```

#### 6. Behavior Settings (`quiz_behavior_settings`)

```php
array(
    'randomize_questions' => false,
    'randomize_options'   => false,
    'question_per_page'   => 1,  // 0 = all on one page
    'allow_navigation'    => true,
)
```

#### 7. Security Settings (`quiz_security_settings`)

```php
array(
    'require_password' => false,
    'quiz_password'    => '',
)
```

#### 8. Feedback Settings (`quiz_feedback_settings`)

```php
array(
    'enable_feedback'    => true,
    'feedback_correct'   => 'Correct! Well done.',
    'feedback_incorrect' => 'Incorrect. Please review the material and try again.',
)
```

#### 9. Results Settings (`quiz_results_settings`)

```php
array(
    'results_timing' => 'after_passing',  // immediately | after_passing | manual
)
```

### Getting Settings

```php
// Get all settings
$settings = splms_get_quiz_settings( $quiz_id );

// Get specific setting values
$quiz_type     = splms_get_quiz_type( $quiz_id );
$time_limit    = splms_get_quiz_time_limit( $quiz_id );
$passing_grade = splms_get_quiz_passing_grade( $quiz_id );
```

### Updating Settings

```php
$quizzes = SkillPulse_LMS_Quizzes::get_instance();

$result = $quizzes->update_quiz_settings( $quiz_id, array(
    'quiz_type'          => 'graded',
    'passing_grade'      => 80,
    'max_attempts'       => 2,
    'time_limit_enabled' => true,
    'time_limit'         => 45,
    'randomize_questions' => true,
) );

if ( is_wp_error( $result ) ) {
    echo $result->get_error_message();
}
```

---

## Quiz Attempts & Progress

### Attempt Lifecycle

```
1. START → 2. IN PROGRESS → 3. COMPLETED → 4. GRADED (if manual review)
```

#### 1. Starting an Attempt

```php
$attempts_query = SkillPulse_LMS_Quiz_Attempts_Query::get_instance();

// Start new attempt
$attempt_id = $attempts_query->start_attempt( $user_id, $quiz_id, $course_id );

// Prevents duplicates - returns existing in-progress attempt if one exists
```

**Duplicate Prevention**:
- Checks for existing in-progress attempts (score=0, passed=0, time_taken=0)
- Checks for race conditions (same timestamp)
- Returns existing attempt ID if found

#### 2. Updating Progress

```php
// Update answers during quiz (autosave)
$attempts_query->update_progress(
    $attempt_id,
    $answers,      // current answers array
    $time_taken    // seconds elapsed
);
```

#### 3. Completing an Attempt

```php
$attempts_query->complete_attempt(
    $attempt_id,
    $final_answers,  // submitted answers
    $score,          // points earned
    $max_score,      // total points possible
    $passed,         // true/false
    $time_taken      // total seconds
);
```

**Validation**:
- Verifies attempt exists
- Validates score/max_score are floats
- Encodes answers as JSON
- Returns false if update fails

#### 4. Getting Attempts

```php
// Get user's completed attempts
$attempts = $attempts_query->get_completed_attempts( $user_id, $quiz_id );

// Get best attempt
$best_score = splms_get_user_best_quiz_score( $user_id, $quiz_id );

// Check if user passed
$has_passed = $attempts_query->has_user_passed( $user_id, $quiz_id );

// Count attempts
$attempt_count = $attempts_query->count_completed_attempts( $user_id, $quiz_id );
```

### Attempt Statuses

An attempt is considered **COMPLETED** if ANY of these are true:
- `time_taken > 0` (quiz was submitted)
- `answers != '' AND answers != '[]'` (answers were saved)
- `score > 0.00 OR passed = 1` (quiz was graded)

An attempt is **IN PROGRESS** if ALL of these are true:
- `score = 0.00`
- `passed = 0`
- `time_taken = 0`
- `answers = '' OR answers = '[]' OR answers IS NULL`

---

## Quiz Evaluation & Grading

The `SkillPulse_LMS_Quiz_Evaluator` class handles all grading logic.

### Auto-Grading Process

```php
$evaluator = SkillPulse_LMS_Quiz_Evaluator::get_instance();

// Evaluate complete quiz
$results = $evaluator::evaluate_attempt(
    $quiz_id,
    $questions,          // array of question objects
    $submitted_answers,  // array of user answers
    $passing_grade       // passing percentage
);

// Results structure:
array(
    'correct_answers'      => 8,      // number correct
    'total_questions'      => 10,     // total questions
    'points_earned'        => 42.5,   // points scored
    'total_points'         => 50.0,   // max points
    'percentage'           => 85.0,   // score percentage
    'passed'               => true,   // pass/fail
    'pending_review'       => false,  // has essay questions?
    'manual_review_points' => 0.0,    // points from manual questions
    'has_manual_review'    => false,  // has essay/file upload?
    'detailed_results'     => array( /* per-question results */ ),
)
```

### Grading Individual Questions

```php
$result = $evaluator::evaluate_question(
    $question,        // question object
    $submitted_answer // user's answer
);

// Result structure:
array(
    'is_correct'             => true,
    'score'                  => 5.0,    // points earned
    'points'                 => 5.0,    // points possible
    'correct_answer'         => 'Paris',
    'user_answer_display'    => 'Paris',
    'correct_answer_display' => 'Paris',
    'needs_manual_review'    => false,
)
```

### Partial Credit

Some question types support partial credit:

#### Multiple Select
```php
// 4 correct options: A, B, C, D
// User selects: A, B, C, E

$correct_selected = 3  // A, B, C
$wrong_selected   = 1  // E
$total_correct    = 4

$partial_score = (3 / 4) - (1 × 0.1) = 0.75 - 0.1 = 0.65
$points_earned = 10 × 0.65 = 6.5 points
```

#### Matching
```php
// 5 pairs total
// User matches 3 correctly

$partial_score = 3 / 5 = 0.6
$points_earned = 10 × 0.6 = 6 points
```

#### Ordering
```php
// 4 items in correct order: A, B, C, D
// User submits: A, C, B, D

$correct_positions = 2  // A and D are in correct positions
$total_items       = 4

$partial_score = 2 / 4 = 0.5
$points_earned = 10 × 0.5 = 5 points
```

### Manual Grading Workflow

For essay and file upload questions:

1. **Student Submits**:
   ```php
   $results = $evaluator::evaluate_attempt( /* ... */ );
   // Returns: pending_review = true, has_manual_review = true
   ```

2. **Initial Score**:
   - Essay/file upload questions start with 0 points
   - Excluded from auto-calculated pass/fail
   - Quiz status: "Pending Review"

3. **Instructor Grades**:
   ```php
   // Update question score in attempt
   $attempt = $attempts_query->get_attempt( $attempt_id );
   $answers = json_decode( $attempt->answers, true );

   // Add graded scores
   $answers['_graded_scores'] = array(
       $question_id => 8.5,  // out of 10 points
   );

   // Re-evaluate with manual scores
   $new_score = calculate_final_score_with_manual_grades( $answers, $questions );

   // Update attempt
   $attempts_query->update( /* ... */ );
   ```

4. **Final Result**:
   - Total score = auto-graded points + manual points
   - Pass/fail determined by total percentage
   - Quiz status: "Completed"

---

## CRUD Operations

### Create Quiz

```php
// Method 1: Direct post creation
$quiz_id = wp_insert_post( array(
    'post_title'   => 'My Quiz',
    'post_content' => 'Quiz description',
    'post_type'    => SPLMS_POST_TYPES['quiz'],
    'post_status'  => 'publish',
) );

// Method 2: Via REST API
POST /wp-json/splms/v1/quizzes
{
    "title": "My Quiz",
    "content": "Quiz description",
    "quiz_type": "graded",
    "passing_grade": 70
}
```

### Read Quiz

```php
// Get quiz settings
$settings = splms_get_quiz_settings( $quiz_id );

// Get quiz questions
$questions = splms_get_quiz_questions( $quiz_id );

// Get question count
$count = splms_get_quiz_questions_count( $quiz_id );

// Via REST API
GET /wp-json/splms/v1/quizzes/{id}
```

### Update Quiz

```php
// Update quiz post
wp_update_post( array(
    'ID'           => $quiz_id,
    'post_title'   => 'Updated Title',
    'post_content' => 'Updated description',
) );

// Update settings
$quizzes = SkillPulse_LMS_Quizzes::get_instance();
$quizzes->update_quiz_settings( $quiz_id, array(
    'passing_grade' => 80,
    'max_attempts'  => 5,
) );

// Via REST API
PUT /wp-json/splms/v1/quizzes/{id}
```

### Delete Quiz

```php
// Soft delete (trash)
wp_trash_post( $quiz_id );

// Permanent delete
wp_delete_post( $quiz_id, true );

// Via REST API
DELETE /wp-json/splms/v1/quizzes/{id}
```

### Questions CRUD

```php
$questions_query = SkillPulse_LMS_Quiz_Questions_Query::get_instance();

// Add question
$question_id = $questions_query->add_question( array(
    'quiz_id'     => $quiz_id,
    'title'       => 'Question text',
    'type'        => 'multiple_choice',
    'points'      => 5,
    'options_json' => json_encode( $options ),
    'correct_answer_json' => json_encode( array( 'answers' => array( 'Paris' ) ) ),
) );

// Update question
$questions_query->update_question( $question_id, array(
    'title'  => 'Updated question',
    'points' => 10,
) );

// Get questions
$questions = $questions_query->get_questions( $quiz_id );

// Delete question
$questions_query->delete_question( $question_id );
```

---

## Access Control

### Quiz Access Layers

1. **Enrollment Check**: User must be enrolled in course
2. **Password Protection**: Quiz password if enabled
3. **Attempt Limits**: Max attempts restriction
4. **Time Windows**: Quiz availability schedule (future feature)

### Checking Access

```php
// Check if user can access quiz
$quizzes = SkillPulse_LMS_Quizzes::get_instance();
$can_access = $quizzes->user_can_access_quiz( $quiz_id, $user_id );

if ( ! $can_access ) {
    // Show access denied message
    return;
}
```

### Password Protection

```php
// Enable password
$quizzes->update_quiz_settings( $quiz_id, array(
    'require_password' => true,
    'quiz_password'    => 'secretpass123',
) );

// Verify password before starting quiz
$settings = splms_get_quiz_settings( $quiz_id );
if ( $settings['require_password'] ) {
    $submitted_password = $_POST['quiz_password'];
    if ( $submitted_password !== $settings['quiz_password'] ) {
        wp_die( 'Incorrect password' );
    }
}
```

### Attempt Limits

```php
// Check remaining attempts
$settings = splms_get_quiz_settings( $quiz_id );
$max_attempts = $settings['max_attempts']; // 0 = unlimited

if ( $max_attempts > 0 ) {
    $attempts_query = SkillPulse_LMS_Quiz_Attempts_Query::get_instance();
    $used_attempts = $attempts_query->count_completed_attempts( $user_id, $quiz_id );

    if ( $used_attempts >= $max_attempts ) {
        wp_die( 'Maximum attempts reached' );
    }

    $remaining = $max_attempts - $used_attempts;
    echo "You have {$remaining} attempts remaining";
}
```

---

## Database Structure

### `splms_quiz_attempts` Table

```sql
CREATE TABLE splms_quiz_attempts (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT(20) UNSIGNED NOT NULL,
    quiz_id BIGINT(20) UNSIGNED NOT NULL,
    course_id BIGINT(20) UNSIGNED NOT NULL,
    answers LONGTEXT NOT NULL,           -- JSON encoded answers
    score DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    max_score DECIMAL(10,2) NOT NULL DEFAULT 100.00,
    passed TINYINT(1) NOT NULL DEFAULT 0,
    attempt_time DATETIME NOT NULL,
    time_taken INT(11) NOT NULL DEFAULT 0,  -- seconds
    PRIMARY KEY (id),
    KEY user_quiz (user_id, quiz_id),
    KEY course_id (course_id)
);
```

### `splms_quiz_questions` Table

```sql
CREATE TABLE splms_quiz_questions (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    quiz_id BIGINT(20) UNSIGNED NOT NULL,
    title TEXT NOT NULL,
    description TEXT,
    type VARCHAR(50) NOT NULL,          -- question type
    points DECIMAL(10,2) NOT NULL DEFAULT 1.00,
    order_index INT(11) NOT NULL DEFAULT 0,
    options_json LONGTEXT,              -- JSON encoded options
    correct_answer_json LONGTEXT,       -- JSON encoded correct answers
    settings LONGTEXT,                  -- JSON encoded settings
    created_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    KEY quiz_id (quiz_id),
    KEY type (type)
);
```

### Post Meta (Quiz Settings)

All quiz settings are stored as grouped post meta:

```
_splms_quiz_basic_settings          # quiz_type, passing_grade, max_attempts
_splms_quiz_timing_settings         # time_limit_enabled, time_limit
_splms_quiz_grading_settings        # passing_grade
_splms_quiz_attempts_settings       # max_attempts
_splms_quiz_display_settings        # show_correct_answers, timing
_splms_quiz_behavior_settings       # randomize, pagination, navigation
_splms_quiz_security_settings       # require_password, quiz_password
_splms_quiz_feedback_settings       # enable_feedback, messages
_splms_quiz_results_settings        # results_timing
```

---

## REST API Endpoints

### Quiz CRUD

```
GET    /splms/v1/quizzes              List quizzes
POST   /splms/v1/quizzes              Create quiz
GET    /splms/v1/quizzes/{id}         Get single quiz
PUT    /splms/v1/quizzes/{id}         Update quiz
DELETE /splms/v1/quizzes/{id}         Delete quiz
```

### Quiz Actions

```
POST   /splms/v1/quizzes/{id}/start   Start quiz attempt
POST   /splms/v1/quizzes/{id}/submit  Submit quiz answers
GET    /splms/v1/quizzes/{id}/progress Get current progress
```

### Quiz Attempts

```
GET    /splms/v1/quiz-attempts                    List all attempts
GET    /splms/v1/quiz-attempts/{id}               Get single attempt
PUT    /splms/v1/quiz-attempts/{id}               Update attempt (grade manually)
DELETE /splms/v1/quiz-attempts/{id}               Delete attempt
GET    /splms/v1/quiz-attempts/{id}/grade         Grade attempt manually
POST   /splms/v1/quiz-attempts/{id}/verify        Verify attempt authenticity
```

### Questions (Admin)

```
GET    /splms/v1/quizzes/{id}/questions           List questions
POST   /splms/v1/quizzes/{id}/questions           Add question
PUT    /splms/v1/quizzes/{id}/questions/{q_id}    Update question
DELETE /splms/v1/quizzes/{id}/questions/{q_id}    Delete question
POST   /splms/v1/quizzes/{id}/questions/bulk      Bulk add questions
```

---

## Hooks & Filters

### Actions

```php
// Quiz submission
do_action( 'splms_quiz_submitted', $quiz_id, $user_id, $attempt_id );

// Quiz completed
do_action( 'splms_quiz_completed', $quiz_id, $user_id, $attempt_id, $passed );

// Quiz passed
do_action( 'splms_quiz_passed', $quiz_id, $user_id, $attempt_id, $score );

// Quiz failed
do_action( 'splms_quiz_failed', $quiz_id, $user_id, $attempt_id, $score );

// Question evaluated
do_action( 'splms_evaluate_question', $question_id, $is_correct, $user_answer );

// Attempt started
do_action( 'splms_quiz_attempt_started', $attempt_id, $quiz_id, $user_id );

// Attempt deleted
do_action( 'splms_quiz_attempt_deleted', $attempt_id, $quiz_id, $user_id );
```

### Filters

```php
// Modify quiz settings
apply_filters( 'splms_quiz_settings', $settings, $quiz_id );

// Modify questions before display
apply_filters( 'splms_quiz_questions', $questions, $quiz_id );

// Modify evaluation before grading
apply_filters( 'splms_evaluate_answer_before', $user_answer, $question );

// Modify evaluation result
apply_filters( 'splms_evaluate_answer_result', $is_correct, $question, $user_answer );

// Modify passing grade
apply_filters( 'splms_quiz_passing_grade', $passing_grade, $quiz_id );

// Modify max attempts
apply_filters( 'splms_quiz_max_attempts', $max_attempts, $quiz_id, $user_id );
```

---

## Complete Workflows

### Workflow 1: Student Takes Quiz

```php
// 1. Student clicks "Start Quiz"
$quiz_id   = 123;
$course_id = 456;
$user_id   = get_current_user_id();

// 2. Check access
$quizzes = SkillPulse_LMS_Quizzes::get_instance();
if ( ! $quizzes->user_can_access_quiz( $quiz_id, $user_id ) ) {
    wp_die( 'Access denied' );
}

// 3. Check password
$settings = splms_get_quiz_settings( $quiz_id );
if ( $settings['require_password'] && $_POST['password'] !== $settings['quiz_password'] ) {
    wp_die( 'Incorrect password' );
}

// 4. Start attempt
$attempts_query = SkillPulse_LMS_Quiz_Attempts_Query::get_instance();
$attempt_id = $attempts_query->start_attempt( $user_id, $quiz_id, $course_id );

// 5. Get questions
$questions = splms_get_quiz_questions( $quiz_id );

// 6. Display quiz interface
// (Student answers questions...)

// 7. Submit answers
$submitted_answers = $_POST['answers'];  // array of answers

// 8. Evaluate
$evaluator = SkillPulse_LMS_Quiz_Evaluator::get_instance();
$results = $evaluator::evaluate_attempt(
    $quiz_id,
    $questions,
    $submitted_answers,
    $settings['passing_grade']
);

// 9. Complete attempt
$attempts_query->complete_attempt(
    $attempt_id,
    $submitted_answers,
    $results['points_earned'],
    $results['total_points'],
    $results['passed'],
    $_POST['time_taken']
);

// 10. Fire hooks
if ( $results['passed'] ) {
    do_action( 'splms_quiz_passed', $quiz_id, $user_id, $attempt_id, $results['percentage'] );
} else {
    do_action( 'splms_quiz_failed', $quiz_id, $user_id, $attempt_id, $results['percentage'] );
}

// 11. Update course progress
if ( $results['passed'] && 'graded' === $settings['quiz_type'] ) {
    // Mark quiz as complete in course progress
}

// 12. Show results
return array(
    'score'      => $results['points_earned'],
    'max_score'  => $results['total_points'],
    'percentage' => $results['percentage'],
    'passed'     => $results['passed'],
    'attempts_remaining' => $max_attempts - $used_attempts,
);
```

### Workflow 2: Instructor Grades Essay

```php
// 1. Get attempt with essay questions
$attempt = $attempts_query->get_attempt( $attempt_id );
$answers = json_decode( $attempt->answers, true );

// 2. Get questions
$questions = splms_get_quiz_questions( $attempt->quiz_id );

// 3. Find essay questions
$essay_questions = array_filter( $questions, function( $q ) {
    return in_array( $q['type'], array( 'essay', 'long_answer', 'file_upload' ) );
} );

// 4. Instructor assigns scores
$graded_scores = array();
foreach ( $essay_questions as $question ) {
    $question_id = $question['id'];
    $points = $question['points'];

    // Instructor enters score (e.g., via admin form)
    $awarded_points = $_POST['question_' . $question_id . '_score'];  // 0-$points

    $graded_scores[ $question_id ] = floatval( $awarded_points );
}

// 5. Save graded scores
$answers['_graded_scores'] = $graded_scores;

// 6. Recalculate total score
$total_score = 0;
$max_score = 0;

foreach ( $questions as $question ) {
    $max_score += $question['points'];

    if ( isset( $graded_scores[ $question['id'] ] ) ) {
        // Use manual score
        $total_score += $graded_scores[ $question['id'] ];
    } else {
        // Use auto-graded score from evaluation
        $result = $evaluator::evaluate_question( $question, $answers[ $question['id'] ] );
        $total_score += $result['score'];
    }
}

// 7. Update attempt
$percentage = $max_score > 0 ? ( $total_score / $max_score ) * 100 : 0;
$passed = $percentage >= $settings['passing_grade'];

$wpdb->update(
    $wpdb->prefix . 'splms_quiz_attempts',
    array(
        'answers'   => wp_json_encode( $answers ),
        'score'     => $total_score,
        'max_score' => $max_score,
        'passed'    => $passed ? 1 : 0,
    ),
    array( 'id' => $attempt_id )
);

// 8. Notify student
do_action( 'splms_quiz_manually_graded', $quiz_id, $user_id, $attempt_id, $percentage );
```

---

## Security

### Input Validation

```php
// Validate quiz ID
$quiz_id = absint( $_POST['quiz_id'] );
if ( ! $quiz_id || get_post_type( $quiz_id ) !== SPLMS_POST_TYPES['quiz'] ) {
    wp_die( 'Invalid quiz ID' );
}

// Validate answers
$answers = isset( $_POST['answers'] ) && is_array( $_POST['answers'] ) ? $_POST['answers'] : array();

// Sanitize text answers
foreach ( $answers as $question_id => &$answer ) {
    if ( is_string( $answer ) ) {
        $answer = sanitize_text_field( $answer );
    } elseif ( is_array( $answer ) ) {
        $answer = array_map( 'sanitize_text_field', $answer );
    }
}
```

### Nonce Verification

```php
// AJAX handlers
if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'splms_nonce' ) ) {
    wp_die( 'Security check failed' );
}
```

### Permission Checks

```php
// Check if user is logged in
if ( ! is_user_logged_in() ) {
    wp_die( 'You must be logged in to take quizzes' );
}

// Check if user is enrolled
if ( ! splms_is_user_enrolled( $course_id, $user_id ) ) {
    wp_die( 'You must be enrolled in this course' );
}

// Admin-only operations
if ( ! current_user_can( 'edit_post', $quiz_id ) ) {
    wp_die( 'Permission denied' );
}
```

### Anti-Cheating Measures

```php
// Time validation
$attempt = $attempts_query->get_attempt( $attempt_id );
$time_limit = $settings['time_limit'] * 60;  // convert to seconds
$elapsed = time() - strtotime( $attempt->attempt_time );

if ( $settings['time_limit_enabled'] && $elapsed > $time_limit + 60 ) {
    // Grace period of 60 seconds
    wp_die( 'Time limit exceeded' );
}

// Prevent answer tampering
$submitted_questions = array_keys( $answers );
$actual_questions = wp_list_pluck( $questions, 'id' );

if ( array_diff( $submitted_questions, $actual_questions ) ) {
    wp_die( 'Invalid question IDs detected' );
}
```

---

## Best Practices

### 1. Always Validate Attempt Ownership

```php
$attempt = $attempts_query->get_attempt( $attempt_id );
if ( $attempt->user_id !== $user_id ) {
    wp_die( 'Access denied' );
}
```

### 2. Check Attempt State Before Operations

```php
// Don't allow completing an already-completed attempt
if ( $attempt->time_taken > 0 ) {
    wp_die( 'This attempt is already completed' );
}
```

### 3. Use Transactions for Multi-Step Operations

```php
global $wpdb;
$wpdb->query( 'START TRANSACTION' );

try {
    // Update attempt
    $result1 = $attempts_query->complete_attempt( /* ... */ );

    // Update course progress
    $result2 = update_course_progress( /* ... */ );

    if ( $result1 && $result2 ) {
        $wpdb->query( 'COMMIT' );
    } else {
        $wpdb->query( 'ROLLBACK' );
    }
} catch ( Exception $e ) {
    $wpdb->query( 'ROLLBACK' );
}
```

### 4. Handle JSON Encoding Errors

```php
$json = wp_json_encode( $answers );
if ( JSON_ERROR_NONE !== json_last_error() ) {
    error_log( 'JSON encoding failed: ' . json_last_error_msg() );
    wp_die( 'Failed to save answers' );
}
```

### 5. Clean Up Abandoned Attempts

```php
// Cron job to clean up old in-progress attempts
function splms_cleanup_abandoned_attempts() {
    global $wpdb;
    $table = $wpdb->prefix . 'splms_quiz_attempts';

    // Delete attempts older than 7 days that are still in-progress
    $wpdb->query(
        "DELETE FROM {$table}
         WHERE time_taken = 0
         AND score = 0.00
         AND passed = 0
         AND attempt_time < DATE_SUB(NOW(), INTERVAL 7 DAY)"
    );
}
add_action( 'splms_daily_cleanup', 'splms_cleanup_abandoned_attempts' );
```

---

## Testing Checklist

### Quiz Creation
- [ ] Create graded quiz
- [ ] Create practice quiz
- [ ] Create survey
- [ ] Set passing grade
- [ ] Set time limit
- [ ] Set max attempts
- [ ] Enable password protection
- [ ] Add all question types

### Quiz Taking
- [ ] Start quiz attempt
- [ ] Answer all question types
- [ ] Test time limit countdown
- [ ] Test autosave functionality
- [ ] Test navigation (previous/next)
- [ ] Test randomization
- [ ] Submit quiz

### Grading
- [ ] Verify auto-grading accuracy for all types
- [ ] Test partial credit for multiple select
- [ ] Test partial credit for matching
- [ ] Test partial credit for ordering
- [ ] Manually grade essay questions
- [ ] Verify pass/fail calculation
- [ ] Test pending review status

### Attempts Management
- [ ] View attempt history
- [ ] Check best score calculation
- [ ] Test max attempts limit
- [ ] Verify attempt count
- [ ] Test attempt deletion
- [ ] Check duplicate attempt prevention

### Edge Cases
- [ ] Submit quiz with no answers
- [ ] Submit after time limit
- [ ] Submit with invalid question IDs
- [ ] Submit duplicate attempt
- [ ] Grade quiz with all essay questions
- [ ] Test with 0% score
- [ ] Test with 100% score

---

## Common Errors & Solutions

### Error: "Maximum attempts reached"

**Cause**: User has used all allowed attempts
**Solution**:
```php
// Increase max attempts or reset
$quizzes->update_quiz_settings( $quiz_id, array(
    'max_attempts' => 5,  // or 0 for unlimited
) );

// Or delete previous attempts (admin only)
foreach ( $attempts as $attempt ) {
    $attempts_query->delete_attempt( $attempt->id );
}
```

### Error: "Time limit exceeded"

**Cause**: Quiz submission after time limit
**Solution**:
```php
// Extend time limit
$quizzes->update_quiz_settings( $quiz_id, array(
    'time_limit' => 90,  // minutes
) );

// Or disable time limit
$quizzes->update_quiz_settings( $quiz_id, array(
    'time_limit_enabled' => false,
) );
```

### Error: "Failed to start quiz attempt"

**Cause**: Database insert failure or duplicate attempt
**Solution**:
```php
// Clear in-progress attempt
$attempts_query->clear_in_progress_attempt( $user_id, $quiz_id );

// Try again
$attempt_id = $attempts_query->start_attempt( $user_id, $quiz_id, $course_id );
```

### Error: "Incorrect password"

**Cause**: Wrong quiz password
**Solution**:
```php
// Update password
$quizzes->update_quiz_settings( $quiz_id, array(
    'quiz_password' => 'newpassword123',
) );

// Or disable password requirement
$quizzes->update_quiz_settings( $quiz_id, array(
    'require_password' => false,
) );
```

### Error: "No questions found"

**Cause**: Quiz has no questions
**Solution**:
```php
// Add questions via Questions Query
$questions_query->add_question( array(
    'quiz_id' => $quiz_id,
    'title'   => 'Question 1',
    'type'    => 'multiple_choice',
    // ...
) );
```

---

## Performance Optimization

### 1. Cache Quiz Settings

```php
function splms_get_quiz_settings_cached( $quiz_id ) {
    $cache_key = 'quiz_settings_' . $quiz_id;
    $settings = wp_cache_get( $cache_key, 'splms_quizzes' );

    if ( false === $settings ) {
        $settings = splms_get_quiz_settings( $quiz_id );
        wp_cache_set( $cache_key, $settings, 'splms_quizzes', HOUR_IN_SECONDS );
    }

    return $settings;
}
```

### 2. Batch Load Questions

```php
// Instead of loading questions one by one
foreach ( $quiz_ids as $quiz_id ) {
    $questions[ $quiz_id ] = splms_get_quiz_questions( $quiz_id );
}

// Load all at once
$questions = $questions_query->get_questions_for_quizzes( $quiz_ids );
```

### 3. Optimize Attempt Queries

```php
// Add database index
ALTER TABLE splms_quiz_attempts ADD INDEX user_quiz_status (user_id, quiz_id, passed, time_taken);

// Use indexed columns in WHERE clause
SELECT * FROM splms_quiz_attempts
WHERE user_id = %d
AND quiz_id = %d
AND time_taken > 0  -- Use indexed column
ORDER BY attempt_time DESC;
```

### 4. Lazy Load Questions on Frontend

```javascript
// Load questions via AJAX as needed instead of all at once
function loadQuestion(questionIndex) {
    $.ajax({
        url: splms_ajax.url,
        method: 'POST',
        data: {
            action: 'splms_get_quiz_question',
            question_index: questionIndex,
            quiz_id: quizId,
            nonce: splms_ajax.nonce
        },
        success: function(response) {
            displayQuestion(response.data);
        }
    });
}
```

---

## Migration Guide

### From Legacy Quiz System

```php
/**
 * Migrate old quiz attempts to new schema.
 */
function splms_migrate_quiz_attempts() {
    global $wpdb;

    // Old table structure
    $old_table = $wpdb->prefix . 'old_quiz_attempts';

    // Get all old attempts
    $old_attempts = $wpdb->get_results( "SELECT * FROM {$old_table}" );

    $attempts_query = SkillPulse_LMS_Quiz_Attempts_Query::get_instance();

    foreach ( $old_attempts as $old ) {
        // Map old fields to new schema
        $new_data = array(
            'user_id'      => $old->student_id,
            'quiz_id'      => $old->test_id,
            'course_id'    => $old->course_id,
            'answers'      => $old->responses,
            'score'        => $old->points_earned,
            'max_score'    => $old->points_possible,
            'passed'       => $old->grade >= 70 ? 1 : 0,
            'attempt_time' => $old->submitted_at,
            'time_taken'   => $old->duration,
        );

        $wpdb->insert(
            $wpdb->prefix . 'splms_quiz_attempts',
            $new_data,
            array( '%d', '%d', '%d', '%s', '%f', '%f', '%d', '%s', '%d' )
        );
    }

    echo 'Migrated ' . count( $old_attempts ) . ' attempts';
}
```

---

## Conclusion

The Quiz Module provides a comprehensive, flexible assessment system with:

- **12 question types** for diverse assessment needs
- **3 quiz types** (graded, practice, survey)
- **Intelligent auto-grading** with partial credit support
- **Manual grading workflow** for essays and uploads
- **Robust attempt tracking** with detailed analytics
- **Security features** including passwords and attempt limits
- **Flexible display options** for results and feedback

For issues or improvements, see `QUIZ-ISSUES.md`.

---

**Last Updated**: 2026-01-04
**Maintainer**: SkillPulse Development Team
**Support**: https://github.com/skillpulse-lms/issues
