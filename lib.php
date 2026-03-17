<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Library functions for mod_courselink.
 *
 * These are the functions Moodle core calls by convention. They must exist in
 * lib.php and must be named {modname}_{hookname}.
 *
 * @package   mod_courselink
 * @copyright 2026 Your Name <you@example.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// ---------------------------------------------------------------------------
// Core CRUD hooks
// ---------------------------------------------------------------------------

/**
 * Add a new courselink instance.
 *
 * Called by Moodle core when a teacher saves the activity settings form.
 *
 * @param  stdClass $data  Form data from mod_form.
 * @param  object   $mform The form object (unused but required by signature).
 * @return int             The new instance id.
 */
function courselink_add_instance(stdClass $data, $mform = null): int {
    global $DB;

    $data->timecreated  = time();
    $data->timemodified = time();

    return $DB->insert_record('courselink', $data);
}

/**
 * Update an existing courselink instance.
 *
 * @param  stdClass $data  Form data from mod_form.
 * @param  object   $mform The form object (unused but required by signature).
 * @return bool            True on success.
 */
function courselink_update_instance(stdClass $data, $mform = null): bool {
    global $DB;

    $data->id           = $data->instance;
    $data->timemodified = time();

    return $DB->update_record('courselink', $data);
}

/**
 * Delete a courselink instance and any associated data.
 *
 * @param  int  $id The instance id.
 * @return bool     True on success.
 */
function courselink_delete_instance(int $id): bool {
    global $DB;

    if (!$DB->record_exists('courselink', ['id' => $id])) {
        return false;
    }

    $DB->delete_records('courselink', ['id' => $id]);

    return true;
}

// ---------------------------------------------------------------------------
// Feature support flags
// ---------------------------------------------------------------------------

/**
 * Declare which Moodle features this module supports.
 *
 * @param  string $feature FEATURE_* constant from lib/moodlelib.php.
 * @return mixed           True, false, or null (null = unknown/unsupported).
 */
function courselink_supports(string $feature) {
    switch ($feature) {
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS:
            return false;
        case FEATURE_COMPLETION_HAS_RULES:
            return true;
        case FEATURE_GRADE_HAS_GRADE:
            return false;
        case FEATURE_BACKUP_MOODLE2:
            return false; // Backup/restore support can be added in a later release.
        case FEATURE_MOD_PURPOSE:
            return MOD_PURPOSE_CONTENT;
        default:
            return null;
    }
}

// ---------------------------------------------------------------------------
// Completion
// ---------------------------------------------------------------------------

/**
 * Return the completion rule descriptions shown in the activity completion UI.
 *
 * Called by Moodle core (4.0+). The actual logic lives in
 * classes/completion/custom_completion.php.
 *
 * @param  stdClass $coursemodule Row from mdl_course_modules.
 * @return array                  Array of description strings keyed by rule name.
 */
function courselink_get_completion_active_rule_descriptions(stdClass $coursemodule): array {
    global $DB;

    $descriptions = [];

    $instance = $DB->get_record('courselink', ['id' => $coursemodule->instance]);
    if (!$instance || empty($instance->completiontracking)) {
        return $descriptions;
    }

    $targetcourse = get_course($instance->targetcourseid);
    if (!$targetcourse) {
        return $descriptions;
    }

    $descriptions['completiontracking'] = get_string(
        'completiondetail:targetcourse',
        'mod_courselink',
        format_string($targetcourse->fullname)
    );

    return $descriptions;
}
