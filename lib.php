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
 * Live Class Schedule
 *
 * @package   block_class_schedule
 * @copyright NED {@link http://ned.ca}
 * @developer Michael Gardener <mgardener@cissq.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

function block_class_schedule_get_user_groups($userid, $courseid) {
    global $DB;

    if (has_capability('moodle/course:managegroups', context_course::instance($courseid))) {
        return $DB->get_records('groups', array('courseid' => $courseid));
    }

    $sql = "SELECT g.*
              FROM {groups} g
              JOIN {groups_members} m
                ON g.id = m.groupid
             WHERE g.courseid = ?
               AND m.userid = ?";

    return $DB->get_records_sql($sql, array($courseid, $userid));
}

function block_class_schedule_get_group_members_role($roleid, $groupid, $courseid) {
    global $DB;

    $sql = "SELECT u.*
              FROM {groups_members} gm
              JOIN {user} u
                ON gm.userid = u.id
              JOIN {role_assignments} ra
                ON u.id = ra.userid
              JOIN {context} ctx
                ON ra.contextid = ctx.id
             WHERE gm.groupid = ?
               AND ctx.contextlevel = ?
               AND ctx.instanceid = ?
               AND ra.roleid = ?
               AND u.deleted = ?
          ORDER BY u.lastname ASC";

    return $DB->get_records_sql($sql, array($groupid, CONTEXT_COURSE, $courseid, $roleid, 0));
}
function block_class_schedule_user_profile_link($user, $courseid) {
    $url = new moodle_url('/user/view.php', array('id' => $user->id, 'course' => $courseid));
    return html_writer::link($url, fullname($user));
}