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

namespace mod_courselink\task;

/**
 * Ad hoc task: re-evaluate courselink completion for all users after a course reset.
 *
 * Queued by the observer when a target course is reset. Runs outside the
 * originating HTTP request so that bulk backfill cannot cause a timeout.
 *
 * Custom data keys:
 *  - resetcourseid  (int) The course that was reset.
 *
 * @package   mod_courselink
 * @copyright 2026 David Rohr (tidewatercreative.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class sync_course_reset extends \core\task\adhoc_task {
    /**
     * Return the component that owns this task.
     *
     * @return string
     */
    public function get_component(): string {
        return 'mod_courselink';
    }

    /**
     * Execute the task.
     *
     * Calls courselink_backfill_completion() for every courselink instance
     * that tracked the reset course. The backfill handles demotion for users
     * who were previously complete and are now not.
     *
     * @return void
     */
    public function execute(): void {
        global $CFG, $DB;

        require_once($CFG->libdir . '/completionlib.php');
        require_once($CFG->dirroot . '/mod/courselink/lib.php');

        $data          = $this->get_custom_data();
        $resetcourseid = (int) $data->resetcourseid;

        if (!$resetcourseid) {
            mtrace('mod_courselink sync_course_reset: missing resetcourseid — skipping.');
            return;
        }

        $instances = $DB->get_records('courselink', ['targetcourseid' => $resetcourseid]);

        if (empty($instances)) {
            mtrace("mod_courselink sync_course_reset: no courselink instances track course $resetcourseid.");
            return;
        }

        mtrace("mod_courselink sync_course_reset: processing " . count($instances)
            . " instance(s) for reset course $resetcourseid.");

        foreach ($instances as $instance) {
            courselink_backfill_completion(
                (int) $instance->course,
                (int) $instance->id,
                $resetcourseid
            );
            mtrace("  instance {$instance->id} (host course {$instance->course}) re-evaluated.");
        }
    }
}
