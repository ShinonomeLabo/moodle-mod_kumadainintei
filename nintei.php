<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Prints an instance of mod_kumadainintei.
 *
 * @package     mod_kumadainintei
 * @copyright   2021 Shinonome Laboratory <info@shinonomelabo.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_kumadainintei;

require(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');

global $DB, $PAGE, $OUTPUT;

// Course module id.
$id = optional_param('id', 0, PARAM_INT);

// Activity instance id.
$k = optional_param('k', 0, PARAM_INT);

if ($id) {
    $cm = get_coursemodule_from_id('kumadainintei', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $moduleinstance = $DB->get_record('kumadainintei', array('id' => $cm->instance), '*', MUST_EXIST);
} else if ($k) {
    $moduleinstance = $DB->get_record('kumadainintei', array('id' => $n), '*', MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $moduleinstance->course), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('kumadainintei', $moduleinstance->id, $course->id, false, MUST_EXIST);
} else {
    print_error(get_string('missingidandcmid', 'mod_kumadainintei'));
}

require_login($course, true, $cm);

$modulecontext = \context_module::instance($cm->id);

$PAGE->set_url('/mod/kumadainintei/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);

$target_grades = $DB->get_records("kumadainintei_grades", ["kumadaininteiid" => $id]);

echo $OUTPUT->header();
foreach($target_grades as $target_grade){

}


echo $OUTPUT->footer();
