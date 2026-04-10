# mod_courselink — Development Guide for Claude

## Moodle Plugin Development

- Moodle 5 uses `/public/` as the web root. Plugin paths must be under `/public/` (e.g., `/public/mod/`, `/public/local/`).
- Plugin type determines directory: `mod_*` goes in `/mod/`, `local_*` goes in `/local/`, `block_*` goes in `/blocks/`.
- Never overwrite or regenerate `version.php` — always preserve the existing file and only make targeted edits.
- When copying or moving plugin directories, always verify the destination path exists and matches the plugin type prefix (`mod_`, `local_`, `block_`) before executing the copy command.

---

## General Workflow Rules

- After any file copy or move operation, run `ls` on the target directory to confirm files landed in the correct location before proceeding.

---

## Quick Context

**Plugin:** Course Link Activity Module  
**Purpose:** An activity that embeds a link to another course and automatically tracks completion. When a student completes the linked course, the activity is marked complete on the host course.

**Author:** David Rohr  
**Current Version:** 1.0.3  
**Requires:** Moodle 4.5+  
**License:** GNU GPL v3

---

## Development Environment

### Setup
- **Moodle Installation:** Windows 10 Pro, Laragon (fast local dev server)
- **Moodle Location:** `C:\dev\moodle\`
- **Plugin Source Code:** `C:\dev\plugins\mod_courselink\`
- **Installation Method:** Symlink from `C:\dev\moodle\public\mod\courselink` → `C:\dev\plugins\mod_courselink`

### Key Detail
Changes made in `C:\dev\plugins\mod_courselink\` are immediately visible to Moodle (no copy/rebuild step needed). Refresh Moodle to see changes.

---

## How It Works

### The Problem
Teachers need to embed links to other courses in their course. Students must complete the linked course, and the host course needs to track this completion automatically.

### The Solution
- Activity added to a course with a dropdown to select the target course
- Teacher can enable "Require completion of target course" on the same form
- Student sees a direct link that opens the target course in a new tab
- Completion state is automatically synced 4 ways:
  1. `course_completed` observer → promotes to complete
  2. `course_completion_updated` observer → demotes if revoked
  3. `course_reset_ended` observer → bulk demote on reset
  4. Nightly scheduled task → reconciliation/catch-all

### No Manual Data Store
- Uses Moodle core's `mdl_course_completions` table
- Implements `activity_custom_completion` API
- Implements privacy provider (GDPR compliant)

---

## Key Files & Architecture

### Core Files
- **`version.php`** — Plugin metadata (version, requires, maturity)
- **`lib.php`** — Main plugin API (hooks, callback functions)
- **`mod_form.php`** — Activity settings form (course selector, completion checkbox)
- **`view.php`** — Student-facing page (just redirects to linked course)

### Completion
- **`classes/completion/custom_completion.php`** — Implements `activity_custom_completion` API
- **`db/install.xml`** — Database schema (minimal — only `mdl_courselink` activity table)

### Event Observers & Tasks
- **`db/events.php`** — Registers observers
- **`classes/observer.php`** — Event handler logic
- **`db/tasks.php`** — Scheduled/ad hoc tasks
- **`classes/task/*.php`** — Task implementations:
  - `update_user_completion.php` — Promote/demote on observer trigger
  - `check_completion.php` — Nightly reconciliation
  - `sync_user_completion.php` — Backfill on activity create/edit
  - `sync_course_reset.php` — Bulk demote on course reset
  - `backfill_completion.php` — Initial backfill

### Backup/Restore
- **`backup/moodle2/`** — Backup/restore classes (includes activity state)

### UI & Languages
- **`lang/en/courselink.php`** — English strings
- **`templates/view.mustache`** — Activity view template
- **`pix/monologo.svg`** — Plugin icon

---

## Testing & Verification

### Manual Testing Checklist
1. **Add activity:** Create a Course Link activity, select a target course
2. **Enable completion rule:** Check "Require completion of target course"
3. **Test as student:** Complete the linked course → check activity marked complete
4. **Test demotion:** Admin revokes completion → check activity marked incomplete
5. **Test backfill:** Change target course → check existing completions updated
6. **Test reset:** Reset target course → check host course activity completions cleared

### Common Scenarios
- **Completion observer lag:** If completion doesn't sync immediately, check ad hoc task queue
- **Backfill timeout:** Large course links may take time to backfill
- **Wrong course selected:** Verify course selector loads correctly (max 500 courses)

---

## Code Style & Conventions

- **Language:** PHP (Moodle standard)
- **Namespace:** `mod_courselink\*` for classes
- **Standards:** Moodle coding standards (https://moodledev.io/dev/policies/codingstyle)
- **No external dependencies** (pure Moodle core)

---

## Git Workflow

- **Repository Location:** `C:\dev\plugins\mod_courselink` (separate from Moodle)
- **Remote:** Check `.git/config` for upstream
- **Branches:** Use `main` for stable, feature branches for new work
- **Commits:** Clear, atomic commits with descriptive messages

---

## How to Work with Claude

### Best Practices
1. **Describe the feature/bug clearly** — Include the scenario, expected behavior, actual behavior
2. **Include error messages** — If there's a Moodle error, paste the full debug output
3. **Specify test steps** — How to reproduce or verify the fix
4. **Reference Moodle API** — If unsure, I'll look up the current API

### What I Can Help With
- ✅ Fix bugs and add features
- ✅ Debug completion sync issues
- ✅ Optimize queries or task performance
- ✅ Write/update unit tests
- ✅ Refactor code while maintaining functionality
- ✅ Troubleshoot observer/task issues

### Context-Specific Tasks
- **"Add X feature"** → I'll check the code, suggest approach, implement
- **"Fix completion not syncing"** → I'll trace through observer/task logic
- **"Optimize the nightly task"** → I'll profile and suggest improvements

---

## Debugging

### Enable Debug Mode
In `C:\dev\moodle\config.php`:
```php
@error_reporting(E_ALL | E_STRICT);
@ini_set('display_errors', '1');
$CFG->debug = (DEBUG_ALL | DEBUG_DEVELOPER);
$CFG->debugdisplay = 1;
```

### View Logs
- **Cron/task logs:** Site Admin → Reports → Logs (filter by task component)
- **Database:** `mdl_log` table
- **Browser console:** Check for JavaScript errors

### Common Issues
- **Course not appearing in selector:** Check course visibility and user permissions
- **Completion not syncing:** Check task queue in `mdl_adhoc_tasks`
- **Database errors:** Check `mdl_courselink` schema against `db/install.xml`

---

## Performance Notes

- **Nightly task:** Runs on all courselinks; can be slow on 1000+ activities
- **Backfill:** Deferred to ad hoc task to avoid form submission timeout
- **Course selector:** Limited to 500 courses; contact admin if site has more

---

## Resources

- **Moodle Dev Docs:** https://moodledev.io/
- **Activity Modules:** https://moodledev.io/docs/guides/development/activity
- **Custom Completion:** https://moodledev.io/dev/plugins/custom_completion
- **Privacy API:** https://moodledev.io/dev/plugins/privacy
