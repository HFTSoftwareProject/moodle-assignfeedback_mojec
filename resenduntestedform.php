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

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot . '/mod/assign/feedback/mojec/locallib.php');

/**
 * Assignment grading options form
 *
 * @package   assignfeedback_mojec
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class assignfeedback_mojec_resend_untested_form extends moodleform {
    /**
     * Define this form - called by the parent constructor
     */
    public function definition() {
        global $COURSE, $USER;

        $mform = $this->_form;
        $params = $this->_customdata;

        $mform->addElement('header', 'batchresenduntestedforusers',
            get_string('batchresenduntestedforusers', 'assignfeedback_mojec', count($params['users'])));
        $mform->addElement('static', 'userslist', get_string('selectedusers', 'assignfeedback_mojec'), $params['usershtml']);

        $mform->addElement('hidden', 'id', $params['cm']);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'action', 'viewpluginpage');
        $mform->setType('action', PARAM_ALPHA);
        $mform->addElement('hidden', 'pluginaction', 'resenduntested');
        $mform->setType('pluginaction', PARAM_ALPHA);
        $mform->addElement('hidden', 'plugin', 'mojec');
        $mform->setType('plugin', PARAM_PLUGIN);
        $mform->addElement('hidden', 'pluginsubtype', 'assignfeedback');
        $mform->setType('pluginsubtype', PARAM_PLUGIN);
        $mform->addElement('hidden', 'selectedusers', implode(',', $params['users']));
        $mform->setType('selectedusers', PARAM_SEQUENCE);
        $this->add_action_buttons(true, get_string('resenduntested', 'assignfeedback_mojec'));

    }

}

