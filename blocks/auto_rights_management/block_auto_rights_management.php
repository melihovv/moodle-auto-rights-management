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

use block_auto_rights_management\checkers\cheating_checker;
use block_auto_rights_management\checkers\different_ip_checker;

defined('MOODLE_INTERNAL') || die();

/**
 * Class block_auto_rights_management.
 *
 * The main idea of auto rights management block is to add ability to teacher to automatically manage students rights.
 * For example, it is useful for the following task. When student starts his exam, he should not be allowed to
 * chat or to use forum. When student finishes his exam, his rights should be restored.
 *
 * @package    block
 * @subpackage auto_rights_management
 * @author     Alexander Melihov <amelihovv@ya.ru>
 * @copyright  2018 Oleg Sychev, Volgograd State Technical University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_auto_rights_management extends block_base {
    /**
     * @inheritdoc
     */
    public function init() {
        $this->title = get_string('auto_rights_management', 'block_auto_rights_management');
    }

    /**
     * @inheritdoc
     * @throws \moodle_exception
     */
    public function get_content() {
        if (!has_capability('block/auto_rights_management:view', $this->context)) {
            return '';
        }

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass;

        global $COURSE;
        $url = new moodle_url('/blocks/auto_rights_management/view.php', [
            'blockid' => $this->instance->id,
            'courseid' => $COURSE->id,
        ]);
        $this->content->footer = html_writer::link($url, get_string('more', 'block_auto_rights_management'));

        return $this->content;
    }

    /**
     * @inheritdoc
     */
    public function applicable_formats() {
        return [
            'course-view' => true,
        ];
    }
}
