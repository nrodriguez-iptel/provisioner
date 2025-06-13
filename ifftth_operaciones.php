<?php

/* 20240318
 * operaciones de cada llamada al webservice de aion para ser ejecutadas
 */
include_once("funciones.php");
include_once("cnn.php");

$_token=$ispfull_token;//"8A2899CC-21C3-4654-8732-4D3BFE2992DF";
$_operator=$ispfull_operator;//"2681111";
$_url=$ispfull_url_api;
/*'https://iptel_api.ispfull.com:4437/ram/iptel/Masterservicesftth.asp';*/
    

///////////////////
//estatus de una ont para su posterior uso
///////////////////
function ont_status($fsan){
    try{
       logger("datos autorizacion fsan: ont_status|".$fsan);
        global $_token;
        global $_operator;  
        
        $data_array =  array(
            "method"=> 'ont_status',
            "fsan"=> $fsan,
            "token"=> $_token,
            "operator"=> $_operator
        ); 
        
        $resultado=llamar_api_ispfull($data_array);
        return $resultado;
        
    } catch (Exception $ex) {
        logger ('ont_status'.$ex->getMessage());  
    }
}



///////////////////
//autorizar una ont para su posterior uso
///////////////////
function ont_autorizacion($fsan, $client, $model){
    try{
          logger("datos autorizacion fsan:ont_authorize|".$fsan."|".$client."|".$model);
          global $_token;
          global $_operator;
        $data_array =  array(
            "method"=> "ont_authorize",
            "fsan"=> $fsan,
            "model"=> $model,
            "client"=> $client,
            "token"=> $_token,
            "operator"=>$_operator
        );
          $resultado=llamar_api_ispfull($data_array);
        return $resultado;
        
    } catch (Exception $ex) {
        logger ('ont_autorizacion'.$ex->getMessage());  
    }
}
///////////////////
//alta de una ont 
///////////////////
function ont_alta($fsan, $name, $address, $tel, $lat, $lon, $dni, $service, $id_cliente){
    try{
        
         logger("datos alta fsan:ont_provision|".$fsan."|".$name."|".$address."|".$tel."|".$lat."|".$lon."|".$dni."|".$service);
          global $_token;
          global $_operator;
        $data_array =  array(
            "method"=> "ont_provision",
            "fsan"=> $fsan,
            "name"=> $name,
            "id_client"=> $id_cliente,
            "Address"=> $address,
            "tel"=> $tel,
            "lat"=> $lat,
            "lon"=> $lon,
            "dni"=> $dni,
            "service"=> $service,
            "token"=> $_token,
            "operator"=>$_operator
        );
          $resultado=llamar_api_ispfull($data_array);
        return $resultado;
        
    } catch (Exception $ex) {
        logger ('ont_alta'.$ex->getMessage());  
    }
}
///////////////////
//baja de una ont 
///////////////////
function ont_baja($fsan){
    try{
       logger("datos baja fsan: ont_baja|".$fsan);
        global $_token;
        global $_operator;  
        
        $data_array =  array(
            "method"=> 'ont_disconnect',
            "fsan"=> $fsan,
            "token"=> $_token,
            "operator"=> $_operator
        ); 
        
        $resultado=llamar_api_ispfull($data_array);
        return $resultado;
        
    } catch (Exception $ex) {
        logger ('ont_baja'.$ex->getMessage());  
    }
}

