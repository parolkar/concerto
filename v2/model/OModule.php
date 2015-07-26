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

class OModule extends OTable {

    public $Owner_id = 0;
    public $Sharing_id = 1;

    public function get_Owner() {
        return User::from_mysql_id($this->Owner_id);
    }

    public function has_Owner() {
        if ($this->get_Owner() != null)
            return true;
        else
            return false;
    }

    public function get_Sharing() {
        return DS_Sharing::from_mysql_id($this->Sharing_id);
    }

}

?>