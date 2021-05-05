<?php

use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;

return function (App $app) {
    $container = $app->getContainer();

    /*
    *
    *   === VALIDASI API KEY ===
    *
    */
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




    /*
    *
    *   === KATEGORI ===
    *
    */
    //katgeori -semua kategori
    $app->get('/kategori', function(Request $request, Response $response, array $args){
        $sql = "SELECT kode_kategori, nama_kategori FROM aset_kategori_aset";
        $query = $this->db->prepare($sql);
        $result = $query->execute();
        
        if($result){
            if($query->rowCount()){
                $data = array(
                    'kode'      => '1',
                    'status'    => 'Sukses',
                    'data'      => $query->fetchAll()
                );
            }else{
                $data = array(
                    'kode'      => '2',
                    'status'    => 'Tidak ada data',
                    'data'      => null
                );
            }
        }else{
            $data = array(
                'kode'      => '100',
                'status'    => 'Terdapat error',
                'data'      => null
            );
        }
        return $response->withJson($data);
    })->add($cekAPIKey);

    //kategori - satu kategori
    $app->get('/kategori/{kodeKategori}', function(Request $request, Response $response, array $args){
        $kode_kategori = trim(strip_tags($args['kodeKategori']));
        $sql = "SELECT kode_kategori, nama_kategori FROM aset_kategori_aset WHERE kode_kategori=:kode_kategori";
        $query = $this->db->prepare($sql);
        $query->bindParam(':kode_kategori', $args['kodeKategori']);
        $result = $query->execute();
        if($result){
            if($query->rowCount()){
                $data = array(
                    'kode'      => '1',
                    'status'    => 'Sukses',
                    'data'      => $query->fetch()
                );
            }else{
                $data = array(
                    'kode'      => '2',
                    'status'    => 'Tidak ada data',
                    'data'      => null
                );
            }
        }else{
            $data = array(
                'kode'      => '100',
                'status'    => 'Terjadi error',
                'data'      => null
            );
        }
        return $response->withJson($data);
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
        $namaKategori= filter_var($input['namaKategori'], FILTER_SANITIZE_STRING);
        $sql = "INSERT INTO aset_kategori_aset (kode_kategori, nama_kategori) VALUES (:kode_kategori, :nama_kategori)";
        $query = $this->db->prepare($sql);
        $query->bindParam("kode_kategori", $kodeKategori);
        $query->bindParam("nama_kategori", $namaKategori);
        $query->execute();
        $result=$query->rowCount();
        if($result>0){
            $data = array(
                'kode'      => '1',
                'status'    => 'Sukses',
                'data'      => 'Data berhasil diubah'
            );
        }else{
            $data = array(
                'kode'      => '100',
                'status'    => 'Terdapat error',
                'data'      => null
            );
        }
        return $response->withJson($data);
    })->add($cekAPIKey);
    
    //Kategori - ubah kategori
    $app->put('/kategori/{kodeKategori}', function (Request $request, Response $response, array $args){
        $input = $request->getParsedBody();

        $kodeKategori = $args['kodeKategori'];
        $namaKategori = filter_var($input['namaKategori'], FILTER_SANITIZE_STRING);
        
        $sql = "UPDATE aset_kategori_aset SET nama_kategori=:nama_kategori WHERE kode_kategori=:kode_kategori";
        $query = $this->db->prepare($sql);
        $query->bindParam('kode_kategori', $kodeKategori);
        $query->bindParam('nama_kategori', $namaKategori);
        $query->execute();
        $result=$query->rowCount();
        if($result>0){
            $data = array(
                'kode'      => '1',
                'status'    => 'Sukses',
                'data'      => 'Data berhasil diubah'
            );
        }else{
            $data = array(
                'kode'      => '100',
                'status'    => 'Terdapat error',
                'data'      => null
            );
        }
        return $response->withJson($data);
    })->add($cekAPIKey);

    //Kategori - hapus kategori
    $app->delete('/kategori/{kodeKategori}', function(Request $request, Response $response, array $args){
        
        $sql = "DELETE FROM aset_kategori_aset WHERE kode_kategori=:kode_kategori";
        $query = $this->db->prepare($sql);
        $query->bindParam(":kode_kategori", $args['kodeKategori']);
        $query->execute();
        $result=$query->rowCount();
        if($result>0){
            $data = array(
                'kode'      => '1',
                'status'    => 'Sukses',
                'data'      => 'Data berhasil diubah'
            );
        }else{
            $data = array(
                'kode'      => '100',
                'status'    => 'Terdapat error',
                'data'      => null
            );
        }

        return $response->withJson($data);
    })->add($cekAPIKey);;



    //tampilan default
    $app->get('/[{name}]', function (Request $request, Response $response, array $args) use ($container) {
        // Sample log message
        $container->get('logger')->info("Slim-Skeleton '/' route");

        // Render index view
        return $container->get('renderer')->render($response, 'index.phtml', $args);
    });
};
