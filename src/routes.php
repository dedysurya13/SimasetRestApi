<?php

use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;

return function (App $app) {
    $container = $app->getContainer();

    $app->get('/[{name}]', function (Request $request, Response $response, array $args) use ($container) {
        // Sample log message
        $container->get('logger')->info("Slim-Skeleton '/' route");

        // Render index view
        return $container->get('renderer')->render($response, 'index.phtml', $args);
    });


    $app->get("/kategori/{kode_kategori}", function(Request $request, Response $response, array $args){
        $kode_kategori = trim(strip_tag($args['kode_kategori']));
        $sql = "SELECT kode_kategori, nama_kategori FROM aset_kategori_aset WHERE kode_kategori=:kode_kategori";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam("kode_kategori", $kode_kategori);
        $stmt->execute();
        $mainCount = $stmt->rowCount();
        $result = $stmt->fetchObject();
        if($mainCount==0){
            return $this->response->withJson(['status' => 'error', 'message' => 'no result data'], 200);
        }
        return $response->withJson(['status' => 'success', 'data' => $result], 200);
    });
};
