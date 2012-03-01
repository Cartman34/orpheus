<?php
//! The MYSQL Mapper class
/*!
	This class is the sql mapper for MySQL.
*/
class SQLMapper_MySQL extends SQLMapper {
	
	protected static $IDFIELD = 'id';
	
	//! Defaults for selecting
	protected static $selectDefaults = array(
			'what'			=> '*',//* => All fields
			'whereclause'	=> '',//Additionnal Whereclause
			'orderby'		=> '',//Ex: Field1 ASC, Field2 DESC
			'number'		=> -1,//-1 => All
			'offset'		=> 0,//0 => The start
			'output'		=> '2',//2 => ARR_ASSOC
	);
	
	//! Defaults for updating
	protected static $updateDefaults = array(
			'lowpriority'	=> false,//false => Not low priority
			'ignore'		=> false,//false => Not ignore errors
			'whereclause'	=> '',//Additionnal Whereclause
			'orderby'		=> '',//Ex: Field1 ASC, Field2 DESC
			'number'		=> -1,//-1 => All
			'offset'		=> 0,//0 => The start
	);
	
	//! Defaults for deleting
	protected static $deleteDefaults = array(
			'lowpriority'	=> false,//false => Not low priority
			'quick'			=> false,//false => Not merge index leaves
			'ignore'		=> false,//false => Not ignore errors
			'whereclause'	=> '',//Additionnal Whereclause
			'orderby'		=> '',//Ex: Field1 ASC, Field2 DESC
			'number'		=> -1,//-1 => All
			'offset'		=> 0,//0 => The start
	);
	
	//! Defaults for inserting
	protected static $insertDefaults = array(
			'lowpriority'	=> false,//false => Not low priority
			'delayed'		=> false,//false => Not delayed
			'ignore'		=> false,//false => Not ignore errors
			'into'			=> true,//true => INSERT INTO
	);
	
	//! The function to use for SELECT queries
    /*!
		\param $options The options used to build the query.
		\return Mixed return, depending on the 'output' option.
	 	\sa http://dev.mysql.com/doc/refman/5.0/en/select.html
	 	\sa SQLMapper::select()
	 	
		Using pdo_query(), It parses the query from an array to a SELECT query.
    */
	public static function select(array $options=array()) {
		$options += self::$selectDefaults;
		if( empty($options['table']) ) {
			throw new Exception("Empty table");
		}
		if( empty($options['what']) ) {
			throw new Exception("No selection");
		}
		$WHAT = ( is_array($options['what']) ) ? implode(', ', $options['what']) : $options['what'];
		$WC = ( !empty($options['whereclause']) ) ? 'WHERE '.$options['whereclause'] : '';
		$ORDERBY = ( !empty($options['orderby']) ) ? 'ORDER BY '.$options['orderby'] : '';
		$LIMIT = ( $options['number'] > 0 ) ? 'LIMIT '.
				( ($options['offset'] > 0) ? $options['offset'].', ' : '' ).$options['number'] : '';
		
		$QUERY = "SELECT {$WHAT} FROM {$options['table']} {$WC} {$ORDERBY} {$LIMIT};";
		if( $options['output'] == static::SQLQUERY ) {
			return $QUERY;
		}
		$results = pdo_query($QUERY, ($options['output'] == static::STATEMENT) ? PDOSTMT : PDOFETCHALL );
		if( $options['output'] == static::ARR_OBJECTS ) {
			foreach($results as &$r) {
				$r = (object)$r;//stdClass
			}
		}
		return $results;
	}
	