///////////////////
//suspension de una ont 
///////////////////
function ont_suspension($fsan){
    try{
      logger("datos suspension fsan: ont_suspension|".$fsan);
        global $_token;
        global $_operator;  
        
        $data_array =  array(
            "method"=> 'ont_suspend',
            "fsan"=> $fsan,
            "token"=> $_token,
            "operator"=> $_operator
        ); 
        
        $resultado=llamar_api_ispfull($data_array);
        return $resultado;
        
    } catch (Exception $ex) {
        logger ('ont_suspension'.$ex->getMessage());  
    }
}
///////////////////
//suspension de cliente 20241218
///////////////////
function suspender_cliente_ispfull($idCliente){
    try{
      logger("datos suspension cliente: suspender_cliente|".$idCliente);
        global $_token;
        global $_operator;  
        //https://iptel_api.ispfull.com:4437/ram/iptel/Masterservicesftth.asp?method=suspend_client&id_client=1100&token=8A2899CC-21C3-4654-8732-4D3BFE2992DF&operator=2681111
        $data_array =  array(
            "method"=> 'suspend_client',
            "id_client"=> $idCliente,
            "que"=> 5,
            "token"=> $_token,
            "operator"=> $_operator
        ); 
        //20250610 agregamos la consulta al estado del cliente y del equipo si está desconectado o suspendido el cliente
        //no hacemos nada
        //$cliente_suspendido=client_status_ftth($idCliente);
        
        //logger("suspender_cliente_ispfull->estado_cliente_suspendido".$cliente_suspendido['cliente_desconectado']);
        $resultado=llamar_api_ispfull($data_array);
        return $resultado;
        
    } catch (Exception $ex) {
        logger ('suspender_cliente'.$ex->getMessage());  
    }
}
///////////////////
//habilitar de cliente 20241218
///////////////////
function habilitar_cliente_ispfull($idCliente){
    try{
      logger("datos habilitacion cliente: habilitar_cliente_ispfull|".$idCliente);
        global $_token;
        global $_operator;  
        //https://iptel_api.ispfull.com:4437/ram/iptel/Masterservicesftth.asp?method=reconnect_client&id_client=1100&token=8A2899CC-21C3-4654-8732-4D3BFE2992DF&operator=2681111
        $data_array =  array(
            "method"=> 'reconnect_client',
            "id_client"=> $idCliente,
            "que"=> 5,
            "token"=> $_token,
            "operator"=> $_operator
        ); 
        
        $resultado=llamar_api_ispfull($data_array);
        return $resultado;
        
    } catch (Exception $ex) {
        logger ('habilitar_cliente'.$ex->getMessage());  
    }
}
///////////////////
//habilitacion de una ont 
///////////////////
function ont_habilitacion($fsan){
    try{
        logger("datos habilitacion fsan: ont_habilitacion|".$fsan);
        global $_token;
        global $_operator;  
        
        $data_array =  array(
            "method"=> 'ont_reconnect',
            "fsan"=> $fsan,
            "token"=> $_token,
            "operator"=> $_operator
        ); 
        
        $resultado=llamar_api_ispfull($data_array);
        return $resultado;
        
    } catch (Exception $ex) {
        logger ('ont_habilitacion'.$ex->getMessage());  
    }
}

