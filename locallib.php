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
 * This file contains the definition for the library class for mojec feedback plugin
 *
 *
 * @package   assignfeedback_mojec
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();


/**
 * Library class for mojec feedback plugin extending feedback plugin base class.
 *
 * @package   assignfeedback_mojec
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class assign_feedback_mojec extends assign_feedback_plugin {

    const COMPONENT_NAME = "assignfeedback_mojec";

    /**
     * Get the name of the mojec feedback plugin.
     *
     * @return string
     */
    public function get_name() {
        return get_string("mojec", self::COMPONENT_NAME);
    }

    /**
     * Called by the assignment module when someone chooses something from the
     * grading navigation or batch operations list.
     *
     * @param string $action - The page to view
     * @return string - The html response
     */
    public function view_page($action) {
        if ($action == 'resenduntested') {
            $users = $this->get_students_without_test_results();
            return $this->view_resend_untested($users);
        }

        return '';
    }

    /**
     * Return a list of the grading actions performed by this plugin.
     *
     * @return array The list of grading actions
     */
    public function get_grading_actions() {
        return array("resenduntested" => get_string("resenduntested", self::COMPONENT_NAME));
    }

    private function get_students_without_test_results() {
        global $DB;

        $assignment = $this->assignment;

        $currentgroup = groups_get_activity_group($assignment->get_course_module(), true);
        $users = array_keys($assignment->list_participants($currentgroup, true));
        if (count($users) == 0) {
            // Insert a record that will never match to the sql is still valid.
            $users[] = -1;
        }

        $sql = "SELECT DISTINCT s.userid
                FROM {mojec_testresult} t, {assignsubmission_mojec} m, {assign_submission} s
                WHERE s.id=m.submission_id
                AND t.mojec_id = m.id
                AND m.assignment_id = :assignmentid";
        $params = array("assignmentid" => $assignment->get_instance()->id);
        $records = $DB->get_fieldset_sql($sql, $params);

        $result = array_diff($users, $records);

        return $result;
    }

    private function view_resend_untested($users) {
        global $DB, $CFG;

        require_once($CFG->dirroot . '/mod/assign/feedback/mojec/resenduntestedform.php');
        require_once($CFG->dirroot . '/mod/assign/renderable.php');


        $formparams = array('cm' => $this->assignment->get_course_module()->id,
            'users' => $users,
            'context' => $this->assignment->get_context());

        $usershtml = '';

        $usercount = 0;
        foreach ($users as $userid) {
            $user = $DB->get_record('user', array('id' => $userid), '*', MUST_EXIST);

            $usersummary = new assign_user_summary($user,
                $this->assignment->get_course()->id,
                has_capability('moodle/site:viewfullnames',
                    $this->assignment->get_course_context()),
                $this->assignment->is_blind_marking(),
                $this->assignment->get_uniqueid_for_user($user->id),
                get_extra_user_fields($this->assignment->get_context()));
            $usershtml .= $this->assignment->get_renderer()->render($usersummary);
            $usercount += 1;
        }

        $formparams['usershtml'] = $usershtml;

        $mform = new assignfeedback_mojec_resend_untested_form(null, $formparams);

        if ($mform->is_cancelled()) {
            redirect(new moodle_url('view.php',
                array('id' => $this->assignment->get_course_module()->id,
                    'action' => 'grading')));
            return "";
        } else if ($data = $mform->get_data()) {
            foreach ($users as $userid) {
            }

            redirect(new moodle_url('view.php',
                array('id' => $this->assignment->get_course_module()->id,
                    'action' => 'grading')));
            return "";
        } else {

            $header = new assign_header($this->assignment->get_instance(),
                $this->assignment->get_context(),
                false,
                $this->assignment->get_course_module()->id,
                get_string('batchresenduntested', 'assignfeedback_mojec'));
            $o = '';
            $o .= $this->assignment->get_renderer()->render($header);
            $o .= $this->assignment->get_renderer()->render(new assign_form('batchresenduntested', $mform));
            $o .= $this->assignment->get_renderer()->render_footer();
        }

        return $o;
    }

}
