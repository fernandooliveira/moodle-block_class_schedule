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

require_once($CFG->dirroot . '/blocks/class_schedule/lib.php');
require_once($CFG->dirroot . '/group/lib.php');

class block_class_schedule extends block_list {

    public function init() {
        $this->title = get_string('pluginname', 'block_class_schedule');
    }

    public function specialization() {
        if (!empty($this->config->blocktitle)) {
            $this->title = $this->config->blocktitle;
        } else if ($title = get_config('block_class_schedule', 'blocktitle')) {
            $this->title = $title;
        }
    }

    public function get_content() {
        global $DB, $USER, $OUTPUT;

        if ($this->content !== null) {
            return $this->content;
        }

        if (empty($this->instance)) {
            $this->content = '';
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->items = array();
        $this->content->icons = array();
        $this->content->footer = '';

        $currentcontext = $this->page->context->get_course_context(false);

        $thispageurl = clone $this->page->url;

        $this->content = '';
        if (empty($currentcontext)) {
            return $this->content;
        }

        if ($this->page->course->id == SITEID) {
            return $this->content;
        }

        // Settings.
        $sitesetting = get_config('block_class_schedule');

        // Override global settings with instance settings.
        if (!empty($this->config->role1)) {
            $sitesetting->role1 = $this->config->role1;
        }
        if (!empty($this->config->role1name)) {
            $sitesetting->role1name = $this->config->role1name;
        }
        if (!empty($this->config->role2)) {
            $sitesetting->role2 = $this->config->role2;
        }
        if (!empty($this->config->role2name)) {
            $sitesetting->role2name = $this->config->role2name;
        }

        $groupid = optional_param('groupid', 0, PARAM_INT);

        // Groups.
        if ($groups = block_class_schedule_get_user_groups($USER->id, $this->page->course->id)) {

            $spacer = html_writer::img($OUTPUT->pix_url('spacer'), '', array('class' => 'icon'));
            $usericon = html_writer::img($OUTPUT->pix_url('i/user'), '', array('class' => 'icon icon-inner'));
            $groupicon = html_writer::img($OUTPUT->pix_url('group_circle', 'block_class_schedule'), '',
                array('class' => 'icon'));

            if (count($groups) > 1) {
                $groupmenuurl = [];
                $groupoptions = [];

                // Select group.
                $thispageurl->param('groupid', 0);
                $groupmenuurl[0] = $thispageurl->out(false);
                $groupoptions[$thispageurl->out(false)] = get_string('selectgroup', 'block_class_schedule');

                foreach ($groups as $group) {
                    $thispageurl->param('groupid', $group->id);
                    $groupmenuurl[$group->id] = $thispageurl->out(false);
                    $groupoptions[$thispageurl->out(false)] = $group->name;
                }

                // Show all.
                $thispageurl->param('groupid', -1);
                $groupmenuurl[-1] = $thispageurl->out(false);
                $groupoptions[$thispageurl->out(false)] = get_string('showall', 'block_class_schedule');

                $this->content->icons[] = null;
                $this->content->items[] = html_writer::div(
                    html_writer::tag('form',
                        get_string('group', 'block_class_schedule') . ' ' .
                        html_writer::select(
                            $groupoptions,
                            'groupid',
                            $groupmenuurl[$groupid],
                            null,
                            array(
                                'onChange' => 'location=document.jump1.groupid.options[document.jump1.groupid.selectedIndex].value;'
                            )
                        ),
                        array('id' => 'groupselectionform', 'name' => 'jump1')
                    ),
                    'group-form-wrapper'
                );
            } else {
                // Show group if it is single.
                $groupid = -1;
            }
            foreach ($groups as $group) {
                if ($groupid != $group->id && $groupid != -1) {
                    continue;
                }
                $groupurl = new moodle_url('/user/index.php',
                    array(
                        'contextid' => $currentcontext->id,
                        'roleid' => 0,
                        'id' => $this->page->course->id,
                        'group' => $group->id
                    )
                );
                $this->content->icons[] = $groupicon;
                $this->content->items[] = html_writer::div(get_string('group', 'block_class_schedule').': ', 'item-label').
                    html_writer::div(html_writer::link($groupurl, $group->name), 'item-text');

                // Role 1.
                if ($role1members = block_class_schedule_get_group_members_role($sitesetting->role1,
                    $group->id, $this->page->course->id)) {
                    $fullnames = [];
                    foreach ($role1members as $role1member) {
                        $fullnames[] = block_class_schedule_user_profile_link($role1member, $this->page->course->id);
                    }
                    $this->content->icons[] = $spacer;
                    $this->content->items[] = $usericon.html_writer::div($sitesetting->role1name.': ', 'item-label').
                        html_writer::div(implode('<br />', $fullnames), 'item-text');
                }

                // Role 2.
                if ($role2members = block_class_schedule_get_group_members_role($sitesetting->role2,
                    $group->id, $this->page->course->id)) {
                    $fullnames = [];
                    foreach ($role2members as $role2member) {
                        $fullnames[] = block_class_schedule_user_profile_link($role2member, $this->page->course->id);
                    }
                    $this->content->icons[] = $spacer;
                    $this->content->items[] = $usericon.html_writer::div($sitesetting->role2name.': ', 'item-label').
                        html_writer::div(implode('<br />', $fullnames), 'item-text');
                }

                // Iframe.
                $this->content->icons[] = null;
                $this->content->items[] = html_writer::tag('iframe', '',
                    array(
                        'src' => $sitesetting->iframe.'/'.strtolower($group->name),
                        'class' => 'live-class-iframe',
                        'scrolling' => 'no',
                        'style' => 'border:1px dotted gray;',
                        'width' => '350',
                        'height' => '200'
                    )
                );
            }
        }
        return $this->content;
    }

    public function applicable_formats() {
        return array(
            'all' => false,
            'my' => false,
            'course-*' => true,
        );
    }

    public function instance_allow_multiple() {
          return false;
    }

    public function has_config() {
        return true;
    }
}
