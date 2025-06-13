<?php
$usuariocontraseña='sopporte:gSDtFCVE$JSJ';

//uso de la api de flowdat3 para hacer post, delete.
function CallAPI($method, $url, $data=false)
{
    try
    {  
    global $usuariocontraseña;
    $curl = curl_init();
   
    switch ($method)
    {
        case "POST":
            curl_setopt($curl, CURLOPT_POST, 1);
            if ($data)
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                logger("CallAPI->url POST:".$curl);
            break;
        case "PUT":
                curl_setopt($curl, CURLOPT_PUT, 1);
                logger("CallAPI->url PUT:".$curl);
            break;
        case "DELETE":
                $url =$url."?id=".$data;
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST,"DELETE");
                logger("CallAPI->url DELETE:".$url);
            break;
        case "LIST":
                $url =$url.$data;
                curl_setopt($curl, CURLOPT_GET, true);
                logger("CallAPI->url LIST:".$url);
            break;
        default:
            if ($data)
            {
                //curl_setopt($curl, CURLOPT_GET, true);
                $url =$url."?".http_build_query($data);
                logger("CallAPI->url default:".$url);
                //$url =sprintf("%s?%s", $url, http_build_query($data)); //oRIGINAL
            }
    }
    //echo "<br>El contenido de url es: ".$url;
    // Optional Authentication:
    curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($curl, CURLOPT_USERPWD, $usuariocontraseña);
    curl_setopt($curl,CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

    $result = curl_exec($curl);
    if(!$result){
        //die("Connection Failure");
        logger("Error de conexión a la API de FD3");
        //nro20230510 Capturamos el error de la conexión en el log y enviamos un mail a mi cuenta para alertar
        logger ("Error result:".$result);
        enviarmail("Error FTTH", 'nrodriguez@iptel.com.ar', "ERROR");
        die ("Error de conexión a la API de FD3");
         
    }
    curl_close($curl);

    return $result;
    
    } catch (Exception $ex) {
        logger($ex->getMessage);
    }
}

