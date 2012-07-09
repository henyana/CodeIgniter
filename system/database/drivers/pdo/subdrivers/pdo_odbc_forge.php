<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP 5.2.4 or newer
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the Open Software License version 3.0
 *
 * This source file is subject to the Open Software License (OSL 3.0) that is
 * bundled with this package in the files license.txt / license.rst.  It is
 * also available through the world wide web at this URL:
 * http://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to obtain it
 * through the world wide web, please send an email to
 * licensing@ellislab.com so we can send you a copy immediately.
 *
 * @package		CodeIgniter
 * @author		EllisLab Dev Team
 * @copyright	Copyright (c) 2008 - 2012, EllisLab, Inc. (http://ellislab.com/)
 * @license		http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * @link		http://codeigniter.com
 * @since		Version 2.1.0
 * @filesource
 */

/**
 * PDO ODBC Forge Class
 *
 * @category	Database
 * @author		EllisLab Dev Team
 * @link		http://codeigniter.com/database/
 */
class CI_DB_pdo_odbc_forge extends CI_DB_pdo_forge {

	/**
	 * Create Table
	 *
	 * @param	string	the table name
	 * @param	bool	should 'IF NOT EXISTS' be added to the SQL
	 * @return	mixed
	 */
	protected function _create_table($table, $if_not_exists)
	{
		if ($if_not_exists === TRUE && $this->db->table_exists($table))
		{
			return TRUE;
		}

		$sql = 'CREATE TABLE '.$this->db->escape_identifiers($table).' (';

		$current_field_count = 0;
		foreach ($this->fields as $field => $attributes)
		{
			// Numeric field names aren't allowed in databases, so if the key is
			// numeric, we know it was assigned by PHP and the developer manually
			// entered the field information, so we'll simply add it to the list
			if (is_numeric($field))
			{
				$sql .= "\n\t".$attributes;
			}
			else
			{
				$attributes = array_change_key_case($attributes, CASE_UPPER);

				$sql .= "\n\t".$this->db->escape_identifiers($field).' '.$attributes['TYPE'];

				empty($attributes['CONSTRAINT']) OR $sql .= '('.$attributes['CONSTRAINT'].')';

				if ( ! empty($attributes['UNSIGNED']) && $attributes['UNSIGNED'] === TRUE)
				{
					$sql .= ' UNSIGNED';
				}

				if (isset($attributes['DEFAULT']))
				{
					$sql .= " DEFAULT '".$attributes['DEFAULT']."'";
				}

				$sql .= ( ! empty($attributes['NULL']) && $attributes['NULL'] === TRUE)
					? ' NULL' : ' NOT NULL';

				if ( ! empty($attributes['AUTO_INCREMENT']) && $attributes['AUTO_INCREMENT'] === TRUE)
				{
					$sql .= ' AUTO_INCREMENT';
				}
			}

			// don't add a comma on the end of the last field
			if (++$current_field_count < count($this->fields))
			{
				$sql .= ',';
			}
		}

		return $sql
			.$this->_process_primary_keys()
			."\n);";
	}

	// --------------------------------------------------------------------

	/**
	 * Drop Table
	 *
	 * Generates a platform-specific DROP TABLE string
	 *
	 * @param	string	the table name
	 * @param	bool
	 * @return	mixed
	 */
	protected function _drop_table($table, $if_exists)
	{
		if ($if_exists === TRUE && ! $this->db->table_exists($table))
		{
			return TRUE;
		}

		return 'DROP TABLE '.$this->db->escape_identifiers($table);
	}

	// --------------------------------------------------------------------

	/**
	 * Alter table query
	 *
	 * Generates a platform-specific query so that a table can be altered
	 * Called by add_column(), drop_column(), and column_alter(),
	 *
	 * @param	string	the ALTER type (ADD, DROP, CHANGE)
	 * @param	string	the column name
	 * @param	string	the table name
	 * @param	string	the column definition
	 * @param	string	the default value
	 * @param	bool	should 'NOT NULL' be added
	 * @param	string	the field after which we should add the new field
	 * @return	string
	 */
	protected function _alter_table($alter_type, $table, $column_name, $column_definition = '', $default_value = '', $null = '', $after_field = '')
	{
		$sql = 'ALTER TABLE '.$this->db->escape_identifiers($table).' '.$alter_type.' '.$this->db->escape_identifiers($column_name);

		// DROP has everything it needs now.
		if ($alter_type === 'DROP')
		{
			return $sql;
		}

		return $sql.' '.$column_definition
			.($default_value != '' ? ' DEFAULT "'.$default_value.'"' : '')
			.($null === NULL ? ' NULL' : ' NOT NULL')
			.($after_field != '' ? ' AFTER '.$this->db->escape_identifiers($after_field) : '');
	}

}

/* End of file pdo_odbc_forge.php */
/* Location: ./system/database/drivers/pdo/subdrivers/pdo_odbc_forge.php */