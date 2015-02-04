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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * This file contains classes used to manage homework
 *
 * @since Moodle 2.8
 * @package block_homework
 * @copyright 2015 Simon Bosman
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
include_once ($CFG->dirroot . '/course/lib.php');
include_once ($CFG->libdir . '/coursecatlib.php');

/**
 *
 * @author s.bosman
 * @package block_homework
 * @copyright simonbosman@gmail.com
 */
class block_homework extends block_list {
	/**
	 *
	 * @var int
	 */
	private $courseId = 0;
	
	/**
	 * Sets the initial parameters for the homework block
	 */
	function init() {
		$this->courseId = optional_param ( 'id', 0, PARAM_INT );
		$this->title = get_string ( 'homework', 'block_homework' );
	}
	/**
	 * Get the content for the homework_blok
	 * returns a list with all the needed elements
	 * 
	 * @see block_base::get_content()
	 */
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
		
		/*
		 * User should be a teacher
		 */
		if (empty ( $CFG->disablemycourses ) and (user_has_role_assignment ( $USER->id, 3 )) and isloggedin () and ! isguestuser ()) {
			
			/**
			 * Makes an array in this format:
			 * 
			 * array (size=1)
			 * 	723 =>
			 * 		array (size=2)
			 * 			'course' => string 'HAVO3_EN' (length=8)
			 * 			'groups' =>
			 * 				array (size=4)
			 * 					2793 => string 'V HAVO 3' (length=8)
			 * 					3915 => string 'VH3D' (length=4)
			 * 					875 => string 'VH3E' (length=4)
			 * 					3139 => string 'VH3F' (length=4)
			 */
					
			// Get the groups for the chosen course
			if ($this->courseId > 0) {
				$params = [ 
						'id' => $this->courseId 
				];
				$course = $DB->get_record ( 'course', $params, '*', MUST_EXIST );
				$courseGroups = [ 
						$course->id => [ 
								'course' => $course->fullname,
								'groups' => [ ] 
						] 
				];
				$groups = groups_get_all_groups ( $course->id );
				foreach ( $groups as $group ) {
					$courseGroups [$course->id] ['groups'] += [ 
							$group->id => $group->name 
					];
				}
			} else {
				// Course not chosen so get all the cources and groups
				if ($courses = enrol_get_my_courses ( NULL, $sortorder )) {
					foreach ( $courses as $course ) {
						$groups = groups_get_all_groups ( $course->id );
						$courseGroups += [ 
								$course->id => [ 
										'course' => $course->fullname,
										'groups' => [ ] 
								] 
						];
						foreach ( $groups as $group ) {
							$courseGroups [$course->id] ['groups'] += [ 
									$group->id => $group->name 
							];
						}
					}
				}
			}
			foreach ( $courseGroups as $courseId => $course ) {
				// if course not chosen, show all
				if ($this->courseId == 0) {
					$this->content->items [] = "" . $course ['course'] . "";
				}
				foreach ( $course ['groups'] as $groupId => $group ) {
					$this->content->items [] = "<a href=\"$CFG->wwwroot/calendar/event.php?action=new&homework=true&groupid=$groupId&courseid=$courseId\">" . $icon . format_string ( $group ) . "</a>";
				}
				$this->content->items [] = "<p></p>";
			}
		}
		//Content is builded, so return it
		return $this->content;
	}
}
