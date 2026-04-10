# Activity Duplication Support for mod_courselink

## Overview

As of version 1.1.0, mod_courselink fully supports activity duplication when duplicating courses.

## What It Does

When a course containing Course Link activities is duplicated:

1. **Automatic Duplication**: Each Course Link activity is automatically created in the new course with identical settings
2. **Maintained Configuration**: The target course and completion settings are preserved in the duplicated activity
3. **Completion Backfill**: Any users already enrolled in the new course who have completed the target course will automatically have their completion status synced

## Implementation Details

### Function Signature

```php
function courselink_duplicate_activity(stdClass $coursemodule, stdClass $newcoursemodule): stdClass
```

### Parameters

- `$coursemodule`: The original course module object (from mdl_course_modules)
- `$newcoursemodule`: The new course module object with updated course context

### Returns

- `stdClass`: The newly created courselink instance

### What Gets Duplicated

- `name`: Activity name
- `intro`: Activity description
- `introformat`: Description formatting
- `targetcourseid`: The linked target course ID
- `completiontracking`: Whether completion tracking is enabled

### What Doesn't Get Duplicated

- Activity completions (users' individual completion status) - these are handled separately by Moodle's course duplication process
- User data (there is none - courselink is a read-only linking activity)

## Testing the Feature

### Manual Test Steps

1. Create a course with a Course Link activity pointing to another course
2. Enable completion tracking on the Course Link activity
3. Duplicate the entire course using Moodle's course duplication tool
4. Verify that:
   - The duplicated course contains the Course Link activity
   - The target course setting is preserved
   - The completion tracking setting is preserved
   - Any enrolled users who completed the target course have their completion status synced

### Automated Testing

The duplication function can be tested in unit tests using PHPUnit:

```php
// Create original activity
$original = $DB->get_record('courselink', ['id' => $instance->id]);

// Call duplication function
$duplicated = courselink_duplicate_activity($coursemodule, $newcoursemodule);

// Verify
assert($duplicated->targetcourseid === $original->targetcourseid);
assert($duplicated->completiontracking === $original->completiontracking);
assert($duplicated->course === $newcoursemodule->course);
```

## Moodle Compatibility

- Requires Moodle 4.5 or later
- Uses Moodle's standard activity duplication callback mechanism
- No external dependencies

## Database Impact

- Creates one new row in `mdl_courselink` per duplicated activity
- May trigger completion syncs if configured on the new activity
- Does not modify the original activity or course data

## Troubleshooting

### Activity not duplicating

Check that:
1. The original course has the Course Link activity installed
2. You have permission to duplicate the course
3. Moodle cache has been cleared (visit Site Admin > Development > Purge caches)

### Completion not syncing after duplication

Check that:
1. The target course is accessible to the duplicated course's users
2. Completion tracking is enabled on the duplicated activity
3. Check scheduled tasks: Site Admin > Server > Scheduled tasks

## Version History

- **1.1.0** (2026-04-10): Initial implementation of activity duplication support
