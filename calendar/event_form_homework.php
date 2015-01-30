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
 * The mform for creating and editing a calendar event
 *
 * @copyright 2009 Sam Hemelryk Simon Bosman
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package calendar
 */

 /**
  * Always include formslib
  */
if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

require_once($CFG->dirroot.'/lib/formslib.php');

/**
 * The mform class for creating and editing a calendar
 *
 * @copyright 2015 Simon Bosman
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class event_form_homework extends moodleform {
    /**
     * The form definition
     */
    function definition () {
        global $CFG, $USER, $OUTPUT;
        $mform = $this->_form;
        $newevent = (empty($this->_customdata->event) || empty($this->_customdata->event->id));
        $repeatedevents = (!empty($this->_customdata->event->eventrepeats) && $this->_customdata->event->eventrepeats>0);
        $hasduration = (!empty($this->_customdata->hasduration) && $this->_customdata->hasduration);
        $mform->addElement('header', 'general', 'Huiswerk');

         // Add some hidden fields
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->setDefault('id', 0);

        $mform->addElement('hidden', 'eventtype');
        $mform->setType('eventtype', PARAM_ALPHA);

        $mform->addElement('hidden', 'homework');
        $mform->setType('homework', PARAM_BOOL);
        $mform->setDefault('homework', True);
        
        $mform->addElement('hidden', 'courseid');
        $mform->setType('courseid', PARAM_INT);
        
        $mform->addElement('hidden', 'groupid');
        $mform->setType('groupid', PARAM_INT);

        $mform->addElement('hidden', 'userid');
        $mform->setType('userid', PARAM_INT);
        $mform->setDefault('userid', $USER->id);

        $mform->addElement('hidden', 'modulename');
        $mform->setType('modulename', PARAM_INT);
        $mform->setDefault('modulename', '');

        $mform->addElement('hidden', 'instance');
        $mform->setType('instance', PARAM_INT);
        $mform->setDefault('instance', 0);

        $mform->addElement('hidden', 'action');
        $mform->setType('action', PARAM_INT);

        // Normal fields
        $mform->addElement('text', 'name', get_string('eventname','calendar'), 'size="50"');
        $mform->addRule('name', get_string('required'), 'required');
        $mform->setType('name', PARAM_TEXT);
        $mform->setDefault('name', 'huiswerk ' . $this->_customdata->event->groupname);

        $mform->addElement('editor', 'description', get_string('eventdescription','calendar'), null, $this->_customdata->event->editoroptions);
        $mform->setType('description', PARAM_RAW);

        $mform->addElement('date_selector', 'timestart', get_string('date'));
        $mform->addRule('timestart', get_string('required'), 'required');

        $this->add_action_buttons(false, get_string('savechanges'));
    }

    /**
     * A bit of custom validation for this form
     *
     * @param array $data An assoc array of field=>value
     * @param array $files An array of files
     * @return array
     */
    function validation($data, $files) {
        global $DB, $CFG;

        $errors = parent::validation($data, $files);

        if ($data['courseid'] > 0) {
            if ($course = $DB->get_record('course', array('id'=>$data['courseid']))) {
                if ($data['timestart'] < $course->startdate) {
                    $errors['timestart'] = get_string('errorbeforecoursestart', 'calendar');
                }
            } else {
                $errors['courseid'] = get_string('invalidcourse', 'error');
            }

        }

        if ($data['duration'] == 1 && $data['timestart'] > $data['timedurationuntil']) {
            $errors['timedurationuntil'] = get_string('invalidtimedurationuntil', 'calendar');
        } else if ($data['duration'] == 2 && (trim($data['timedurationminutes']) == '' || $data['timedurationminutes'] < 1)) {
            $errors['timedurationminutes'] = get_string('invalidtimedurationminutes', 'calendar');
        }

        return $errors;
    }

}