///////////////////
//asignar telefonia a una ont 
///////////////////
function asignar_telefonia($fsan, $did, $voip, $regusername, $regpass){
    try{
        logger("datos habilitacion telefonia: asignar_telefonia|".$fsan);
        global $_token;
        global $_operator;  
        //https://iptel_api.ispfull.com:4437/ram/iptel/Masterservicesftth.asp?method=ont_tel_add&fsan=48575443961FE420&did=12103055899&limit=-1&registry=-1&reg_server_url=&reg_server_dom=&tel_mode=-1&regusername=&regpass=&hit_server=-1&dialplan=-1&codec=-1&voip=-1&id_profile_wan=-1&id_lucent=-1&siplar_vz=&token=8A2899CC-21C3-4654-8732-4D3BFE2992DF&operator=2681111
        $data_array =  array(
            "method"=> 'ont_tel_add',
            "fsan"=> $fsan,
            "did"=>$did,
            "limit"=>-1,
            "registry"=>-1,
            "reg_server_url"=>null,
            "reg_server_dom"=>Null,
            "tel_mode"=>-1,
            "regusername"=>$regusername,
            "regpass"=>$regpass,
            "hit_server"=>-1,
            "dialplan"=>-1,
            "codec"=>-1,
            "voip"=>$voip,
            "id_profile_wan"=>-1,
            "id_lucent"=>-1,
            "siplar_vz"=>Null,
            "token"=> $_token,
            "operator"=> $_operator
        ); 
        
        $resultado=llamar_api_ispfull($data_array);
        return $resultado;
        
    } catch (Exception $ex) {
        logger ('asignar_telefonia'.$ex->getMessage());  
    }
}
///////////////////
//asignar telefonia a una ont 
///////////////////
function bajar_telefonia($fsan, $did){
    try{
        logger("datos baja telefonia: bajar_telefonia|".$fsan);
        global $_token;
        global $_operator;  
        
        $data_array =  array(
            "method"=> 'ont_tel_del',
            "fsan"=> $fsan,
            "did"=>$did,
            "limit"=>-1,
            "registry"=>-1,
            "reg_server_url"=>null,
            "reg_server_dom"=>Null,
            "tel_mode"=>-1,
            "regusername"=>Null,
            "regpass"=>Null,
            "hit_server"=>-1,
            "dialplan"=>-1,
            "codec"=>-1,
            "voip"=>-1,
            "id_profile_wan"=>-1,
            "id_lucent"=>-1,
            "siplar_vz"=>Null,
            "token"=> $_token,
            "operator"=> $_operator
        ); 
        
        $resultado=llamar_api_ispfull($data_array);
        return $resultado;
        
    } catch (Exception $ex) {
        logger ('bajar_telefonia'.$ex->getMessage());  
    }
}

///////////////////
//upgrade de una ont 
///////////////////
function ont_upgrade($fsan, $name, $address, $tel, $lat, $lon, $dni, $service, $id_cliente){
    try{
        
         logger("datos upgrade fsan:ont_provision|".$fsan."|".$name."|".$address."|".$tel."|".$lat."|".$lon."|".$dni."|".$service);
          global $_token;
          global $_operator;
        $data_array =  array(
            "method"=> "ont_provision",
            "fsan"=> $fsan,
            "name"=> $name,
            "id_client"=> $id_cliente,
            "Address"=> $address,
            "tel"=> $tel,
            "lat"=> $lat,
            "lon"=> $lon,
            "dni"=> $dni,
            "service"=> $service,
            "token"=> $_token,
            "operator"=>$_operator
        );
          $resultado=llamar_api_ispfull($data_array);
        return $resultado;
        
    } catch (Exception $ex) {
        logger ('ont_upgrade'.$ex->getMessage());  
    }
}


///////////////////
//devuelve el estado de un cliente
//return  $resultado_status['cliente_desconectado']
//uso: ftth_main.php
//20250512
///////////////////
function client_status_ftth($client_id){
    try{
       logger("client_status_ftth: client_status|".$client_id);
        global $_token;
        global $_operator;  
        global $_url;  
        
        $data_array =  array(
            "method"=> 'getClient',
            "client"=> $client_id,
            "token"=> $_token,
            "operator"=> $_operator
        ); 
        
        $resultado=llamar_api_ispfull_estado_cliente($data_array,$_url);
        return $resultado;
        
    } catch (Exception $ex) {
        logger ('client_status_ftth'.$ex->getMessage());  
    }
}







////////////////////
///LLAMAR API
/////
function llamar_api_ispfull($data){
    
    try{ 
    global $_url;
    
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
        $array_return['admin_status']=$xml_array["admin_status"];
    var_dump($array_return);
    
    
    curl_close($curl);
    return $array_return;
        
   
    } catch (Exception $ex) {
        logger ("llamar_api_ispfull:".$ex->getMessage());
    }
}

function SimpleXML2Array($xml){
    try{
        
    $array = (array)$xml;

    //recursive Parser
    foreach ($array as $key => $value){
        if(strpos(get_class($value),"SimpleXML")!==false){
            $array[$key] = SimpleXML2Array($value);
        }
    }

    return $array;
    
    } catch (Exception $ex) {
        logger("SimpleXML2Array:".$ex->getMessage());
    }
}