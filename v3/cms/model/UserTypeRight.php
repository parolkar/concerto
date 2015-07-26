<?php

/*
  Concerto Platform - Online Adaptive Testing Platform
  Copyright (C) 2011-2013, The Psychometrics Centre, Cambridge University

  This program is free software; you can redistribute it and/or
  modify it under the terms of the GNU General Public License
  as published by the Free Software Foundation; version 2
  of the License, and not any of the later versions.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

class UserTypeRight extends OTable
{
    public $UserType_id = 0;
    public $Module_id = 0;
    public $read = 1;
    public $write = 1;
    public $ownership = 1;
    public static $mysql_table_name = "UserTypeRight";

    public function get_UserType()
    {
        return UserType::from_mysql_id($this->UserType_id);
    }

    public static function create_db($delete = false)
    {
        if ($delete)
        {
            if (!mysql_query("DROP TABLE IF EXISTS `UserTypeRight`;"))
                    return false;
        }
        $sql = "
            CREATE TABLE IF NOT EXISTS `UserTypeRight` (
            `id` bigint(20) NOT NULL auto_increment,
            `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
            `created` timestamp NOT NULL default '0000-00-00 00:00:00',
            `Module_id` int(11) NOT NULL,
            `UserType_id` bigint(20) NOT NULL,
            `read` int(11) NOT NULL,
            `write` int(11) NOT NULL,
            `ownership` tinyint(1) NOT NULL,
            PRIMARY KEY  (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=20 ;
            ";
        if (!mysql_query($sql)) return false;

        $sql = "
            INSERT INTO `UserTypeRight` (`id`, `updated`, `created`, `Module_id`, `UserType_id`, `read`, `write`, `ownership`) VALUES
            (1, '2012-01-13 19:57:12', '0000-00-00 00:00:00', 1, 1, 5, 5, 1),
            (2, '2012-02-22 19:55:23', '0000-00-00 00:00:00', 2, 1, 5, 5, 1),
            (3, '2012-01-13 19:57:20', '0000-00-00 00:00:00', 3, 1, 5, 5, 1),
            (4, '2012-01-13 19:57:20', '0000-00-00 00:00:00', 4, 1, 5, 5, 1),
            (5, '2012-01-13 20:16:12', '0000-00-00 00:00:00', 5, 1, 5, 5, 1),
            (6, '2012-01-13 19:57:20', '0000-00-00 00:00:00', 6, 1, 5, 5, 1),
            (7, '2012-01-11 15:05:29', '2012-01-11 15:04:58', 1, 4, 3, 3, 0),
            (8, '2012-01-11 15:05:56', '2012-01-11 15:04:58', 2, 4, 3, 3, 0),
            (9, '2012-01-11 15:05:56', '2012-01-11 15:04:58', 6, 4, 3, 3, 0),
            (10, '2012-02-06 13:52:38', '2012-01-11 15:04:58', 4, 4, 3, 3, 0),
            (11, '2012-02-06 13:55:54', '2012-01-11 15:04:58', 5, 4, 3, 1, 0),
            (12, '2012-02-06 13:52:38', '2012-01-11 15:04:58', 3, 4, 2, 2, 0),
            (19, '2012-01-18 18:59:34', '2012-01-18 18:59:34', 7, 1, 5, 5, 1),
            (20, '2012-02-03 18:29:48', '2012-02-03 18:29:48', 7, 4, 3, 3, 0),
            (21, '2012-01-18 18:59:34', '2012-01-18 18:59:34', 8, 1, 5, 5, 1),
            (22, '2012-02-03 18:29:48', '2012-02-03 18:29:48', 8, 4, 3, 3, 0);
            ";
        return mysql_query($sql);
    }

}

?>