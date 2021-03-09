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
 * This file is used for AJAX callbacks.
 *
 * @package    profilefield_branching
 * @copyright  2015 onwards Catalyst IT
 * @author     Tim Price <timprice@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('MOODLE_INTERNAL', 1);
define('AJAX_SCRIPT', 1);

require_once('../../../../config.php');
require_once($CFG->dirroot . '/user/profile/field/branching/locallib.php');

$response = null;

$PAGE->set_context(null);
echo $OUTPUT->header();
@header('Content-type: application/json; charset=utf-8');

$f = json_decode(file_get_contents('php://input'));
$parent = $DB->get_record('user_info_field', array('shortname' => $f->parentname));
$response = array();

// This ajax returns an array of options and thats it.

if (!$parent) {
    echo json_encode(array());
    exit;
}

switch ($parent->datatype) {
    case 'multicheckbox':
        $response[] = explode(PHP_EOL, $parent->param1);
        break;

    case 'menu':
        $response[] = explode(PHP_EOL, $parent->param1);
        break;

    case 'branching':
        switch ($parent->param1) {
            case USERPF_BRANCHING_CHECKLIST:
                $options = $parent->param2;
                $options = str_replace('<br />', '<br>', $options);
                $options = explode("<br>", $options);

                $response[] = $options;
                break 2;
            case USERPF_BRANCHING_SECONDARY:
            case USERPF_BRANCHING_DECLARATION:
            case USERPF_BRANCHING_TEXT:
                // Do nothing at this point.
        }

    default:
        $response[] = array();

}


echo json_encode($response);
