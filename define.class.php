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
 * Branching profile field definition.
 *
 * @package    profilefield_branching
 * @copyright  2015 onwards Catalyst IT
 * @author     Tim Price <timprice@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class profile_define_branching extends profile_define_base {

    /**
     * Adds elements to the form for creating/editing this type of profile field.
     * @param moodleform $form
     */
    public function define_form_specific($form) {
        // Param 1 is the type of field.
        $options = array('text', 'menu', 'qual');
        $form->addElement('select', 'param1', get_string('fieldtype', 'profilefield_branching'), $options);
        $form->setType('param1', PARAM_TEXT);

        // Param 2 for menu type contains the options or default size.
        $form->addElement('textarea', 'param2', get_string('param2', 'profilefield_branching'), array('rows' => 6, 'cols' => 40));
        $form->setType('param2', PARAM_TEXT);

        // Param 3 is the field to branch from.
        $form->addElement('text', 'param3', get_string('branchfield', 'profilefield_branching'), 'size="50"');
        $form->setType('param3', PARAM_TEXT);

        // Param 4 is the value to show field on.
        $form->addElement('text', 'param4', get_string('branchvalue', 'profilefield_branching'), 'size="50"');
        $form->setType('param4', PARAM_TEXT);

        // Param 5 is the item in the field list.
        $form->addElement('text', 'param5', get_string('itemname', 'profilefield_branching'), 'size="50"');
        $form->setType('param5', PARAM_TEXT);

        // Default data.
        $form->addElement('text', 'defaultdata', get_string('profiledefaultdata', 'admin'), 'size="50"');
        $form->setType('defaultdata', PARAM_TEXT);
    }

    /**
     * Validates data for the profile field.
     *
     * @param array $data
     * @param array $files
     * @return array
     */
    public function define_validate_specific($data, $files) {
        $err = array();

        if ($data->param1 != 0) {

            $data->param2 = str_replace("\r", '', $data->param2);

            // Check that we have at least 2 options.
            if (($options = explode("\n", $data->param2)) === false) {
                $err['param2'] = get_string('profilemenunooptions', 'admin');
            } else if (count($options) < 2) {
                $err['param2'] = get_string('profilemenutoofewoptions', 'admin');
            } else if (!empty($data->defaultdata) and !in_array($data->defaultdata, $options)) {
                // Check the default data exists in the options.
                $err['defaultdata'] = get_string('profilemenudefaultnotinoptions', 'admin');
            }
        }
        return $err;
    }

    /**
     * Processes data before it is saved.
     * @param array|stdClass $data
     * @return array|stdClass
     */
    public function define_save_preprocess($data) {
        if ($data->param1 != 0) {
            $data->param2 = str_replace("\r", '', $data->param2);
        }

        return $data;
    }

}
