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


class block_class_schedule_edit_form extends block_edit_form {

    protected function specific_definition($mform) {
        global $DB;

        $mform->addElement('text', 'config_blocktitle', get_string('blocktitle', 'block_class_schedule'));
        $mform->setDefault('config_blocktitle', get_string('pluginname', 'block_class_schedule'));
        $mform->setType('config_blocktitle', PARAM_TEXT);

        $roleoptions = $DB->get_records_menu('role', null, 'shortname ASC', 'id,shortname');

        $mform->addElement('select', 'config_role1', get_string('role1', 'block_class_schedule'), $roleoptions);
        $mform->setDefault('config_role1', '3');

        $mform->addElement('text', 'config_role1name', get_string('role1name', 'block_class_schedule'));
        $mform->setType('config_role1name', PARAM_TEXT);

        $mform->addElement('select', 'config_role2', get_string('role2', 'block_class_schedule'), $roleoptions);
        $mform->setDefault('config_role2', '4');

        $mform->addElement('text', 'config_role2name', get_string('role2name', 'block_class_schedule'));
        $mform->setType('config_role2name', PARAM_TEXT);
    }
}
