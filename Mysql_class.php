<?php
/**
 * MySQL Query Class
 *
 * This is global query class to build and execute sql statement as well as get table data.
 *
 * @package	Fellowshipv2
 * @subpackage	Admin
 * @category	Shared Functions
 * @version     Version 2.0
 * @author	Damber Prasad Gautam
 * @copyright	Copyright (c) 2016, APNIC.
 * @link	https://www.apnic.net/
 */
class Mysql_class{
    /**
     * Define class variables
     */

    //Settings
    public $FS_CONFIG ="";
    public $FS_SALT ="";
    private $_DBPRIFIX = "";
    private $_FS_ENV_PRODUCTION="";


    //Mysql Connection link
    protected $_link = "";

    //table name
    protected $_table = "";

    //where condition
    protected $_where = "";

    //fields name
    protected $_fields = "*";

    //set order
    protected $_order = "";

    //set join string
    public $join = "";

    //limit
    public $limit = "";




    /**
     * ---------------
     * CONSTRUCTOR
     * ---------------
     * @param array $config Config array
     * @param string $db_prifix Database connection variable's prefix
     */
    public function __construct($config, $db_prifix){
        //get common properties
        $this->FS_CONFIG["URL"] = $config["URL"];
        $this->FS_CONFIG["DEF"] = $config["DEFINE"];
        $this->FS_SALT = $config["DEFINE"]["VAR_SALT"];
        $this->_FS_ENV_PRODUCTION = $config["ENVIRONMENT"]["PRODUCTION"];

        //connect database and set chareset
        $db = $config['DB'];
        $this->_DBPRIFIX = $db_prifix;
        $this->_link = mysqli_connect($db[$this->_DBPRIFIX."HOST_NAME"], $db[$this->_DBPRIFIX."DB_USER"], $db[$this->_DBPRIFIX."DB_PSWD"], $db[$this->_DBPRIFIX."DB_NAME"]);
        if(! $this->_link || ! mysqli_set_charset($this->_link, 'UTF8')){ $this->_handle_mysql_error("Please contact web administrator. ", mysqli_error($this->_link)); }
    }


    /**
     * ========================
     *  SETTER Functions
     * ========================
     */
    //set table name
    public function from($table){ $this->_table = $table; }

    //select fields
    public function select($fields){ $this->_fields = $fields; }

    //set limit
    public function limit($limit){  $this->limit = " LIMIT ".$limit; }

    //set orderby
    public function order_by($order_field, $order_type=""){  $this->_order = " ORDER BY ".$order_field.' '.$order_type; }

    //set where
    public function where($where){ $this->_where = (is_array($where))? " WHERE ".$this->_array_to_string_and($where) : " WHERE ".$where; }

    //append custom where query
    public function query_where($query){  $this->_where .= $query; }

    //set or where
    public function or_where($cond, $value, $operation=""){  $this->_where .= ($operation == "")? " OR ".$cond." = '".$value."'" : " OR ".$cond. $operation ."'".$value."'" ; }

    //join tables
    public function join_table($join_table, $join_fields, $join_type = "INNER JOIN"){ $this->join = $this->join. " " . $join_type. " " . $join_table ." ON ". $join_fields." "; }

    //set get_where_* function parameters
    protected function _set_get_where($table, $where, $limit, $offset){ $this->_set_limit($limit, $offset); $this->_where = " WHERE ".$this->_array_to_string_and($where); $this->_table = $table; }

    //set get* function parameters
    protected function _set_get($table, $limit, $offset){ $this->_set_limit($limit, $offset); $this->_table = ($table != "")? $table : $this->_table; }

    //Return sql dump query 
    public function dump_sql(){ return $this->_select_query_builder(); }


    /**
     *=======================================
     *  PUBLIC FUNCTION: SELECT DATA
     *=======================================
     */

    /**
     * @brief Get table data result
     * @param string $table
     * @param int $limit
     * @param int $offset
     * @return SQLResouce Return sql resource result
     */
    public function get($table="", $limit ="", $offset =""){
        //Set parameter
        $this->_set_get($table, $limit, $offset);

        //Sql Query
        $query = $this->_select_query_builder();

        //query run result
        return $this->_execute($query);
    }


