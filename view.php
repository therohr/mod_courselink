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
 * Renders the linked course card (name, description, link, completion status)
 * and triggers the course-module viewed event for activity completion purposes.
 *
 * @package   mod_courselink
 * @copyright 2026 Your Name <you@example.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->dirroot . '/mod/courselink/lib.php');

// -----------------------------------------------------------------------
// Bootstrap: validate params, load records, check access.
// -----------------------------------------------------------------------

$id = required_param('id', PARAM_INT); // Course-module id.

$cm      = get_coursemodule_from_id('courselink', $id, 0, false, MUST_EXIST);
$course  = get_course($cm->course);
$instance = $DB->get_record('courselink', ['id' => $cm->instance], '*', MUST_EXIST);

require_login($course, true, $cm);

$context = context_module::instance($cm->id);
require_capability('mod/courselink:view', $context);

// -----------------------------------------------------------------------
// Page setup.
// -----------------------------------------------------------------------

$PAGE->set_url('/mod/courselink/view.php', ['id' => $cm->id]);
$PAGE->set_title(format_string($instance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

// -----------------------------------------------------------------------
// Resolve target course.
// -----------------------------------------------------------------------

$targetcourse = $DB->get_record('course', ['id' => $instance->targetcourseid]);

// -----------------------------------------------------------------------
// Determine completion status for the target course.
// -----------------------------------------------------------------------

$iscomplete  = false;
$isenrolled  = false;
$targetmissing = false;

if (!$targetcourse) {
    $targetmissing = true;
} else {
    $isenrolled = is_enrolled(context_course::instance($targetcourse->id), $USER->id, '', true);

    $completionrecord = $DB->get_record_select(
        'course_completions',
        'userid = :userid AND course = :course AND timecompleted IS NOT NULL',
        ['userid' => $USER->id, 'course' => $targetcourse->id]
    );
    $iscomplete = !empty($completionrecord);
}

// -----------------------------------------------------------------------
// Trigger viewed event (required for COMPLETION_TRACKS_VIEWS if enabled).
// -----------------------------------------------------------------------

$event = \mod_courselink\event\course_module_viewed::create([
    'objectid' => $instance->id,
    'context'  => $context,
]);
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('courselink', $instance);
$event->trigger();

// Notify completion system this activity has been viewed.
$completion = new completion_info($course);
$completion->set_module_viewed($cm);

// -----------------------------------------------------------------------
// Build template context and render.
// -----------------------------------------------------------------------

$targeturl = $targetcourse
    ? (new moodle_url('/course/view.php', ['id' => $targetcourse->id]))->out(false)
    : null;

$templatecontext = [
    'activityname'    => format_string($instance->name),
    'hasintro'        => !empty($instance->intro),
    'intro'           => format_module_intro('courselink', $instance, $cm->id),
    'targetmissing'   => $targetmissing,
    'targetname'      => $targetcourse ? format_string($targetcourse->fullname) : '',
    'targeturl'       => $targeturl,
    'isenrolled'      => $isenrolled,
    'notenrolledmsg'  => get_string('notenrolled', 'mod_courselink'),
    'iscomplete'      => $iscomplete,
    'statuslabel'     => $iscomplete
        ? get_string('status_complete', 'mod_courselink')
        : get_string('status_incomplete', 'mod_courselink'),
    'statusclass'     => $iscomplete ? 'success' : 'warning',
    'gotocoursestr'   => get_string('gotocourse', 'mod_courselink'),
    'targetmissingmsg' => get_string('targetcoursemissing', 'mod_courselink'),
    'completionstatus' => get_string('completionstatus', 'mod_courselink'),
];

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('mod_courselink/view', $templatecontext);
echo $OUTPUT->footer();
