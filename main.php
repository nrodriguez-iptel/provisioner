<?php
///obtenemos las operaciones de la cola de operaciones
//20250612 agregamos un control de operaciones que tengan el mismo xml_string para no repetir la operacion a procesar

include_once ("db.php");
include_once ("cnn.php");
include_once ("ifftth_operaciones.php");
include_once ("ifhfc_operaciones.php"); //operaciones de hfc ispfull 20250106


try {
    
    $_esquema="provisioner_inet";
    //$_esquema=$postgres_esquema;
    
    //Istancio la clase db para acceder a los metodos
    $odb = new DB($_esquema);
    //consulta
    $version="20250613";
    logger("Inicio main $version...");
    echo "\n Inicio main $version...".date("h:i:sa");
    $sQuery = "SELECT id, type, status, insert_date, execution_date, xml_string::xml, username, 
       response, id_customer, customer_item, location, id_orden, userid, 
       contrato "
            . "FROM ".$_esquema.".operations_queue where not id is null AND status IN ('INIC') order by cast(id as integer)";
    //nr20230510 agregamos que tome las operaciones en estado SEND para que vuelva a reaprovisionar por un error que devuelve la api.
    $result=$odb->EjecutarSql($sQuery);
    $reg_count= pg_num_rows ($result);
    $tenencia=2; //nr 20250218 definimos el valor de la tenencia para FD3 igual a 2    
    logger("Nro. OPs: ".$reg_count);
    //Recorro los datos consultados
    while($row = pg_fetch_array($result))
    {
        echo "\n Id de operacion: ".$row['id'];
        echo "\n Estado: ".$row['status'];
        echo "\n Tipo: ".$row['type'];
        echo "\n xml_string: ".$row['xml_string'];
        echo "\n";
        logger("json_xml_string: ".$row['xml_string']);
        
        $resultado_operacion="";
        $operacionid=$row['id']; //id de operacion
        $tipo=$row['type']; //tipo de operacion
     
        ////json to array
        $json_string=json_decode($row['xml_string'], true);
        
        
       if ($tipo=="ATCON" || $tipo=="BACON" || $tipo=="SUCON" || $tipo=="HACON" || $tipo=="STSON" 
                || $tipo=="AUTON" || $tipo=="ATCIF" || $tipo=="BACIF" || $tipo=="SUCIF" || $tipo=="HACIF" || $tipo=="ATLON" || $tipo=="BALON" || $tipo=="UPHIF" || $tipo=="HAMIF" || $tipo=="UPFON" || $tipo=="STMIF" || $tipo=="STCIF" ) 
            {
        
            logger("Ingreso por operaciones de ISPFULL ".$tipo );
            //20250609 solo aplicable en FTTH para los ONU
            if($tipo=="ATCON" || $tipo=="BACON" || $tipo=="STSON" || $tipo=="ATLON" || $tipo=="BALON" || $tipo=="AUTON" || $tipo=="UPFON"){
                logger("Convirtiendo serial para el tipo de operacion: ".$tipo);
                $_fsan=convertirSerialEquipo($json_string['fsan']); //20240510 llamamos a la funcion para verificar si cambia la mac
            }else{
                
                 if (isset($json_string['fsan']))
                    {
                    $_fsan=$json_string['fsan'];
                    logger("valor fsan encontrado: ".$_fsan);
                    }
            }
            
            ///////////VALIDAMOS LOS VALORES SI ESTÁN DEFINIDOS PARA CADA OPERACION
            if (isset($json_string['client'])){
                $_client=$json_string['client'];
            }
            if (isset($json_string['model'])) {
                //echo '$var está definida a pesar que está vacía';
                $_model=$json_string['model'];
            }
            if (isset($json_string['name'])){
                $_clientname=$json_string['name'];
            }
            if (isset($json_string['address'])){
                $_address=$json_string['address'];
            }
            if (isset($json_string['Tel'])){
                $_Tel=$json_string['Tel'];
            }
            //20241223 reemplazamos la coma por un punto en la longitud y latitud 
            if (isset($json_string['Lat'])){
                $_Lat=str_replace(",",".",$json_string['Lat']);
            }
            if (isset($json_string['Lon'])){
                $_Lon=str_replace(",",".",$json_string['Lon']);
            }
            if (isset($json_string['Dni'])){
                $_Dni=$json_string['Dni'];
            }
            if (isset($json_string['service'])){
                $_service=$json_string['service'];
            }
            if (isset($json_string['modelo'])){
                $_cmModel=$json_string['modelo'];
            }
            if (isset($json_string['cmts'])){
                $_cmts=$json_string['cmts'];
            }
            ////////////
            //20250124telefonia
            ///////////////////
            if (isset($json_string['did'])){
                $_did=$json_string['did'];
            }
            if (isset($json_string['limit'])){
                $_limit=$json_string['limit'];
            }
            if (isset($json_string['registry'])){
                $_registry=$json_string['registry'];
            }
            if (isset($json_string['reg_server_url'])){
                $_reg_server_url=$json_string['reg_server_url'];
            }
            if (isset($json_string['reg_server_dom'])){
                $_reg_server_dom=$json_string['reg_server_dom'];
            }
            if (isset($json_string['tel_mode'])){
                $_tel_mode=$json_string['tel_mode'];
            }
            if (isset($json_string['tel_mode'])){
                $_tel_mode=$json_string['tel_mode'];
            }
            if (isset($json_string['regusername'])){
                $_regusername=$json_string['regusername'];
            }
            if (isset($json_string['regpass'])){
                $_regpass=$json_string['regpass'];
            }
            if (isset($json_string['hit_server'])){
                $_hit_server=$json_string['hit_server'];
            }
            if (isset($json_string['dialplan'])){
                $_dialplan=$json_string['dialplan'];
            }
            if (isset($json_string['codec'])){
                $_codec=$json_string['codec'];
            }
            if (isset($json_string['voip'])){
                $_voip=$json_string['voip'];
            }
            if (isset($json_string['id_profile_wan'])){
                $_id_profile_wan=$json_string['id_profile_wan'];
            }
            if (isset($json_string['id_lucent'])){
                $_id_lucentd=$json_string['id_lucent'];
            }
            if (isset($json_string['siplar_vz'])){
                $_siplar_vz=$json_string['siplar_vz'];
            }
        }
        ////////////////////
        //PONEMOS LA OPERACION EN ESTADO SENDING PARA SABER QUE LA ESTAMOS PROCESANDO
        //////////////////////////////////////////////////////////////////////////////////
        actualizar_operacion_cola($operacionid,$resultado_operacion, "SEND"); 
        switch ($tipo)
         {
            case "ATCON":    
                            //ALTA ONU fsan 20240318
                            logger("Iniciando  alta FSAN en Ispfull");

                            // 1. Realizamos la alta usando la función genérica
                            list($_estado, $resultado_operacion) = procesarOperacionIspfull(
                                'alta fsan en Ispfull',
                                'ont_alta',
                                [$_fsan, $_clientname, $_address,$_Tel,$_Lat,$_Lon,$_Dni,$_service, $_client],
                                'DONE',
                                'ERRO',
                                'Operación exitosa',
                                'Error desconocido en la alta de fsan'
                            );

                            // 2. Solo si fue exitosa la alta, verificamos si el cliente está desconectado
                            if ($_estado === 'DONE') {
                                $estado_cliente = client_status($_client);
                                $cliente_desconectado = strtolower(trim($estado_cliente['cliente_desconectado'] ?? 'false'));

                                logger("Estado cliente_desconectado ftth: $cliente_desconectado");

                                if ($cliente_desconectado === 'true') {
                                    logger("Habilitando cliente desconectado dentro de alta ftth");

                                    $rehab = habilitar_cliente_ispfull($_client);
                                    $rehab_result_id = $rehab['result_id'] ?? -1;
                                    $rehab_result_msg = $rehab['result'] ?? '';
                                    $rehab_error = $rehab['error'] ?? 'Error desconocido';

                                    if ($rehab_result_id == 0) {
                                        logger("Resultado habilitación dentro de alta: $rehab_result_msg");
                                    } else {
                                        logger("Error al habilitar cliente dentro de alta - ID: $rehab_result_id - Descripción: $rehab_error");
                                    }
                                }
                            }
                            logger("finalizando  alta FSAN en Ispfull");
                             break;  
                case "BACON":    
                    //BAJA FSAN  20240318
                            ///////////////////////////////////////////////
                                    //20250612 si viene sin mac/onu proceso el error sin llamar a la api ispfull
                            //////////////////////////////
                            if (empty($_fsan)){
                                $resultado_operacion_fsan['result_id']=-112;
                                $resultado_operacion_fsan['error']='Falta FSAN';
                                $resultado_operacion=$resultado_operacion_fsan['result_id']."|".$resultado_operacion_fsan['error'];
                                 // error ispfull -12|Invalid MAC
                                
                            }else{
                                //$resultado_operacion_fsan= ont_baja($_fsan);
                                 list($_estado, $resultado_operacion) = procesarOperacionIspfull(
                                'baja fsan en ispfull',
                                'ont_baja',
                                [$_client],
                                'DONE',
                                'ERRO',
                                'Operación exitosa - ',
                                'Error desconocido en la baja de la fsan'
                            );
                            
                            } 
                            break;  
                case "SUCON":    
                    //SUSPENSION FSAN  20241218
                            
                            //$resultado_operacion_fsan= suspender_cliente_ispfull($_client);
                            list($_estado, $resultado_operacion) = procesarOperacionIspfull(
                                'suspender cliente en ispfull',
                                'suspender_cliente_ispfull',
                                [$_client],
                                'DONE',
                                'ERRO',
                                'Operación exitosa - ',
                                'Error desconocido en la suspension del cliente'
                            );
                            break; 
                case "HACON":    
                    //habilitacion de Cliente 20241218
                            
                            ///PRIMERO AUTORIZAMOS LA FSAN
                                //$resultado_operacion_fsan= habilitar_cliente_ispfull($_client);
                            list($_estado, $resultado_operacion) = procesarOperacionIspfull(
                                'habilitar cliente en ispfull',
                                'habilitar_cliente_ispfull',
                                [$_client],
                                'DONE',
                                'ERRO',
                                'Operación exitosa - ',
                                'Error desconocido en la habilitacion del cliente'
                            );
                            break;
                case "STSON":    
                    //estado FSAN  20240418
                            
                            //$resultado_operacion_fsan= ont_status($_fsan);
                             list($_estado, $resultado_operacion) = procesarOperacionIspfull(
                                'estado FSAN en ispfull',
                                'ont_status',
                                [$_fsan],
                                'DONE',
                                'ERRO',
                                'Operación exitosa - ',
                                'Error desconocido en el estado de FSAN'
                            );
                            break;
                case "AUTON":    
                    //autorizacion FSAN  20240418
                           
                            //$resultado_operacion_fsan= ont_autorizacion($_fsan, $_client, $_model);
                             list($_estado, $resultado_operacion) = procesarOperacionIspfull(
                                'autorizacion FSAN en ispfull',
                                'ont_autorizacion',
                                [$_fsan, $_client, $_model],
                                'DONE',
                                'ERRO',
                                'Operación exitosa - ',
                                'Error desconocido en la autorizacion FSAN'
                            );
                            break;
                case "ATCIF":
                            logger("Iniciando alta de cliente/módem HFC en Ispfull");

                             if (empty($_fsan)){
                                $resultado_operacion_fsan['result_id']=-112;
                                $resultado_operacion_fsan['error']='Falta FSAN';
                                $resultado_operacion=$resultado_operacion_fsan['result_id']."|".$resultado_operacion_fsan['error'];
                                 // error ispfull -12|Invalid MAC
                                $_estado='ERRO';
                            }else{
                                // 1. Realizamos la alta usando la función genérica
                                list($_estado, $resultado_operacion) = procesarOperacionIspfull(
                                    'alta de módem HFC en Ispfull',
                                    'cm_alta',
                                    [$_fsan, $_cmModel, $_cmts, $_clientname, $_address, $_Tel, $_Lat, $_Lon, $_Dni, $_service, $_client],
                                    'DONE',
                                    'ERRO',
                                    'Operación exitosa',
                                    'Error desconocido en la alta del CM'
                                );
                            }

                            // 2. Solo si fue exitosa la alta, verificamos si el cliente está desconectado
                            if ($_estado === 'DONE') {
                                $estado_cliente = client_status($_client);
                                $cliente_desconectado = strtolower(trim($estado_cliente['cliente_desconectado'] ?? 'false'));

                                logger("Estado cliente_desconectado HFC: $cliente_desconectado");

                                if ($cliente_desconectado === 'true') {
                                    logger("Habilitando cliente desconectado dentro de alta");

                                    $rehab = habilitar_cliente_hfc_ispfull($_client);
                                    $rehab_result_id = $rehab['result_id'] ?? -1;
                                    $rehab_result_msg = $rehab['result'] ?? '';
                                    $rehab_error = $rehab['error'] ?? 'Error desconocido';

                                    if ($rehab_result_id == 0) {
                                        logger("Resultado habilitación dentro de alta: $rehab_result_msg");
                                    } else {
                                        logger("Error al habilitar cliente dentro de alta - ID: $rehab_result_id - Descripción: $rehab_error");
                                    }
                                }
                            }

                            logger("Finalizando alta de módem HFC en Ispfull");
                            break;

                case "BACIF":    
                    //baja MODEM HFC ISPFULL  20250109
                    ///////////////////////////////////////////////
                            //20250612 si viene sin mac/onu proceso el error sin llamar a la api ispfull
                    //////////////////////////////
                            if (empty($_fsan)){
                                $resultado_operacion_fsan['result_id']=-112;
                                $resultado_operacion_fsan['error']='Falta MAC';
                                $resultado_operacion=$resultado_operacion_fsan['result_id']."|".$resultado_operacion_fsan['error'];
                                 // error ispfull -12|Invalid MAC
                            }else{
                                //$resultado_operacion_fsan= cm_baja($_fsan);
                                list($_estado, $resultado_operacion) = procesarOperacionIspfull(
                                'baja modem hfc en ispfull',
                                'cm_baja',
                                [$_fsan],
                                'DONE',
                                'ERRO',
                                'Operación exitosa - ',
                                'Error desconocido en la baja modem hfc'
                            );
                                }  
                     break;  
                case "SUCIF":    
                    //suspension cliente HFC ISPFULL  20250109
                            
                            //$resultado_operacion_fsan= suspender_cliente_hfc_ispfull($_client);
                             list($_estado, $resultado_operacion) = procesarOperacionIspfull(
                                'suspension cliente hfc en ispfull',
                                'suspender_cliente_hfc_ispfull',
                                [$_client],
                                'DONE',
                                'ERRO',
                                'Operación exitosa - ',
                                'Error desconocido en la suspension cliente hfc'
                            );
                            break;
                case "HACIF":    
                    //suspension cliente HFC ISPFULL  20250109
                           
                            //$resultado_operacion_fsan= habilitar_cliente_hfc_ispfull($_client);
                              list($_estado, $resultado_operacion) = procesarOperacionIspfull(
                                'habilitacion cliente hfc en ispfull',
                                'habilitar_cliente_hfc_ispfull',
                                [$_client],
                                'DONE',
                                'ERRO',
                                'Operación exitosa - ',
                                'Error desconocido en la habilitacion cliente hfc'
                            );
                            break;
                case "ATLON":    
                    //ALATA LINEA EN FTTH 20250124
                               list($_estado, $resultado_operacion) = procesarOperacionIspfull(
                                'alta linea FTTH en Ispfull',
                                'asignar_telefonia',
                                [$_fsan, $_did, $_voip, $_regusername, $_regpass],
                                'DONE',
                                'ERRO',
                                'Operación exitosa - ',
                                'Error desconocido en la alta linea FTTH'
                            );
                            break;
                case "BALON":    
                    //ALATA LINEA EN FTTH 20250124
                                 list($_estado, $resultado_operacion) = procesarOperacionIspfull(
                                'baja linea FTTH en Ispfull',
                                'bajar_telefonia',
                                [$_fsan, $_did],
                                'DONE',
                                'ERRO',
                                'Operación exitosa - ',
                                'Error desconocido en la baja de la linea en FTTH'
                            );
                            break;
                case "UPHIF":    
                          //UPGRADE DE VELOCIDAD HFC ISPFULL  20250411
                            if (empty($_fsan)){
                                $resultado_operacion_fsan['result_id']=-112;
                                $resultado_operacion_fsan['error']='Falta FSAN';
                                $resultado_operacion=$resultado_operacion_fsan['result_id']."|".$resultado_operacion_fsan['error'];
                                 // error ispfull -12|Invalid MAC
                                
                            }else{
                                  list($_estado, $resultado_operacion) = procesarOperacionIspfull(
                                'upgrade velocidad HFC en Ispfull',
                                'cm_alta',
                                [$_fsan, $_cmModel, $_clientname, $_address,$_Tel,$_Lat,$_Lon,$_Dni,$_service, $_client],
                                'DONE',
                                'ERRO',
                                'Operación exitosa - ',
                                'Error desconocido en upgrade HFC del CM'
                            );
                            }
                            break;     
                   case "HAMIF":    
                    //reconexion de modem HFC ISPFULL  20250109
                            //logger("Iniciando reconexion modem hfc en ispfull");
                            list($_estado, $resultado_operacion) = procesarOperacionIspfull(
                                'reconexión de módem HFC en Ispfull',
                                'habilitar_modem_hfc_ispfull',
                                [$_fsan],
                                'DONE',
                                'ERRO',
                                'Operación exitosa - ',
                                'Error desconocido en la reconexión del CM'
                            );
                            break;
                    case "UPFON":    
                    //Upgrade plan ONU/fsan 20250424 ispfull
                                list($_estado, $resultado_operacion) = procesarOperacionIspfull(
                                    'upgrade de cliente FTTH en Ispfull',
                                    'ont_upgrade',
                                    [$_fsan, $_clientname, $_address, $_Tel, $_Lat, $_Lon, $_Dni, $_service, $_client],
                                    'DONE',
                                    'ERRO',
                                    'Operación exitosa - ',
                                    'Error desconocido en upgrade de la ONU'
                                );
                                break;
                     case "STCIF":    
                                list($_estado, $resultado_operacion) = procesarOperacionIspfull(
                                    'consulta de estado cliente HFC en Ispfull',
                                    'client_status',
                                    [$_client],
                                    'DONE',
                                    'ERRO',
                                    'Operación exitosa - Estado conectado cliente: ',
                                    'Error desconocido en estado cliente HFC en Ispfull'
                                );
                                break;
                     case "STMIF":    
                                list($_estado, $resultado_operacion) = procesarOperacionIspfull(
                                    'consulta de estado CM HFC en Ispfull',
                                    'cm_status',
                                    [$_fsan],
                                    'DONE',
                                    'ERRO',
                                    'Operación exitosa - Estado conectado cliente: ',
                                    'Error desconocido en estado CM en Ispfull'
                                );
                                break;
         }
          logger("Response: ".$resultado_operacion);
          actualizar_operacion_cola($operacionid,$resultado_operacion, $_estado); 
         
    }
    pg_free_result($result);
    logger("Fin main $version");
    echo "\n Fin main $version...".date("h:i:sa") ."\n";
    
} catch (Exception $ex) {
    echo $ex->mesagge;
    logger($ex->mesagge);
    //$datos="Error en main.php: ".$ex->mesagge; //20250218 quitado
    //enviarmail($datos, $destinatariomail); //20250218 quitado
}