	//! The function to use for UPDATE queries
	/*!
		\param $options The options used to build the query.
		\return The number of affected rows.
		\sa http://dev.mysql.com/doc/refman/5.0/en/update.html
		
		Using pdo_query(), It parses the query from an array to a UPDATE query.
	*/
	public static function update(array $options=array()) {
		$options += self::$updateDefaults;
		if( empty($options['table']) ) {
			throw new Exception("Empty table");
		}
		if( empty($options['what']) ) {
			throw new Exception("No field");
		}
		$OPTIONS = '';
		$OPTIONS .= (!empty($options['lowpriority'])) ? ' LOW_PRIORITY' : '';
		$OPTIONS .= (!empty($options['ignore'])) ? ' IGNORE' : '';
		$WHAT = ( is_array($options['what']) ) ? implode(', ', $options['what']) : $options['what'];
		$WC = ( !empty($options['whereclause']) ) ? 'WHERE '.$options['whereclause'] : '';
		$ORDERBY = ( !empty($options['orderby']) ) ? 'ORDER BY '.$options['orderby'] : '';
		$LIMIT = ( $options['number'] > 0 ) ? 'LIMIT '.
				( ($options['offset'] > 0) ? $options['offset'].', ' : '' ).$options['number'] : '';
	
		$QUERY = "UPDATE {$OPTIONS} {$options['table']} {$WHAT} {$WC} {$ORDERBY} {$LIMIT};";
		if( $options['output'] == static::SQLQUERY ) {
			return $QUERY;
		}
		$results = pdo_query($QUERY, ($options['output'] == static::STATEMENT) ? PDOSTMT : PDOEXEC );
	}
	
	//! The function to use for DELETE queries
	/*!
		\param $options The options used to build the query.
		\return The number of deleted rows.
	
	It parses the query from an array to a DELETE query.
	*/
	public static function delete(array $options=array()) {
		$options += self::$deleteDefaults;
		if( empty($options['table']) ) {
			throw new Exception("Empty table");
		}
		$OPTIONS = '';
		$OPTIONS .= (!empty($options['lowpriority'])) ? ' LOW_PRIORITY' : '';
		$OPTIONS .= (!empty($options['quick'])) ? ' QUICK' : '';
		$OPTIONS .= (!empty($options['ignore'])) ? ' IGNORE' : '';
		$WC = ( !empty($options['whereclause']) ) ? 'WHERE '.$options['whereclause'] : '';
		$ORDERBY = ( !empty($options['orderby']) ) ? 'ORDER BY '.$options['orderby'] : '';
		$LIMIT = ( $options['number'] > 0 ) ? 'LIMIT '.
			( ($options['offset'] > 0) ? $options['offset'].', ' : '' ).$options['number'] : '';
		
		$QUERY = "DELETE {$OPTIONS} FROM {$options['table']} {$WC} {$ORDERBY} {$LIMIT};";
		if( $options['output'] == static::SQLQUERY ) {
			return $QUERY;
		}
		$results = pdo_query($QUERY, ($options['output'] == static::STATEMENT) ? PDOSTMT : PDOEXEC );
	}
	
	//! The function to use for INSERT queries
	/*!
		\param $options The options used to build the query.
		\return The number of inserted rows.
		\todo Implement Array syntax for SET.
		\todo Implement Array syntax for VALUES.
		
		It parses the query from an array to a INSERT query.
		Accept only the String syntax for what option.
	*/
	public static function insert(array $options=array()) {
		$options += self::$insertDefaults;
		if( empty($options['table']) ) {
			throw new Exception("Empty table");
		}
		if( empty($options['what']) ) {
			throw new Exception("No field");
		}
		$OPTIONS = '';
		$OPTIONS .= (!empty($options['lowpriority'])) ? ' LOW_PRIORITY' : (!empty($options['delayed'])) ? ' DELAYED' : '';
		$OPTIONS .= (!empty($options['ignore'])) ? ' IGNORE' : '';
		$OPTIONS .= (!empty($options['into'])) ? ' INTO' : '';
		
		$COLS = '';
		//Is an array
		if( is_array($options['what']) ) {
			//Is an indexed array of fields Arrays
			if( !empty($options['what'][0]) ) {
				$COLS = '('.implode(', ', array_keys($options['what'][0])).')';
				foreach($options['what'] as $row) {
					$WHAT .= (!empty($WHAT) ? ', ' : '').'('.implode(', ', $row).')';
				}
				$WHAT = 'VALUES '.$WHAT;
			//Is associative fields Arrays
			} else {
				$WHAT = 'SET '.parseFields($options['what']);
			}
			
		//Is a string
		} else {
			$WHAT = $options['what'];
		}
		
		$QUERY = "INSERT {$OPTIONS} {$options['table']} {$COLS} {$WHAT};";
		if( $options['output'] == static::SQLQUERY ) {
			return $QUERY;
		}
		$results = pdo_query($QUERY, ($options['output'] == static::STATEMENT) ? PDOSTMT : PDOEXEC );
	}
}