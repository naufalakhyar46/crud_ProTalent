<?php

function success($data,$msg=null)
{
   
    $resp = array(
        'status' => 'success',
        'message' => !empty($msg) ? $msg : 'Data ditemukan',
        'code' => 200,
        'data' => $data
    );

    return response()->json($resp, 200);
}

function successForm($cond){
    $resp['status'] = 'success';
    $resp['code'] = 200;
    if($cond == 'add'){
        $resp['message'] = 'Data berhasil ditambahkan';
    }else if($cond == 'update'){
        $resp['message'] = 'Data berhasil diperbarui';
    }else{
        $resp['message'] = 'Data berhasil dihapus';
    }
    return response()->json($resp, 200);
}

function errorForm($msg, $code = 400)
{
    $resp = array(
        'status' => 'error',
        'code' => $code,
        'message' => $msg
    );

    return response()->json($resp, 400);
}

function errorRespApi($message=null)
{
    $resp = array(
        'status' => 'error',
        'code' => 400,
        'message' => !empty($message) ? $message : "Data tidak ditemukan"
    );

    return response()->json($resp, 400);
}