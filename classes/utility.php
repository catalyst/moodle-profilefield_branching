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
 * Helper class for profilefield_branching
 *
 * @package    profilefield_branching
 * @copyright  2021 Nicholas Hoobin <nicholashoobin@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace profilefield_branching;

use moodle_exception;
use moodle_url;

defined('MOODLE_INTERNAL') || die;

/**
 * Helper class for profilefield_branching
 *
 * @package    profilefield_branching
 * @copyright  2021 Nicholas Hoobin <nicholashoobin@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class utility {

    /**
     * Hook via moodlelib.php function get_plugins_with_function('after_require_login').
     *
     * @param $user
     * @param bool $setwantsurltome Define if we want to set $SESSION->wantsurl, defaults to
     *             true. Used to avoid (=false) some scripts (file.php...) to set that variable,
     *             in order to keep redirects working properly. MDL-14495
     * @param bool $preventredirect set to true in scripts that can not redirect (CLI, rss feeds, etc.), throws exceptions
     */
    public function after_require_login($user, $setwantsurltome, $preventredirect) {
        if (!$this->user_fully_setup_check($user)) {
            $this->user_fully_setup_redirect($user, $setwantsurltome, $preventredirect);
        }
    }

    /**
     * Checks the user profile data, and parent branching field to determine if the required fields have content.
     *
     * @param $user
     * @return bool $fullysetup true is the user has been setup
     */
    public function user_fully_setup_check($user) {
        global $DB, $SESSION;

        $fullysetup = true;

        $params = ['datatype' => 'branching'];
        $recordset = $DB->get_recordset('user_info_field', $params);

        if ($recordset) {
            foreach ($recordset as $infofield) {

                // Param 7 is the 'field is required' flag.
                if ($this->obtain_param7($infofield->param5)) {

                    // Check to see if this branching field, actually was set/saved so that data must be required for it.
                    if ($this->check_parent_field_for_data($user, $infofield)) {

                        // Hey, there is no data, or we're missing some! This user is not fully set up!
                        if (!$this->check_that_data_is_set($user, $infofield->id)) {
                            $fullysetup = false;
                        }
                    }
                }
            }
        }

        $recordset->close();

        // Session variable set to help with caching this function.
        $SESSION->profilefield_branching_user_is_fully_setup = $fullysetup;

        return $fullysetup;
    }

    /**
     * The core database does not hold enough columns and early on in this plugin the last field was used as a JSON dump.
     * This is pulling param7 out of param5.
     *
     * @param $param5
     * @return bool
     */
    private function obtain_param7($param5) {
        // param 7 is the new required flag for nested elements.
        $values = json_decode($param5);

        if (is_object($values)) {
            if (isset($values->param7)) {
                return $values->param7 == 1;
            }
        }

        return false;
    }

    /**
     * The parent field is what determines the branching. If the parent field matches the branch data, then it means we
     * must look for the child fields required setting.
     *
     * @param $user
     * @param $record
     * @return bool
     */
    private function check_parent_field_for_data($user, $record) {
        global $DB;

        // The parent branching field shortname must have the associated data.
        $parentfieldshortname = $record->param3;
        // Once we check to see if the data matches this variable, then we know to check the branching element, and if it is required or not.
        $parentfielddata = $record->param4;

        // Obtain the field so we can hunt for the data.
        $select = $DB->sql_compare_text('shortname') . ' = ' . $DB->sql_compare_text(':shortname');
        $field = $DB->get_record_select('user_info_field', $select, ['shortname' => $parentfieldshortname]);

        $params = [
            'userid' => $user->id,
            'fieldid' => $field->id,
        ];
        $infodata = $DB->get_record('user_info_data', $params);

        if ($infodata) {
            return $infodata->data == $parentfielddata;
        }

        return false;
    }

    /**
     * Checks that the record exists, meaning we've met the requirement for profile field data set in the child.
     *
     * @param $user
     * @param $fieldid
     * @return mixed
     */
    private function check_that_data_is_set($user, $fieldid) {
        global $DB;

        $params = [
            'userid' => $user->id,
            'fieldid' => $fieldid
        ];

        $infodata = $DB->get_record('user_info_data', $params);

        return $infodata;
    }

    /**
     * The redirect to user profile page function.
     *
     * @param $user
     * @param bool $setwantsurltome Define if we want to set $SESSION->wantsurl, defaults to
     *             true. Used to avoid (=false) some scripts (file.php...) to set that variable,
     *             in order to keep redirects working properly. MDL-14495
     * @param bool $preventredirect set to true in scripts that can not redirect (CLI, rss feeds, etc.), throws exceptions
     */
    private function user_fully_setup_redirect($user, $setwantsurltome, $preventredirect) {
        global $SESSION;

        if ($preventredirect) {
            throw new moodle_exception('usernotfullysetup');
        }
        if ($setwantsurltome) {
            $SESSION->wantsurl = qualified_me();
        }

        $url = new moodle_url('/user/profile/field/branching/set_required_fields.php');

        redirect($url);
    }

    /**
     * Returns an object with the custom profile fields set for the given user.
     *
     * @param integer $userid
     * @param bool $onlyinuserobject True if you only want the ones in $USER.
     * @return stdClass
     */
    public static function profile_user_record($userid, $onlyinuserobject = true) {
        global $CFG, $DB;

        $usercustomfields = new \stdClass();

        // The userid, used with the profile_save_data() call.
        $usercustomfields->id = $userid;

        if ($fields = $DB->get_records('user_info_field')) {
            foreach ($fields as $field) {
                require_once($CFG->dirroot.'/user/profile/field/'.$field->datatype.'/field.class.php');
                $newfield = 'profile_field_'.$field->datatype;
                $formfield = new $newfield($field->id, $userid);
                if (!$onlyinuserobject || $formfield->is_user_object_data()) {
                    $usercustomfields->{$formfield->inputname} = $formfield->datakey;
                }
            }
        }

        return $usercustomfields;
    }
}
