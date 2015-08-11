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
 * Library definitions and functions for profilefield_branching 
 *
 * @package     profilefield
 * @subpackage  branching
 * @author      Marcus Boon<marcus@catalyst-au.net>
 */

defined('MOODLE_INTERNAL') || die;

// Check and define constants for branching type.
defined('USERPF_BRANCHING_TEXT')        || define('USERPF_BRANCHING_TEXT',        0);
defined('USERPF_BRANCHING_CHECKLIST')   || define('USERPF_BRANCHING_CHECKLIST',   1);
defined('USERPF_BRANCHING_SECONDARY')   || define('USERPF_BRANCHING_SECONDARY',   2);
defined('USERPF_BRANCHING_DECLARATION') || define('USERPF_BRANCHING_DECLARATION', 3);
