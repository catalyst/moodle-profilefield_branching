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
        global $DB, $PAGE;

        // Param 1 is the type of field.
        $options = array('Text', 'Checklist', 'Secondary branching', 'Declaration');
        $form->addElement('select', 'param1', get_string('fieldtype', 'profilefield_branching'), $options);
        $form->setType('param1', PARAM_TEXT);

        // Param 2 for menu type contains the options, default size or text dump.
        $form->addElement('editor', 'param2', get_string('param2', 'profilefield_branching'), null, null);
        $form->setType('param2', PARAM_RAW);

        // Param 3 is the field to branch from.
        $fields = $DB->get_records_sql(
            "SELECT f.shortname,
                    f.name,
                    f.categoryid,
                    c.name category,
                    f.param1
               FROM {user_info_field} f
               JOIN {user_info_category} c ON c.id = f.categoryid
              WHERE f.datatype IN ('menu', 'multicheckbox')
                 OR (f.datatype = 'branching' AND f.param1 = ?)
           ORDER BY c.sortorder,
                    f.sortorder
            ",
            array(USERPF_BRANCHING_CHECKLIST)
        );
        $parents = array();
        array_unshift($parents, 'Choose...');
        foreach ($fields as $field) {
            $parents[$field->shortname] = "$field->name ($field->shortname)";
        }
        $form->addElement('select', 'param3', get_string('branchfield', 'profilefield_branching'), $parents);
        $form->setType('param3', PARAM_TEXT);

        // Param 4 is the value to show field on.
        // These fields need to have an initial array with more options than the javascript loads,
        // otherwise it won't save. So we need to do this.
        $options = array('Choose...');
        for ($i = 0; $i < 50; $i++) {
            $array[] = '@';
        }
        $form->addElement('select', 'param4', get_string('branchvalue', 'profilefield_branching'), $options);
        $form->setType('param4', PARAM_TEXT);


        // Param 5 is the second optional parent
        $form->addElement('select', 'param5', get_string('branchfield2', 'profilefield_branching'), $parents);
        $form->setType('param5', PARAM_TEXT);

        // Param 6 is the second optional value
        $form->addElement('select', 'param6', get_string('branchvalue2', 'profilefield_branching'), $options);
        $form->setType('param6', PARAM_TEXT);

        // Default data.
        $form->addElement('text', 'defaultdata', get_string('profiledefaultdata', 'admin'), 'size="50"');
        $form->setType('defaultdata', PARAM_TEXT);

        // Load javascript to populate options.
        $jsmod = array(
            'name' => 'profile_field_branching_options',
            'fullpath' => '/user/profile/field/branching/branching.js'
        );

        $PAGE->requires->js_init_call(
            'M.profile_field_branching_options.init',
            array('#fitem_id_param3', '#fitem_id_param4', false),
            false,
            $jsmod
        );
        $PAGE->requires->js_init_call(
            'M.profile_field_branching_options.init',
            array('#fitem_id_param5', '#fitem_id_param6', true),
            false,
            $jsmod
        );
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

        if ($data->param1 == USERPF_BRANCHING_CHECKLIST ||
            $data->param1 == USERPF_BRANCHING_SECONDARY) {

            // Editors are stupid and inconsistant, so change everything to be the same.
            $data->param2['text'] = str_replace("\r", '', $data->param2['text']);
            $data->param2['text'] = str_replace("<p>", '', $data->param2['text']);
            $data->param2['text'] = str_replace("</p>", '<br />', $data->param2['text']);
            $data->param2['text'] = str_replace("<br>", '<br />', $data->param2['text']);

            // Check that we have at least 2 options.
            if (($options = explode("<br />", $data->param2['text'])) === false) {
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
        if ($data->param1 == USERPF_BRANCHING_TEXT) {
            $data->param2 = str_replace("<br>", '', $data->param2['text']);
        } else {
            $data->param2 = str_replace("\r", '', $data->param2['text']);
        }

        // Because we have run out of columns we merge param5 and param6
        // into a single json value for storage.
        $json = array();
        $json['param5'] = $data->param5;
        $json['param6'] = $data->param6;
        $data->param5 = json_encode($json);
        unset($data->param6);

        return $data;
    }

    /**
     * Returns an array of editors used when defining this type of profile field.
     * @return array
     */
    public function define_editors() {
        return array('param2');
    }

    /**
     * Get the param5 json and split into param5 and param6.
     */
    public function define_after_data(&$mform) {

        global $DB;

        $id = required_param('id', PARAM_INT);
        if ($id === 0) {
            return;
        }

        $json = $DB->get_field('user_info_field', 'param5', array('id' => $id), MUST_EXIST);
        $values = json_decode($json);
        if (is_object($values)) {
            foreach ($values as $key => $value) {
                $mform->setDefault($key, $value);
            }
        }

        // When the form first loads make sure at least the currect option is an option so it's gets selected
        // $form->addElement('select', 'param4', get_string('branchvalue', 'profilefield_branching'), $options);
        $param4 = $mform->getElementValue('param4');
        $mform->getElement('param4')->addOption($param4[0], $param4[0]);
        $param6 = $mform->getElementValue('param6');
        if (!empty($param6)) {
            $mform->getElement('param6')->addOption($param6[0], $param6[0]);
        }
    }
}
