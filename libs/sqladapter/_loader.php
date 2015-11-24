<?php
/** SQL Adapter Library
 * 
 * SQL Adapter library brings sql adapters for DBMS
 */

addAutoload('SQLAdapter',					'sqladapter/SQLAdapter');
addAutoload('SQLAdapter_MySQL',				'sqladapter/SQLAdapter_MySQL');
addAutoload('SQLAdapter_MSSQL',				'sqladapter/SQLAdapter_MSSQL');
addAutoload('SQLAdapter_PgSQL',				'sqladapter/SQLAdapter_PgSQL');

addAutoload('SQLRequest',					'sqladapter/sqlrequest');
addAutoload('SQLSelectRequest',				'sqladapter/sqlselectrequest');

require_once '_pdo.php';
