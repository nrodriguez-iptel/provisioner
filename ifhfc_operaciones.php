<?php

/* 20240318
 * operaciones de cada llamada al webservice de aion para ser ejecutadas
 */
include_once("funciones.php");
include_once("cnn.php");

$_token=$ispfull_token;
$_operator=$ispfull_operator;
$_url=$ispfull_url_api;
    
///////////////////
//estatus de una ont para su posterior uso
//uso: ftth_main.php
///////////////////
function cm_status($fsan){
    try{
       logger("datos cm: cm_status|".$fsan);
        global $_token;
        global $_operator;  
        global $_url;  
        
        $data_array =  array(
            "method"=> 'cm_status',
            "mac"=> $fsan,
            "token"=> $_token,
            "operator"=> $_operator
        ); 
        
        $resultado=llamar_api_ispfull_hfc($data_array,$_url);
        return $resultado;
        
    } catch (Exception $ex) {
        logger ('cm_status'.$ex->getMessage());  
    }
}
///////////////////
//estatus de una ont para su posterior uso
//uso: ftth_main.php
//20250512
///////////////////
function client_status($client_id){
    try{
       logger("datos cm: client_status|".$client_id);
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
        logger ('cm_status'.$ex->getMessage());  
    }
}

///////////////////
//alta de una CM 
///////////////////
function cm_alta($fsan, $cmModel, $cmts, $name, $address, $tel, $lat, $lon, $dni, $service, $id_cliente){
    try{
         logger("datos alta CM:cm_provision|$fsan|$cmModel|$cmts|$name|$address|$tel|$lat|$lon|$dni|$service");
          global $_token;
          global $_operator;
          global $_url;  
        $data_array =  array(
            "method"=> "cm_provision",
            "mac"=> $fsan,
            "cm_model"=> $cmModel,
            "name"=> $name,
            "id_client"=> $id_cliente,
            "Address"=> $address,
            "tel"=> $tel,
            "lat"=> $lat,
            "lon"=> $lon,
            "dni"=> $dni,
            "service"=> $service,
            "token"=> $_token,
            "operator"=>$_operator);
          $resultado=llamar_api_ispfull_hfc($data_array,$_url);
        return $resultado;
    } catch (Exception $ex) {
        logger ('cm_alta'.$ex->getMessage());  
    }
}
///////////////////
//baja de una ont 
///////////////////
function cm_baja($fsan){
    try{
       logger("datos baja cm: cm_baja|".$fsan);
        global $_token;
        global $_operator;
        global $_url;  
        
        $data_array =  array(
            "method"=> 'cm_disconnect',
            "mac"=> $fsan,
            "token"=> $_token,
            "operator"=> $_operator
        ); 
        
        $resultado=llamar_api_ispfull_hfc($data_array,$_url);
        return $resultado;
        
    } catch (Exception $ex) {
        logger ('cm_baja'.$ex->getMessage());  
    }
}


///////////////////
//suspension de cliente 20241218
///////////////////
function suspender_cliente_hfc_ispfull($idCliente){
    try{
      logger("datos suspension cliente: suspender_cliente|".$idCliente);
        global $_token;
        global $_operator;  
        global $_url;  
        //https://iptel_api.ispfull.com:4437/ram/iptel/Masterservicesftth.asp?method=suspend_client&id_client=1100&token=8A2899CC-21C3-4654-8732-4D3BFE2992DF&operator=2681111
        $data_array =  array(
            "method"=> 'suspend_client',
            "id_client"=> $idCliente,
            "que"=> 5,
            "token"=> $_token,
            "operator"=> $_operator
        ); 
        
        $resultado=llamar_api_ispfull_hfc($data_array,$_url);
        return $resultado;
        
    } catch (Exception $ex) {
        logger ('suspender_cliente'.$ex->getMessage());  
    }
}
///////////////////
//habilitar de cliente 20241218
///////////////////
function habilitar_cliente_hfc_ispfull($idCliente){
    try{
      logger("datos habilitacion cliente: habilitar_cliente_hfc_ispfull|".$idCliente);
        global $_token;
        global $_operator;  
        global $_url;  
        //https://iptel_api.ispfull.com:4437/ram/iptel/Masterservicesftth.asp?method=reconnect_client&id_client=1100&token=8A2899CC-21C3-4654-8732-4D3BFE2992DF&operator=2681111
        $data_array =  array(
            "method"=> 'reconnect_client',
            "id_client"=> $idCliente,
            "que"=> 5,
            "token"=> $_token,
            "operator"=> $_operator
        ); 
        
        $resultado=llamar_api_ispfull_hfc($data_array,$_url);
        return $resultado;
        
    } catch (Exception $ex) {
        logger ('habilitar_cliente'.$ex->getMessage());  
    }
}
///////////////////
//habilitar de modem 20250416
///////////////////
function habilitar_modem_hfc_ispfull($mac){
    try{
      logger("datos habilitacion modem hfc: habilitar_modem_hfc_ispfull|".$idCliente);
        global $_token;
        global $_operator;  
        global $_url;  
        //https://iptel_api.ispfull.com:4437/ram/iptel/Masterservicesftth.asp?method=cm_reconnect&mac=602AD087C832&token=8A2899CC-21C3-4654-8732-4D3BFE2992DF&operator=2681111

        $data_array =  array(
            "method"=> 'cm_reconnect',
            "mac"=> $mac,
            "token"=> $_token,
            "operator"=> $_operator
        ); 
        
        $resultado=llamar_api_ispfull_hfc($data_array,$_url);
        return $resultado;
        
    } catch (Exception $ex) {
        logger ('habilitar_cliente'.$ex->getMessage());  
    }
}