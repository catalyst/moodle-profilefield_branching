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
 * Page to set required form fields which are required in profilefield_branching.
 *
 * @author     Nicholas Hoobin <nicholashoobin@catalyst-au.net>
 * @copyright  2021 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


use profilefield_branching\form\set_require_fields_form;
use profilefield_branching\utility;

require_once(__DIR__.'/../../../../config.php');

$context = context_system::instance();
$PAGE->set_context($context);
$output = $PAGE->get_renderer('core');

$baseurl = new moodle_url('/user/profile/field/branching/set_required_fields.php');
$PAGE->set_url($baseurl);

// Not the full require_login() check, as it creates a cyclic loop around profilefield_branching_after_require_login().
if ((!isloggedin() or isguestuser())) {
    echo $output->header();
    echo $output->heading(get_string('loggedinnot'));
    echo $output->footer();
    die();
}

// The require_login() function would usually check this. But we don't want to get stuck in a loop. Is this user actually setup?
$fullysetup = (new utility())->user_fully_setup_check($USER);
if ($fullysetup) {
    echo $output->header();
    echo $output->heading(get_string('nothingtosetup', 'profilefield_branching'));
    echo $output->footer();
    die();
}

require_capability('profilefield/branching:managebranchingprofilefields', context_system::instance());

$customdata = [
    'user' => $USER,
    'fields' => utility::profile_user_record($USER->id, true),
];

$form = new set_require_fields_form($baseurl, $customdata);

if ($form->is_submitted()) {
    $data = $form->get_data();

    // Only save data for the logged in $USER at the time.
    $data->id = $USER->id;

    // The userid is part of the $USER object. This should only add fields with the profile_field_ prefix that are specified in the rendered form.
    profile_save_data($data);

    // Redirect to the base moodle page. If the profile was not saved or setup correctly, the hook 'after_require_login' will fire off.
    redirect(new moodle_url('/'));
}

echo $output->header();
echo $output->heading(get_string('header_set_required_fields', 'profilefield_branching'));

$form->display();

echo $output->footer();