//////20250219 mejorado por chatgtp para la captura de error
function CallAPI_new($method, $url, $data = false)
{
    try {  
        global $usuariocontraseña;
        $curl = curl_init();

        switch ($method) {
            case "POST":
                curl_setopt($curl, CURLOPT_POST, 1);
                if ($data) {
                    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
                }
                logger("CallAPI-> Método: POST | URL: $url | Datos: " . json_encode($data));
                break;
            case "PUT":
                curl_setopt($curl, CURLOPT_PUT, 1);
                logger("CallAPI-> Método: PUT | URL: $url");
                break;
            case "DELETE":
                $url = $url . "?id=" . $data;
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
                logger("CallAPI-> Método: DELETE | URL: $url");
                break;
            case "LIST":
                $url = $url . $data;
                curl_setopt($curl, CURLOPT_HTTPGET, true);
                logger("CallAPI-> Método: LIST | URL: $url");
                break;
            default:
                if ($data) {
                    $url = $url . "?" . http_build_query($data);
                    logger("CallAPI-> Método: GET (default) | URL: $url");
                }
        }

        // Configuración de cURL
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($curl, CURLOPT_USERPWD, $usuariocontraseña);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        // Registrar información de la petición
        logger("CallAPI-> Configuración cURL: " . print_r(curl_getinfo($curl), true));

        // Ejecutar la solicitud
        $result = curl_exec($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if (curl_errno($curl)) {
            $error_msg = curl_error($curl);
            logger("Error en cURL: " . $error_msg);
            die("Error de conexión a la API: " . $error_msg);
        }

        logger("CallAPI-> Respuesta HTTP: $http_code | Respuesta: " . $result);

        curl_close($curl);
        return $result;
    } catch (Exception $ex) {
        logger("CallAPI-> Excepción: " . $ex->getMessage());
    }
}


////////////////////////////////////////
/////LOG/////////////////
/////////////////////////////////////

function logger($texto)
{
  
  $date_current = date("Y-m-d H:i:s");
  $date_actual = date("Ymd");
  $file_log = fopen('/var/log/www/html/provisioner/'.$date_actual.'_provisioner.log', 'a+');
  fwrite($file_log , $date_current."-".$texto."\n" );	
  fclose($file_log);
  
}


/////////////////////////////////////////
/////OBJETO TO ARRAY/////////////////
/////////////////////////////////////

function obj2array($obj) {
  $out = array();
  foreach ($obj as $key => $val) {
    switch(true) {
        case is_object($val):
         $out[$key] = obj2array($val);
         break;
      case is_array($val):
         $out[$key] = obj2array($val);
         break;
      default:
        $out[$key] = $val;
    }
  }
  return $out;
}


//20240510 funcion para convertir los nro de serie de los equipos BDCOM
function convertirSerialEquipo($serialnumber)
{
    try {


        logger("Serial number original: ".$serialnumber);
       $_serialnumberBDCOM=substr($serialnumber, 0, 4); //extraemos los primeros cuatro valores
       $_serialnumberBDCOM_final=substr($serialnumber, 4, strlen($serialnumber)-1); //extramos los valores finales de la mac
       $_mascara='48575443'; //mascara para remplazar los primeros 4 valores de la mac
       if ($_serialnumberBDCOM=='0055')
           {
           $_serialnumberCambiado=$_mascara.$_serialnumberBDCOM_final; //concatenemos la mascara con los valores finales de la mac
           logger("El serial number cambiado es ".$_serialnumberCambiado);
           }
       elseif($_serialnumberBDCOM=='AC12')
            {
           $_serialnumberCambiado='48575443'.$_serialnumberBDCOM_final; //concatenemos la mascara con los valores finales de la mac
           logger("El serialnumber cambiado es ".$_serialnumberCambiado);
           }
        elseif($_serialnumberBDCOM=='0018') //20250128 equipo XPON
                     {
                    $_serialnumberCambiado=$_mascara.$_serialnumberBDCOM_final; //concatenemos la mascara con los valores finales de la mac
                    logger("El serialnumber cambiado es ".$_serialnumberCambiado);
                    }    
           else{
               $_serialnumberCambiado=$serialnumber;
           }
           return $_serialnumberCambiado;

    } catch (Exception $ex) {
         logger ("convertirSerialEquipo->".$ex->getMessage());  
    }   

}


////////////////////
///LLAMAR API
/////
function llamar_api_ispfull_hfc($data, $_url){
    
try{ 
    
    $url =$_url."?".http_build_query($data);
    //echo "formato de la url: ".$url;
    logger ("llamar_api_ispfull->url: ".$url);
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    //asigno el xml del curl a una variable string
    $elementos=(string)curl_exec($curl);
    logger("RESPUESTA curl:".$elementos);
    //transformo en array el string xml
    $xml_array = (array)simplexml_load_string($elementos);
        $array_return['result_id']=$xml_array["result_id"];
        $array_return['result']=$xml_array["result"];
        $array_return['error']=$xml_array["error"];
        $array_return['acct_admin_stat']=$xml_array["acct_admin_stat"];
        $array_return['cliente_desconectado']=$xml_array["disconnected"];
    var_dump($array_return);
    curl_close($curl);
    return $array_return;
        
   
    } catch (Exception $ex) {
        logger ("llamar_api_ispfull:".$ex->getMessage());
    }
}
////////////////////
///LLAMAR API con datos del estado del cliente 20250514
/////
function llamar_api_ispfull_estado_cliente($data, $_url){
    
try{ 
    
    $url =$_url."?".http_build_query($data);
    //echo "formato de la url: ".$url;
    logger ("llamar_api_ispfull->url: ".$url);
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    //asigno el xml del curl a una variable string
    $elementos=(string)curl_exec($curl);
    logger("RESPUESTA curl:".$elementos);
    //transformo en array el string xml
   $xml = simplexml_load_string($elementos);

    $disconnected = (string)$xml->clients->client->disconnected;
    $fsan = (string)$xml->clients->client->access->ani->account;
    logger("llamar_api_ispfull_hfc_estado_cliente->cliente desconectado = $disconnected");
    $array_return['cliente_desconectado'] = $disconnected;
    $array_return['fsan'] = $fsan;
    
    var_dump($array_return);
    curl_close($curl);
    return $array_return;
        
   
    } catch (Exception $ex) {
        logger ("llamar_api_ispfull:".$ex->getMessage());
    }
}

//20250613 funcion para procesar el resultado de una operacion
function analizarResultadoOperacion($resultado_operacion_fsan)

{
    if ($resultado_operacion_fsan['result_id']==0)
    {
      //baja OK
        $resultado_operacion='operacion exitosa-'.$resultado_operacion_fsan['result'];
        logger("Resultado: ".$resultado_operacion_fsan['result']);
        $_estado='DONE';
    }else 
    {
        //erro en la baja
        logger("error en el estado id: ".$resultado_operacion_fsan['result_id']);
        logger("error en el estado error nombre: ".$resultado_operacion_fsan['error']);
        $resultado_operacion=$resultado_operacion_fsan['result_id']."|".$resultado_operacion_fsan['error'];
        if (!$resultado_operacion)
            {
            $resultado_operacion="error desconocido en la suspension del  cliente";
            logger("Error: ".$resultado_operacion);
            }
        $_estado='ERRO';    
    }
    
}