    /**
     * @brief Get table data single array
     * @param string $table
     * @param int $limit
     * @param int $offset
     * @return array Return single array
     */
    public function get_row($table="", $limit ="", $offset =""){
        //Set parameter
        $this->_set_get($table, $limit, $offset);

        //Sql Query
        $query = $this->_select_query_builder();

        //query run result
        $result = $this->_execute($query);

        return $this->result_row($result);
    }


    /**
     * @brief Get all matching table rows in multi-dimensional array
     * @param string $table
     * @param int $limit
     * @param int $offset
     * @return array Return multi-dimensional array
     */
    public function get_array($table="", $limit ="", $offset =""){
        //Set parameter
        $this->_set_get($table, $limit, $offset);

        //Sql Query
        $query = $this->_select_query_builder();

        //query run result
        $result = $this->_execute($query);

        return $this->result_array($result);
    }



    /**
     * @brief Get table data using where condition
     * @param string $table
     * @param array $where   example array('name !=' => $name, 'id <' => $id, 'date >' => $date); or array('id' => $id);
     * @param int $limit
     * @param int $offset
     * @return SQLResouce Return sql resource result
     */
    public function get_where($table, $where , $limit="", $offset=""){
        //set get_where parameters
        $this->_set_get_where($table, $where, $limit, $offset);

        //Sql Query
        $query = $this->_select_query_builder();

        //query run result
        return $this->_execute($query);
    }


    /**
     * @brief Get single row data using where condition in array
     * @param string $table
     * @param array $where   example array('name !=' => $name, 'id <' => $id, 'date >' => $date); or array('id' => $id);
     * @param int $limit
     * @param int $offset
     * @return array Return single row in an array
     */
    public function get_where_row($table, $where , $limit="", $offset=""){
        //set get_where parameters
        $this->_set_get_where($table, $where, $limit, $offset);

        //Sql Query
        $query = $this->_select_query_builder();

        //query run result
        $result = $this->_execute($query);

        //return array
        return $this->result_row($result);
    }


    /**
     * @brief Get all matching table rows in multi-dimensional array using where condition
     * @param string $table
     * @param array $where   example array('name !=' => $name, 'id <' => $id, 'date >' => $date); or array('id' => $id);
     * @param int $limit
     * @param int $offset
     * @return array Return multi-dimensional array
     */
    public function get_where_array($table, $where , $limit="", $offset=""){
        //set get_where parameters
        $this->_set_get_where($table, $where, $limit, $offset);

        //Sql Query
        $query = $this->_select_query_builder();

        //query run result
        $result = $this->_execute($query);

        //return array
        return $this->result_array($result);
    }



    /**
     * @brief It execute direct query statement and return SQLResource object
     * @param String $sql
     * @return SQLResouce
     */
    public function query($sql, $is_insert = FALSE){ return $this->_execute($sql, $is_insert); }


    /**
     * @brief Count table rows
     * @param string $table
     * @param array $where array('name !=' => $name, 'id <' => $id, 'date >' => $date); or array('id' => $id);
     * @return int
     */
    public function count($table ="", $where=""){
        //set where
        $this->_where = ($where !="") ?  " WHERE ".$this->_array_to_string_and($where) : $this->_where;

        //set table
        $this->_table = ($table !="") ? $table: $this->_table;

        //Sql Query
        $query = $this->_select_query_builder();

        //query run result
        $result = $this->_execute($query);

        return $this->num_rows($result);
    }



    /**
     * @brief Count table rows
     * @param SQLResouce $result
     * @return int
     */
    public function num_rows($result){ return $result->num_rows; }



    /**
     * @brief Retrive all rows in an array
     * @param SQLResouce $result
     * @return array
     */
    public function result_array($result){
        $output = array();
        //Collect all rows in multi-dimensional array
        if (!empty($result) && $result->num_rows > 0 ) { while($row = $result->fetch_assoc()) { $output[] = $row; } }
        return $output;
    }


