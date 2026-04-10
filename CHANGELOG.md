# Changelog

All notable changes to mod_courselink will be documented in this file.

## [1.2.1] - 2026-04-10

### Fixed
- Activity duplication now works correctly via Moodle's backup/restore system
- Enabled `FEATURE_BACKUP_MOODLE2` so the "Duplicate" action in the course page context menu functions properly
- Fixed `courselink_queue_backfill()` call in restore step (renamed to `courselink_backfill_completion()`)
- Removed dead `courselink_duplicate_activity()` stub that was never called by Moodle core

## [1.2.0] - 2026-04-10

### Added
- Group support: Course Link activities now support Moodle groups and groupings
- Declared `FEATURE_GROUPS` and `FEATURE_GROUPINGS` so teachers can restrict activity visibility by group
- Group mode selector now appears in the activity settings form and course page context menu
- Groupings support allows fine-grained access control when using groupings

## [1.1.0] - 2026-04-10

### Added
- Activity duplication support: Course Link activities can now be duplicated when duplicating courses
- `courselink_duplicate_activity()` function to handle activity copying with settings preservation
- Automatic completion backfill when duplicating activities to new courses
- Comprehensive duplication feature documentation in `DUPLICATION_FEATURE.md`

## [1.0.3] - 2026-03-25

### Added
- Initial stable release of mod_courselink
- Core Course Link activity module for embedding course links
- Activity-level completion tracking for linked courses
- Custom completion API implementation
- Privacy provider for GDPR compliance
- Event observers for automatic completion syncing
- Scheduled tasks for completion reconciliation
- Support for course reset

### Features
- Select target course from dropdown (max 500 courses)
- Enable "require completion of target course" option
- Automatic completion state synchronization via 4 mechanisms:
  - Course completion observer
  - Course completion update observer
  - Course reset observer
  - Nightly reconciliation task
- Direct links to target courses
- No custom data storage (uses core completion tables)

## [1.0.0] - 2026-03-20

### Initial Release
- Basic courselink activity module
- Target course selection
- Completion tracking foundation
