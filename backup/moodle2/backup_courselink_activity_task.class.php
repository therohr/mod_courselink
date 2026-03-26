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
 * Backup activity task for mod_courselink.
 *
 * @package   mod_courselink
 * @copyright 2026 David Rohr (tidewatercreative.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/courselink/backup/moodle2/backup_courselink_stepslib.php');

/**
 * Backup task for a courselink activity instance.
 */
class backup_courselink_activity_task extends backup_activity_task {
    /**
     * No module-level settings beyond the defaults.
     */
    protected function define_my_settings() {
    }

    /**
     * Register the single structure step that writes courselink.xml.
     */
    protected function define_my_steps() {
        $this->add_step(new backup_courselink_activity_structure_step(
            'courselink_structure',
            'courselink.xml'
        ));
    }

    /**
     * No content links to encode (courselink stores no embedded HTML content).
     *
     * @param string $content
     * @return string
     */
    public static function encode_content_links($content) {
        return $content;
    }
}
