<?php
$GLOBALS['starttime'] = microtime(true);
// headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');
//include the estimator file
include_once 'estimator.php';
//set response format, default to json
$GLOBALS['format'] = (isset($_GET['format']) && $_GET['format'] == 'xml') ? 'xml' : 'json' ;
// double check to make sure request method is correct
if ($_SERVER['REQUEST_METHOD'] !== 'POST') returnResponse(405, null, 'Method not Allowed');
//get the raw posted data 
$data = json_decode(file_get_contents('php://input'));
// check if data is empty
if(empty($data)) returnResponse(400, null, 'Invalid Request');
//make sure the input data is in array format, to match the estimator
if( is_object($data) ) {
    $data = (array) $data;
    $data['region'] = (array) $data['region'];
}

//process data
$result =  covid19ImpactEstimator($data);
//return response
returnResponse(200, $result);

function returnResponse($code, $data = null, $message = null) {
    http_response_code($code);
    if(!empty($data)) $return = $data;
    if(!empty($message)) $return['message'] = $message;
    if($GLOBALS['format'] == 'json') returnJSON($return);
    if($GLOBALS['format'] == 'xml') returnXML($return);
    //log the transaction once complete
    $GLOBALS['stoptime'] = microtime(true);
    include_once('process-log.php');
    exit();
}

function returnJSON($response) {
    header('Content-Type: application/json');
    echo json_encode($response);
}

function returnXML($response) {
    header('Content-Type: application/xml; charset=utf-8');
    $xmlTree = new DOMDocument('1.0', 'UTF-8');
    $xmlRoot = $xmlTree->createElement('estimate');
    $xmlTree->appendChild($xmlRoot);

    foreach($response as $key => $value) {
        if(!is_array($value)) {
            $xmlRoot->appendChild($xmlTree->createElement($key, $value));
            continue;
        }
        $xmlElement = $xmlTree->createElement($key);
        $xmlRoot->appendChild($xmlElement);
        foreach($value as $vKey => $vValue) {
            if(!is_array($vValue)) {
                $xmlElement->appendChild($xmlTree->createElement($vKey, $vValue));
                continue;
            }
            $xmlElementInner = $xmlTree->createElement($vKey);
            $xmlElement->appendChild($xmlElementInner);
            foreach($vValue as $vVKey => $vVValue) {
                if(!is_array($vVValue)) {
                    $xmlElementInner->appendChild($xmlTree->createElement($vVKey, $vVValue));
                    continue;
                }
            }
        }
    }
    echo $xmlTree->saveXML();
}