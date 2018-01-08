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

class auto_rights_management_events_handler {
    /**
     * @param \core\event\base $event
     * @throws dml_exception
     * @throws coding_exception
     */
    public static function on_quiz_started(\core\event\base $event) {
        static::deny($event);
    }

    /**
     * @param \core\event\base $event
     * @throws dml_exception
     * @throws coding_exception
     */
    public static function on_quiz_ended(\core\event\base $event) {
        static::rollback_deny($event);
    }

    /**
     * @param \core\event\base $event
     * @throws dml_exception
     * @throws coding_exception
     */
    private static function deny(\core\event\base $event) {
        static::deny_or_rollback($event, 'deny');
    }

    /**
     * @param \core\event\base $event
     * @throws dml_exception
     * @throws coding_exception
     */
    private static function rollback_deny(\core\event\base $event) {
        static::deny_or_rollback($event, 'rollback_deny');
    }

    /**
     * @param \core\event\base $event
     * @param $method
     * @throws dml_exception
     * @throws coding_exception
     */
    private static function deny_or_rollback(\core\event\base $event, $method) {
        global $DB;

        // Block instance in course where event is occurred.
        $instance = $DB->get_record('block_instances', [
            'blockname' => 'auto_rights_management',
            'parentcontextid' => $event->get_context()->get_parent_context()->id,
        ]);

        $block = block_instance('auto_rights_management', $instance);

        $config = $block->config;
        if ($config === null) {
            return;
        }

        $context = context::instance_by_id($config->context);
        (new \block_auto_rights_management\rights_manager)->{$method}($event->userid, $config->capabilities, $context);
    }
}

