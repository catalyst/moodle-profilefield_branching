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

require_once($CFG->dirroot.'/user/profile/field/branching/locallib.php');

/**
 * Branching profile field.
 *
 * @package    profilefield_branching
 * @copyright  2015 onwards Catalyst IT
 * @author     Tim Price <timprice@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class profile_field_branching extends profile_field_base {

    /** @var array $options */
    public $options;

    /** @var int $datakey */
    public $datakey;

    /**
     * Constructor method.
     *
     * Pulls out the options for the menu from the database and sets the the corresponding key for the data if it exists.
     *
     * @param int $fieldid
     * @param int $userid
     */
    public function profile_field_branching($fieldid = 0, $userid = 0) {
        global $DB;
        // First call parent constructor.
        $this->profile_field_base($fieldid, $userid);

        // Only need to do this for select types.
        if (isset($this->field->param1)
            && ($this->field->param1 == USERPF_BRANCHING_CHECKLIST
                || $this->field->param1 == USERPF_BRANCHING_SECONDARY)
        ) {

            // Param 2 for menu type is the options.
            if (isset($this->field->param2)) {
                $options = explode("<br />", $this->field->param2);
            } else {
                $options = array();
            }
            $this->options = array();
            $this->options[''] = get_string('choose').'...';
            foreach ($options as $key => $option) {
                $key++;
                $this->options[$key] = format_string($option); // Multilang formatting.
            }

            // Set the data key.
            if ($this->data !== null) {
                $this->datakey = (int)array_search($this->data, $this->options);
            }
        } else if (!empty($this->field)
            && $this->field->param1 == USERPF_BRANCHING_DECLARATION
        ) { // Checkbox declaration.
            $datafield = $DB->get_field('user_info_data', 'data', array('userid' => $this->userid, 'fieldid' => $this->fieldid));
            if ($datafield !== false) {
                $this->data = $datafield;
            } else {
                $this->data = $this->field->defaultdata;
            }
        }
    }

    /**
     * Create the code snippet for this field instance
     * Overwrites the base class method
     * @param moodleform $mform Moodle form instance
     */
    public function edit_field_add($mform) {
        global $PAGE;

        switch ($this->field->param1) {
            case USERPF_BRANCHING_TEXT:
                $size = $this->field->param2;
                // Create the form field.
                $mform->addElement(
                    'text',
                    $this->inputname,
                    format_string($this->field->name),
                    ' size="'.$size.'" '
                );
                $mform->setType($this->inputname, PARAM_MULTILANG);
                break;
            case USERPF_BRANCHING_CHECKLIST:
                $mform->addElement(
                    'select',
                    $this->inputname,
                    format_string($this->field->name),
                    $this->options
                );
                break;
            case USERPF_BRANCHING_SECONDARY:
                $mform->addElement(
                    'select',
                    $this->inputname,
                    format_string($this->field->name),
                    $this->options
                );
                break;
            case USERPF_BRANCHING_DECLARATION:

                $text = $mform->createElement('static', '', '', $this->field->param2);

                $checkbox = $mform->createElement(
                    'advcheckbox',
                    $this->inputname,
                    '',
                    'I accept this declaration'
                );
                if ($this->data == '1') {
                    $checkbox->setChecked(true);
                }
                $group = array( $text, $checkbox );
                $mform->addGroup($group, $this->inputname, format_string($this->field->name), '<br>', false);

                $mform->setType($this->inputname, PARAM_BOOL);

        }

        $jsmod = array(
            'name' => 'profile_field_branching',
            'fullpath' => '/user/profile/field/branching/branching.js'
        );

        if ($this->field->param1 == USERPF_BRANCHING_SECONDARY) { // Qual type.
            $PAGE->requires->js_init_call(
                'M.profile_field_branching.init',
                array(
                    '#fitem_id_' . $this->inputname,
                    '#id_profile_field_' . $this->field->param3,
                    $this->field->param4,
                    $this->field->param5
                ),
                false,
                $jsmod
            );
        } else {

            $PAGE->requires->js_init_call(
                'M.profile_field_branching.init',
                array(
                    '#fitem_id_' . $this->inputname,
                    '#id_profile_field_' . $this->field->param3,
                    $this->field->param4
                ),
                false,
                $jsmod
            );
        }
    }

    /**
     * Set the default value for this field instance
     * Overwrites the base class method.
     * @param moodleform $mform Moodle form instance
     */
    public function edit_field_set_default($mform) {
        if ($this->field->param1 == USERPF_BRANCHING_CHECKLIST
            || $this->field->param1 == USERPF_BRANCHING_SECONDARY
        ) {
            if (false !== array_search($this->field->defaultdata, $this->options)) {
                $defaultkey = (int)array_search($this->field->defaultdata, $this->options);
            } else {
                $defaultkey = '';
            }
            $mform->setDefault($this->inputname, $defaultkey);
        } else {
            parent::edit_field_set_default($mform);
        }
    }

    /**
     * The data from the form returns the key.
     *
     * This should be converted to the respective option string to be saved in database
     * Overwrites base class accessor method.
     *
     * @param mixed $data The key returned from the select input in the form
     * @param stdClass $datarecord The object that will be used to save the record
     * @return mixed Data or null
     */
    public function edit_save_data_preprocess($data, $datarecord) {
        if ($this->field->param1 == USERPF_BRANCHING_CHECKLIST
            || $this->field->param1 == USERPF_BRANCHING_SECONDARY
        ) {
            return isset($this->options[$data]) ? $this->options[$data] : null;
        } else {
            return $data;
        }
    }

    /**
     * When passing the user object to the form class for the edit profile page
     * we should load the key for the saved data
     *
     * Overwrites the base class method.
     *
     * @param stdClass $user User object.
     */
    public function edit_load_user_data($user) {
        if ($this->field->param1 == USERPF_BRANCHING_CHECKLIST
            || $this->field->param1 == USERPF_BRANCHING_SECONDARY
        ) {
            $user->{$this->inputname} = $this->datakey;
        } else {
            parent::edit_load_user_data($user);
        }
    }

    /**
     * HardFreeze the field if locked.
     * @param moodleform $mform instance of the moodleform class
     */
    public function edit_field_set_locked($mform) {
        if ($this->field->param1 == USERPF_BRANCHING_CHECKLIST
            || $this->field->param1 == USERPF_BRANCHING_SECONDARY
        ) {
            if (!$mform->elementExists($this->inputname)) {
                return;
            }
            if ($this->is_locked() and !has_capability('moodle/user:update', context_system::instance())) {
                $mform->hardFreeze($this->inputname);
                $mform->setConstant($this->inputname, $this->datakey);
            }
        } else {
            parent::edit_field_set_locked($mform);
        }
    }
    /**
     * Convert external data (csv file) from value to key for processing later by edit_save_data_preprocess
     *
     * @param string $value one of the values in menu options.
     * @return int options key for the menu
     */
    public function convert_external_data($value) {
        if (!$mform->elementExists($this->inputname)) {
            $retval = array_search($value, $this->options);

            // If value is not found in options then return null, so that it can be handled
            // later by edit_save_data_preprocess.
            if ($retval === false) {
                $retval = null;
            }
            return $retval;
        } else {
            parent::convert_external_data($value);
        }
    }

    function edit_validate_field($usernew) {
        global $DB;

        $errors = array();
        $property = "profile_field_" . $this->field->param3;
        $value = $this->field->param4;

        if (isset($usernew->{$property}) && $usernew->$property == $value) {
            // The firts item in the select menus has the value of '0'. This needs to be valid.
            if (empty($usernew->{$this->inputname}) && $usernew->{$this->inputname} !== '0') {
                $errors[$this->inputname] = get_string('invalidentry', 'profilefield_branching');
            }
        }

        return $errors;
    }

    public function edit_save_data($usernew) {
        global $DB;

        if (!isset($usernew->{$this->inputname})) {
            // Field not present in form, probably locked and invisible - skip it.
            return;
        }

        // If this field is hidden we don't want to save a value. This is mostly for the menu type ones.
        $property = "profile_field_" . $this->field->param3;
        $value = $this->field->param4;
        $data = new stdClass();
        switch ($this->field->param1) {
            case USERPF_BRANCHING_TEXT:
            case USERPF_BRANCHING_CHECKLIST:
            case USERPF_BRANCHING_DECLARATION:
                $parent = $DB->get_record('user_info_field', array('shortname' => $this->field->param3));
                if ($parent->datatype == 'menu') {
                    $list = explode("\n", $parent->param1);
                    // Need to rekey the array;
                    $i = 1;
                    $options = array();
                    foreach ($list as $item) {
                        $options[$i] = $item;
                        $i++;
                    }
                    if (!isset($options[$value] )) {
                        $desired = '';
                    } else {
                        $desired = $options[$value];
                    }
                } else {
                    $desired = $value;
                }
                // Getting weird errors where some menus report the value, others the option, so just check for both.
                if (isset($usernew->{$property}) && ($usernew->$property == $desired || $usernew->$property == $value)) {
                    $usernew->{$this->inputname} = $this->edit_save_data_preprocess($usernew->{$this->inputname}, $data);
                } else {
                    $usernew->{$this->inputname} = '';
                }
                break;
            case USERPF_BRANCHING_SECONDARY:
                if ($usernew->profile_field_vettrakrstate == 'Vic') { // Victoria.
                    if (is_array($usernew->$property)) {
                        // Get rid of spaces in array keys and do array_keys() as we go
                        $temp = array();
                        foreach ($usernew->$property as $key => $value) {
                            $temp[] = trim($key);
                        }
                        if (in_array($this->field->param5, $temp)) {
                            $usernew->{$this->inputname} = $this->edit_save_data_preprocess($usernew->{$this->inputname}, $data);
                        } else {
                            $usernew->{$this->inputname} = '';
                        }
                    }
                    else {
                        $haystack = $usernew->$property . "\n"; // This is so that we don't match Cert II when searching for Cert I.
                        if (isset($usernew->{$property}) && strstr($haystack, $this->field->param5 . "\n") !== false) {
                            $usernew->{$this->inputname} = $this->edit_save_data_preprocess($usernew->{$this->inputname}, $data);
                        } else {
                            $usernew->{$this->inputname} = '';
                        }
                    }
                } else {
                    $usernew->{$this->inputname} = '';
                }
                break;
        }
        // Do the standard stuff, but without the param assign.
        $data->userid  = $usernew->id;
        $data->fieldid = $this->field->id;
        $data->data    = $usernew->{$this->inputname};

        if ($dataid = $DB->get_field('user_info_data', 'id', array('userid' => $data->userid, 'fieldid' => $data->fieldid))) {
            $data->id = $dataid;
            $DB->update_record('user_info_data', $data);
        } else {
            $DB->insert_record('user_info_data', $data);
        }
    }

    /**
     * Display the data for this field
     *
     * @return string HTML.
     */
    public function display_data() {
        if ($this->field->param1 == USERPF_BRANCHING_DECLARATION) {
            $options = new stdClass();
            $options->para = false;
            $checked = intval($this->data) === 1 ? 'checked="checked"' : '';
            return '<input disabled="disabled" type="checkbox" name="'.$this->inputname.'" '.$checked.' />';
        } else {
            return parent::display_data();
        }
    }
}
