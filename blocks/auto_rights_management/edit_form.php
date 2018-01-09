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
     * @throws coding_exception
     * @throws dml_exception
     * @throws PEAR_Error
     */
    protected function specific_definition($mform) {
        $mform->addElement('header', 'settings_header', get_string('settings_header', 'block_auto_rights_management'));

        global $COURSE;
        $mform->addElement('hidden', 'config_courseid', $COURSE->id);
        $mform->setType('config_courseid', PARAM_INT);

        $this->capabilites_definition($mform);
        $this->context_definition($mform);
        $this->action_definition($mform);
    }

    /**
     * @param MoodleQuickForm $mform
     * @throws coding_exception
     * @throws HTML_QuickForm_Error
     */
    private function capabilites_definition($mform) {
        $caps = [];
        foreach (get_all_capabilities() as $cap) {
            $caps[$cap['name']] = "{$cap['name']}: " . get_capability_string($cap['name']);
        }

        $mform->addElement(
            'autocomplete', 'config_capabilities', get_string('settings_capabilities', 'block_auto_rights_management'), $caps, [
                'multiple' => true,
            ]
        );
    }

    /**
     * @param $data
     * @return array
     * @throws coding_exception
     */
    private function capabilities_validation($data) {
        $errors = [];

        if (!isset($data['config_capabilities']) || !is_array($data['config_capabilities'])) {
            $errors['config_capabilities'] = get_string('settings_capabilities_required', 'block_auto_rights_management');
        } else {
            $capnames = array_map(function ($cap) {
                return $cap['name'];
            }, get_all_capabilities());

            foreach ($data['config_capabilities'] as $cap) {
                if (!in_array($cap, $capnames, false)) {
                    $errors['config_capabilities'] = get_string('settings_capabilities_not_exist', 'block_auto_rights_management');
                }
            }
        }

        return $errors;
    }

    /**
     * @param MoodleQuickForm $mform
     * @throws HTML_QuickForm_Error
     * @throws coding_exception
     * @throws dml_exception
     */
    private function context_definition($mform) {
        global $COURSE;

        $systemcontext = context_system::instance();
        $coursecatcontext = context_coursecat::instance($COURSE->category);
        $coursecontext = context_course::instance($COURSE->id);

        $contexts = [
            $systemcontext->id => $systemcontext->get_context_name(),
            $coursecatcontext->id => $coursecatcontext->get_context_name(),
            $coursecontext->id => $coursecontext->get_context_name(),
        ];

        foreach ($coursecontext->get_child_contexts() as $context) {
            $contexts[$context->id] = $context->get_context_name();
        }

        $mform->addElement('select', 'config_context', get_string('settings_context', 'block_auto_rights_management'), $contexts);
        $mform->setType('config_context', PARAM_INT);
    }

    /**
     * @param $data
     * @return array
     * @throws coding_exception
     * @throws dml_exception
     */
    private function context_validation($data) {
        $errors = [];

        if (!isset($data['config_context'])) {
            $errors['config_context'] = get_string('settings_context_required', 'block_auto_rights_management');
        } else {
            global $COURSE;

            $contextids = [
                context_system::instance()->id,
                context_coursecat::instance($COURSE->category)->id,
                ($coursecontext = context_course::instance($COURSE->id))->id,
            ];

            foreach ($coursecontext->get_child_contexts() as $context) {
                $contextids[] = $context->id;
            }

            if (!in_array($data['config_context'], $contextids, false)) {
                $errors['config_context'] = get_string('settings_context_not_exist', 'block_auto_rights_management');
            }
        }

        return $errors;
    }

    /**
     * @param MoodleQuickForm $mform
     * @throws \HTML_QuickForm_Error
     * @throws coding_exception
     * @throws PEAR_Error
     */
    private function action_definition($mform) {
        $mform->addGroup(
            [
                $mform->createElement(
                    'radio', 'config_action', '', get_string('settings_action_enable', 'block_auto_rights_management'), 'enable'
                ),
                $mform->createElement(
                    'radio', 'config_action', '', get_string('settings_action_disable', 'block_auto_rights_management'), 'disable'
                ),
            ],
            'radioar',
            '',
            [' '],
            false
        );
        $mform->setDefault('config_action', 'enable');
    }

    /**
     * @inheritdoc
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function validation($data, $files) {
        return array_merge($this->context_validation($data), $this->capabilities_validation($data));
    }
}
