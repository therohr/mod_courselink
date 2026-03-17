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
 * Privacy language strings for mod_courselink.
 *
 * Kept in a separate file so Moodle's string loader can find it via
 * the standard privacy subsystem lookup path.
 *
 * @package   mod_courselink
 * @copyright 2026 Your Name <you@example.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['privacy:metadata:core_completion'] =
    'The Course link activity reads course completion records from the Moodle ' .
    'core completion subsystem to determine whether a student has completed the ' .
    'linked course. No personal data is stored by this plugin directly.';
