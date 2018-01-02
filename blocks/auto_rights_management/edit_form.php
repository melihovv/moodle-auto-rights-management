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

defined('MOODLE_INTERNAL') || die();

/**
 * Class block_auto_rights_management_edit_form
 *
 * Class for edit block settings.
 *
 * @package    block
 * @subpackage auto_rights_management
 * @author     Alexander Melihov <amelihovv@ya.ru>
 * @copyright  2018 Oleg Sychev, Volgograd State Technical University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_auto_rights_management_edit_form extends block_edit_form {
    /**
     * @param MoodleQuickForm $mform
     * @throws \HTML_QuickForm_Error
     */
    protected function specific_definition($mform) {
        $mform->addElement('header', 'config_header', get_string('settings_header', 'block_auto_rights_management'));

        $mform->addElement('text', 'config_foo', 'Foo');
        $mform->setDefault('config_foo', 'Foo');
        $mform->setType('config_foo', PARAM_ALPHA);
    }
}
