<?php
include_once($CFG->dirroot . '/course/lib.php');
include_once($CFG->libdir . '/coursecatlib.php');

class block_homework extends block_list {

	function init() {
		$this->title = get_string('homework', 'block_homework');
	}

	public function get_content() {
		global $CFG, $USER, $DB, $OUTPUT;
		
		if ($this->content !== null) {
			return $this->content;
		}
	
		$this->content         =  new stdClass;
		$this->content->items = array();
		$this->content->icons = array();
		$this->content->footer = '';
		
		$icon  = '<img src="' . $OUTPUT->pix_url('i/course') . '" class="icon" alt="" />';
		$sortorder = 'visible DESC, sortorder ASC';
		 
 		if ($courses = enrol_get_my_courses(NULL, $sortorder)) {
			foreach ($courses as $course) {
 				$coursecontext = context_course::instance($course->id);
				$groups = groups_get_all_groups($course->id);
				foreach ($groups as $group) {
					$this->content->items[]="<a title=\"" . format_string($group->name, true, array('context' => $coursecontext)) . "\" ".
					"href=\"$CFG->wwwroot/calendar/event.php?action=new&homework=true&groupid=$group->id\">".$icon.format_string($group->name). "</a>";
				}
			}	
		}
		return $this->content;
	}
}