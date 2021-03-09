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
 * profilefield_branching lib.php
 *
 * @package    profilefield_branching
 * @copyright  2021 Nicholas Hoobin <nicholashoobin@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * Hook via moodlelib.php the 'require_login' function.
 * After making a call to get_plugins_with_function, 'after_require_login'.
 *
 * @param int $courseorid
 * @param bool $autologinguest
 * @param int $cm
 * @param bool $setwantsurltome
 * @param bool $preventredirect
 */
function profilefield_branching_after_require_login($courseorid, $autologinguest, $cm, $setwantsurltome, $preventredirect) {
    global $USER, $SESSION;

    $userfullysetup = isset($SESSION->profilefield_branching_user_is_fully_setup)
        && $SESSION->profilefield_branching_user_is_fully_setup;

    if (!$userfullysetup) {
        if (has_capability('profilefield/branching:managebranchingprofilefields', context_system::instance())) {
            // Only redirect if the user has the ability to modify branched profile fields.
            (new \profilefield_branching\utility())->after_require_login($USER, $setwantsurltome, $preventredirect);

        } else {
            // We can't edit the fields, so this user is a set up as they can be.
            $SESSION->profilefield_branching_user_is_fully_setup = true;
        }
    }

    // We could now say, yes the user is fully setup and return nothing from this callback.
}