    /**
     * @brief Retrive single row in an array
     * @param SQLResource $result
     * @return array
     */
    public function result_row($result){ return (!empty($result) && $result->num_rows > 0 ) ? $result->fetch_assoc() : array(); }





    /**
     * =======================
     *  UPDATE
     * =======================
     */
    /**
     * @brief Update table data
     * @param string $table
     * @param array $setValues
     * @param array $where
     */
    public function update($table, $setValues, $where=""){

        //set where
        $this->_where = ($where !="") ?  " WHERE ".$this->_array_to_string_and($where) : $this->_where;

        //Set limit
        $this->limit = ($this->limit !="")? $this->limit : " LIMIT 1";

        //generate SET field and value as string
        $holder_array = array();
        foreach($setValues as $field => $value) { $holder_array[] = ($value == NULL || $value == 'NULL') ? $field. "= NULL" : $field. "= '".$value."'"; }
        $set_value_as_string = implode(", ", $holder_array);

        //Build query
        $queryString = "UPDATE ". $table . " SET ". $set_value_as_string . $this->_where . $this->limit;

        //Execute query
        return $this->_execute($queryString);
    }




    /**
     * =====================
     *  INSERT
     * =====================
     */

    /**
     * @brief Insert record in table
     * @param String $table
     * @param array $data
     * @param Boolean $last_id
     * @return SQLResource
     */
    public function insert($table, $data, $last_id = FALSE){
        //Fetch fileds
        $fieldValueString = "( ".implode(", ", array_keys($data))." ) VALUES ( '".implode("', '", array_values($data))."' )";

        //Build query
        $queryString = "INSERT INTO $table $fieldValueString";

        //Execute query
        return $this->_execute($queryString, $last_id);
    }




    /**
     * ====================
     *  DELETE
     * ====================
     */

    /**
     * @brief Delete mysql table row(s)
     * @param String $table
     * @param array $where_array
     * @param int $del_limit
     * @return SQLResource
     */
    public function delete($table, $where_array, $del_limit=1){
        //Set limit
        $limit = ($del_limit >0 )? " LIMIT ".$del_limit : " LIMIT 1";

        //Set condition
        $where = " WHERE ".$this->_array_to_string_and($where_array);

        //Build query
        $queryString = "DELETE FROM ".$table." ". $where . $limit;

        //Execute query
        return $this->_execute($queryString);
    }




    /**
     * ===============================
     *  Other Public MySQL Functions
     * ===============================
     */

    /**
     * @brief Clean mysql single field value
     * @param string $val
     * @return string
     */
    public function mysql_clean_value($val, $strip_tag = TRUE, $trim = TRUE){
        if($strip_tag == TRUE && $trim == TRUE){ return strip_tags(mysqli_real_escape_string($this->_link, trim($val)));
        }else if ($strip_tag == FALSE && $trim == TRUE){ return (mysqli_real_escape_string($this->_link, trim($val)));
        }else if ($strip_tag == TRUE && $trim == FALSE){ return strip_tags(mysqli_real_escape_string($this->_link, $val));
        }else{ return mysqli_real_escape_string($this->_link, $val); }
        return $val;
    }


    /**
     * @brief Clean mysql array field values and return new array
     * @param array $array Raw array
     * @return array Clean array
     */
    public function mysql_clean_array($array, $strip_tag = TRUE, $trim = TRUE){
        $output = array();
        foreach ($array as $key => $val){
            if($strip_tag == TRUE && $trim == TRUE){ $output[$key] = strip_tags(mysqli_real_escape_string($this->_link, trim($val)));
            }else if ($strip_tag == FALSE && $trim == TRUE){ $output[$key] = (mysqli_real_escape_string($this->_link, trim($val)));
            }else if ($strip_tag == TRUE && $trim == FALSE){ $output[$key] = strip_tags(mysqli_real_escape_string($this->_link, $val));
            }else{ $output[$key] = mysqli_real_escape_string($this->_link, $val); }
        }
        return $output;
    }



    /**
     * =============================
     *  PROTECTED SYSTEM FUNCTIONS
     * =============================
     */

