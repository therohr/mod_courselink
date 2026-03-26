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
 * Restore activity task for mod_courselink.
 *
 * @package   mod_courselink
 * @copyright 2026 David Rohr (tidewatercreative.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/courselink/backup/moodle2/restore_courselink_stepslib.php');
require_once($CFG->dirroot . '/mod/courselink/lib.php');

/**
 * Restore task for a courselink activity instance.
 */
class restore_courselink_activity_task extends restore_activity_task {
    /**
     * No module-level settings beyond the defaults.
     */
    protected function define_my_settings() {
    }

    /**
     * Register the single structure step that reads courselink.xml.
     */
    protected function define_my_steps() {
        $this->add_step(new restore_courselink_activity_structure_step(
            'courselink_structure',
            'courselink.xml'
        ));
    }

    /**
     * No embedded HTML content to decode.
     *
     * @return array
     */
    public static function define_decode_contents() {
        return [];
    }

    /**
     * No URL decode rules needed.
     *
     * @return array
     */
    public static function define_decode_rules() {
        return [];
    }

    /**
     * No restore log rules.
     *
     * @return array
     */
    public static function define_restore_log_rules() {
        return [];
    }

    /**
     * No course-level restore log rules.
     *
     * @return array
     */
    public static function define_restore_log_rules_for_course() {
        return [];
    }
}
