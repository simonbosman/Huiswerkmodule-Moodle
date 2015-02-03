<?php
include_once ($CFG->dirroot . '/course/lib.php');
include_once ($CFG->libdir . '/coursecatlib.php');
error_reporting(E_ALL);
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
		
		if (empty ( $CFG->disablemycourses )and (user_has_role_assignment($USER->id, 3)) and isloggedin () and ! isguestuser ()) {
			// Get the cources and groups
			if ($courses = enrol_get_my_courses ( NULL, $sortorder )) {
				foreach ( $courses as $course ) {
					$groups  = groups_get_all_groups ( $course->id );
					$courseGroups += [$course->id => ['course' => $course->fullname, 'groups' =>[]]];
					foreach ($groups as $group){
							$courseGroups[$course->id]['groups'] += [$group->id => $group->name];
					}
				}
			}
			
			foreach ($courseGroups as $courseId => $course){
				$this->content->items[] = "<p>" . $course['course'] . "</p>";
				foreach($course['groups'] as $groupId => $group){
					$this->content->items[] = "<a href=\"$CFG->wwwroot/calendar/event.php?action=new&homework=true&groupid=$groupId&courseid=$courseId\">" . $icon . format_string($group) . "</a>";
				}
				$this->content->items[] = "<p></p>";
			}
		}
	
		return $this->content;
	}
}
