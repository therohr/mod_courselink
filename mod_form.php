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
 * @copyright 2026 Your Name <you@example.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/moodleform_mod.php');

/**
 * Form for adding and editing a courselink activity instance.
 *
 * Teachers configure which course to link to and whether completion of that
 * course is required before this activity is marked complete.
 */
class mod_courselink_mod_form extends moodleform_mod {

    /**
     * Build the form definition.
     *
     * @return void
     */
    public function definition(): void {
        global $DB;

        $mform = $this->_form;

        // ---------------------------------------------------------------
        // Standard: name + intro.
        // ---------------------------------------------------------------
        $mform->addElement('text', 'name', get_string('name'), ['size' => '64']);
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        $this->standard_intro_elements();

        // ---------------------------------------------------------------
        // Target course selector.
        // ---------------------------------------------------------------
        $mform->addElement('header', 'courselinksettings', get_string('pluginname', 'mod_courselink'));

        // Build a select list of all site courses except the current one.
        $courses    = get_courses('all', 'c.fullname ASC', 'c.id, c.fullname, c.shortname');
        $courseopts = [];
        foreach ($courses as $c) {
            if ($c->id == $this->current->course) {
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

        // Completion tracking toggle.
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

        // ---------------------------------------------------------------
        // Standard: grading + completion tabs added by parent.
        // ---------------------------------------------------------------
        $this->standard_coursemodule_elements();
        $this->add_action_buttons();
    }

    /**
     * Add completion-rule elements to the completion settings tab.
     *
     * Called by the parent form when building the completion tab.
     *
     * @return array Element names that are completion rules.
     */
    public function add_completion_rules(): array {
        // The completiontracking field is the sole custom rule.
        // It is defined in definition() so no additional elements are needed here;
        // we just declare its name so Moodle knows to show it in the completion tab.
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
    public function completion_rule_enabled(array $data): bool {
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
