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

use block_auto_rights_management\rights_manager;

class rights_manager_test extends advanced_testcase {
    public function test_it_should_deny_permission_for_student() {
        $this->resetAfterTest();

        list($student, $course) = $this->setup_course_with_student();
        $coursecontext = context_course::instance($course->id);

        $this->assertTrue(has_capability('moodle/grade:view', $coursecontext, $student));

        (new rights_manager)->deny($student->id, ['moodle/grade:view'], $coursecontext);
        accesslib_clear_all_caches(true);

        $this->assertFalse(has_capability('moodle/grade:view', $coursecontext, $student));
    }

    public function test_it_should_deny_permission_for_student_and_allow_for_other_one() {
        $this->resetAfterTest();

        list($studentdeny, $course) = $this->setup_course_with_student();
        $studentallow = $this->create_user_and_enrol_him_to_course($course->id);
        $coursecontext = context_course::instance($course->id);

        $this->assertTrue(has_capability('moodle/grade:view', $coursecontext, $studentdeny));
        $this->assertTrue(has_capability('moodle/grade:view', $coursecontext, $studentallow));

        (new rights_manager)->deny($studentdeny->id, ['moodle/grade:view'], $coursecontext);
        accesslib_clear_all_caches(true);

        $this->assertFalse(has_capability('moodle/grade:view', $coursecontext, $studentdeny));
        $this->assertTrue(has_capability('moodle/grade:view', $coursecontext, $studentallow));
    }

    public function test_it_should_deny_permission_for_student_even_if_he_has_allow_permission_in_the_same_context() {
        $this->resetAfterTest();

        list($studentdeny, $course) = $this->setup_course_with_student();
        $student = $this->create_user_and_enrol_him_to_course($course->id);
        $coursecontext = context_course::instance($course->id);

        $this->assertFalse(has_capability('moodle/site:configview', $coursecontext, $studentdeny));

        assign_capability('moodle/site:configview', CAP_ALLOW, $studentrole = 5, $coursecontext);
        accesslib_clear_all_caches(true);

        $this->assertTrue(has_capability('moodle/site:configview', $coursecontext, $studentdeny));

        (new rights_manager)->deny($studentdeny->id, ['moodle/site:configview'], $coursecontext);
        accesslib_clear_all_caches(true);

        $this->assertFalse(has_capability('moodle/site:configview', $coursecontext, $studentdeny));
    }

    public function test_it_should_deny_permission_for_student_in_one_course_and_allow_in_another() {
        $this->resetAfterTest();

        list($student, $coursedeny) = $this->setup_course_with_student();
        $coursecontextdeny = context_course::instance($coursedeny->id);

        $courseallow = $this->create_course();
        $coursecontextallow = context_course::instance($courseallow->id);
        $this->enrol_student($student->id, $courseallow->id);

        $this->assertTrue(has_capability('moodle/grade:view', $coursecontextdeny, $student));
        $this->assertTrue(has_capability('moodle/grade:view', $coursecontextallow, $student));

        (new rights_manager)->deny($student->id, ['moodle/grade:view'], $coursecontextdeny);
        accesslib_clear_all_caches(true);

        $this->assertFalse(has_capability('moodle/grade:view', $coursecontextdeny, $student));
        $this->assertTrue(has_capability('moodle/grade:view', $coursecontextallow, $student));
    }

    public function test_it_should_deny_permission_for_student_in_the_course_and_allow_in_above_contexts() {
        $this->resetAfterTest();

        list($student, $course) = $this->setup_course_with_student();
        $coursecontext = context_course::instance($course->id);
        $categorycontext = $coursecontext->get_parent_context();
        $systemcontext = $categorycontext->get_parent_context();

        $this->assertTrue(has_capability('block/badges:myaddinstance', $coursecontext, $student));
        $this->assertTrue(has_capability('block/badges:myaddinstance', $categorycontext, $student));
        $this->assertTrue(has_capability('block/badges:myaddinstance', $systemcontext, $student));

        (new rights_manager)->deny($student->id, ['block/badges:myaddinstance'], $coursecontext);
        accesslib_clear_all_caches(true);

        $this->assertFalse(has_capability('block/badges:myaddinstance', $coursecontext, $student));
        $this->assertTrue(has_capability('block/badges:myaddinstance', $categorycontext, $student));
        $this->assertTrue(has_capability('block/badges:myaddinstance', $systemcontext, $student));
    }

    /**
     * @return array
     */
    private function setup_course_with_student() {
        $course = $this->create_course();

        return [$this->create_user_and_enrol_him_to_course($course->id), $course];
    }

    /**
     * @return object|stdClass
     */
    private function create_course() {
        $generator = static::getDataGenerator();

        global $COURSE;
        $COURSE = $generator->create_course();

        return $COURSE;
    }

    /**
     * @param $courseid
     * @return stdClass
     */
    private function create_user_and_enrol_him_to_course($courseid) {
        $student = static::getDataGenerator()->create_user();
        $this->enrol_student($student->id, $courseid);

        return $student;
    }

    /**
     * @param int $studentid
     * @param int $courseid
     */
    private function enrol_student($studentid, $courseid) {
        static::getDataGenerator()->enrol_user($studentid, $courseid, 'student');
    }
}
