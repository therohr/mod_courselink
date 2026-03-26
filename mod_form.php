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
 * Activity settings form for mod_courselink.
 *
 * @package   mod_courselink
 * @copyright 2026 David Rohr (tidewatercreative.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/moodleform_mod.php');

/**
 * Form for adding and editing a courselink activity instance.
 *
 * Teachers configure which course to link to and whether completion of that
 * course is required before this activity is marked complete.
 *
 * The course list is capped at 500 results to prevent memory exhaustion on
 * large Workplace tenants. On sites with more than 500 courses, replace the
 * autocomplete element with an AJAX datasource backed by
 * core_course_category::search_courses().
 */
class mod_courselink_mod_form extends moodleform_mod {
    /** Maximum number of courses to load into the target-course selector. */
    const MAX_COURSE_LIST = 500;

    /**
     * Build the form definition.
     *
     * @return void
     */
    public function definition(): void {
        global $DB;

        $mform = $this->_form;

        // Standard: name + intro.
        $mform->addElement('text', 'name', get_string('name'), ['size' => '64']);
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        $this->standard_intro_elements();

        // Target course selector.
        $mform->addElement('header', 'courselinksettings', get_string('pluginname', 'mod_courselink'));

        // Build the course list using core_course_category::search_courses(), which
        // Workplace overrides to add tenant isolation. The list is capped at
        // MAX_COURSE_LIST to prevent memory exhaustion on large tenants.
        $courselist = core_course_category::search_courses(
            ['search' => ''],
            ['offset' => 0, 'limit' => self::MAX_COURSE_LIST, 'sort' => ['fullname' => 1]]
        );

        $courseopts = [];
        foreach ($courselist as $c) {
            if ((int) $c->id === (int) $this->current->course) {
                // Exclude the host course — linking to itself is meaningless.
                continue;
            }
            $courseopts[$c->id] = format_string($c->fullname) . ' (' . format_string($c->shortname) . ')';
        }

        $mform->addElement(
            'autocomplete',
            'targetcourseid',
            get_string('targetcourseid', 'mod_courselink'),
            $courseopts
        );
        $mform->addHelpButton('targetcourseid', 'targetcourseid', 'mod_courselink');
        $mform->addRule('targetcourseid', get_string('nocourseselected', 'mod_courselink'), 'required', null, 'client');
        $mform->setType('targetcourseid', PARAM_INT);

        // Standard: grading + completion tabs added by parent.
        $this->standard_coursemodule_elements();
        $this->add_action_buttons();
    }

    /**
     * Add completion-rule elements to the completion settings tab.
     *
     * Called by the parent form when building the completion tab. Elements added
     * here are shown only when the teacher selects automatic completion tracking.
     *
     * @return array Element names that are completion rules.
     */
    public function add_completion_rules(): array {
        $mform = $this->_form;

        $mform->addElement(
            'advcheckbox',
            'completiontracking',
            get_string('completiontracking', 'mod_courselink'),
            '',
            [],
            [0, 1]
        );
        $mform->addHelpButton('completiontracking', 'completiontracking', 'mod_courselink');
        $mform->setDefault('completiontracking', 1);

        return ['completiontracking'];
    }

    /**
     * Determine whether any completion rule is currently enabled.
     *
     * Moodle uses this to decide whether to show the custom completion section.
     *
     * @param  array $data Form data.
     * @return bool        True when at least one custom rule is active.
     */
    public function completion_rule_enabled($data): bool {
        return !empty($data['completiontracking']);
    }

    /**
     * Server-side validation.
     *
     * @param  array $data  Submitted form data.
     * @param  array $files Uploaded files (unused).
     * @return array        Validation errors keyed by element name.
     */
    public function validation($data, $files): array {
        $errors = parent::validation($data, $files);

        if (empty($data['targetcourseid'])) {
            $errors['targetcourseid'] = get_string('nocourseselected', 'mod_courselink');
        }

        return $errors;
    }
}
