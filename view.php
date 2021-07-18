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
require_once(__DIR__ . '/locallib.php');

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

if (!has_capability("mod/kumadainintei:viewadmin", $modulecontext)) {
    $url = new \moodle_url("nintei.php", ["id" => $id]);
    redirect($url);
}

$submit_grades = optional_param_array("grade", null, PARAM_INT);

echo $OUTPUT->header();

if ($submit_grades != null) {
    foreach ($submit_grades as $key => $submit_grade) {
        if ($submit_grade == null || empty($submit_grade)) {
            continue;
        }

        $modname = explode("_", $key)[0];
        $cmid = explode("_", $key)[1];

        kumadainintei_update_grade($modname, $id, $cmid, $submit_grade);
    }

    echo \html_writer::div("保存しました", "alert alert-primary");
}

$courses = get_courses();

$action_url = new \moodle_url("view.php", ["sesskey" => sesskey(), "id" => $id]);
echo \html_writer::start_tag("form", ["method" => "post", "action" => $action_url->out(false)]);

foreach ($courses as $course) {
    if ($course->id == 1) {
        continue;
    }

    $table = new \html_table();
    $table->head = ["コース", "種別", "名前", "説明", "合格点"];
    echo \html_writer::tag("h3", $course->fullname);

    $modules = get_course_mods($course->id);

    foreach ($modules as $module) {
        if ($module->modname === "forum" || $module->modname === "kumadainintei") {
            continue;
        }

        $instance = $DB->get_record($module->modname, ["id" => $cm->instance]);
        if ($instance) {
            $instance_intro = $instance->intro;
        } else {
            $instance_intro = "";
        }

        $modname = get_string("pluginname", "mod_" . $module->modname);
        $cm = get_coursemodule_from_instance($module->modname, $module->instance);
        $cm_course = get_course($cm->course);

        $target_grade = $DB->get_record("kumadainintei_grades", ["kumadaininteiid" => $id, "grade_cmid" => $cm->id]);
        if ($target_grade) {
            $table->data[] = [$cm_course->fullname, $modname, $cm->name, $instance_intro, \html_writer::empty_tag("input", ["name" => "grade[" . $module->modname . "_" . $cm->id . "]", "type" => "text", "value" => $target_grade->grade])];
        } else {
            $table->data[] = [$cm_course->fullname, $modname, $cm->name, $instance_intro, \html_writer::empty_tag("input", ["name" => "grade[" . $module->modname . "_" . $cm->id . "]", "type" => "text", "value" => ""])];
        }
    }

    echo \html_writer::table($table);
}

echo \html_writer::start_div("d-flex flex-wrap align-items-center");
echo \html_writer::start_div("form-group fitem form-submit");
echo \html_writer::start_span("", ["data-fieldtype" => "submit"]);
echo \html_writer::empty_tag("input", ["type" => "submit", "value" => "保存", "name" => "grade_submit", "class" => "btn btn-primary"]);
echo \html_writer::end_span();
echo \html_writer::end_div();
echo \html_writer::end_div();

echo \html_writer::end_tag("form");

echo $OUTPUT->footer();
