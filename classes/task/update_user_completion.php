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
 * Ad-hoc task: update courselink completion for a single user.
 *
 * Queued by the course_completed observer so that the web request returns
 * immediately. Cron processes this task in the background.
 *
 * Custom data shape: { userid: int, targetcourseid: int }
 *
 * @package   mod_courselink
 * @copyright 2026 David Rohr (tidewatercreative.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class update_user_completion extends \core\task\adhoc_task {
    /**
     * Execute the task.
     *
     * Finds every courselink instance pointing at the completed target course
     * and updates the completion state for the affected user.
     *
     * @return void
     */
    public function execute(): void {
        global $CFG, $DB;

        require_once($CFG->libdir . '/completionlib.php');

        $data           = $this->get_custom_data();
        $userid         = (int) $data->userid;
        $targetcourseid = (int) $data->targetcourseid;

        if (!$userid || !$targetcourseid) {
            return;
        }

        $instances = $DB->get_records('courselink', ['targetcourseid' => $targetcourseid]);

        if (empty($instances)) {
            return;
        }

        // Group by host course so get_fast_modinfo() is called once per course,
        // not once per instance.
        $bycourse = [];
        foreach ($instances as $instance) {
            $bycourse[(int) $instance->course][] = $instance;
        }

        foreach ($bycourse as $courseid => $courseinstances) {
            $modinfo    = get_fast_modinfo($courseid, $userid);
            $course     = get_course($courseid);
            $completion = new \completion_info($course);

            foreach ($courseinstances as $instance) {
                $cminfo = null;
                foreach ($modinfo->get_instances_of('courselink') as $cm) {
                    if ((int) $cm->instance === (int) $instance->id) {
                        $cminfo = $cm;
                        break;
                    }
                }

                if (!$cminfo || !$completion->is_enabled($cminfo)) {
                    continue;
                }

                $completion->update_state($cminfo, \COMPLETION_UNKNOWN, $userid);
            }
        }
    }
}
