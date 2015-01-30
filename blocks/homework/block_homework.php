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
 * @copyright 2015 Simon Bosman
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package homework
 */
include_once ($CFG->dirroot . '/course/lib.php');
include_once ($CFG->libdir . '/coursecatlib.php');
class block_homework extends block_list {
	function init() {
		$this->title = get_string ( 'homework', 'block_homework' );
	}
	public function get_content() {
		global $CFG, $USER, $DB, $OUTPUT;
		
		if ($this->content !== null) {
			return $this->content;
		}
		
		$this->content = new stdClass ();
		$this->content->items = array ();
		$this->content->icons = array ();
		$this->content->footer = '';
		
		$icon = '<img src="' . $OUTPUT->pix_url ( 'i/course' ) . '" class="icon" alt="" />';
		$sortorder = 'visible DESC, sortorder ASC';
		$courseGroups = array ();
		$groups = array ();
		$groupsSorted = array ();
		
		if (empty ( $CFG->disablemycourses ) and isloggedin () and ! isguestuser () and ! (has_capability ( 'moodle/course:update', context_system::instance () ) and $adminseesall)) {
			// Get the cources and groups
			if ($courses = enrol_get_my_courses ( NULL, $sortorder )) {
				foreach ( $courses as $course ) {
					$coursecontext = context_course::instance ( $course->id );
					$courseGroups [] = groups_get_all_groups ( $course->id );
				}
			}
			
			// Get the group objects from the coursegroups array
			foreach ( $courseGroups as $courseGroup ) {
				foreach ( $courseGroup as $group ) {
					$groups [] = $group;
				}
			}
			
			// Get the groupname of the grouparray with group Objects
			foreach ( $groups as $key => $value ) {
				$groupsSorted [] = $value->name;
			}
			// Order the goup name alphabetically
			natcasesort ( $groupsSorted );
			
			foreach ( $groupsSorted as $groupSorted ) {
				// Get the group Object belonging to the group name
				// Not very efficient but sufficient for the intended purpose
				foreach ( $groups as $group ) {
					if ($group->name == $groupSorted) {
						$this->content->items [] = "<a title=\"" . format_string ( $group->name, true, array (
								'context' => $coursecontext 
						) ) . "\" " . "href=\"$CFG->wwwroot/calendar/event.php?action=new&homework=true&groupid=$group->id\">" . $icon . format_string ( $group->name ) . "</a>";
					}
				}
			}
		}
		return $this->content;
	}
}