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

class TestSection extends OTable {

    public $counter = 0;
    public $TestSectionType_id = 0;
    public $Test_id = 0;
    public $parent_counter = 0;
    public $end = 0;
    public static $mysql_table_name = "TestSection";

    public function mysql_delete() {
        $this->delete_object_links(TestSectionValue::get_mysql_table());
        $this->delete_object_links(TestTemplate::get_mysql_table());
        parent::mysql_delete();
    }

    public function get_Test() {
        return Test::from_mysql_id($this->Test_id);
    }

    public function get_TestSectionType() {
        return DS_TestSectionType::from_mysql_id($this->TestSectionType_id);
    }

    public function get_parent_TestSection() {
        return TestSection::from_property(array("Test_id" => $this->Test_id, "counter" => $this->parent_counter), false);
    }

    public function get_values() {
        $result = array();
        $vals = TestSectionValue::from_property(array("TestSection_id" => $this->id));
        foreach ($vals as $v) {
            $result[$v->index] = $v->value;
        }
        return $result;
    }

    public function delete_values() {
        $sql = sprintf("DELETE FROM `%s` WHERE `TestSection_id`=%d", TestSectionValue::get_mysql_table(), $this->id);
        mysql_query($sql);
    }

    public function get_RFunctionName() {
        return "CONCERTO_Test" . $this->Test_id . "Section" . $this->counter;
    }