    /**
     * @brief Build query
     * @return String
     */
    protected function _select_query_builder(){ return trim("SELECT $this->_fields FROM $this->_table $this->join $this->_where $this->_order $this->limit"); }


    /**
     * @brief Execute query string
     * @param String $query
     * @param BOOLEAN $is_insert optional value
     * @return SQLResource
     */
    protected function _execute($query, $is_insert = FALSE){

        if($is_insert === TRUE){
           if(! $this->_link->query($query)){ $this->_handle_mysql_error($query, mysqli_error($this->_link)); }
           $res = $this->_link->insert_id;
           $this->_reset_all();

       }else{
           $res = $this->_link->query($query);
           if(! $res){ $this->_handle_mysql_error($query, mysqli_error($this->_link)); }
           $this->_reset_all();
       }
       return $res;
    }


    /**
     * @brief array to string converter using AND seperator
     * @param type $array array('name !=' => $name, 'id <' => $id, 'date >' => $date); or array('id' => $id);
     * @return type
     */
    protected function _array_to_string_and($array){
        //Fetch where condition in a string
        if(! is_array($array) || count($array) < 1){ return; }

        $string_array = array();
        foreach($array as $key => $value) {

            switch ($key) {
                case (strpos($key, '<') || strpos($key, '>') || strpos($key, '<=') || strpos($key, '>=')): $quote = true; $op = ''; break;

                case (strpos($key, 'NOT IN') || strpos($key, 'IN')): $quote = false; $op = ''; break;

                default: $quote = true; $op = '='; break;
            }

            $string_val = $this->_set_quote_value($value, $quote);
            $string_array[] = ($value == NULL || $value == 'NULL')? $key." IS NULL" : $key.$op . $string_val;
        }
        $string = implode(" and ", $string_array);
        return $string;
    }

    //set string with quote or without quote
    protected function _set_quote_value($value, $quote = true){ return ($quote === true) ? "'".$value."'" : $value; }


    /**
     * @brief Set limit for query if requested
     */
    protected function _set_limit($limit, $offset=""){ $this->limit = (!empty($limit) && $offset != "" && $offset > 0)? " LIMIT $offset, $limit " : (!empty($limit)? " LIMIT $limit " : $this->limit); }


    /**
     * @brief Display database error according to system environment.
     * @param type $query
     */
    private function _handle_mysql_error($query, $mysql_error){
        //CSS fro script
        $style_script = "style='color:#0086b3;background-color:#FFF; padding:5px; font-family:Consolas, Menlo, Courier, monospace; font-size: 13px; line-height:30px;'";
        $style_mysqli_err = "style='background-color:#FFF; padding:5px; font-size: 13px; line-height:30px;'";

        //message
        $msg = ($this->_FS_ENV_PRODUCTION == "ON")? " <h2>Database Error!!!</h2> Please contact web administrator. " : "<h2>Query Execution Failed!!!</h2><p ".$style_script."> ".$query."</p>" ;

        $mysqli_error = ($this->_FS_ENV_PRODUCTION == "OFF")? "<br /><p ".$style_mysqli_err." >".$mysql_error."</p>": "";

        //Output
        $url = 'url("../../images/logo.png")';
        echo "<!DOCTYPE html>
                <html>
                    <head><title>Fatal Error!! | ".$this->FS_CONFIG["DEF"]["SITE_NAME"]."</title></head>
                    <body>
                    <div>&nbsp;</div><div style='background: $url no-repeat scroll center top rgba(0, 0, 0, 0); height: 100px; width: 100%;'></div>
                    <h1 style='text-align:center;'>".$this->FS_CONFIG["DEF"]["SITE_NAME"]."</h1><div style= 'width: 80%; margin: 10px auto 0px; padding: 30px; color: #D8000C; background-color: #FFBABA; border-radius: 10px;' >
                    ". $msg .$mysqli_error."</div></body>
                </html>"; die();
    }


    /**
     * @brief UNSET VARIABLES
     */
    protected function _reset_all(){ $this->_fields ="*"; $this->_table =""; $this->join = ""; $this->_where =""; $this->_order =""; $this->limit =""; }
}
