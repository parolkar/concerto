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

class UserType extends OModule
{
    public $name = "";
    public static $mysql_table_name = "UserType";

    public function __construct($params = array())
    {
        $this->name = Language::string(80);
        parent::__construct($params);
    }

    public function get_rights()
    {
        return UserTypeRight::from_property(array(
                    "UserType_id" => $this->id
                ));
    }

    public function mysql_delete()
    {
        $this->clear_object_links(User::get_mysql_table());

        $utr = UserTypeRight::from_property(array(
                    "UserType_id" => $this->id
                ));
        foreach ($utr as $obj) $obj->mysql_delete();

        $this->mysql_delete_object();
    }

    public function mysql_save_from_post($post)
    {
        $post['oid'] = parent::mysql_save_from_post($post);

        $obj = self::from_mysql_id($post['oid']);

        if (array_key_exists('ids', $post) && array_key_exists('values', $post) && array_key_exists('rws', $post))
        {
            for ($i = 0; $i < count($post['ids']); $i++)
            {
                $id = $post['ids'][$i];
                $val = $post['values'][$i];
                $rw = $post['rws'][$i];

                if ($id != "" && $val != "" && $rw != "")
                {
                    $right = UserTypeRight::from_property(array(
                                "UserType_id" => $obj->id,
                                "Module_id" => $id
                                    ), false);
                    if ($right == null)
                    {
                        $right = new UserTypeRight();
                        $right->UserType_id = $obj->id;
                        $right->Module_id = $id;
                    }

                    if ($rw == "r") $right->read = $val;
                    if ($rw == "w") $right->write = $val;
                    if ($rw == "o") $right->ownership = $val;
                    $right->mysql_save();
                }
            }
        }
        return $post['oid'];
    }

    public function get_rights_by_module($Module_id)
    {
        foreach ($this->get_rights() as $right)
        {
            if ($right->Module_id == $Module_id) return $right;
        }

        $right = new UserTypeRight();
        $right->Module_id = $Module_id;
        $right->UserType_id = $this->id;
        return $right;
    }

    public function get_rights_by_module_table($table_name)
    {
        $Module_id = 0;
        $module = DS_Module::from_value($table_name);
        if ($module != null) $Module_id = $module->id;
        return $this->get_rights_by_module($Module_id);
    }

    public static function create_db($delete = false)
    {
        if ($delete)
        {
            if (!mysql_query("DROP TABLE IF EXISTS `UserType`;")) return false;
        }
        $sql = "
            CREATE TABLE IF NOT EXISTS `UserType` (
            `id` int(11) NOT NULL auto_increment,
            `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
            `created` timestamp NOT NULL default '0000-00-00 00:00:00',
            `name` text NOT NULL,
            `Sharing_id` int(11) NOT NULL,
            `Owner_id` bigint(20) NOT NULL,
            PRIMARY KEY  (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;
            ";
        if (!mysql_query($sql)) return false;

        $sql = "
            INSERT INTO `UserType` (`id`, `updated`, `created`, `name`, `Sharing_id`, `Owner_id`) VALUES
            (1, '2012-01-13 20:07:27', '2011-12-05 13:46:52', 'super admin', 1, 1),
            (4, '2012-01-11 14:24:56', '2012-01-11 14:24:56', 'standard', 3, 1);
            ";
        return mysql_query($sql);
    }
}

?>