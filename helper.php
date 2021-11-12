<?php


/**
 * Another way to create a url friendly string
 *
 * @param string $title
 * @return string
 */
if( ! function_exists('generate_slug') ) {
function generate_slug($phrase) {
  $result = strtolower($phrase);

  $result = preg_replace("/[^a-z0-9\s-]/", "", $result);
  $result = trim(preg_replace("/[\s-]+/", " ", $result));
  $result = preg_replace("/\s/", "-", $result);

  return $result;
}
}



if( ! function_exists('show_field') ) {
function show_field( $array, $key, $no_value_sign='') {
  if( isset($array[$key]) ) {
    
    if( empty( $array[$key] ) && !empty( $no_value_sign ) ) {
      echo $no_value_sign;
    
    } else {
      echo trim($array[$key]);
    }
  
  } else {
    echo '';
    
  }
}
}



if( ! function_exists('form_is_selected') ) {
function form_is_selected( $checked_value, $matched_value ) {
  if( $checked_value == $matched_value )
    echo 'selected="selected"';
}
}



if( ! function_exists('form_is_checked') ) {
function form_is_checked( $checked_value, $matched_value ) {
  if( $checked_value == $matched_value )
    echo 'checked="checked"';
}
}


/**
 * 'var_dump' a variable with tree structure, far better than var_dump
 *
 * @link http://www.php.net/manual/en/function.var-dump.php#80288
 * @param mixed $var
 * @param string $var_name
 * @param string $indent
 * @param string $reference
 */
if( ! function_exists('tree_dump') ) {
function tree_dump(&$var, $var_name = NULL, $indent = NULL, $reference = NULL)  {

    $tree_dump_indent = "<span style='color:#eeeeee;'>|</span> &nbsp;&nbsp; ";
    $reference = $reference.$var_name;
    $keyvar = 'the_tree_dump_recursion_protection_scheme'; $keyname = 'referenced_object_name';

    if (is_array($var) && isset($var[$keyvar]))
    {
        $real_var = &$var[$keyvar];
        $real_name = &$var[$keyname];
        $type = ucfirst(gettype($real_var));
        echo "$indent$var_name <span style='color:#a2a2a2'>$type</span> = <span style='color:#e87800;'>&amp;$real_name</span><br>";
    }
    else
    {
        $var = array($keyvar => $var, $keyname => $reference);
        $avar = &$var[$keyvar];

        $type = ucfirst(gettype($avar));
        if($type == "String") $type_color = "<span style='color:green'>";
        elseif($type == "Integer") $type_color = "<span style='color:red'>";
        elseif($type == "Double"){ $type_color = "<span style='color:#0099c5'>"; $type = "Float"; }
        elseif($type == "Boolean") $type_color = "<span style='color:#92008d'>";
        elseif($type == "NULL") $type_color = "<span style='color:black'>";

        if(is_array($avar))
        {
            $count = count($avar);
            echo "$indent" . ($var_name ? "$var_name => ":"") . "<span style='color:#a2a2a2'>$type ($count)</span><br>$indent(<br>";
            $keys = array_keys($avar);
            foreach($keys as $name)
            {
                $value = &$avar[$name];
                tree_dump($value, "['$name']", $indent.$tree_dump_indent, $reference);
            }
            echo "$indent)<br>";
        }
        elseif(is_object($avar))
        {
            echo "$indent$var_name <span style='color:#a2a2a2'>$type</span><br>$indent(<br>";
            foreach($avar as $name=>$value) tree_dump($value, "$name", $indent.$tree_dump_indent, $reference);
            echo "$indent)<br>";
        }
        elseif(is_int($avar)) echo "$indent$var_name = <span style='color:#a2a2a2'>$type(".strlen($avar).")</span> $type_color$avar</span><br>";
        elseif(is_string($avar)) echo "$indent$var_name = <span style='color:#a2a2a2'>$type(".strlen($avar).")</span> $type_color\"$avar\"</span><br>";
        elseif(is_float($avar)) echo "$indent$var_name = <span style='color:#a2a2a2'>$type(".strlen($avar).")</span> $type_color$avar</span><br>";
        elseif(is_bool($avar)) echo "$indent$var_name = <span style='color:#a2a2a2'>$type(".strlen($avar).")</span> $type_color".($avar == 1 ? "TRUE":"FALSE")."</span><br>";
        elseif(is_null($avar)) echo "$indent$var_name = <span style='color:#a2a2a2'>$type(".strlen($avar).")</span> {$type_color}NULL</span><br>";
        else echo "$indent$var_name = <span style='color:#a2a2a2'>$type(".strlen($avar).")</span> $avar<br>";

        $var = $var[$keyvar];
    }
}
}



