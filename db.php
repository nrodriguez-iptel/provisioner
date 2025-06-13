<?php
/**
 * Conexi�n a la base de datos
 * Funciones de consulta
 * @version 1.0
 * @copyright 2007
 **/
Class DB
{
	Private $esquema; //Esquema de base de datos recibida

	//constructor
	function DB($Esquema){
		$this->esquema = $Esquema;
	}

	Private function ConnOpen()
	{ 
                $strconn="host=10.1.9.60 port=5432 dbname=provisioner user=sistemas password=M0rl4k0s!";
		$dbh = pg_connect($strconn) or die('connection failed');
		if($dbh)
                    {
                    //devuelvo la variable de conexi�n
                    return $dbh;
		}
		else
		{	exit();}
	}
	//cerrar la conexi�n
	Private function ConnClose()
	{
		pg_close ($dbh);
		if (!$dbh)
		{
                    echo "Error al cerrar la conexi�n";
		}
	}

	//funci�n que recibe por par�metro la consulta a ejecutar en Postgres
	Public function EjecutarSql($sql)
	{
		$linker = $this->ConnOpen();
		$consulta = $sql;

		$resultado = pg_query ($linker , $consulta )  or die('Error: '.$consulta);
		//echo $consulta;
		return $resultado;
		ConnClose();
	}
}
