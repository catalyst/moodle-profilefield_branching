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
 * This contains the profilefield_branching set required fields form.
 *
 * @package    profilefield_branching
 * @author     Nicholas Hoobin <nicholashoobin@catalyst-au.net>
 * @copyright  2021 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace profilefield_branching\form;

require_once($CFG->libdir . "/formslib.php");

defined('MOODLE_INTERNAL') || die;

/**
 * Set required fields form.
 *
 * @package    profilefield_branching
 * @author     Nicholas Hoobin <nicholashoobin@catalyst-au.net>
 * @copyright  2021 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class set_require_fields_form extends \moodleform {

    /**
     * Define the form - called by parent constructor
     */
    protected function definition() {
        $mform = $this->_form;

        $user = $this->_customdata['user'];
        $fields = $this->_customdata['fields'];

        profile_definition($mform, $user->id);

        // The userid, used with the profile_save_data() call.
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $this->add_action_buttons(false);

        $this->set_data($fields);

        // Set's fields locked state.
        profile_definition_after_data($mform, $user->id);
    }
}