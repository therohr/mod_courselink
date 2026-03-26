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
 * View page for mod_courselink.
 *
 * Redirects the student directly to the target course. If the target course
 * has been deleted, renders an error page instead. The course_module_viewed
 * event and completion tracking are handled before any output or redirect.
 *
 * @package   mod_courselink
 * @copyright 2026 David Rohr (tidewatercreative.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');

// Bootstrap: validate params, load records, check access.

$id = required_param('id', PARAM_INT); // Course-module id.

$cm       = get_coursemodule_from_id('courselink', $id, 0, false, MUST_EXIST);
$course   = get_course($cm->course);
$instance = $DB->get_record('courselink', ['id' => $cm->instance], '*', MUST_EXIST);

require_login($course, true, $cm);

$context = context_module::instance($cm->id);
require_capability('mod/courselink:view', $context);

// Page setup — required even when redirecting so events are correctly attributed.
$PAGE->set_url('/mod/courselink/view.php', ['id' => $cm->id]);
$PAGE->set_title($course->shortname . ': ' . $instance->name);
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

// Trigger the viewed event and mark the module viewed for completion.
$event = \mod_courselink\event\course_module_viewed::create([
    'objectid' => $instance->id,
    'context'  => $context,
]);
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('courselink', $instance);
$event->trigger();

$completion = new completion_info($course);
$completion->set_module_viewed($cm);

// Redirect to the target course, or show an error page if it is missing.
if (empty($instance->targetcourseid) || !$DB->record_exists('course', ['id' => $instance->targetcourseid])) {
    echo $OUTPUT->header();
    echo $OUTPUT->notification(get_string('targetcoursemissing', 'mod_courselink'), 'error');
    echo $OUTPUT->footer();
} else {
    redirect(new moodle_url('/course/view.php', ['id' => $instance->targetcourseid]));
}
