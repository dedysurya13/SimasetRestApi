<?php

use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;

return function (App $app) {
    $container = $app->getContainer();


    //validasi api key
    $cekAPIKey = function($request, $response, $next){
        $error = null;
        if($request->getHeader('ApiKey')){
            $key = $request->getHeader('ApiKey')[0];
            try{
                $sql  = "SELECT * FROM aset_api WHERE api_key = :ApiKey";
                $stmt = $this->db->prepare($sql);
                $stmt->bindParam("ApiKey", $key);
                $stmt->execute();
                $result = $stmt->fetchObject();    
                if(!$result){
                //Api key tidak ada
                $error = 'Unidentified key';
                }
            }catch(PDOException $e){
                $error = array('error'=>$e->getMessage());
            }
        }else{
            $error = 'Missing key';
        }
        if($error){
            return $response->withJson(array('error'=>$error),401);
        }
        return $next($request, $response);
    };



    // //katgeori -semua kategori
    $app->get('/kategori', function(Request $request, Response $response, array $args){
        $sql = "SELECT kode_kategori, nama_kategori FROM aset_kategori_aset";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $mainCount = $stmt->rowCount();
        $result = $stmt->fetchAll();
        if($mainCount==0){
            return $this->response->withJson(['status' => 'error', 'message' => 'no result data'], 200);
        };
        return $response->withJson(['status' => 'success', 'data' => $result], 200);
    })->add($cekAPIKey);

    // //kategori - satu kategori
    $app->get("/kategori/{kode_kategori}", function(Request $request, Response $response, array $args){
        $kode_kategori = trim(strip_tags($args['kode_kategori']));
        $sql = "SELECT kode_kategori, nama_kategori FROM aset_kategori_aset WHERE kode_kategori=:kode_kategori";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam("kode_kategori", $kode_kategori);
        $stmt->execute();
        $mainCount = $stmt->rowCount();
        $result = $stmt->fetchObject();
        if($mainCount==0){
            return $this->response->withJson(['status' => 'error', 'message' => 'no result data'], 200);
        };
        return $response->withJson(['status' => 'success', 'data' => $result], 200);
    })->add($cekAPIKey);

    
    //tampilan default
    $app->get('/[{name}]', function (Request $request, Response $response, array $args) use ($container) {
        // Sample log message
        $container->get('logger')->info("Slim-Skeleton '/' route");

        // Render index view
        return $container->get('renderer')->render($response, 'index.phtml', $args);
    });
};