///20250613 funcion para procesar las llamadas de cada operacion de ispfull agrupando los log y los resultados

function procesarOperacionIspfull($operacion, $callback, $params = [], $estadoOK = 'DONE', $estadoError = 'ERRO', $msgExito = '', $msgErrorDefault = 'Error desconocido') {
    logger("Iniciando $operacion");

    // Llamada dinámica a la función correspondiente
    $resultado = call_user_func_array($callback, $params);

    $result_id = $resultado['result_id'] ?? -1;
    $result_msg = $resultado['result'] ?? '';
    $error_msg = $resultado['error'] ?? $msgErrorDefault;

    if ($result_id == 0 || !empty($resultado['cliente_desconectado'])) {
        $_estado = $estadoOK;
        $estado_desc = $resultado['cliente_desconectado'] ?? $result_msg;
        $resultado_operacion = $msgExito . $estado_desc;
        logger("Resultado: $estado_desc");
    } else {
        $_estado = $estadoError;
        $resultado_operacion = "$result_id | $error_msg";
        logger("Error en el estado - ID: $result_id");
        logger("Error en el estado - Descripción: $error_msg");
    }

    logger("Finalizando $operacion");

    return [$_estado, $resultado_operacion];
}



//actualiza el estado de la operacion
//20250613 antes en ftth_operaciones
function actualizar_operacion_cola($ID_operation,$resultado, $estado)
{
    try {
        $hoy = date('Y-m-d G:i:s');
        $_esquema="provisioner_inet";
        $odb = new DB($_esquema);
        $strSQL = "UPDATE $_esquema.operations_queue
                    SET status = '".$estado."', execution_date='$hoy', response='".$resultado."'
                    WHERE id ='".$ID_operation."'" ;
        
        logger("sql: ".$strSQL);
        $result =$odb->EjecutarSql($strSQL);
        pg_free_result($result);
    } catch (Exception $ex) {
        logger ($ex->getMessage());  
    }
}