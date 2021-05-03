<?php

use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;

return function (App $app) {
    $container = $app->getContainer();


    //validasi api key
    $cekAPIKey = function($request, $response, $next){
        $error = null;
        if($request->getHeader('X-API-KEY')){
            $key = $request->getHeader('X-API-KEY')[0];
            try{
                $sql  = "SELECT * FROM aset_api WHERE api_key = :api_key";
                $stmt = $this->db->prepare($sql);
                $stmt->bindParam("api_key", $key);
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



    //katgeori -semua kategori
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

    //kategori - satu kategori
    $app->get('/kategori/{kodeKategori}', function(Request $request, Response $response, array $args){
        $kode_kategori = trim(strip_tags($args['kodeKategori']));
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

    //Kategori - tambah kategori
    $app->post('/kategori', function(Request $request, Response $response, array $args){
        $sqlID = "SELECT MAX(kode_kategori) FROM aset_kategori_aset";
        $incrementID = $this->db->prepare($sqlID);
        $incrementID->execute();
        $kodeTerakhir = $incrementID->fetchColumn();

        $tglSekarang = date("ymd");

        $kodeHuruf=substr($kodeTerakhir,0,2);
        $kodeTanggal=substr($kodeTerakhir,2,6);
        $kodeAngka=substr($kodeTerakhir,8);

        if ($kodeTanggal==$tglSekarang){
            $kodeAngka=(int)$kodeAngka;
            $kodeAngka=$kodeAngka + 10001;
            $kodeAngka=substr($kodeAngka,1);
            $kodeBaru = $kodeHuruf.$kodeTanggal.$kodeAngka;
        }else{
            $kodeAngka=10001;
            $kodeAngka=substr($kodeAngka,1);
            $kodeBaru="KT".$tglSekarang.$kodeAngka;;
        }

        $input = $request->getParsedBody();
        $kodeKategori = $kodeBaru;
        $namaKategori=trim(strip_tags($input['namaKategori']));
        $sql = "INSERT INTO aset_kategori_aset (kode_kategori, nama_kategori) VALUES (:kode_kategori, :nama_kategori)";
        $sth = $this->db->prepare($sql);
        $sth->bindParam("kode_kategori", $kodeKategori);
        $sth->bindParam("nama_kategori", $namaKategori);
        $statusInsert=$sth->execute();
        if($statusInsert){
            return $this->response->withJson(['status' => 'success', 'data'=>'success insert produk.'], 200);
        }else{
            return $this->response->withJson(['status' => 'error', 'data'=>'error insert produk.'], 200);
        }
    })->add($cekAPIKey);
    
    //Kategori - ubah kategori
    $app->put('/kategori/{kodeKategori}', function (Request $request, Response $response, array $args){
        $input = $request->getParsedBody();

        $kodeKategori = trim(strip_tags($args['kodeKategori']));
        $namaKategori = trim(strip_tags($input['namaKategori']));

        $sql = "UPDATE aset_kategori_aset SET nama_kategori=:nama_kategori WHERE kode_kategori=:kode_kategori";
        $sth = $this->db->prepare($sql);
        $sth->bindParam("kode_kategori", $kodeKategori);
        $sth->bindParam("nama_kategori", $namaKategori);

        $statusUpdate = $sth->execute();
        if($statusUpdate){
            return $this->response->withJson(['status'=>'success', 'data'=>'success update kategori.'], 200);
        }else{
            return $this->response->withJson(['status'=>'error', 'data'=>'error update kategori']);
        }
    });

    //tampilan default
    $app->get('/[{name}]', function (Request $request, Response $response, array $args) use ($container) {
        // Sample log message
        $container->get('logger')->info("Slim-Skeleton '/' route");

        // Render index view
        return $container->get('renderer')->render($response, 'index.phtml', $args);
    });
};
