<?php

// This file is part of Student Access Control Kit - https://bitbucket.org/oasychev/moodle-plugins/overview
//
// Student Access Control Kit is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Student Access Control Kit is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package    block
 * @subpackage auto_rights_management
 * @author     Alexander Melihov <amelihovv@ya.ru>
 * @copyright  2018 Oleg Sychev, Volgograd State Technical University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$observers = [
    [
        'eventname' => '\mod_quiz\event\attempt_started',
        'callback' => 'auto_rights_management_events_handler::on_quiz_started',
        'includefile' => '/blocks/auto_rights_management/lib.php',
    ],
    [
        'eventname' => '\mod_quiz\event\attempt_submitted',
        'callback' => 'auto_rights_management_events_handler::on_quiz_ended',
        'includefile' => '/blocks/auto_rights_management/lib.php',
    ],
    [
        'eventname' => '\mod_quiz\event\attempt_becameoverdue',
        'callback' => 'auto_rights_management_events_handler::on_quiz_ended',
        'includefile' => '/blocks/auto_rights_management/lib.php',
    ],
];
