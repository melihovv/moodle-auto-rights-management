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

namespace block_auto_rights_management;

use context;

defined('MOODLE_INTERNAL') || die();

/**
 * Class rights_manager.
 *
 * @package    block
 * @subpackage auto_rights_management
 * @author     Alexander Melihov <amelihovv@ya.ru>
 * @copyright  2017 Oleg Sychev, Volgograd State Technical University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class rights_manager {
    /**
     * @param int $userid
     * @param array|string[] $capabilities
     * @param context $context
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function deny($userid, array $capabilities, context $context) {
        $rolename = $this->generate_role_name($userid, $capabilities, $context);
        $roleid = $this->assign_role_to_user($rolename, $userid, $context);

        foreach ($capabilities as $capability) {
            if (!has_capability($capability, $context, $userid)) {
                continue;
            }

            assign_capability($capability, CAP_PROHIBIT, $roleid, $context);
        }
    }

    /**
     * @param int $userid
     * @param array|string[] $capabilities
     * @param context $context
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function rollback_deny($userid, array $capabilities, context $context) {
        $rolename = $this->generate_role_name($userid, $capabilities, $context);
        $this->unassign_role_from_user($rolename, $userid, $context);
    }

    /**
     * @param string $rolename
     * @param int $userid
     * @param context $context
     * @return int
     * @throws \coding_exception
     * @throws \dml_exception
     */
    private function assign_role_to_user($rolename, $userid, context $context) {
        global $DB;

        $role = $DB->get_record('role', ['name' => $rolename]);

        if ($role) {
            $roleid = $role->id;
        } else {
            $roleid = create_role($rolename, $rolename, '');
        }

        role_assign($roleid, $userid, $context);

        return $roleid;
    }

    /**
     * @param string $rolename
     * @param int $userid
     * @param context $context
     * @throws \coding_exception
     * @throws \dml_exception
     */
    private function unassign_role_from_user($rolename, $userid, context $context) {
        global $DB;

        $role = $DB->get_record('role', ['name' => $rolename]);

        if ($role) {
            role_unassign($role->id, $userid, $context->id);
            delete_role($role->id);
        }
    }

    /**
     * @param int $userid
     * @param array $capabilities
     * @param context $context
     * @return string
     */
    private function generate_role_name($userid, array $capabilities, context $context) {
        return md5($userid . json_encode($capabilities) . $context->id);
    }
}