if( ! function_exists('get_table_column_names') ) {
function get_table_column_names( $tablename, $return='ARRAY_N' ) {
  global $wpdb;

  $tablenames = array();
  $columns    = array();
  $tables = $wpdb->get_results("SHOW COLUMNS FROM {$tablename}");
  foreach ($tables as $table) {
    $tablenames[$table->Field] = '';
    $columns[] = $table->Field;
  }


  if( $return=='OBJECT' ) 
    return (object)$tablenames;
  
  else if ( $return=='ARRAY_A' ) 
    return $tablenames;

  else if ( $return=='ARRAY_N' ) 
   return $columns; 

}
}



/*------------------------------------------------------------------------------
          QUERY CONSTRUCTORS
------------------------------------------------------------------------------*/

/**
 * Construct an INSERT query from an array source
 *
 * @param string $tablename
 * @param array $args = array(
 *  'field1' => 'field1_value',
 *  'field2' => 'field2_value',
 *  'field3' => 'field3_value'
 *  )
 *
 * @return string
 */
if( ! function_exists('get_table_column_names') ) {
function get_table_column_names($tablename, $args) {
  global $wpdb;

  /*$table_columns = get_table_column_names( $tablename, 'ARRAY_A' );
  
  $args = array_merge($table_columns, $args);*/
  
  $fields = array_keys($args);
  
  // escape user input
  foreach ($args as $key => $arg) {
    $args[$key] = esc_sql($arg);
  }
  
  $fields = '' .implode(",", $fields). '';
  $values = "'".implode("', '", $args)."'";

  $query = "INSERT INTO ". $tablename;
  $query .= " (". $fields.")";
  $query .= " VALUES (". $values.")";

  return $query;

}
}


/**
 * Construct a REPLACE query from an array source
 *
 * @param string $tablename
 * @param array $args = array(
 *  'field1' => 'field1_value',
 *  'field2' => 'field2_value',
 *  'field3' => 'field3_value'
 *  )
 *
 * @return string
 */
if( ! function_exists('construct_query_replace') ) {
function construct_query_replace($tablename, $args) {
    $tablename = "".$tablename."";

    $fields = array_keys($args);
    $fields = '' .implode(",", $fields). '';
    $values = "'".implode("', '", $args)."'";

    $query = "REPLACE ". $tablename;
    $query .= " (". $fields.")";
    $query .= " VALUES (". $values.")";
    return $query;

}
}


/**
 * Construct an UPDATE query from an array source
 *
 * @param string $tablename
 * @param mixed $sets
 * @param string $wheres
 * @return string
 */
if( ! function_exists('construct_query_update') ) {
function construct_query_update($tablename, $sets, $wheres) {

  $set_string = set_imploder($sets);
  $query = "UPDATE ". $tablename;
  $query .= " SET ". $set_string;
  $query .= " WHERE ". $wheres;

  return $query;

}
}


/**
 * Construct a DELETE query to be queried
 *
 * @param string $tablename
 * @param string $wheres
 * @return string
 */
if( ! function_exists('construct_query_delete') ) {
function construct_query_delete($tablename, $wheres) {

  $query_delete = "DELETE FROM ".$tablename." WHERE ".$wheres;

  return $query_delete;

}
}


/**
 * Construct the WHERE clause in an UPDATE or DELETE query statement
 *
 * @param mixed
 * can be $args = array(
 *  'field1' => 'value1',
 *  'field2' => 'value2',
 *  'field3' => 'value3'
 *  )
 *
 * or only $string
 * @param string $mode Whether OR or AND
 * @param string $operand Whether =, <, >
 *
 * @return string
 */
if( ! function_exists('where_imploder') ) {
function where_imploder($args, $mode = "AND", $operand = "=") {

  if (!is_array($args)) {

    return $args;

  } else {

    $wheres = array();
    foreach($args as $key => $value) {
        $value = sanitize_string($value);
        $wheres[] = "$key $operand '$value'";
    }
    if($mode == "AND") return implode(" AND ", $wheres);
    elseif($mode == "OR") return implode(" OR ", $wheres);

  }
}
}


/**
 * Construct the SET clause in an UPDATE query statement
 * @return string
 */
if( ! function_exists('set_imploder') ) {
function set_imploder($args) {
  
  global $wpdb;

  if (!is_array($args)) {

  return $args;

  } else {

  $sets = array();
  foreach($args as $key => $value) {
      $sets[] = $key . " = '" . esc_sql($value) . "'";
  }
  return implode(",", $sets);

  }

}
}