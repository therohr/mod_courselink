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

namespace mod_courselink;

/**
 * Event observer for mod_courselink.
 *
 * Listens for \core\event\course_completed. When fired, finds every
 * courselink activity whose targetcourseid matches the just-completed
 * course and re-evaluates the activity completion state for that user.
 * This provides real-time gating without relying on cron lag.
 *
 * @package   mod_courselink
 * @copyright 2026 Your Name <you@example.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class observer {

    /**
     * Handle the course_completed event.
     *
     * Iterates over all courselink instances that reference the completed
     * course and triggers a completion re-check for the affected user.
     *
     * @param  \core\event\course_completed $event The fired event.
     * @return void
     */
    public static function course_completed(\core\event\course_completed $event): void {
        global $DB;

        $completedcourseid = (int) $event->courseid;
        $userid            = (int) $event->relateduserid;

        if (!$completedcourseid || !$userid) {
            return;
        }

        // Find all courselink instances that track the completed course.
        $instances = $DB->get_records('courselink', ['targetcourseid' => $completedcourseid]);

        if (empty($instances)) {
            return;
        }

        foreach ($instances as $instance) {
            // Load the course-module record for this instance.
            $cm = get_coursemodule_from_instance('courselink', $instance->id, $instance->course, false, IGNORE_MISSING);

            if (!$cm) {
                // Activity may have been deleted; skip cleanly.
                continue;
            }

            // Load completion info for the host course.
            $course     = get_course($instance->course);
            $completion = new \completion_info($course);

            if (!$completion->is_enabled($cm)) {
                continue;
            }

            // Re-evaluate and persist the completion state for this user.
            // Passing COMPLETION_AND_CONDITION forces a fresh recalculation.
            $completion->update_state($cm, COMPLETION_UNKNOWN, $userid);
        }
    }
}
