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
 * Restore structure step for mod_courselink.
 *
 * @package   mod_courselink
 * @copyright 2026 David Rohr (tidewatercreative.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Restores a single courselink activity instance from backup XML.
 */
class restore_courselink_activity_structure_step extends restore_activity_structure_step {
    /**
     * Declare the XML path elements to process during restore.
     *
     * @return restore_path_element[]
     */
    protected function define_structure() {
        $paths = [];
        $paths[] = new restore_path_element('courselink', '/activity/courselink');
        return $this->prepare_activity_structure($paths);
    }

    /**
     * Process a restored courselink element.
     *
     * The targetcourseid is remapped via get_mappingid() so that when the linked
     * course is also present in the backup package the reference is updated.
     * targetcourseid is preserved as-is from the backup — it is an external
     * reference and is not annotated, so no context mapping is attempted.
     *
     * @param array|object $data Raw data from the XML element.
     */
    public function process_courselink($data) {
        global $DB;

        $data = (object) $data;
        $data->course       = $this->get_courseid();
        $data->timecreated  = $this->apply_date_offset($data->timecreated);
        $data->timemodified = $this->apply_date_offset($data->timemodified);

        $newid = $DB->insert_record('courselink', $data);
        $this->apply_activity_instance($newid);
    }

    /**
     * Restore intro files after the activity instance is restored.
     */
    protected function after_execute() {
        $this->add_related_files('mod_courselink', 'intro', null);
    }

    /**
     * Backfill completion for users who have already completed the target
     * course, so they are not left waiting for the scheduled task after
     * a duplicate or restore.
     */
    protected function after_restore() {
        global $DB;

        $instanceid = $this->task->get_activityid();
        $instance = $DB->get_record('courselink', ['id' => $instanceid], 'course, targetcourseid');
        if ($instance && $instance->targetcourseid) {
            courselink_backfill_completion(
                (int) $instance->course,
                (int) $instanceid,
                (int) $instance->targetcourseid
            );
        }
    }
}
