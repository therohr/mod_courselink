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
 * Language strings for mod_courselink.
 *
 * @package   mod_courselink
 * @copyright 2026 David Rohr (tidewatercreative.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['completiondetail:targetcourse'] = 'Complete the linked course: {$a}';
$string['completionstatus']              = 'Your completion status';
$string['completiontracking']            = 'Require completion of target course';
$string['completiontracking_help']       = 'When enabled, this activity is only marked complete once the student has completed the selected target course. Use the Restrict access settings on downstream activities to gate them on this completion.';
$string['courselink:addinstance']        = 'Add a course link activity';
$string['courselink:view']               = 'View a course link activity';
$string['gotocourse']                    = 'Go to course';
$string['modulename']                    = 'Course link';
$string['modulename_help']               = 'The Course link activity displays a link to another course and tracks whether the student has completed it. Downstream activities can be gated on that completion.';
$string['modulenameplural']              = 'Course links';
$string['nocourseselected']              = 'You must select a target course.';
$string['notenrolled']                   = 'You are not currently enrolled in the target course. Contact your administrator if you believe this is an error.';
$string['pluginadministration']          = 'Course link administration';
$string['pluginname']                    = 'Course link';
$string['privacy:metadata:core_completion'] =
    'The Course link activity reads course completion records from the Moodle ' .
    'core completion subsystem to determine whether a student has completed the ' .
    'linked course. No personal data is stored by this plugin directly.';
$string['status_complete']               = 'Complete';
$string['status_incomplete']             = 'Not yet complete';
$string['targetcourseid']                = 'Target course';
$string['targetcourseid_help']           = 'Select the course whose completion will be tracked and linked from this activity.';
$string['targetcoursemissing']           = 'The target course could not be found. Please contact your course administrator.';
$string['targetcourse']                  = 'Linked course';
$string['task_check_completion']         = 'Course link: backfill activity completion';
