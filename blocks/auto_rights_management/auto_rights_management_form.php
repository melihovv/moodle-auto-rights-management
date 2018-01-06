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

global $CFG;
require_once("$CFG->libdir/formslib.php");

/**
 * Class auto_rights_management_form.
 *
 * @package    block
 * @subpackage auto_rights_management
 * @author     Alexander Melihov <amelihovv@ya.ru>
 * @copyright  2018 Oleg Sychev, Volgograd State Technical University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class auto_rights_management_form extends moodleform {
    /**
     * @inheritdoc
     * @throws \dml_exception
     * @throws \coding_exception
     * @throws \PEAR_Error
     * @throws \HTML_QuickForm_Error
     */
    public function definition() {
        $mform = $this->_form;

        $mform->addElement('header', 'formheader', get_string('auto_rights_management', 'block_auto_rights_management'));

        global $COURSE, $DB;
        $mform->addElement('hidden', 'courseid', $COURSE->id);
        $mform->setType('courseid', PARAM_INT);

        $mform->addElement('hidden', 'blockid', $blockid = $this->_customdata['blockid']);
        $mform->setType('blockid', PARAM_INT);

        $record = $DB->get_record('block_auto_rights_management', ['courseid' => $COURSE->id]);
        $settings = [];

        if ($record) {
            $settings = json_decode(base64_decode($record->settings), true);
        }

        $this->capabilites_definition($settings);
        $this->context_definition($settings);

        $this->buttons_definition();
    }

    /**
     * @param array $settings
     * @throws HTML_QuickForm_Error
     * @throws coding_exception
     */
    private function capabilites_definition($settings) {
        $mform = $this->_form;

        $caps = [];
        foreach (get_all_capabilities() as $cap) {
            $caps[$cap['id']] = "{$cap['name']}: " . get_capability_string($cap['name']);
        }

        $mform->addElement('autocomplete', 'capabilities', get_string('form_capabilities', 'block_auto_rights_management'), $caps, [
            'multiple' => true,
        ]);
        $mform->setType('capabilities', PARAM_INT);

        if (isset($settings['capabilities'])) {
            $mform->setDefault('capabilities', $settings['capabilities']);
        }
    }

    /**
     * @param $data
     * @return array
     * @throws coding_exception
     */
    private function capabilities_validation($data) {
        $errors = [];

        if (!isset($data['capabilities']) || !is_array($data['capabilities'])) {
            $errors['capabilities'] = get_string('form_capabilities_required', 'block_auto_rights_management');
        } else {
            $capids = array_map(function ($cap) {
                return $cap['id'];
            }, get_all_capabilities());

            foreach ($data['capabilities'] as $cap) {
                if (!in_array($cap, $capids, false)) {
                    $errors['capabilities'] = get_string('form_capabilities_not_exist', 'block_auto_rights_management');
                }
            }
        }

        return $errors;
    }

    /**
     * @param array $settings
     * @throws HTML_QuickForm_Error
     * @throws coding_exception
     * @throws dml_exception
     */
    private function context_definition($settings) {
        global $COURSE;

        $mform = $this->_form;

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

        $mform->addElement('select', 'context', get_string('form_context', 'block_auto_rights_management'), $contexts);
        $mform->setType('context', PARAM_INT);

        if (isset($settings['context'])) {
            $mform->setDefault('context', $settings['context']);
        }
    }

    /**
     * @param $data
     * @return array
     * @throws coding_exception
     * @throws dml_exception
     */
    private function context_validation($data) {
        $errors = [];

        if (!isset($data['context'])) {
            $errors['context'] = get_string('form_context_required', 'block_auto_rights_management');
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

            if (!in_array($data['context'], $contextids, false)) {
                $errors['context'] = get_string('form_context_not_exist', 'block_auto_rights_management');
            }
        }

        return $errors;
    }

    /**
     * @throws \HTML_QuickForm_Error
     * @throws coding_exception
     * @throws PEAR_Error
     */
    private function buttons_definition() {
        $mform = $this->_form;

        $mform->addGroup(
            [
                $mform->createElement('submit', 'enable_button', get_string('form_btn_enable', 'block_auto_rights_management')),
                $mform->createElement('submit', 'disable_button', get_string('form_btn_disable', 'block_auto_rights_management')),
                $mform->createElement('cancel'),
            ],
            'buttonar',
            '',
            [' '],
            false
        );
        $mform->closeHeaderBefore('buttonar');
    }

    /**
     * @inheritdoc
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function validation($data, $files) {
        return array_merge($this->context_validation($data), $this->capabilities_validation($data));
    }

    /**
     * Save form data to database.
     * @throws dml_exception
     */
    public function save() {
        global $DB, $COURSE;

        $data = $this->get_data();

        $record = $DB->get_record('block_auto_rights_management', ['courseid' => $COURSE->id]);
        $params = [
            'courseid' => $COURSE->id,
            'settings' => base64_encode(json_encode([
                'context' => $data->context,
                'capabilities' => $data->capabilities,
                'action' => $data->enable_button ? 'enable' : 'disable',
            ])),
        ];

        if ($record) {
            $DB->update_record('block_auto_rights_management', (object)array_merge($params, ['id' => $record->id]));
        } else {
            $DB->insert_record('block_auto_rights_management', (object)$params);
        }
    }
}
