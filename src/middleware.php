<?php

use Slim\App;

return function (App $app) {
    // e.g: $app->add(new \Slim\Csrf\Guard);

    //validasi api key
    // $app->add(function ($request, $response, $next){
    //     $error = NULL;
    //     if($request->getHeader('apiKey')){
    //         $key = $request->getHeader('apiKey')[0];
    //         try{
    //             $sql = "SELECT * FROM aset_api where api_key=:apiKey";
    //             $stmt = $this->db->prepare($sql);
    //             $stmt->bindParam("apiKey", $key);
    //             $stmt->execute();
    //             $result = $stmt->fetchObject();
    //             if(!$result){
    //                 //Api Key tidak ada
    //                 $error = 'Unidentified Key';
    //             }
    //         }catch(PDOException $e){
    //             $error = array('error'=>$e->getMessage());
    //         }
    //     }else{
    //         $error = 'Missing Key';
    //     }

    //     if($error){
    //         return $response->withJson(array('error'=>$error),401);
    //     }
        
    //     return $next($request, $response);
    // });
};
