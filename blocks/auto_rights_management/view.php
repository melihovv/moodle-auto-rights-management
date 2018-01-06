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

require_once('../../config.php');
require_once('./auto_rights_management_form.php');

global $CFG, $DB, $PAGE, $OUTPUT;

$courseid = required_param('courseid', PARAM_INT);
$blockid = required_param('blockid', PARAM_INT);

if (!$course = $DB->get_record('course', ['id' => $courseid])) {
    print_error('invalidcourse', 'block_auto_rights_management', $courseid);
}

require_login($course);

$PAGE->set_url('/blocks/auto_rights_management/view.php', ['id' => $courseid]);
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('auto_rights_management', 'block_auto_rights_management'));

require_capability('block/auto_rights_management:view', $PAGE->context);

$settingsnode = $PAGE->settingsnav->add(get_string('auto_rights_management', 'block_auto_rights_management'));
$blocknode = $settingsnode->add(
    get_string('auto_rights_management', 'block_auto_rights_management'),
    $blockurl = new moodle_url('/blocks/auto_rights_management/view.php', [
        'courseid' => $courseid,
        'blockid' => $blockid,
    ])
);
$blocknode->make_active();

$mform = new auto_rights_management_form(null, ['blockid' => $blockid]);

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/course/view.php', ['id' => $courseid]));
} else if ($mform->get_data()) {
    $mform->save();
    redirect($blockurl);
} else {
    echo $OUTPUT->header();
    $mform->display();
    echo $OUTPUT->footer();
}