    public function get_RFunction() {
        $code = $this->get_RCode();

        if (substr($this->get_RCode(), 0, 5) == "stop(")
            return sprintf("print('Start of section with index: %s')
                    %s", $this->counter, $this->get_RCode());

        return sprintf("
            %s <<- function(){
            print('Start of section with index: %s')
                            %s
                 }
                 ", $this->get_RFunctionName(), $this->counter, $code);
    }

    public static function replace_invalid_code($code) {
        $code = preg_replace('/([^A-Z^a-z^_^\.^0-9]|^)(q\((.*)\))/', '${1}stop("invalid function call")', $code);
        return $code;
    }

    public function get_RCode() {
        $code = "";

        $next = $this->get_next_TestSection();
        $next_counter = ($next != null ? $next->counter : 0);

        $end_of_loop = $this->is_end_of_loop();
        $loop = null;
        $loop_vals = null;
        if ($end_of_loop) {
            $loop = $this->get_section_loop();
            $loop_vals = $loop->get_values();
            $next_counter = $loop->counter;
        }

        $vals = $this->get_values();
        switch ($this->TestSectionType_id) {
            case DS_TestSectionType::START: {
                    $test = Test::from_mysql_id($this->Test_id);
                    if ($test == null) {
                        return sprintf("stop('Invalid test id: %s in section #%s')", $this->Test_id, $this->counter);
                    }
                    $code = sprintf("
                            print('Starting test with id: %s')
                            CONCERTO_TEST_ID <<- %s
                            return(%d)
                            ", $this->Test_id, $this->Test_id, $next_counter);
                    break;
                }
            case DS_TestSectionType::END: {
                    $code = sprintf("
                    update.session.status(%d)    
                    update.session.counter(%d)
                    return(%d)
                    ", TestSession::TEST_SESSION_STATUS_COMPLETED, $next_counter, $next_counter);
                    break;
                }
            case DS_TestSectionType::CUSTOM: {
                    $cs = CustomSection::from_mysql_id($vals[0]);
                    if ($cs == null)
                        return sprintf("stop('Invalid custom section #%s')", $this->counter);
                    $parameters = $cs->get_parameter_CustomSectionVariables();
                    $returns = $cs->get_return_CustomSectionVariables();
                    $code = "";
                    foreach ($parameters as $param) {
                        $val = $param->name;
                        for ($j = 0; $j < $vals[1] * 2; $j = $j + 2) {
                            if ($vals[3 + $j] == $param->name && isset($vals[3 + $j + 1]) && $vals[3 + $j + 1] != "") {
                                $val = $vals[3 + $j + 1];
                                break;
                            }
                        }
                        $code.=sprintf("
                            %s <- %s
                            ", $param->name, $val);
                    }
                    $code.=$cs->code;
                    foreach ($returns as $ret) {
                        $val = $ret->name;

                        for ($j = 0; $j < $vals[2] * 2; $j = $j + 2) {
                            if ($vals[3 + $vals[1] * 2 + $j] == $ret->name && isset($vals[3 + $vals[1] * 2 + $j]) && $vals[3 + $vals[1] * 2 + $j] != "") {
                                $val = $vals[3 + $vals[1] * 2 + $j + 1];
                                break;
                            }
                        }
                        $code.=sprintf("
                            %s <<- %s
                            ", $val, $ret->name);
                    }
                    $code.=sprintf("
                        return(%d)
                        ", ($this->end == 0 ? $next_counter : -2));
                    break;
                }
            case DS_TestSectionType::R_CODE: {
                    $code = sprintf("
                        %s
                        return(%d)
                        ", $vals[0], ($this->end == 0 ? $next_counter : -2)
                    );
                    break;
                }
            case DS_TestSectionType::LOWER_LEVEL_R_CODE: {
                    $code = sprintf("
                        %s
                        ", $vals[0]
                    );
                    break;
                }
            case DS_TestSectionType::QTI_INITIALIZATION: {
                    $ai = QTIAssessmentItem::from_mysql_id($vals[0]);
                    if ($ai == null)
                        return sprintf("stop('Invalid QTI assessment item id: %s in section #%s')", $vals[0], $this->counter);
                    $map = QTIAssessmentItem::get_mapped_variables($this->id);
                    $result = $ai->validate($map, null, $this->id);
                    if (json_decode($result)->result != 0)
                        return sprintf("stop('Validation failed on QTI assessment item id: %s in section #%s')", $vals[0], $this->counter);

                    $qtir = $ai->get_QTI_ini_R_code($map, $this->id);

                    $code = sprintf("
                        %s
                        return(%d)
                        ", $qtir, ($this->end == 0 ? $next_counter : -2)
                    );
                    break;
                }
            case DS_TestSectionType::QTI_RESPONSE_PROCESSING: {
                    $ts = TestSection::from_property(array("counter" => $vals[0], "Test_id" => $this->Test_id), false);
                    if ($ts == null)
                        return sprintf("stop('Invalid test section id: %s in section #%s')", $vals[0], $this->counter);
                    $tsvals = $ts->get_values();
                    $ai = QTIAssessmentItem::from_mysql_id($tsvals[0]);
                    if ($ai == null)
                        return sprintf("stop('Invalid QTI assessment item id: %s in section #%s')", $tsvals[0], $this->counter);
                    $map = QTIAssessmentItem::get_mapped_variables($ts->id);
                    $result = $ai->validate($map, null, $ts->id);
                    if (json_decode($result)->result != 0)
                        return sprintf("stop('Validation failed on QTI assessment item id: %s in section #%s')", $tsvals[0], $this->counter);

                    $qtir = $ai->get_response_processing_R_code();

                    $code = sprintf("
                        %s
                        return(%d)
                        ", $qtir, ($this->end == 0 ? $next_counter : -2)
                    );
                    break;
                }
            case DS_TestSectionType::LOAD_HTML_TEMPLATE: {
                    $template_id = $vals[0];
                    $template = Template::from_mysql_id($template_id);
                    if ($template == null)
                        return sprintf("stop('Invalid template id: %s in section #%s')", $template_id, $this->counter);

                    $code = sprintf("
                        update.session.template_id(%d)
                        if(!exists('TIME_LIMIT')) TIME_LIMIT <<- 0
                        update.session.time_limit(TIME_LIMIT)
                        update.session.status(%d)
                        update.session.counter(%d)
                        update.session.template_testsection_id(%d)
                        update.session.HTML(%d,%d,%d)
                        update.session.effects('%s','%s','%s','%s')
                        return(%d)
                        ", $template_id, TestSession::TEST_SESSION_STATUS_TEMPLATE, $next_counter, $this->id, $this->Test_id, $this->id, $template_id, addcslashes($template->effect_show, "'"), addcslashes($template->effect_hide, "'"), addcslashes($template->effect_show_options, "'"), addcslashes($template->effect_hide_options, "'"), ($this->end == 0 ? -1 : -2)
                    );

                    break;
                }
            case DS_TestSectionType::GO_TO: {
                    $code = "";
                    foreach ($this->get_parent_loops_counters() as $loop) {
                        $target = TestSection::from_property(array("Test_id" => $this->Test_id, "counter" => $vals[0]), false);
                        if ($target != null) {
                            $target_loops = $target->get_parent_loops_counters();
                            if (!in_array($loop, $target_loops)) {
                                $code.=sprintf("
                                    %s <<- FALSE
                                    ", TestSection::build_for_initialization_variable($this->Test_id, $loop));
                            }
                        }
                    }
                    $code .= sprintf("
                        return(%d)
                        ", ($vals[0] == 0 ? $next_counter : $vals[0])
                    );
                    break;
                }
            case DS_TestSectionType::IF_STATEMENT: {
                    $contents = TestSection::from_property(array("Test_id" => $this->Test_id, "parent_counter" => $this->counter));
                    $is_empty = count($contents) == 0;

                    $code = "";
                    $next_not_child = $this->get_next_not_child_TestSection();
                    $next_not_child_counter = $next_not_child->counter;
                    if ($end_of_loop) {
                        $next_not_child_counter = $next_counter;
                    } else {
                        $is_end_of_loop = $this->is_end_of_loop(false);
                        if ($is_end_of_loop) {
                            $parent_loop = $this->get_section_loop();
                            if ($parent_loop != null) {
                                $next_not_child_counter = $parent_loop->counter;
                            }
                        }
                    }

                    if ($is_empty) {
                        $code.=sprintf("
                            return(%d)
                            ", $next_not_child_counter);
                        break;
                    }

                    $additional_conds = "";
                    $i = 3;
                    while (isset($vals[$i])) {
                        $additional_conds.=sprintf("%s %s %s %s", $vals[$i], $vals[$i + 1], $vals[$i + 2], $vals[$i + 3]);
                        $i+=4;
                    }

                    $code .= sprintf("
                if(%s %s %s %s) {
                    return(%d)
                    }
                    else {
                    return(%d)
                    }
                    ", $vals[0], $vals[1], $vals[2], $additional_conds, $next->counter, $next_not_child_counter);

                    break;
                }
            case DS_TestSectionType::TEST: {
                    $test = Test::from_mysql_id($vals[0]);
                    if ($test == null) {
                        return sprintf("stop('Invalid test id: %s in section #%s')", $vals[0], $this->counter);
                    } else {
                        $code = sprintf("
                        CONCERTO_TEST_ID <<- %s
                        ", $test->id);
                        $parameters = $test->get_parameter_TestVariables();
                        $params_code = "";
                        $returns = $test->get_return_TestVariables();
                        $returns_code = "";

                        foreach ($parameters as $param) {
                            $val = $param->name;
                            for ($j = 0; $j < $vals[1] * 2; $j = $j + 2) {
                                if ($vals[3 + $j] == $param->name && isset($vals[3 + $j + 1]) && $vals[3 + $j + 1] != "") {
                                    $val = $vals[3 + $j + 1];
                                    break;
                                }
                            }
                            $params_code.=sprintf("
                            %s <- %s
                            ", $param->name, $val);
                        }
                        foreach ($returns as $ret) {
                            $val = $ret->name;

                            for ($j = 0; $j < $vals[2] * 2; $j = $j + 2) {
                                if ($vals[3 + $vals[1] * 2 + $j] == $ret->name && isset($vals[3 + $vals[1] * 2 + $j]) && $vals[3 + $vals[1] * 2 + $j] != "") {
                                    $val = $vals[3 + $vals[1] * 2 + $j + 1];
                                    break;
                                }
                            }
                            $returns_code.=sprintf("
                            %s <<- %s
                            ", $val, $ret->name);
                        }

                        $code.=sprintf("
                            %s
                            ", $params_code);

                        $sections = TestSection::from_property(array("Test_id" => $test->id));
                        foreach ($sections as $section) {
                            if ($section->TestSectionType_id != DS_TestSectionType::END) {
                                $code.=$section->get_RFunction();
                            } else {
                                $end_code = sprintf("
                                    CONCERTO_TEST_ID <<- %s
                                    %s
                                    return(%d)
                                    ", $this->Test_id, $returns_code, $next_counter);
                                $code.=sprintf("
                                    %s <<- function(){
                                    print('Start of section with index: %s')
                                    %s
                                    }
                                    ", 'CONCERTO_Test' . $vals[0] . 'Section2', 2, $end_code);
                            }
                        }
                        $code.="
                            return(1)
                            ";
                    }
                    break;
                }
            case DS_TestSectionType::LOOP: {
                    $contents = TestSection::from_property(array("Test_id" => $this->Test_id, "parent_counter" => $this->counter));
                    $is_empty = count($contents) == 0;

                    $next_not_child = $this->get_next_not_child_TestSection();
                    $next_not_child_counter = $next_not_child->counter;
                    if ($end_of_loop) {
                        $next_not_child_counter = $next_counter;
                    } else {
                        $is_end_of_loop = $this->is_end_of_loop(false);
                        if ($is_end_of_loop) {
                            $parent_loop = $this->get_section_loop();
                            if ($parent_loop != null) {
                                $next_not_child_counter = $parent_loop->counter;
                            }
                        }
                    }
                    $code = "";

                    if ($is_empty) {
                        $code.=sprintf("
                            return(%d)
                            ", $next_not_child_counter);
                        break;
                    }

                    if ($vals[0] == 1) {
                        $code = sprintf("
                if(%s %s %s) {
                    return(%d)
                    }
                    else {
                    return(%d)
                    }
                    ", $vals[1], $vals[2], $vals[3], $next->counter, $next_not_child_counter);
                    } else {
                        $code = sprintf("
                if(exists('%s') && %s) {
                    %s <<- %s + as.numeric(%s)
                }
                else {
                    %s <<- TRUE
                    %s <<- as.numeric(%s)
                }
                if(%s %s %s) {
                    return(%d)
                    }
                    else {
                    %s <<- FALSE
                    return(%d)
                    }
                    ", $this->get_for_initialization_variable(), $this->get_for_initialization_variable(), $vals[1], $vals[1], $vals[5], $this->get_for_initialization_variable(), $vals[1], $vals[4], $vals[1], $vals[2], $vals[3], $next->counter, $this->get_for_initialization_variable(), $next_not_child_counter);
                    }
                    break;
                }
            case DS_TestSectionType::FOR_LOOP: {
                    $contents = TestSection::from_property(array("Test_id" => $this->Test_id, "parent_counter" => $this->counter));
                    $is_empty = count($contents) == 0;

                    $next_not_child = $this->get_next_not_child_TestSection();
                    $next_not_child_counter = $next_not_child->counter;
                    if ($end_of_loop) {
                        $next_not_child_counter = $next_counter;
                    } else {
                        $is_end_of_loop = $this->is_end_of_loop(false);
                        if ($is_end_of_loop) {
                            $parent_loop = $this->get_section_loop();
                            if ($parent_loop != null) {
                                $next_not_child_counter = $parent_loop->counter;
                            }
                        }
                    }
                    $code = "";

                    if ($is_empty) {
                        $code.=sprintf("
                            return(%d)
                            ", $next_not_child_counter);
                        break;
                    }

                    $code = sprintf("
                if(exists('%s') && %s) {
                    %s <<- %s + 1
                }
                else {
                    %s <<- TRUE
                    %s <<- 1
                }

                CONCERTO_TEMP_FOR_INDEX <- 1
                
                for(CONCERTO_TEMP_FOR_VALUE in %s){
                    if(CONCERTO_TEMP_FOR_INDEX==%s){
                        %s <<- CONCERTO_TEMP_FOR_VALUE
                        return(%d)
                        break
                    }
                    CONCERTO_TEMP_FOR_INDEX <- CONCERTO_TEMP_FOR_INDEX+1
                }
                
                %s <<- FALSE
                return(%d)
                    ", $this->get_for_initialization_variable(), $this->get_for_initialization_variable(),
                            $this->get_for_index_variable(), $this->get_for_index_variable(),
                            $this->get_for_initialization_variable(),
                            $this->get_for_index_variable(),
                            $vals[1],
                            $this->get_for_index_variable(),
                            $vals[0],
                            $next->counter, 
                            $this->get_for_initialization_variable(), 
                            $next_not_child_counter);

                    break;
                }
            case DS_TestSectionType::WHILE_LOOP: {
                    $contents = TestSection::from_property(array("Test_id" => $this->Test_id, "parent_counter" => $this->counter));
                    $is_empty = count($contents) == 0;

                    $next_not_child = $this->get_next_not_child_TestSection();
                    $next_not_child_counter = $next_not_child->counter;
                    if ($end_of_loop) {
                        $next_not_child_counter = $next_counter;
                    } else {
                        $is_end_of_loop = $this->is_end_of_loop(false);
                        if ($is_end_of_loop) {
                            $parent_loop = $this->get_section_loop();
                            if ($parent_loop != null) {
                                $next_not_child_counter = $parent_loop->counter;
                            }
                        }
                    }
                    $code = "";

                    if ($is_empty) {
                        $code.=sprintf("
                            return(%d)
                            ", $next_not_child_counter);
                        break;
                    }

                    $additional_conds = "";
                    $i = 3;
                    while (isset($vals[$i])) {
                        $additional_conds.=sprintf("%s %s %s %s", $vals[$i], $vals[$i + 1], $vals[$i + 2], $vals[$i + 3]);
                        $i+=4;
                    }

                    $code = sprintf("
                if(%s %s %s %s) {
                    return(%d)
                    }
                    else {
                    return(%d)
                    }
                    ", $vals[0], $vals[1], $vals[2], $additional_conds, $next->counter, $next_not_child_counter);
                    break;
                }
            case DS_TestSectionType::TABLE_MOD: {
                    $type = $vals[0];
                    $set_count = $vals[2];
                    $where_count = $vals[1];

                    $table = Table::from_mysql_id($vals[3]);
                    if ($table == null)
                        return sprintf("stop('Invalid table id: %s in section #%s')", $vals[3], $this->counter);

                    $set = "";
                    for ($i = 0; $i < $vals[2]; $i++) {
                        $column = TableColumn::from_property(array("Table_id" => $vals[3], "index" => $vals[4 + $i * 2]), false);
                        if ($column == null)
                            return sprintf("stop('Invalid table column index: %s of table id: %s in section #%s')", $vals[4 + $i * 2], $vals[3], $this->counter);
                        if ($i > 0)
                            $set.=",";
                        $set.=sprintf("`%s`='\",dbEscapeStrings(CONCERTO_DB_CONNECTION,toString(%s)),\"'", $column->name, $vals[4 + $i * 2 + 1]);
                    }

                    $where = "";
                    for ($i = 0; $i < $vals[1]; $i++) {
                        $j = 4 + $vals[2] * 2 + $i * 4;
                        $column = TableColumn::from_property(array("Table_id" => $vals[3], "index" => $vals[$j + 1]), false);
                        if ($column == null)
                            return sprintf("stop('Invalid table column index: %s of table id: %s in section #%s')", $vals[$j + 1], $vals[3], $this->counter);

                        if ($i > 0)
                            $where .=sprintf("%s", $vals[$j]);
                        $where.=sprintf("`%s` %s '\",dbEscapeStrings(CONCERTO_DB_CONNECTION,toString(%s)),\"'", $column->name, $vals[$j + 2], $vals[$j + 3]);
                    }

                    $sql = "";
                    if ($type == 0) {
                        $sql.=sprintf("INSERT INTO `%s` SET %s", $table->get_table_name(), $set);
                    }
                    if ($type == 1) {
                        $sql.=sprintf("UPDATE `%s` SET %s WHERE %s", $table->get_table_name(), $set, $where);
                    }
                    if ($type == 2) {
                        $sql.=sprintf("DELETE FROM `%s` WHERE %s", $table->get_table_name(), $where);
                    }

                    $code = sprintf('
                        CONCERTO_SQL <- paste("%s",sep="")
                        CONCERTO_SQL_RESULT <- dbSendQuery(CONCERTO_DB_CONNECTION,CONCERTO_SQL)
                        return(%d)
                        ', $sql, ($this->end == 0 ? $next_counter : -2));

                    break;
                }
            case DS_TestSectionType::SET_VARIABLE: {
                    $type = $vals[2];
                    $columns_count = $vals[0];
                    $conds_count = $vals[1];

                    if ($type == 0) {
                        $table = Table::from_mysql_id($vals[5]);
                        if ($table == null)
                            return sprintf("stop('Invalid table id: %s in section #%s')", $vals[5], $this->counter);

                        $column = TableColumn::from_property(array("Table_id" => $table->id, "index" => $vals[6]), false);
                        if ($column == null)
                            return sprintf("stop('Invalid table column index: %s of table id: %s in section #%s')", $vals[6], $table->id, $this->counter);

                        $sql = sprintf("SELECT `%s`", $column->name);
                        for ($i = 1; $i <= $columns_count; $i++) {
                            $column = TableColumn::from_property(array("Table_id" => $table->id, "index" => $vals[6 + $i]), false);
                            if ($column == null)
                                return sprintf("stop('Invalid table column index: %s of table id: %s in section #%s')", $vals[6 + $i], $table->id, $this->counter);

                            $sql.=sprintf(",`%s`", $column->name);
                        }
                        $sql.=sprintf(" FROM `%s` ", $table->get_table_name());

                        if ($conds_count > 0) {
                            $sql.=sprintf("WHERE ");

                            $j = 7 + $columns_count;
                            for ($i = 1; $i <= $conds_count; $i++) {
                                if ($i > 1) {
                                    $link = $vals[$j];
                                    $j++;
                                }
                                else
                                    $j++;
                                $cond_col = TableColumn::from_property(array("Table_id" => $table->id, "index" => $vals[$j]), false);
                                if ($cond_col == null)
                                    return sprintf("stop('Invalid table column index: %s of table id: %s in section #%s')", $vals[$j], $table->id, $this->counter);

                                $j++;
                                $operator = $vals[$j];
                                $j++;
                                $exp = $vals[$j];
                                $j++;

                                if ($i > 1)
                                    $sql.=sprintf("%s `%s` %s '\",dbEscapeStrings(CONCERTO_DB_CONNECTION,toString(%s)),\"' ", $link, $cond_col->name, $operator, $exp);
                                else
                                    $sql.=sprintf("`%s` %s '\",dbEscapeStrings(CONCERTO_DB_CONNECTION,toString(%s)),\"' ", $cond_col->name, $operator, $exp);
                            }
                        }

                        $code = sprintf('
                        CONCERTO_SQL <- paste("%s",sep="")
                        CONCERTO_SQL_RESULT <- dbSendQuery(CONCERTO_DB_CONNECTION,CONCERTO_SQL)
                        %s <<- fetch(CONCERTO_SQL_RESULT,n=-1)
                        return(%d)
                        ', $sql, $vals[4], ($this->end == 0 ? $next_counter : -2));
                        break;
                    }
                    if ($type == 1) {
                        $code = sprintf('
                        %s <<- {
                        %s
                        }
                        return(%d)
                        ', $vals[4], $vals[3], ($this->end == 0 ? $next_counter : -2)
                        );
                        break;
                    }
                }
        }
        return TestSection::replace_invalid_code($code);
    }

    public function get_for_initialization_variable() {
        return TestSection::build_for_initialization_variable($this->Test_id, $this->counter);
    }

    public static function build_for_initialization_variable($Test_id, $counter) {
        return "CONCERTO_FOR_Test" . $Test_id . "Section" . $counter . "_INIT";
    }

    public function get_for_index_variable() {
        return TestSection::build_for_index_variable($this->Test_id, $this->counter);
    }

    public static function build_for_index_variable($Test_id, $counter) {
        return "CONCERTO_FOR_Test" . $Test_id . "Section" . $counter . "_INDEX";
    }

    public function get_parent_loops_counters() {
        $loops = array();
        $parent_counter = $this->parent_counter;
        if ($parent_counter == 0)
            return $loops;

        while ($parent_counter != 0) {
            $parent = TestSection::from_property(array("Test_id" => $this->Test_id, "counter" => $parent_counter), false);
            if ($parent == null)
                return $loops;

            if ($parent->TestSectionType_id == DS_TestSectionType::LOOP || $parent->TestSectionType_id == DS_TestSectionType::WHILE_LOOP || $parent->TestSectionType_id == DS_TestSectionType::FOR_LOOP) {
                array_push($loops, $parent->counter);
            }

            $parent_counter = $parent->parent_counter;
        }
        return $loops;
    }

    public function get_section_loop() {
        $parent_counter = $this->parent_counter;
        if ($parent_counter == 0)
            return false;

        $is_in_loop = false;
        $loop_counter = 0;
        while ($parent_counter != 0) {
            $parent = TestSection::from_property(array("Test_id" => $this->Test_id, "counter" => $parent_counter), false);
            if ($parent == null)
                return false;

            if ($parent->TestSectionType_id == DS_TestSectionType::LOOP || $parent->TestSectionType_id == DS_TestSectionType::WHILE_LOOP || $parent->TestSectionType_id == DS_TestSectionType::FOR_LOOP) {
                $is_in_loop = true;
                $loop_counter = $parent->counter;
                break;
            }

            $parent_counter = $parent->parent_counter;
        }
        if (!$is_in_loop)
            return null;
        return TestSection::from_property(array("Test_id" => $this->Test_id, "counter" => $loop_counter), false);
    }

    public function is_end_of_loop($with_content = true) {
        $loop = $this->get_section_loop();
        if ($loop == null)
            return false;

        $last_id = 0;
        $parent_counter = $loop->counter;
        while ($parent_counter != 0) {
            $children = TestSection::from_property(array("Test_id" => $this->Test_id, "parent_counter" => $parent_counter));
            $parent_counter = 0;

            foreach ($children as $child) {
                if (!$with_content && $child->parent_counter != $this->counter || $with_content)
                    if ($last_id < $child->id) {
                        $last_id = $child->id;
                        $parent_counter = $child->counter;
                    }
            }
        }

        if ($last_id == $this->id)
            return true;
        else
            return false;
    }

    public function get_next_TestSection() {
        $sql = sprintf("SELECT * FROM `%s` WHERE `Test_id`=%d AND `id`>%d ORDER BY `id` ASC LIMIT 0,1", TestSection::get_mysql_table(), $this->Test_id, $this->id);
        $z = mysql_query($sql);
        while ($r = mysql_fetch_array($z)) {
            return TestSection::from_mysql_result($r);
        }
        return null;
    }

    public function get_next_not_child_TestSection($parent_counter = null) {
        if ($parent_counter == null)
            $parent_counter = $this->parent_counter;
        $sql = sprintf("SELECT * FROM `%s` WHERE `Test_id`=%d AND `id`>%d AND `parent_counter`=%d ORDER BY `id` ASC LIMIT 0,1", TestSection::get_mysql_table(), $this->Test_id, $this->id, $parent_counter);
        $z = mysql_query($sql);
        while ($r = mysql_fetch_array($z)) {
            return TestSection::from_mysql_result($r);
        }
        $parent = TestSection::from_property(array("Test_id" => $this->Test_id, "counter" => $parent_counter), false);
        return $this->get_next_not_child_TestSection($parent->parent_counter);
    }

    public function to_XML() {
        $xml = new DOMDocument('1.0', "UTF-8");

        $element = $xml->createElement("TestSection");
        $xml->appendChild($element);

        $counter = $xml->createElement("counter", htmlspecialchars($this->counter, ENT_QUOTES, "UTF-8"));
        $element->appendChild($counter);

        $parent = $xml->createElement("parent_counter", htmlspecialchars($this->parent_counter, ENT_QUOTES, "UTF-8"));
        $element->appendChild($parent);

        $tstid = $xml->createElement("TestSectionType_id", htmlspecialchars($this->TestSectionType_id, ENT_QUOTES, "UTF-8"));
        $element->appendChild($tstid);

        $end = $xml->createElement("end", htmlspecialchars($this->end, ENT_QUOTES, "UTF-8"));
        $element->appendChild($end);

        $tsv = $xml->createElement("TestSectionValues");
        $element->appendChild($tsv);

        $sv = TestSectionValue::from_property(array("TestSection_id" => $this->id));
        foreach ($sv as $v) {
            $elem = $v->to_XML();
            $elem = $xml->importNode($elem, true);

            $tsv->appendChild($elem);
        }

        return $element;
    }

    public static function create_db($delete = false) {
        if ($delete) {
            if (!mysql_query("DROP TABLE IF EXISTS `TestSection`;"))
                return false;
        }
        $sql = "
            CREATE TABLE IF NOT EXISTS `TestSection` (
            `id` bigint(20) NOT NULL auto_increment,
            `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
            `created` timestamp NOT NULL default '0000-00-00 00:00:00',
            `counter` int(11) NOT NULL,
            `TestSectionType_id` int(11) NOT NULL,
            `Test_id` bigint(20) NOT NULL,
            `parent_counter` int(11) NOT NULL,
            `end` tinyint(1) NOT NULL,
            PRIMARY KEY  (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
            ";
        return mysql_query($sql);
    }

}

?>