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
 * @copyright 2026 Your Name <you@example.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Core plugin strings.
$string['modulename']        = 'Course link';
$string['modulenameplural']  = 'Course links';
$string['modulename_help']   = 'The Course link activity displays a link to another course and tracks whether the student has completed it. Downstream activities can be gated on that completion.';
$string['pluginname']        = 'Course link';
$string['pluginadministration'] = 'Course link administration';

// Capabilities.
$string['courselink:addinstance'] = 'Add a course link activity';
$string['courselink:view']        = 'View a course link activity';

// Settings form.
$string['targetcourseid']         = 'Target course';
$string['targetcourseid_help']    = 'Select the course whose completion will be tracked and linked from this activity.';
$string['completiontracking']     = 'Require completion of target course';
$string['completiontracking_help'] = 'When enabled, this activity is only marked complete once the student has completed the selected target course. Use the Restrict access settings on downstream activities to gate them on this completion.';
$string['nocourseselected']       = 'You must select a target course.';

// View page.
$string['targettcourse']          = 'Linked course';
$string['gotocourse']             = 'Go to course';
$string['completionstatus']       = 'Your completion status';
$string['status_complete']        = 'Complete';
$string['status_incomplete']      = 'Not yet complete';
$string['notenrolled']            = 'You are not currently enrolled in the target course. Contact your administrator if you believe this is an error.';
$string['targetcoursemissing']    = 'The target course could not be found. Please contact your course administrator.';

// Custom completion rule.
$string['completiondetail:targetcourse'] = 'Complete the linked course: {$a}';
