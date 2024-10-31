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
 * @package    local_parrot_social
 * @copyright  2018 Digital Education Society (http://www.dibig.at)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

// if ($hassiteconfig) {
//     $settings = new admin_settingpage('local_parrot_social_settings', ''); // We ommit the label, so that it does not show the heading.
//     $ADMIN->add('localplugins', new admin_category('local_parrot_social', get_string('pluginname', 'local_parrot_social')));
//     $ADMIN->add('local_parrot_social', $settings);

//     // Possibly we changed the menu, therefore we delete the cache. We should find a better place for this.
//     $cache = cache::make('local_parrot_social', 'supportmenu');
//     $cache->delete('rendered');

//     $settings->add(
//         new admin_setting_configtextarea(
//             'local_parrot_social/extralinks',
//             get_string('extralinks', 'local_parrot_social'),
//             get_string('extralinks:description', 'local_parrot_social'),
//             '',
//             PARAM_TEXT
//         )
//     );
//     $settings->add(
//         new admin_setting_configcheckbox(
//             'local_parrot_social/trackhost',
//             get_string('trackhost', 'local_parrot_social'),
//             get_string('trackhost:description', 'local_parrot_social'),
//             1
//         )
//     );

//     // FAQ read.
//     $settings->add(
//         new admin_setting_configcheckbox(
//             'local_parrot_social/faqread',
//             get_string('faqread', 'local_parrot_social'),
//             '',
//             1
//         )
//     );

//     // FAQ Link.
//     $settings->add(
//         new admin_setting_configtext(
//             'local_parrot_social/faqlink',
//             get_string('faqlink', 'local_parrot_social'),
//             get_string('faqlink:description', 'local_parrot_social'),
//             ''
//         )
//     );

//     // Disable User Profile Links.
//     $settings->add(
//         new admin_setting_configcheckbox(
//             'local_parrot_social/userlinks',
//             get_string('userlinks', 'local_parrot_social'),
//             get_string('userlinks:description', 'local_parrot_social'),
//             1
//         )
//     );

//     // Priority LVL.
//     $settings->add(
//         new admin_setting_configcheckbox(
//             'local_parrot_social/prioritylvl',
//             get_string('prioritylvl', 'local_parrot_social'),
//             get_string('prioritylvl:description', 'local_parrot_social'),
//             1
//         )
//     );

//     // Disable Telephone Link.
//     $settings->add(
//         new admin_setting_configcheckbox(
//             'local_parrot_social/phonefield',
//             get_string('phonefield', 'local_parrot_social'),
//             get_string('phonefield:description', 'local_parrot_social'),
//             1
//         )
//     );

//     // Delete threshhold.
//     $settings->add(
//         new admin_setting_configduration(
//             'local_parrot_social/deletethreshhold',
//             get_string('deletethreshhold', 'local_parrot_social'),
//             get_string('deletethreshhold:description', 'local_parrot_social'),
//             4 * WEEKSECS
//         )
//     );



//     // @TODO a feature from the future.
//     // $settings->add(new admin_setting_configcheckbox('local_parrot_social/sendreminders', get_string('cron:reminder:title', 'local_parrot_social'), '', '', PARAM_INT));

//     $actions = array(
//         (object) array('name' => 'supporters', 'href' => 'choosesupporters.php')
//     );
//     $links = "<div class=\"grid-eq-3\">";
//     foreach ($actions as $action) {
//         $links .= '<a class="btn btn-secondary" href="' . $CFG->wwwroot . '/local/parrot_social/' . $action->href . '">' .
//             get_string($action->name, 'local_parrot_social') . '</a>';
//     }
//     $links .= "</div>";
//     $settings->add(new admin_setting_heading('local_parrot_social_actions', get_string('settings'), $links));
// }
