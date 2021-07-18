<?php
// This file is part of the Zoom plugin for Moodle - http://moodle.org/
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

function kumadainintei_update_grade($modname, $ninteiid, $grade_cmid, $grade)
{
    global $DB;

    if ($DB->record_exists("kumadainintei_grades", ["kumadaininteiid" => $ninteiid, "grade_modulename" => $modname, "grade_cmid" => $grade_cmid])) {
        $grade = $DB->get_record("kumadainintei_grades", ["kumadaininteiid" => $ninteiid, "grade_modulename" => $modname, "grade_cmid" => $grade_cmid]);
        $data = new stdClass();
        $data->id = $grade->id;
        $data->grade = $grade;

        return $DB->update_record("kumadainintei_grades", $data);
    } else {
        $data = new stdClass();
        $data->kumadaininteiid = $ninteiid;
        $data->grade_modulename = $modname;
        $data->grade_cmid = $grade_cmid;
        $data->grade = $grade;

        return $DB->insert_record("kumadainintei_grades", $data);
    }
}