<?php

/*
  Concerto Testing Platform,
  Web based adaptive testing platform utilizing R language for computing purposes.

  Copyright (C) 2011  Psychometrics Centre, Cambridge University

  This program is free software: you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation, either version 3 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

class User extends OModule
{
    public $login = "";
    public $firstname = "";
    public $lastname = "";
    public $email = "";
    public $phone = "";
    public $md5_password = "";
    public $Group_id = 0;
    public $last_action_time = 0;
    public $superadmin = 0;
    public static $mysql_table_name = "User";

    public function is_superadmin()
    {
        if ($this->superadmin == 1) return true;
        else return false;
    }

    public function has_Group()
    {
        if ($this->Group_id != 0) return true;
        else return false;
    }

    public function get_Group()
    {
        return Group::from_mysql_id($this->Group_id);
    }

    public static function get_logged_user()
    {
        if (isset($_SESSION['tpc_logged_login']) && isset($_SESSION['tpc_logged_md5_password']))
        {
            $user = self::from_property(array(
                        "login" => $_SESSION['tpc_logged_login'],
                        "md5_password" => $_SESSION['tpc_logged_md5_password']
                            ), false);
            if ($user != null)
            {
                $user->last_action_time = time();
                $user->mysql_save();
                return $user;
            }
        }
        return null;
    }

    public static function log_in($login, $password)
    {
        $user = self::from_property(array(
                    "login" => $login,
                    "md5_password" => md5($password)
                        ), false);
        if ($user != null)
        {
            $_SESSION['tpc_logged_login'] = $login;
            $_SESSION['tpc_logged_md5_password'] = md5($password);
        }
        return $user;
    }

    public static function log_out()
    {
        unset($_SESSION['tpc_logged_login']);
        unset($_SESSION['tpc_logged_md5_password']);
    }

    public function get_last_action_time_date($format="Y-m-d H:i:s")
    {
        if ($this->last_action_time == 0) return "&lt;none&gt;";
        return date($format, $this->last_action_time);
    }

    public function get_full_name()
    {
        return $this->firstname . " " . $this->lastname;
    }

    public function mysql_delete()
    {
        $this->clear_object_links(Group::get_mysql_table(), "Owner_id");
        $this->clear_object_links(Item::get_mysql_table(), "Owner_id");
        $this->mysql_delete_object();
    }

    public function mysql_save_from_post($post)
    {
        if ($post['modify_password'] == 1)
                $post['md5_password'] = md5($post['password']);
        $post['oid'] = parent::mysql_save_from_post($post);

        if ($this->id == 0 && $post['oid'] != 0)
        {
            $obj = self::from_mysql_id($post['oid']);
            $obj->Owner_id = $post['oid'];
            $obj->mysql_save();

            if ($post['send_credentials'] == 1)
                    mail($post['email'], "Concerto Platform login credentials.", nl2br($post['welcome']) . "<br/><br/>Your account has been created and you can now log in at: <b>" . Ini::$external_path . "admin</b><br/>Login: <b>" . $post['login'] . "</b><br/>Password: <b>" . $post['password'] . "</b>", "Content-type: text/html; charset=utf-8\r\nFrom: accounts@concerto-platform.org");
        }
        return $post['oid'];
    }

    public function mysql_list_rights_filter($table_name, $sort)
    {
        $where = "(`$table_name`.`Owner_id`='" . $this->id . "' OR owner.`Group_id`='" . $this->Group_id . "' AND `$table_name`.`Sharing_id`>=2 OR `$table_name`.`Sharing_id`=3 OR " . $this->superadmin . "=1)";

        $sql = sprintf("SELECT `$table_name`.`id` FROM `$table_name`
		LEFT JOIN `%s` AS owner ON owner.`id`=`$table_name`.`Owner_id`
		WHERE %s
		ORDER BY %s", self::get_mysql_table(), $where, $sort);
        return $sql;
    }

    public static function initialize_session()
    {
        mysql_query("INSERT INTO `Session` SET `time_created`=" . time());

        return mysql_insert_id();
    }

}

?>