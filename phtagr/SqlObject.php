<?php

include_once("$phtagr_lib/Base.php");
include_once("$phtagr_lib/Constants.php");

/**
  @class SqlObject Class for a database row
*/
class SqlObject extends Base
{

var $_table;
var $_data;
var $_changes;

/** Constructor of an SQL object
  @param table Table name which should be used
  @param id ID of the table row. If the ID is greater 0, the object is
  initialized by the constructor  */
function SqlObject($table, $id=-1)
{
  $this->_table=$table;
  $this->_data=array();
  $this->_changes=array();
  if ($id>0)
    $this->init_by_id($id);
}

/** Initialize the object by an ID
  @param id Id of the Sql object */
function init_by_id($id)
{
  global $db;
  if (!is_numeric($id))
    return false;

  $sql="SELECT *
        FROM ".$this->_table."
        WHERE id=$id";

  $this->init_by_query($sql);
}

/** Initialize the object via an SQL query.
  @param sql SQL query. There is no check on the query.
  @note Do not use this function until you know what you are doing. */
function init_by_query($sql)
{
  global $db;
  $result=$db->query($sql);
  if (!$result || mysql_num_rows($result)!=1)
    return false;

  $this->_data=mysql_fetch_assoc($result);
}

/** @return Returns the unique id of the sql object */
function get_id()
{
  return $this->_get_data('id', -1);
}

/** @return Returns the table name of the SQL object */
function get_table_name()
{
  return $this->_table;
}

/** 
  @param name Name of the db column
  @param default Default value, if column name not exists. Default value is
  null.
  @return If the value is not set, returns default */
function _get_data($name, $default=null)
{
  if (isset($this->_data[$name]))
    return $this->_data[$name];
  else 
    return $default;
}

/** Stores the data for the database temporary to save database accesses. After
 * all changes, the function commit must be called.
  @param name Name of the column
  @param value Value of the column. 
  @result True on success. False otherwise 
  @note The changed data updates not the internal representation
  @see commit */
function _set_data($name, $value)
{
  if ($this->get_id()<=0)
    return false;

  if ($this->_data[$name]==$value)
  {
    if (isset($this->_changes[$name]))
      unset($this->_changes[$name]);
    return true;
  }

  $this->_changes[$name]=$value;
  return true;
}

/** @return True if data were modified, false otherwise */
function is_modified()
{
  if (count($this->_changes)>0)
    return true;
  return false;
}

/** Writes all changes to the database. It also updated the internal data of
 * the object 
  @return True if changes where writen */
function commit()
{
  global $db;

  $id=$this->get_id();
  if ($id<=0)
    return false;

  if (count($this->_changes)==0)
    return false;

  $changes='';
  foreach ($this->_changes as $name => $value)
  {
    if ($value=="NULL" || $value===null)
      $svalue="NULL";
    else if ($value=="NOW()")
      $svalue=$value;
    else
      $svalue="'".mysql_escape_string(strval($value))."'";
    $changes.=",$name=$svalue";
  }
  $changes=substr($changes,1);

  $sql="UPDATE ".$this->_table."
        SET $changes
        WHERE id=$id";
  $result=$db->query($sql);
  if (!$result)
    return false;

  // Successful changes. Update to the data structure and delete changes
  foreach ($this->_changes as $name => $value)
    $this->_data[$name]=$value;
  $this->_changes=array();
  return true;
}

}
?>