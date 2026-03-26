# Course Link — mod_courselink

A Moodle activity module that embeds a direct link to another course and automatically tracks whether the student has completed it. Completion of the linked course drives activity completion on the host course, enabling downstream restriction rules and learning-path gating without any manual grading or custom reports.

**Author:** David Rohr — [tidewatercreative.com](https://tidewatercreative.com)
**Requires:** Moodle 4.5+ (tested on Moodle Workplace 5.0)
**Maturity:** Stable
**License:** GNU GPL v3 or later

---

## How it works

### Activity setup

A teacher adds a **Course Link** activity to a course and selects the target course from a searchable list. On Moodle Workplace the list is automatically filtered to the teacher's tenant. The teacher can also enable the **"Require completion of target course"** completion rule on the same settings screen.

The course selector loads up to 500 courses. On sites with more than 500 visible courses, contact your administrator to discuss replacing the selector with an AJAX datasource.

### Student experience

The activity name on the course page is a direct link that opens the target course in a new tab — no intermediate view page. If the teacher enabled "Display description on course page", the description appears inline beneath the link.

### Completion tracking

Completion state is evaluated in four complementary ways so that no change is ever missed:

| Trigger | Direction | How it works |
|---|---|---|
| **`course_completed` observer** | Promote | When a student finishes the target course the observer immediately re-evaluates and marks the courselink activity complete on the host course. |
| **`course_completion_updated` observer** | Demote | When an admin revokes a user's completion, or the reaggregation cron nulls out `timecompleted`, the observer immediately demotes the courselink activity back to incomplete. |
| **`course_reset_ended` observer** | Demote (bulk) | When the target course is reset via Moodle's course-reset UI, all users previously marked complete are demoted in one pass. |
| **Nightly scheduled task** | Promote + Demote | A background task reconciles all courselink instances nightly. Catches completions missed by any of the above (e.g. bulk imports, direct DB writes on non-SaaS instances). |

### Backfill on add / edit

When the activity is first created, or the target course is changed, the plugin retroactively marks complete any enrolled students who have already finished the target course — and demotes any who were previously marked complete but no longer are.

### Custom completion rule

The plugin implements Moodle's `activity_custom_completion` API (introduced in Moodle 4.0). The completion rule **"Complete the linked course: [course name]"** is shown in the activity completion UI and in students' progress reports. The completion state is derived in real time from `mdl_course_completions` — no separate data store is maintained.

### Privacy

Course Link stores no personal data. It reads `mdl_course_completions`, which is owned and managed by Moodle core (`core_completion`). The privacy provider correctly declares this and satisfies GDPR requirements out of the box.

---

## Installation

1. Download `mod_courselink_v1_0_2.zip`.
2. In Moodle, go to **Site administration → Plugins → Install plugins**.
3. Upload the zip and follow the on-screen prompts.
4. Complete the database upgrade (adds the `mdl_courselink` table).

Alternatively, extract the zip so that the `courselink` folder sits at `{moodleroot}/mod/courselink/`, then visit **Site administration** to trigger the upgrade.

### Requirements

- Moodle 4.5 or later (Moodle Workplace 5.0 supported and tested)
- Course completion must be enabled site-wide: **Site administration → Advanced features → Enable completion tracking**
- Completion must also be enabled on each host course that uses this activity

---

## Configuration

No site-wide admin settings are required. All configuration is per-activity:

| Setting | Description |
|---|---|
| **Target course** | The course whose completion is tracked. Searchable autocomplete, Workplace tenant-filtered. Capped at 500 results. |
| **Require completion of target course** | When enabled (default), the activity counts as complete only when the student has completed the target course. Disable to use the activity as a display-only link. |

---

## Known limitations

- **Workplace Programs and Certifications:** If a student completes the target course via a Workplace Program or Certification pathway rather than through direct course completion, the real-time observer may not fire immediately. The nightly scheduled task will reconcile the state overnight.
- **Backup and restore:** Backup/restore of activity instances is not yet supported (`FEATURE_BACKUP_MOODLE2` is disabled). The activity can be re-added manually after a course restore.
- **Cross-tenant linking:** Linking to a course in a different Workplace tenant is possible but not recommended. The target course selector filters by the teacher's tenant.
- **Large course lists:** The target-course autocomplete is capped at 500 results. On very large tenants some courses may not appear in the selector.

---

## Changelog

### 1.0.3 (2026-03-25)
- Fixed HTTP request timeout (503) when a user completes the last activity in a
  linked course. The event observer was calling `get_fast_modinfo()` and
  `update_state()` synchronously inside the originating AJAX request, causing it
  to exceed the web server timeout on Moodle Workplace.
- All observer methods are now thin dispatchers only: they extract the minimum
  event payload and queue an ad hoc task, then return immediately. No DB-heavy
  work or modinfo lookups occur inside an HTTP request.
- Added `task\sync_user_completion` ad hoc task: handles promotion and demotion
  for a single user after `course_completed` or `course_completion_updated` fires.
- Added `task\sync_course_reset` ad hoc task: handles bulk demotion for all
  users after `course_reset_ended` fires.
- `queue_adhoc_task($task, true)` deduplication flag set to prevent redundant
  tasks when the same event fires multiple times in quick succession.

### 1.0.2 (2026-03-24)
- Added real-time demotion observer for individual completion revocations
  (`\core\event\course_completion_updated`). Courselink activities are now
  immediately demoted to incomplete when an admin revokes a user's target-course
  completion or the reaggregation cron nulls out `timecompleted`.
- Changed both existing observers and new observer to `internal => false` so they
  run outside the originating database transaction, preventing a failed
  `update_state()` call from rolling back the triggering completion record.
- Fixed `get_fast_modinfo()` called once per instance in observer loop; now called
  once per host course, eliminating redundant modinfo rebuilds on multi-instance courses.
- Fixed `context_course::instance()` called inside per-user loop in
  `courselink_backfill_completion()`; hoisted outside loop.
- Fixed `view.php` global declarations (`$OUTPUT`, `$USER`) and moved instance
  DB fetch to after `require_login()`.
- Fixed `view.php` target-course existence check to use `record_exists()` instead
  of fetching the full record.
- Capped target-course selector at 500 results (`MAX_COURSE_LIST`) to prevent
  memory exhaustion on large Workplace tenants.
- Changed scheduled task from hourly to nightly (02:00). Real-time observers handle
  immediate updates; the task is a safety net only.
- Removed `targettcourse` language string (double-t typo); corrected to `targetcourse`.
- Fixed Mustache template copyright placeholder.
- CodeSniffer clean — 0 errors, 0 warnings.

### 1.0.1 (2026-03-24)
- Internal build; superseded by 1.0.2.

### 1.0.0 (2026-03-17)
- Initial stable release.
- Real-time completion tracking via `course_completed` event observer.
- Bidirectional completion sync (promote and demote) via UNION backfill query.
- Course reset awareness via `course_reset_ended` event.
- Scheduled task safety net.
- Direct-link from course page (opens target course in new tab).
- Moodle Workplace tenant-aware course selector.
- Full privacy API implementation.
- CodeSniffer compliant.
