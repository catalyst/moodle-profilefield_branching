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

// This file is used for AJAX callbacks.

define('MOODLE_INTERNAL', 1);
define('AJAX_SCRIPT', 1);

require_once('../../../../config.php');

require_login();

$response = null;

$PAGE->set_context(null);
echo $OUTPUT->header();
@header('Content-type: application/json; charset=utf-8');

$f = json_decode(file_get_contents('php://input'));
$field = $DB->get_record('user_info_field', array('shortname' => $f));

$response = explode(PHP_EOL, $field->param1);
echo json_encode($response);
