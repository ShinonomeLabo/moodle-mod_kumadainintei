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

global $DB, $PAGE, $OUTPUT, $USER;

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

$table = new \html_table();
$table->head = ["学習活動名", "内容", "合否"];

$is_passed = true;
foreach ($target_grades as $target_grade) {
    $cm = get_coursemodule_from_id($target_grade->grade_modulename, $target_grade->grade_cmid);
    $grade = grade_get_grades($cm->course, "mod", $target_grade->grade_modulename, $cm->instance, $USER->id);

    $instance = $DB->get_record($target_grade->grade_modulename, ["id" => $cm->instance]);
    if ($instance) {
        $instance_intro = $instance->intro;
    } else {
        $instance_intro = "";
    }

    $rawgrade = (float)current(current($grade->items)->grades)->grade;
    $t_grade = (float)$target_grade->grade;

    if ($rawgrade >= $t_grade) {
        $table->data[] = [$cm->name . '<br>' . '*ID:' . $cm->instance, $instance_intro, "合格"];
    } else {
        $is_passed = false;
        $table->data[] = [$cm->name . '<br>' . '*ID:' . $cm->instance, $instance_intro, "不合格"];
    }
}

if ($is_passed) {
    echo \html_writer::div("合格しました", "alert alert-success");
} else {
    echo \html_writer::div("不合格の活動があります", "alert alert-danger");
}

echo \html_writer::tag("h3", "学習活動一覧");

echo \html_writer::table($table);

echo $OUTPUT->footer();
