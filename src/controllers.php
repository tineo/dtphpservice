<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Doctrine\DBAL\DBALException;

use \Firebase\JWT\JWT;




//Request::setTrustedProxies(array('0.0.0.0', '[::1]:80'));
$app->after(function (Request $request, Response $response) use ($app) {

    //$server = (($_SERVER['SERVER_PORT']==443)?'https':'http').'://'.apache_request_headers()['Host'];
    /*if(apache_request_headers()['Host']=="localhost"){
        $origin = 'http://localhost:3000';
    }else{
        $origin = 'https://'.apache_request_headers()['Host'].' '.'http://'.apache_request_headers()['Host'].
        " http://dtodo-phpserv.rhcloud.com https://dtodo-phpserv.rhcloud.com http://dtodoaqui.com http://www.dtodoaqui.com";

    }*/


    //$response->headers->set('Access-Control-Allow-Origin', "*");
    //$response->headers->set('Access-Control-Allow-Methods', "POST, GET");
    //$response->headers->set('Access-Control-Max-Age', "3600");
    //$response->headers->set('Access-Control-Allow-Headers', "Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


    /*$response->headers->set('Access-Control-Allow-Origin', $origin);
    $response->headers->set("Access-Control-Allow-Credentials", "true");
    //if($request->getMethod() == "OPTIONS") {


        //$response->headers->set("Cache-Control", "no-cache");
        //$response->headers->set("Access-Control-Allow-Methods", "GET, POST, OPTIONS");
        $response->headers->set("Access-Control-Allow-Headers", "Content-Type, Accept");
        //$response->headers->set("Access-Control-Max-Age", "1728000");
    */
    //}
});

$app->before(function (Request $request) use ($app) {
    if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
        $data = json_decode($request->getContent(), true);
        $request->request->replace(is_array($data) ? $data : array());
    }
});




$app->match('/timeline', function (Request $request) use ($app) {

    if ($request->isMethod("post") or true) {

        $jwt = $request->request->get('token');
        $uid = $request->request->get('uid');
        $page = $request->request->get('page');

        JWT::$leeway = 60;

        try {
            $decoded = JWT::decode($jwt, $app['jwt.secret'], array('HS256'));
            $decoded_array = (array)$decoded;
            $sql = "select * from timeline WHERE idusu = ? order by utime DESC LIMIT ".(($page-1)*10).",".($page*10);
            if (empty($uid)) {
                $u = $app['db']->fetchAll($sql, array($decoded_array['user']->idusuario));
            } else {
                $u = $app['db']->fetchAll($sql, array($uid));
            }

            $data = array('code' => 200, 'data' => $u);
            return $app->json($data);
        } catch (DomainException $e) {
            $data = array('code' => 501, 'data' => 'Hey! No hagas eso.');
            return $app->json($data);
        }
    }else{
        return 0;
    }
});


$app->match('/user/name', function (Request $request) use ($app) {


    if ($request->isMethod("post")) {
        $jwt = $request->request->get('token');
        $uid = $request->request->get('uid');

        JWT::$leeway = 60;

        try {
            $decoded = JWT::decode($jwt, $app['jwt.secret'], array('HS256'));
            $decoded_array = (array)$decoded;
            $sql = "SELECT idusuario, nombre,apellido, alias FROM usuarios WHERE idusuario = ?";
            if (empty($uid)) {
                $u = $app['db']->fetchAssoc($sql, array($decoded_array['user']->idusuario));
            } else {
                $u = $app['db']->fetchAssoc($sql, array($uid));
            }
            $data = array('code' => 200, 'data' => $u);
            return $app->json($data);
        } catch (DomainException $e) {
            $data = array('code' => 501, 'data' => 'Hey! No hagas eso.');
            return $app->json($data);
        }
    }else{
        return 0;
    }
});

$app->match('/user/info', function (Request $request) use ($app) {

    if ($request->isMethod("post")) {
        $jwt = $request->request->get('token');
        $uid = $request->request->get('uid');

        /*if(empty($jwt)){hoto
            $data = array('code' => 500, 'data' => 'Error en autheticacion');
            return $app->json($data);
            //$jwt = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9leGFtcGxlLm9yZyIsImF1ZCI6Imh0dHA6XC9cL2V4YW1wbGUuY29tIiwiaWF0IjoxMzU2OTk5NTI0LCJuYmYiOjEzNTcwMDAwMDAsInVzZXIiOnsiaWR1c3VhcmlvIjoiMTk5In19.ky98MtLdPj56JDnvjoZ_jgyJPxtNM9PuvxWAkf8C55E';
        }else{

        }*/

        JWT::$leeway = 60;

        try {
            $decoded = JWT::decode($jwt, $app['jwt.secret'], array('HS256'));
            $decoded_array = (array)$decoded;
            $sql = "SELECT u.*,i.idioma FROM usuarios u LEFT JOIN idiomas i
                ON u.ididioma = i.ididioma
                WHERE idusuario = ?";

            $count = "SELECT COUNT(*) as num FROM establecimientos WHERE idregistrante = ?";

            if (empty($uid)) {
                $u = $app['db']->fetchAssoc($sql, array($decoded_array['user']->idusuario));
                $c = $app['db']->fetchAssoc($count, array($decoded_array['user']->idusuario));

            } else {
                $u = $app['db']->fetchAssoc($sql, array($uid));
                $c = $app['db']->fetchAssoc($count, array($uid));
            }
            $data = array('code' => 200, 'data' => $u, 'count' => $c["num"]);
            return $app->json($data);
        } catch (DomainException $e) {
            $data = array('code' => 501, 'data' => 'Hey! No hagas eso.');
            return $app->json($data);
        }/*catch (Exception $e){
        $data = array('code' => 502, 'data' => $e->getMessage());
        return $app->json($data);
    }*/
    }else{
        return 0;
    }
});





$app->match('/user/register', function (Request $request) use ($app) {
    /*if($request->getMethod() !== 'POST'){
        $data = array('code' => 500, 'data' => 'Error en URL');
        return $app->json($data);
    }*/


    $fname = $request->request->get('first_name');
    $lname = $request->request->get('last_name');
    $email = $request->request->get('email');
    $pass = $request->request->get('pass');

    $sql = "SELECT idusuario FROM usuarios WHERE email = ?";
    $u = $app['db']->fetchAssoc($sql, array($email));

    //var_dump($u);
    if(!$u && !empty($email)){

        //$server = (($_SERVER['SERVER_PORT']==443)?'https':'http').'://'.apache_request_headers()['Host'];
	$server = "http://localhost";

        if($_SERVER['HTTP_HOST'] == "localhost"){
            $api = $server."/old_aqui/services/api";
        }else{
            $api = $server."/services/api";
        }


        $hash = password_hash($email,PASSWORD_DEFAULT);

        $s1 ="INSERT INTO
              usuarios (nombre,apellido,email,pass,ididioma,fecha_reg,hora_reg,estado,codvalida,verify)
                VALUES (?,?,?,?,1,DATE(NOW()),TIME(NOW()),0,?,0);
              );";
        $app['db']->executeQuery($s1,
            array($fname,$lname,$email,password_hash($pass,PASSWORD_DEFAULT), $hash ) );

        $last_id = $app['db']->lastInsertId();

        $data = array('code' => 200, 'data' => 'Hemos enviado un mensaje de confirmacion a su correo.');

        $message = \Swift_Message::newInstance()
            ->setSubject('Dtodoaqui  - Comfirmacion de cuenta '.$email)
            ->setFrom(array('noreply@dtodoaqui.com'))
            ->setTo(array($email))
            ->setBody($app['twig']->render('confirmation.html.twig',
                array('name'=>($fname." ".$lname),'code'=> substr($hash,7), 'uid' => $last_id, "api" =>$api)
            ), 'text/html');

        $app['mailer']->send($message);

        return $app->json($data);
    }elseif ($u) {
        $data = array('code' => 302, 'data' => 'Email ya registrado.');
        return $app->json($data);

    }else{
        $data = array('code' => 404, 'data' => 'Error.');
        return $app->json($data);
    }



});

$app->match('/est/reg', function (Request $request) use ($app) {


    if ($request->isMethod("post")) {
        //sleep(2);
        //$offset = $request->get('offset');
        //$page = $request->get('page');
        //if($offset == null ) $offset = 18;
        //if($page == null ) $page = 1;

        $jwt = $request->get('token');

        $type = $request->get('type');

        $user = $request->get('uid');
        if(empty($user)){
            $user = $request->get('user');
        }

        $sql ="";
        switch($type){
            case 'e': $sql = "SELECT * FROM establecimientos WHERE iddistribuidor = ?"; break;
            case 'r': $sql = "SELECT * FROM establecimientos WHERE idregistrante = ?"; break;
            case 'f': $sql = "SELECT e.* FROM favoritos f INNER JOIN establecimientos e
                                ON f.idestablecimiento = e.idestablecimiento
                                WHERE f.idusuario = ?"; break;
        }

        JWT::$leeway = 60;
        try {
            $decoded = JWT::decode($jwt, $app['jwt.secret'], array('HS256'));
            $decoded_array = (array)$decoded;
            $uid = $decoded_array['user']->idusuario;

            if(!empty($user)){
                $uid = $user;
            }

            $f = $app['db']->fetchAll($sql, array($uid));

            $d = array('count' => count($f),'est' => array_slice($f,0,3));
            $data = array('code' => 200, 'data' => $d);
            return $app->json($data);

        } catch (DomainException $e) {
            $data = array('code' => 501, 'data' => 'Hey! No hagas eso.');
            return $app->json($data);
        }
    }else{
        return 0;
    }



    /*$jwt = $request->request->get('token');
    if(empty($jwt)){
        $data = array('code' => 500, 'data' => 'Error en autheticacion');
        return $app->json($data);
        //$jwt = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9leGFtcGxlLm9yZyIsImF1ZCI6Imh0dHA6XC9cL2V4YW1wbGUuY29tIiwiaWF0IjoxMzU2OTk5NTI0LCJuYmYiOjEzNTcwMDAwMDAsInVzZXIiOnsiaWR1c3VhcmlvIjoiMTk5In19.ky98MtLdPj56JDnvjoZ_jgyJPxtNM9PuvxWAkf8C55E';
    }else{

    }

    JWT::$leeway = 60;

    try{
        $decoded = JWT::decode($jwt, $app['jwt.secret'], array('HS256'));
        $decoded_array = (array) $decoded;
        $sql = "SELECT * FROM establecimientos ORDER BY RAND()";
        $e = $app['db']->fetchAll($sql);//, array($decoded_array['user']->idusuario));
        $d = array('count' => count($e),'est' => array_slice($e,0,3));
        $data = array('code' => 200, 'data' => $d);
        return $app->json($data);
    }catch (DomainException $e){
        $data = array('code' => 501, 'data' => 'Hey! No hagas eso.');
        return $app->json($data);
    }*/
});

$app->match('/est/detail', function (Request $request) use ($app) {

    $jwt = $request->request->get('token');


    if(empty($jwt)){
        $data = array('code' => 500, 'data' => 'Error en autheticacion');
        return $app->json($data);
        //$jwt = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9leGFtcGxlLm9yZyIsImF1ZCI6Imh0dHA6XC9cL2V4YW1wbGUuY29tIiwiaWF0IjoxMzU2OTk5NTI0LCJuYmYiOjEzNTcwMDAwMDAsInVzZXIiOnsiaWR1c3VhcmlvIjoiMTk5In19.ky98MtLdPj56JDnvjoZ_jgyJPxtNM9PuvxWAkf8C55E';
    }else{

    }

    JWT::$leeway = 60;

    try{
        $decoded = JWT::decode($jwt, $app['jwt.secret'], array('HS256'));
        $decoded_array = (array) $decoded;
        $sql = "SELECT * FROM establecimientos WHERE idestablecimiento  = ?";
        $idest = $request->request->get('idest');
        $e = $app['db']->fetchAssoc($sql, array($idest));

        $sql2 = "SELECT * FROM ubigeo WHERE iddistrito  = ?";
        $c = $app['db']->fetchAssoc($sql2, array($e['iddistrito']));


        $data = array('code' => 200, 'data' => $e, 'ubi' => $c);
        return $app->json($data);
    }catch (DomainException $e){
        $data = array('code' => 501, 'data' => 'Hey! No hagas eso.');
        return $app->json($data);
    }/*catch (Exception $e){
        $data = array('code' => 502, 'data' => $e->getMessage());
        return $app->json($data);
    }*/
});





$app->post('/auth', function (Request $request) use ($app) {
    
    $email = $request->get('email');
    $pass = $request->get('pass');

    $sql = "SELECT pass, estado, verify, idusuario, CONCAT(nombre,' ',apellido) as name FROM usuarios WHERE email = ?";
    $u = $app['db']->fetchAssoc($sql, array($email));

    //return $app->json($email);

    if($u['verify'] > 2) {
        $data = array('code' => 500, 'data' => 'Tu cuenta esta desctivada te hemos enviado un mensaje de
        activacion.');

        $hash = password_hash($email,PASSWORD_DEFAULT);

        $s1 ="UPDATE
              usuarios SET codvalida = :hash WHERE email = :email";
        $app['db']->executeQuery($s1,
            array("hash" => $hash, "email" => $email ) );

        /*$message = \Swift_Message::newInstance()
            ->setSubject('Dtodoaqui  - Reactivacion de cuenta '.$email)
            ->setFrom(array('noreply@dtodoaqui.com'))
            ->setTo(array($email))
            ->setBody($app['twig']->render('reactivate.html.twig',
                array('name'=>$u['name'],'code'=> substr($hash,7), 'uid' => $u['idusuario'],
                    "api" => 'http://dtodoaqui.com/services/api')
            ), 'text/html');

        $app['mailer']->send($message);
        */

        return $app->json($data);
        //die();
    }
    if(!$u) {
        $data = array('code' => 500, 'data' => $email." + ".$pass);
    }elseif($u["estado"] == 0){
        $data = array('code' => 500, 'data' => 'Cuenta no confirmada, revise su email.');

    }else{



        if(!password_verify($pass,$u['pass'])&&!is_null($email)&&!is_null($pass)){
            $sq1l = "UPDATE usuarios set verify = verify + 1 WHERE email = ?";
            $app['db']->executeQuery($sq1l, array($email));
        }


        if(password_verify($pass,$u['pass'])){

            $sql = "SELECT idusuario, CONCAT(nombre,' ',apellido) as name FROM usuarios WHERE email = ?";
            $user = $app['db']->fetchAssoc($sql, array($email));
            //$data = array('code' => 200, 'data' => $user);

            $token = array(
                "iss" => "http://example.org",
                "aud" => "http://example.com",
                "iat" => 1356999524,
                "nbf" => 1357000000,
                "user" => $user
            );

            $jwt = JWT::encode($token, $app['jwt.secret']);
            $data = array('code' => 200, 'data' => $jwt, "uid" => $user['idusuario'], "name" => $user['name'] );
        }else{
            $data = array('code' => 500, 'data' => 'Contraseña incorrecta');
        }
    }
    return $app->json($data);
    //return print_r($request->request->get('email'));
});

$app->match('/authfb', function (Request $request) use ($app) {

    $email = $request->request->get('email');

    $code = password_hash($email,PASSWORD_DEFAULT);
    $fname = $request->request->get('first_name');
    $lname = $request->request->get('last_name');
    $gender = $request->request->get('gender');

    if($request->getMethod() !== 'POST'){
        $data = array('code' => 500, 'data' => 'Error en URL');
        return $app->json($data);
    }

    if(empty($email)||$email===""){

        $data = array('code' => 500, 'data' => 'Contraseña incorrecta');
        return $app->json($data);
    }

    $sql = "SELECT idusuario, CONCAT(nombre,' ',apellido) as name FROM usuarios WHERE email = ?";
    $u = $app['db']->fetchAssoc($sql, array($email));


    if(!$u){

        //$data = array('code' => 500, 'data' => 'Usuario no valido');
        $s1 ="INSERT INTO
              usuarios (nombre,apellido,email,genero,fecha_reg,hora_reg,estado,codvalida,verify)
                VALUES (?,?,?,?,DATE(NOW()),TIME(NOW()),1,?,1);
              );";

         $app['db']->executeQuery($s1,array($fname,$lname,$email,$gender,$code) );

        //$app['monolog']->addWarning($i);



        $token = array(
            "iss" => "http://example.org",
            "aud" => "http://example.com",
            "iat" => 1356999524,
            "nbf" => 1357000000,
            "user" => array("idusuario" => $app['db']->lastInsertId())
        );

        $jwt = JWT::encode($token, $app['jwt.secret']);
        $data = array('code' => 200, 'data' => $jwt, 'name' => $fname." ".$lname);


    }else{
        /*if(password_verify($pass,$u['pass'])){
            $sql = "SELECT idusuario FROM usuarios WHERE email = ?";
            $user = $app['db']->fetchAssoc($sql, array($email));
            //$data = array('code' => 200, 'data' => $user);*/

            $token = array(
                "iss" => "http://example.org",
                "aud" => "http://example.com",
                "iat" => 1356999524,
                "nbf" => 1357000000,
                "user" => $u
            );

            $jwt = JWT::encode($token, $app['jwt.secret']);
            $data = array('code' => 200, 'data' => $jwt, 'name' => $u['name']);
        /*}else{
            $data = array('code' => 500, 'data' => 'Contraseña incorrecta');
        }*/
    }
    return $app->json($data);
    //return print_r($request->request->get('email'));
});



$app->get('/whatever', function () use ($app) {
    $s = "SELECT
            palabras as k from palabras ORDER BY cont DESC";

    $c = $app['db']->fetchAll($s);

    foreach($c as $v){
        if($v['k'] != "") $r[] = $v['k'];
    }



    return $app->json($r);
});

$app->get('/wherever', function (Request $request) use ($app) {
    $q = $request->query->get('q');
    $s = 'select * from ubigeo
            where
            pais like ?
            or
            cuidad like ?
            or
            distrito like ?
            and (
              iddistrito is not null or
              idcuidad is not null or
              idpais is not null
            )

            LIMIT 10
        ';


    $c = $app['db']->fetchAll($s, array('%'.$q.'%','%'.$q.'%','%'.$q.'%'));


    foreach($c as $v){
        unset($text);

        //if($v['k'] != "") $r[] = $v['k'];

        if(strlen($v["distrito"]) > 0) $text[] = ''.$v["distrito"];
        if(strlen($v["cuidad"]) > 0) $text[] = ''.$v["cuidad"];
        if(strlen($v["pais"]) > 0) $text[] = ''.$v["pais"];

        $r[] = join(", ",$text);
    }
    $r = $c;

    return $app->json($r);
});

$app->match('/results', function (Request $request) use ($app) {

    if ($request->isMethod("post")) {
        $ipa = $request->request->get('ipa');
        $ici = $request->request->get('ici');
        $idi = $request->request->get('idi');

        $what = $request->request->get('wa');


        $s = "

            SELECT DISTINCT e.* FROM establecimientos e INNER JOIN
              cate_local cl ON cl.idestablecimiento = e.idestablecimiento
              INNER JOIN subcategorias sc  ON sc.idsubcategoria = cl.idsubcategoria
              INNER JOIN categorias c ON c.idcategoria = cl.idcategoria

            WHERE

            idpais = :ip
            and
            idciudad = :ic
            and
            iddistrito = :id

            AND (
            e.palabras_clave LIKE :what
            OR sc.subcategoria LIKE :what
            OR c.categoria LIKE :what
            OR e.nom_comercial LIKE :what
            )
            ORDER BY  IF(e.total_votos>0, (e.votos/e.total_votos) , total_votos ) DESC , e.idestablecimiento DESC, e.nom_comercial DESC

            LIMIT 10

        ";

        $idd = 'SELECT * from distritos WHERE iddistrito = ? ';


        $dis = $app['db']->fetchAll($idd, array($idi));

        $coords = explode(",", $dis[0]['geoloca']);
        $coords[0] = floatval($coords[0]);
        $coords[1] = floatval($coords[1]);

        $zoom = $dis[0]['zoom'];
        $r = $app['db']->fetchAll($s, array("ip"=>$ipa,"ic"=> $ici,"id"=> $idi,"what"=>"%".$what."%"));


        return $app->json(array("center" => $coords,"zoom" => $zoom, "results" => $r));
    }else{
        return 0;
    }
});

$app->get('/public/cat', function () use ($app) {
    $s = "SELECT
            count(e.idestablecimiento) as cant,
            c.idcategoria as id,
            c.categoria as cat,
            c.imagen as img
            FROM categorias c
            inner join cate_local l on c.idcategoria = l.idcategoria
            inner join establecimientos e on e.idestablecimiento = l.idestablecimiento
            group by (c.idcategoria)
            order by c.idcategoria DESC";

    $c = $app['db']->fetchAll($s);
    return $app->json($c);
});

$app->match('/public/ubicacion', function () use ($app) {
    $s = "SELECT CONCAT(p.pais,', ', c.ciudad) as value,
  CONCAT(p.pais,', ', c.ciudad) as label FROM
              ciudades c INNER JOIN paises p
            ON c.idpais = p.idpais";

    $c = $app['db']->fetchAll($s);
    return $app->json($c);
});


$app->get('/public/est', function (Request $request) use ($app) {

    //sleep(3);

    $s = "SELECT
            DISTINCT
            e.idestablecimiento as  id,
            e.nom_comercial as est,
            e.direccion as dir,
            e.foto as img
            FROM categorias c
            inner join cate_local l on c.idcategoria = l.idcategoria
            inner join establecimientos e on e.idestablecimiento = l.idestablecimiento
            WHERE c.idcategoria = ? LIMIT 8";
    $cat = $request->query->get('cat');
    if(empty($cat)) $cat = 21;
    $e = $app['db']->fetchAll($s, array($cat));
    return $app->json($e);
});

$app->get('/categories', function (Request $request) use ($app) {



    if(is_null($request->get("idest"))){


        $s = "(SELECT cat.idcategoria, '' as subcategoria, categoria as des  FROM categorias cat)
            UNION
            (SELECT sc.idcategoria, sc.idsubcategoria, subcategoria as des
            FROM subcategorias sc INNER JOIN categorias c
            ON sc.idcategoria = c.idcategoria
            )
            ORDER BY idcategoria ASC, subcategoria ASC";

        $e = $app['db']->fetchAll($s);

    }else{
        $s2 = "SELECT sc.idcategoria, sc.idsubcategoria as subcategoria, subcategoria as des
            FROM subcategorias sc INNER JOIN categorias c
            ON sc.idcategoria = c.idcategoria
              INNER JOIN cate_local cl ON cl.idsubcategoria = sc.idsubcategoria AND cl.idestablecimiento = ?";

        $e = $app['db']->fetchAll($s2, array($request->get("idest")));
    }


    /*$s2 = "SELECT sc.idcategoria, sc.idsubcategoria, subcategoria as des
            FROM subcategorias sc INNER JOIN categorias c
            ON sc.idcategoria = c.idcategoria
              INNER JOIN cate_local cl ON cl.idsubcategoria = sc.idsubcategoria AND cl.idestablecimiento = ";

    $e2 = $app['db']->fetchAll($s2);
*/



    foreach($e as $v){

        if(strlen($v["subcategoria"]) > 0 ){

            $r[] = array('label' => $v["des"], "value" => $v["subcategoria"], "disabled" => false);

        }else{

            $r[] = array('label' => $v["des"], "value" => $v["des"], "disabled" => true);
        }

    }
    //$r = $e;

    return $app->json($r);
});

$app->get('/ubigeo', function (Request $request) use ($app) {

    $type = $request->query->get('type');
    $param = $request->query->get('param');

    switch($type){
        case 1: $s = "SELECT idpais as value, pais as label FROM paises";
            $e = $app['db']->fetchAll($s); break;
        case 2: $s = "SELECT idciudad as value, ciudad as label FROM ciudades WHERE idpais = ?";
            $e = $app['db']->fetchAll($s, array($param)); break;
        case 3: $s = "SELECT iddistrito as value, distrito as label FROM distritos WHERE idciudad = ?";
            $e = $app['db']->fetchAll($s, array($param)); break;
    }

    return $app->json($e);
});


$app->get('/public/act', function (Request $request) use ($app) {
    $u = $request->query->get('u');
    $limit = 4;
    if(empty( $u )){
        $u = 0;
    }


    $s1 ="SELECT
    a.utime as u,
    a.tipo as tipo,
    a.est as est,
    a.texto as texto,
    a.nombre,
    a.apellido,
    a.foto
    FROM activities a
    WHERE a.utime > ?
    ORDER BY a.utime DESC
    LIMIT $limit
     ";

    try{
        //echo $s1."<br />";
        //echo $u;
        $a = $app['db']->fetchAll($s1, array($u));

    }catch(DBALException $e){
        echo "<pre>";
        echo $e->getTraceAsString();
        echo "</pre>";
        return '';
    }

    //var_dump($a);
    return $app->json($a);
    /*return $app['twig']->render('confirmation.html.twig', array(
        'name' => "tineo",
        'code' => "t2343423ineo",
    ));*/
});

$app->get('/public/ins', function () use ($app) {

    $now = new DateTime();

    $s1 ="INSERT INTO consejos (idusuario, idestablecimiento, consejo, fecha, hora, estado)VALUES (
          '17', '4', CONCAT('consejo -> ',NOW()), DATE(NOW()), TIME(NOW()), '1'

      );
     ";
    //echo $s1;

    $a = $app['db']->executeQuery($s1);

    return $app->json($a);
});

$app->get('/public/ims', function () use ($app) {

    $now = new DateTime();

    $s1 ="INSERT INTO comentarios (idusuario, idestablecimiento, comentario, fecha, hora, estado)VALUES (
            '17', '4', CONCAT('consejo -> ',NOW()), DATE(NOW()), TIME(NOW()), '1');
     ";
    //echo $s1;

    $a = $app['db']->executeQuery($s1);

    return $app->json($a);
});



$app->get('/public/verify', function (Request $request) use ($app) {
    $code = $request->get('code');
    $uid = $request->get('uid');

    $sql = "SELECT email, verify FROM usuarios WHERE idusuario = ?";
    $a = $app['db']->fetchAll($sql,array($uid));
    $email = $a[0]["email"];

    //var_dump($a[0]["verify"]);

    if($a[0]["verify"] == 1){
        $app['session']->getFlashBag()->add('message', 'Ya esta confirmada la cuenta '.$email."");
    }else{
        if(password_verify($a[0]["email"],"$2y$10$". $code)){
            $s1 ="UPDATE usuarios SET estado=1, verify=0 WHERE idusuario = ?";
            $app['db']->executeQuery($s1,array($uid));
            $app['session']->getFlashBag()->add('message', 'Se ha confirmado la cuenta '.$email."");
            //$app->redirect('/here', 301)
            //return new RedirectResponse('http://localhost:3000/#/');
        }else{
            $app['session']->getFlashBag()->add('message', 'Error en el codigo de comfirmacion para '.$email."");
        }
    }

    $server = (($_SERVER['SERVER_PORT']==443)?'https':'http').'://hlocalhost';

    if($_SERVER['HTTP_HOST'] == "localhost"){
        $url = 'http://localhost:3000/#/';
    }else{
        $url = $server;
    }
    return new RedirectResponse($url);


    //return $uid." $2y$10$". $code;
    /*return $app['twig']->render('confirmation.html.twig', array(
        'name' => "tineo",
        'code' => "t2343423ineo",
    ));*/
});


$app->get('/public/fmessage', function (Request $request) use ($app) {
    $message = $app["session"]->getFlashBag()->all();
    return $app->json($message);
});

$app->get('/user/verify', function (Request $request) use ($app) {

    return $app['twig']->render('confirmation.html.twig', array(
        'name' => "tineo",
        'code' => "t2343423ineo",
    ));
});

$app->match('/public/recovery', function (Request $request) use ($app) {
    $email = $request->request->get("email");
    $hash = password_hash($email,PASSWORD_DEFAULT);

    if($_SERVER['HTTP_HOST'] == "localhost"){
        $api = "http://localhost/old_aqui/services/api";
    }else{
        $api = "http://dtodoaqui.com/services/api";
    }

    $sql = "SELECT CONCAT(nombre,' ',apellido) as name, idusuario as uid, email FROM usuarios WHERE email = ?";
    $a = $app['db']->fetchAssoc($sql,array($email));




    if($a){
        $uid = $a['uid'];
        $message = \Swift_Message::newInstance()
            ->setSubject('Dtodoaqui  - Reestablecer Datos de Acceso a '.$email)
            ->setFrom(array('noreply@dtodoaqui.com'))
            ->setTo(array($email))
            ->setBody($app['twig']->render('recovery.html.twig',
                array('name'=>$a["name"],'code'=> substr($hash,7), 'uid' => $uid, "api" =>$api, "email" => $email)
            ), 'text/html');

        $app['mailer']->send($message);
        $j = array("code" => 200, "msg" => "Email encontrado", "email" => $email);
    }else{
        $j = array("code" => 409, "msg" => "Email no registrado");
    }

    return $app->json($j);
});

$app->get('/public/hiroshima', function (Request $request) use ($app) {
    $code = $request->get('code');
    $uid = $request->get('uid');

    $sql = "SELECT email, verify FROM usuarios WHERE idusuario = ?";
    $a = $app['db']->fetchAll($sql,array($uid));
    $email = $a[0]["email"];
    //var_dump($a[0]["verify"]);
        if(password_verify($a[0]["email"],"$2y$10$". $code)){
            $s1 ="UPDATE usuarios SET verify= 0, estado=0 WHERE idusuario = ?";
            $app['db']->executeQuery($s1,array($uid));
            $app['session']->getFlashBag()->add('recovery', array("u" => $email,"c" => $code));
            //$app->redirect('/here', 301)
            //return new RedirectResponse('http://localhost:3000/#/');
        }else{
            $app['session']->getFlashBag()->add('recovery', array("u" => $email,"c" => $code));
        }


    //return $uid." $2y$10$". $code;

    $server = (($_SERVER['SERVER_PORT']==443)?'https':'http').'://localhost';

    if($_SERVER['HTTP_HOST'] == "localhost"){
        $url = 'http://localhost:3000/#/';
    }else{
        $url = $server;
    }
    return new RedirectResponse($url);
});

$app->match('/public/nagasaki', function (Request $request) use ($app) {

    $code = $request->request->get('code');
    $email = $request->request->get('email');
    $pass = $request->request->get('pass');

    $hash = password_hash($pass, PASSWORD_DEFAULT);

    $sql = "SELECT email, verify FROM usuarios WHERE email = ?";
    $a = $app['db']->fetchAll($sql,array($email));
    //$email = $a[0]["email"];

    //var_dump($a);

    if(!(count($a)>0)){
        $data = array("code" => 409, "msg" => "Ese email no existe :v");
    }else{
        if(password_verify($a[0]["email"],"$2y$10$". $code)){
            $s1 ="UPDATE usuarios SET pass = ?, estado = 1 WHERE email = ?";
            $app['db']->executeQuery($s1,array($hash, $email));

            $data = array("code" => 200, "msg" => "Contraseña reestablecida.");
            //$app->redirect('/here', 301)
            //return new RedirectResponse('http://localhost:3000/#/');
        }else{
            $data = array("code" => 501, "msg" => "Data corrupta.");
        }
    }
    //var_dump($data);

    return $app->json($data);

});


$app->match('/public/invitation', function (Request $request) use ($app) {

    if ($request->isMethod("post")) {
        $email =  $request->get('email');
        if(is_null($email)) $email = "itsudatte01@gmail.com";
        $jwt = $request->get('token');
        JWT::$leeway = 60;
        try{
            $decoded = JWT::decode($jwt, $app['jwt.secret'], array('HS256'));
            $decoded_array = (array)$decoded;
            $uid = $decoded_array['user']->idusuario;
            $sql = "SELECT nombre, apellido
                FROM usuarios WHERE idusuario = ?";

            $f = $app['db']->fetchAssoc($sql, array($uid));



        }catch (DomainException $e) {
            $data = array('code' => 501, 'data' => 'Hey! No hagas eso.');
            return $app->json($data);
        }


        $message = \Swift_Message::newInstance()
            ->setSubject('Dtodoaqui  - Invitacion de '.$f["nombre"]." ".$f["apellido"])
            ->setFrom(array('noreply@dtodoaqui.com'))
            ->setTo(array($email))
            ->setBody($app['twig']->render('invitation.html.twig',
                array( 'name' => $f["nombre"]." ".$f["apellido"], 'email'=> $email)
            ), 'text/html');



        $app['mailer']->send($message);

        $data = array('code' => 200, 'data' => 'Enviado');
        return $app->json($data);




    }else{
        return 0;
    }


});


$app->match('/user/friends', function (Request $request) use ($app) {

    if ($request->isMethod("post")) {

        $jwt = $request->get('token');
        $user = $request->get('user');
        JWT::$leeway = 60;
        try {
            $decoded = JWT::decode($jwt, $app['jwt.secret'], array('HS256'));
            $decoded_array = (array)$decoded;
            $uid = $decoded_array['user']->idusuario;
            $sql = "SELECT u.idusuario, u.nombre, u.apellido, u.foto
                FROM amigos a INNER JOIN usuarios u
                    ON u.idusuario = a.idamigo  AND a.idusuario = ?
                WHERE a.estado = 'A'
                UNION
                SELECT u.idusuario, u.nombre, u.apellido, u.foto
                FROM amigos a INNER JOIN usuarios u
                    ON u.idusuario = a.idusuario AND a.idamigo = ?
                WHERE a.estado = 'A'";

            if(empty($user)) {
                $f = $app['db']->fetchAll($sql, array($uid, $uid));
            }else{
                $f = $app['db']->fetchAll($sql, array($user, $user));
            }
            $d = array('count' => count($f), 'fri' => array_slice($f, 0, 3));
            $data = array('code' => 200, 'data' => $d);
            return $app->json($data);
        } catch (DomainException $e) {
            $data = array('code' => 501, 'data' => 'Hey! No hagas eso.');
            return $app->json($data);
        }
    }else{
        return 0;
    }
});

$app->match('/user/allfriends', function (Request $request) use ($app) {

    if ($request->isMethod("post")) {

        $jwt = $request->get('token');
        $user = $request->get('user');
        JWT::$leeway = 60;
        try {
            $decoded = JWT::decode($jwt, $app['jwt.secret'], array('HS256'));
            $decoded_array = (array)$decoded;
            $uid = $decoded_array['user']->idusuario;
            $sql = "SELECT u.idusuario, u.nombre, u.apellido, u.foto
                FROM usuarios u LIMIT 10";

            if(empty($user)) {
                $f = $app['db']->fetchAll($sql, array($uid, $uid));
            }else{
                $f = $app['db']->fetchAll($sql, array($user, $user));
            }
            $d = array('count' => count($f), 'fri' => array_slice($f, 0, 3));
            $data = array('code' => 200, 'data' => $d);
            return $app->json($data);
        } catch (DomainException $e) {
            $data = array('code' => 501, 'data' => 'Hey! No hagas eso.');
            return $app->json($data);
        }
    }else{
        return 0;
    }
});


$app->match('/user/friends/request', function (Request $request) use ($app) {

    if ($request->isMethod("post")) {

        $jwt = $request->get('token');
        $user = $request->get('user');
        $type = $request->get('type');
        JWT::$leeway = 60;
        try {
            $decoded = JWT::decode($jwt, $app['jwt.secret'], array('HS256'));
            $decoded_array = (array)$decoded;
            $uid = $decoded_array['user']->idusuario;
            $sql = "SELECT a.*
                FROM amigos a
                WHERE a.estado = 'A' OR a.estado = 'P'
                      AND (a.idusuario = :profile AND a.idamigo = :uid
                      OR a.idusuario = :uid  AND a.idusuario = :profile)";


            $f = $app['db']->fetchAll($sql, array("uid" => $uid,"profile" => $user));

            //if($f != false){

                if($type=="F"){
                    $sql = "DELETE FROM amigos WHERE  ((idusuario = :profile AND idamigo = :uid)
                      OR (idusuario = :uid  AND idamigo = :profile))";

                    //echo $sql;
                    $app['db']->executeQuery($sql, array("uid" => $uid,"profile" => $user));
                }

            //}else{

                if($type=="NF"){
                    $sql = "INSERT INTO amigos VALUES (NULL, :uid, :profile, CURDATE(),CURTIME(), NULL , NULL ,NULL ,'P')";
                    $app['db']->executeQuery($sql, array("uid" => $uid,"profile" => $user));

                }

            if($type=="B"){
                $sql = "UPDATE amigos SET estado ='A' WHERE idusuario = :profile AND idamigo = :uid";
                $app['db']->executeQuery($sql, array("uid" => $uid,"profile" => $user));

            }

            if($type=="K"){
                $sql = "DELETE FROM amigos WHERE estado = 'P'
                      AND ((idusuario = :profile AND idamigo = :uid)
                      OR (idusuario = :uid  AND idamigo = :profile))";

                //echo $sql;
                $app['db']->executeQuery($sql, array("uid" => $uid,"profile" => $user));
            }
            if($type=="S"){
                $sql = "DELETE FROM amigos WHERE estado = 'P'
                      AND ((idusuario = :profile AND idamigo = :uid)
                      OR (idusuario = :uid  AND idamigo = :profile))";

                //echo $sql;
                $app['db']->executeQuery($sql, array("uid" => $uid,"profile" => $user));
            }

            //}




            $data = array('code' => 200);
            return $app->json($data);
        } catch (DomainException $e) {
            $data = array('code' => 501);
            return $app->json($data);
        }
    }else{
        return 0;
    }
});



$app->get('/', function (Request $request) use ($app) {
    $post = array(
        'title' => $request->request->get('title'),
        'body'  => $request->request->get('body'),
    );


    $token = array(
        "iss" => "http://example.org",
        "aud" => "http://example.com",
        "iat" => 1356999524,
        "nbf" => 1357000000
    );

    $jwt = JWT::encode($token, $app['jwt.secret']);
    $decoded = JWT::decode($jwt, $app['jwt.secret'], array('HS256'));

    //print_r($jwt);

    $decoded_array = (array) $decoded;

    JWT::$leeway = 60; // $leeway in seconds
    $decoded = JWT::decode($jwt, $app['jwt.secret'], array('HS256'));

    //$post['id'] = createPost($post);

    //return $app->json($post, 201);
    //return $app->json(apache_request_headers()['Host'], 201);
    //return $app->json($_SERVER['SERVER_PORT'], 201);

    $server = (($_SERVER['SERVER_PORT']==443)?'https':'http').'://localhost';
    return $app->json($server, 201);
})
->bind('homepage')
;


$app->match('/photo', function (Request $request) use ($app) {
    //echo $app['app.files_path'].'/../files/cat.jpg';
    if (!file_exists( $app['app.files_path'].'/cat.jpg')) {
        //echo $app['app.files_path'].'/../files/cat.jpg';
        $app->abort(404);
    }

    $file =  $app->sendFile($app['app.files_path'].'/cat.jpg');
    return $file;
    //return new Response($file,304,array("Cache-Control" => 's-maxage=30'));
});

$app->match('/profile', function (Request $request) use ($app) {

    if ($request->isMethod("post")) {

        $jwt = $request->get('token');
        $user = $request->get('user');
        JWT::$leeway = 60;


        try {
            $decoded = JWT::decode($jwt, $app['jwt.secret'], array('HS256'));
            $decoded_array = (array)$decoded;
            $uid = $decoded_array['user']->idusuario;

            $state = 0;
            if(!empty($user)) {

                $sql = "SELECT a.*
                FROM amigos a
                WHERE estado = 'A' AND (
                  (a.idusuario = :uid AND a.idamigo = :profile_id) OR
                  (a.idamigo = :uid AND a.idusuario = :profile_id)
                )";
                $a = $app['db']->fetchAssoc($sql,
                    array("uid" => $uid,
                            "profile_id" => $user));


                //var_dump($a);

                if($a){
                    $state = "F";
                }else{


                    $sql = "SELECT a.*
                FROM amigos a
                WHERE estado = 'P' AND
                 (a.idusuario = :uid AND a.idamigo = :profile_id)";
                    $s = $app['db']->fetchAssoc($sql,
                        array("uid" => $uid,
                            "profile_id" => $user));


                    if($s != false){
                        $state = "S";
                    }

                    $sql = "SELECT a.*
                        FROM amigos a
                        WHERE estado = 'P' AND
                         ( a.idamigo = :uid AND a.idusuario = :profile_id)";
                    $l = $app['db']->fetchAssoc($sql,
                        array("uid" => $uid,
                            "profile_id" => $user));
                    if($l != false){
                        $state = "L";
                    }

                    if(!$state){
                        $state = "NF";
                    }




                }


            }else{
                $sql = "SELECT COUNT(*) as sol
                FROM amigos a
                WHERE a.idamigo = ? AND a.estado = 'P' ";
                $a = $app['db']->fetchAssoc($sql, array($uid));
                $state = $a['sol'];
            }

            //data
            if(!empty($user)) {
                $sql = "SELECT u.idusuario as cod, CONCAT(u.nombre,' ',u.apellido) as name, u.foto,
                            u.facebook as fb
                    FROM usuarios u WHERE u.idusuario = ?";
                $us = $app['db']->fetchAll($sql, array($user));
            }else{
                    $sql = "SELECT u.idusuario as cod,
                            CONCAT(u.nombre,' ',u.apellido) as name,
                            u.foto,
                            u.facebook as fb
                  FROM amigos a INNER JOIN usuarios u
                  ON u.idusuario = a.idusuario
                  WHERE a.idamigo = ? AND a.estado = 'P'";
                    $us = $app['db']->fetchAll($sql, array($uid));
            }

            $sql = "SELECT u.foto,
                 CONCAT(u.nombre,' ',u.apellido) as fname, u.facebook as fb
                FROM  usuarios u
                WHERE u.idusuario = ?";

            if(empty($user)) {
                $f = $app['db']->fetchAssoc($sql, array($uid));
            }else{
                $f = $app['db']->fetchAssoc($sql, array($user));
            }
            //var_dump($f);
            //return 0;
            if(is_null($f['foto'])){
                return $app->json(array('image_name' => 'noprofile.jpg','name' => $f['fname'],'fb' => $f['fb'],'status' => $state, 'data' => $us));
            }
            if (!file_exists( $app['app.files_path'].'/'.$f['foto'])) {
                return $app->json(array('image_name' => 'noprofile.jpg','name' => $f['fname'],'fb' => $f['fb'],'status' => $state, 'data' => $us));
            }
            return $app->json(array('image_name' => $f['foto'],'name' => $f['fname'],'fb' => $f['fb'],'status' => $state, 'data' => $us));
        } catch (DomainException $e) {

        }
    }else{
        return 0;
    }




});

$app->match('/marker', function (Request $request) use ($app) {
    if (!file_exists($app['app.files_path'].'/mkr.png')) {
        echo $app['app.files_path'].'/mkr.png';
        $app->abort(404);
    }

    $file =  $app->sendFile($app['app.files_path'].'/mkr.png');
    return $file;
    //return new Response($file,304,array("Cache-Control" => 's-maxage=30'));
});

$app->match('/upload2', function (Request $request) use ($app) {
    $app['debug'] = true;
    //var_dump($request);
    if ($request->isMethod("post")) {

        $jwt = $request->get('token');
        $mode = $request->get('mode');

        JWT::$leeway = 60;

        try{
            $decoded = JWT::decode($jwt, $app['jwt.secret'], array('HS256'));
            $decoded_array = (array)$decoded;
            $uid = $decoded_array['user']->idusuario;
        }catch (DomainException $e) {


        }


        $flag = ($mode=="admin")?1:0;

        //echo "<pre>";
        $file = $request->files->all();

        $rows = 0;
        foreach ($file as $k => $v) {
            $ok = true;

            //var_dump($k);
            //var_dump($v);

            //var_dump($v->getClientOriginalName());
            //var_dump($v->getClientSize());
            //var_dump($v->getClientPathName());

            //var_dump($v->getRealPath());

            //http://php.net/manual/es/function.com-create-guid.php#117893
            $data = openssl_random_pseudo_bytes(16);
            $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
            $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10
            $hash =  vsprintf('%s%s%s%s%s%s%s%s', str_split(bin2hex($data), 4));


            $est = $request->get('idest');

            //echo $hash."\n";
            //echo $est;
            //echo $uid;

            if (exif_imagetype($v->getRealPath()) == IMAGETYPE_JPEG) {
                $ext =  '.jpg';
            }elseif(exif_imagetype($v->getRealPath()) == IMAGETYPE_PNG){
                $ext = '.png';
            }elseif(exif_imagetype($v->getRealPath()) == IMAGETYPE_GIF){
                $ext =  '.gif';
            }else{
                $ok = false;
            }

            $filename = $hash."_n".$ext;


            if($ok) {

                $v->move($app['app.files_path'], $filename);
                //echo $filename;
                $tuple[] = "(NULL,{$flag},{$est},{$uid},'{$filename}',CURDATE(), CURTIME())";
                $rows++;
                //echo $filename;
            }
        }


        //http://stackoverflow.com/a/2098689/992594
        $sql = "INSERT INTO fotos VALUES ".join(",",$tuple);

        $app['db']->executeQuery($sql);

        //echo "</pre>";
        return count($tuple);
    }else{
        return 0;
    }
    //$name = $file->getClientOriginalName();
    //return $app->json($name, 201);
    //return new Response($name);

});

$app->match('/est/gallery', function (Request $request) use ($app) {

    if ($request->isMethod("post")) {
        $jwt = $request->get('token');
        JWT::$leeway = 60;

        try {
            $decoded = JWT::decode($jwt, $app['jwt.secret'], array('HS256'));
            $decoded_array = (array)$decoded;
            $uid = $decoded_array['user']->idusuario;

            $est = $request->get('idest');

            $s = "SELECT iddistribuidor as admin FROM establecimientos
            WHERE idestablecimiento = ?";
            $a = $app['db']->fetchAssoc($s,array($est));

            if($a['admin'] == $uid){
                $type = 'A';
            }else{
                $type = 'NA';
            }


            $s = "SELECT f.id,
                f.foto as name,
                f.idusuario,CONCAT(u.nombre,' ',u.apellido) as fname
                FROM fotos f
                INNER JOIN usuarios u ON u.idusuario = f.idusuario
                WHERE f.idestablecimiento = ?
                AND f.idusuario = ?
                AND flag = 0
                ORDER BY f.fecha DESC, f.hora DESC LIMIT 10";


            $me = $app['db']->fetchAll($s, array($est, $uid));

            $s = "SELECT f.id,
                f.foto as name,
                f.idusuario,CONCAT(u.nombre,' ',u.apellido) as fname
                FROM fotos f
                INNER JOIN usuarios u ON u.idusuario = f.idusuario
                WHERE f.idestablecimiento = ?
                AND f.idusuario != ?
                AND flag = 0
                ORDER BY fecha DESC, hora DESC LIMIT 10";


            $public = $app['db']->fetchAll($s, array($est, $uid));


            $s = "SELECT f.id,
                f.foto as name,
                f.idusuario,
                CONCAT(u.nombre,' ',u.apellido) as fname
                FROM fotos f
                INNER JOIN usuarios u ON u.idusuario = f.idusuario
                WHERE f.idestablecimiento = ?
                AND f.idusuario = ? AND flag = 1
                ORDER BY f.fecha DESC, f.hora DESC LIMIT 10";

            $s ="SELECT f.id,
                f.foto as name,
                f.idusuario,
                es.nom_comercial as fname
                FROM fotos f
                INNER JOIN usuarios u ON u.idusuario = f.idusuario
                INNER JOIN establecimientos es ON es.idestablecimiento = f.idestablecimiento
                WHERE f.idestablecimiento = ?
                AND f.idusuario = ? AND f.flag = 1
                ORDER BY f.fecha DESC, f.hora DESC LIMIT 10";


            $admin = $app['db']->fetchAll($s, array($est, $a['admin']));




            return $app->json(
                array("type" => $type,
                        "me" => $me,
                        "public" => $public,
                        "admin" => $admin,
                )
            );
        }catch (DomainException $e) {

        }
    }else{
        return 0;
    }
});


$app->match('/est/update', function (Request $request) use ($app) {

    $idest = $request->get('idest');

    $direccion =(is_null($request->get('direccion')) or ($request->get('nom_comercial')=="null") )?'':$request->get('direccion');
    $nom_comercial =(is_null($request->get('nom_comercial')) or ($request->get('nom_comercial')=="null"))?'':$request->get('nom_comercial');
    $telefono = (is_null($request->get('telefono'))?'':$request->get('telefono'));

    $email =  (is_null($request->get('email')) or ($request->get('email')=="null"))?'':$request->get('email');
    $pag_web =  (is_null($request->get('pag_web')) or ($request->get('pag_web')=="null"))?'':$request->get('pag_web');

    $facebook = (is_null($request->get('facebook')) or ($request->get('facebook')=="null"))?'':$request->get('facebook');
    $twitter = (is_null($request->get('twitter')) or ($request->get('twitter')=="null"))?'':$request->get('twitter');
    $skype = (is_null($request->get('skype')) or ($request->get('skype')=="null"))?'':$request->get('skype');
    $google = (is_null($request->get('google')) or ($request->get('google')=="null"))?'':$request->get('google');

    $tarjeta_credito = (is_null($request->get('tarjeta_credito')) or ($request->get('tarjeta_credito')=="null"))?'':$request->get('tarjeta_credito');
    $delivery = (is_null($request->get('delivery')) or ($request->get('delivery')=="null"))?'':$request->get('delivery');
    $wifi =  (is_null($request->get('wifi')) or ($request->get('wifi')=="null"))?'':$request->get('wifi');

    $raz_social = (is_null($request->get('raz_social')) or ($request->get('raz_social')=="null"))?'':$request->get('raz_social');
    $ruc =  (is_null($request->get('ruc')) or ($request->get('ruc')=="null"))?'':$request->get('ruc');
    $nom_registrante = (is_null($request->get('nom_registrante')) or ($request->get('nom_registrante')=="null"))?'':$request->get('nom_registrante');

    $descripcion = (is_null($request->get('descripcion')) or ($request->get('descripcion')=="null"))?'':$request->get('descripcion');
    $palabras_clave =  (is_null($request->get('palabras_clave')) or ($request->get('palabras_clave')=="null"))?'':$request->get('palabras_clave');

    $cate = $request->get('categorias');
    //echo $cate;
    $categorias = explode(",", $cate);

    //$dispo = $request->get('dispo');


    $horario_aten_ini = (is_null($request->get('horario_aten_ini')) or ($request->get('horario_aten_ini')=="null"))?'00:00:00':$request->get('horario_aten_ini');
    $horario_aten_fin = (is_null($request->get('horario_aten_fin')) or ($request->get('horario_aten_fin')=="null"))?'00:00:00':$request->get('horario_aten_fin');



    $jwt = $request->get('token');
    JWT::$leeway = 60;
    try {
        $decoded = JWT::decode($jwt, $app['jwt.secret'], array('HS256'));
        $decoded_array = (array)$decoded;
        $uid = $decoded_array['user']->idusuario;

    } catch (DomainException $e) {
        return 2;
    }

   /* echo "<pre>";
    echo $tarjeta_credito."\n";
    echo $wifi."\n";
    echo $delivery."\n";
    echo "</pre>";*/

    $s1 ="UPDATE establecimientos
            SET
            direccion = :direccion,
            nom_comercial = :nom_comercial,
            telefono = :telefono,
            pag_web = :pag_web,
            email = :email,
            facebook = :facebook,

            twitter = :twitter,
            skype = :skype,
            google = :google,
            tarjeta_credito = :tarjeta_credito,
            delivery = :delivery,
            wifi = :wifi,
            raz_social = :raz_social,
            nom_registrante = :nom_registrante,
            ruc = :ruc,
            descripcion = :descripcion,
            palabras_clave = :palabras_clave,
            horario_aten_ini = :horario_aten_ini,
            horario_aten_fin = :horario_aten_fin

            WHERE idestablecimiento = :idest
              ";




    try{
        $app['db']->executeUpdate($s1,
            array(
                'direccion' => $direccion,
                'nom_comercial' => $nom_comercial,
                'telefono' => $telefono,
                'pag_web' => $pag_web,
                'email' => $email,
                'facebook' => $facebook,

                'twitter' => $twitter,
                'skype' => $skype,
                'google' => $google,
                'tarjeta_credito' => $tarjeta_credito,
                'delivery' => $delivery,
                'wifi' => $wifi,
                'raz_social' => $raz_social,
                'nom_registrante' => $nom_registrante,
                'ruc' => $ruc,
                'descripcion' => $descripcion,
                'palabras_clave' => $palabras_clave,
                'horario_aten_ini' => $horario_aten_ini,
                'horario_aten_fin' => $horario_aten_fin,


                'idest' => $idest



            ) );
    }catch(DBALException $e){
        echo "<pre>";
        echo $e->getTraceAsString();
        echo "</pre>";
    }


    //echo $app['db']->getSql();

    //echo "<br/><br/><br/>Last ID: ".$app['db']->lastInsertId();
    //$last_id = $app['db']->lastInsertId();

    $file = $request->files->all();
    // could throw a Symfony\Component\HttpFoundation\File\Exception\FileException
    // will overwrite existing file
    foreach($file as $k => $v){
        if (exif_imagetype($v->getRealPath()) == IMAGETYPE_JPEG) {
            $ext =  '.jpg';
        }elseif(exif_imagetype($v->getRealPath()) == IMAGETYPE_PNG){
            $ext = '.png';
        }elseif(exif_imagetype($v->getRealPath()) == IMAGETYPE_GIF){
            $ext =  '.gif';
        }



        $v->move($app['app.files_path'], $idest.$ext);


        $u = "UPDATE establecimientos SET foto = ? WHERE idestablecimiento = ?";
        $app['db']->executeQuery($u,array($idest.$ext,$idest));

        //echo $idest.$ext;
    }


    $s2 = "SELECT sc.idsubcategoria
            FROM subcategorias sc INNER JOIN categorias c
            ON sc.idcategoria = c.idcategoria
              INNER JOIN cate_local cl ON cl.idsubcategoria = sc.idsubcategoria AND cl.idestablecimiento = ?";

    $cats = $app['db']->fetchAll($s2, array($idest));

    foreach($cats as $c){
        $old_cats[] = $c['idsubcategoria'];
    }




    /*echo "<pre>";
    var_dump($old_cats);
    //var_dump($new_cats);
    var_dump($categorias);
    //quitadas
    var_dump(array_diff($old_cats,$categorias));
    //nuevas
    var_dump(array_diff($categorias,$old_cats));
    echo "</pre>";*/

    $news = array_diff($categorias,$old_cats);
    foreach($news as $c){

        $sql = "SELECT idsubcategoria, idcategoria FROM subcategorias WHERE idsubcategoria = ?";
        $sc = $app['db']->fetchAll($sql, array($c));
        foreach ($sc as $cat){
            $batch[] = "(".implode(",",$cat).",".$idest.")";
        }
    }

    if(count($news)>0) {
        $s3 = "INSERT INTO cate_local (idsubcategoria, idcategoria, idestablecimiento) VALUES " . implode(",", $batch);
        $app['db']->executeQuery($s3);
    }

    $kick = array_diff($old_cats,$categorias);
    if(count($kick)>0) {
        $s4 = "DELETE FROM cate_local WHERE idsubcategoria IN (" . implode(",", $kick) . ") AND idestablecimiento = ?";
        $app['db']->executeQuery($s4, array($idest));
    }

    #Resize Batch
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => 'http://dtodo-phpserv.rhcloud.com/services/api/resize/'.$idest.'?type=refresh',
        CURLOPT_USERAGENT => 'Codular Sample cURL Request'
    ));
    $resp = curl_exec($curl);
    curl_close($curl);

    return $idest;

});

$app->match('/user/update', function (Request $request) use ($app) {




    $email_sec =(is_null($request->get('email_sec')) or ($request->get('email_sec')=="null") )?'':$request->get('email_sec');
    $alias =(is_null($request->get('alias')) or ($request->get('alias')=="null") )?'':$request->get('alias');
    $fecha_nac =(is_null($request->get('fecha_nac')) or ($request->get('fecha_nac')=="null") )?'':$request->get('fecha_nac');
    $genero =(is_null($request->get('genero')) or ($request->get('genero')=="null") )?'':$request->get('genero');
    $estado_civil =(is_null($request->get('estado_civil')) or ($request->get('estado_civil')=="null")or ($request->get('estado_civil')=="undefined") )?'':$request->get('estado_civil');
    $ididioma =(is_null($request->get('ididioma')) or ($request->get('ididioma')=="null") )?'':$request->get('ididioma');
    $ubicacion =(is_null($request->get('ubicacion')) or ($request->get('ubicacion')=="null") )?'':$request->get('ubicacion');
    $facebook =(is_null($request->get('facebook')) or ($request->get('facebook')=="null") )?'':$request->get('facebook');
    $descripcion =(is_null($request->get('descripcion')) or ($request->get('descripcion')=="null") )?'':$request->get('descripcion');
    $celular =(is_null($request->get('celular')) or ($request->get('celular')=="null") )?'':$request->get('celular');


    //if($est_civil=="soltero") $est_civil = "Soltero(a)";
    //if($est_civil=="casado") $est_civil = "Casado(a)";
    //if($est_civil=="En_una_relacion") $est_civil = "En una relacion";


    $jwt = $request->get('token');
    JWT::$leeway = 60;
    try {
        $decoded = JWT::decode($jwt, $app['jwt.secret'], array('HS256'));
        $decoded_array = (array)$decoded;
        $uid = $decoded_array['user']->idusuario;

    } catch (DomainException $e) {
        return 2;
    }

    $s1 ="UPDATE usuarios
            SET
            email_sec = :email_sec,
            alias = :alias,
            fecha_nac = :fecha_nac,
            genero = :genero,
            estado_civil = :estado_civil,
            ididioma = :ididioma,
            ubicacion = :ubicacion,
            facebook = :facebook,
            descripcion = :descripcion,
            celular = :celular

            WHERE idusuario = :uid
              ";


    /*var_dump($email_sec);
    var_dump($alias);
    var_dump($fecha_nac);
    var_dump($genero);
    var_dump(ucfirst($estado_civil));

    var_dump($ididioma);
    var_dump($ubicacion);
    var_dump($facebook);
    var_dump($descripcion);
    var_dump($celular);
    var_dump($uid);*/

    try{
        $app['db']->executeUpdate($s1,
            array(
                'email_sec' => $email_sec,
                'alias' => $alias,
                'fecha_nac' => $fecha_nac,
                'genero' => $genero,
                'estado_civil' => $estado_civil,
                'ididioma' => $ididioma,
                'ubicacion' => $ubicacion,
                'facebook' => $facebook,
                'descripcion' => $descripcion,
                'celular' => $celular,
                'uid' => $uid

            ) );
    }catch(DBALException $e){
        echo "<pre>";
        echo $e->getTraceAsString();
        echo "</pre>";
    }


    //echo $app['db']->getSql();

    //echo "<br/><br/><br/>Last ID: ".$app['db']->lastInsertId();
    //$last_id = $app['db']->lastInsertId();

    $file = $request->files->all();
    // could throw a Symfony\Component\HttpFoundation\File\Exception\FileException
    // will overwrite existing file


    foreach($file as $k => $v){
        if (exif_imagetype($v->getRealPath()) == IMAGETYPE_JPEG) {
            $ext =  '.jpg';
        }elseif(exif_imagetype($v->getRealPath()) == IMAGETYPE_PNG){
            $ext = '.png';
        }elseif(exif_imagetype($v->getRealPath()) == IMAGETYPE_GIF){
            $ext =  '.gif';
        }
        echo ($v->move($app['app.files_path'], 'u'.$uid.$ext))?
        $app['db']->executeUpdate('UPDATE usuarios SET foto = ? WHERE idusuario = ?',array('u'.$uid.$ext,$uid)) : '';


    }


    return $uid;

});




$app->match('/upload', function (Request $request) use ($app) {

    $selPais = $request->get('selPais');
    $selDistrito = $request->get('selDistrito');
    $selCiudad = $request->get('selCiudad');
    $selCategories = $request->get('selCategories');

    $categorias = explode(",", $selCategories);

    $dispo = $request->get('dispo');
    $selOpenH = $request->get('selOpenH');
    $selOpenM = $request->get('selOpenM');
    $selOpenR = $request->get('selOpenR');

    $selCloseH = $request->get('selCloseH');
    $selCloseM = $request->get('selCloseM');
    $selCloseR = $request->get('selCloseR');

    $nom_comercial = $request->get('nom_comercial');
    $direccion = $request->get('direccion');
    $telefono = $request->get('telefono');
    $pagina_web  = $request->get('pagina_web');
    $email = $request->get('email');
    $lat = $request->get('lat');
    $lng = $request->get('lng');


    $jwt = $request->get('token');
    JWT::$leeway = 60;
    try {
        $decoded = JWT::decode($jwt, $app['jwt.secret'], array('HS256'));
        $decoded_array = (array)$decoded;
        $uid = $decoded_array['user']->idusuario;

    } catch (DomainException $e) {
        return 2;
    }

    if($selOpenR == "pm"){
        $selOpenH +=12;
        if($selOpenH > 23) {$selOpenH -=24; }
    }

    if($selCloseR == "pm"){
        $selCloseH +=12;
        if($selCloseH >23) {$selCloseH -=24;}
    }


    if($selOpenM < 10) { $selOpenM = "0".$selOpenM; }
    if($selCloseM < 10) { $selCloseM = "0".$selCloseM; }




    $s1 ="INSERT INTO establecimientos (
                              idregistrante,
                              iddistribuidor,
                              nom_comercial,
                              idpais,
                              idciudad,
                              iddistrito,
                              direccion,
                              telefono,
                              pag_web,
                              email,
                              horario_aten_ini,
                              horario_aten_fin,
                              coor_lt,
                              coor_lng,
                              foto,
                              fecha_regis,hora_regis)
                VALUES (?,'',?,?,?,?,?,?,?,?,?,?,?,?,'',CURDATE(),CURTIME())
              ";

    /*echo "<pre>";
    var_dump(array($uid,
        $nom_comercial,
        $selPais,
        $selCiudad,
        $selDistrito,
        $direccion,
        $telefono,
        $pagina_web,
        $email,
        $selOpenH.":".$selOpenM.":00",
        $selCloseH.":".$selCloseM.":00",
        $lat,
        $lng

    ));
    echo "</pre>";*/

    $s2 ="INSERT INTO establecimientos (
                              idregistrante,
                              iddistribuidor,
                              nom_comercial,
                              idpais,
                              idciudad,
                              iddistrito,
                              direccion,
                              telefono,
                              pag_web,
                              email,
                              horario_aten_ini,
                              horario_aten_fin,
                              coor_lt,
                              coor_lng,
                              foto,
                              fecha_regis,hora_regis)
                VALUES ({$uid},'',
        '{$nom_comercial}',
        {$selPais},
        {$selCiudad},
        {$selDistrito},
        '{$direccion}',
        {$telefono},
        '{$pagina_web}',
        '{$email}',
        '{$selOpenH}:{$selOpenM}:00',
        '{$selCloseH}:{$selCloseM}:00',
        {$lat},
        {$lng},
        '',
        CURDATE(),CURTIME())
              ";
    //echo $s2;

    try {
        $app['db']->executeQuery($s1,
            array($uid,
                $nom_comercial,
                $selPais,
                $selCiudad,
                $selDistrito,
                $direccion,
                $telefono,
                $pagina_web,
                $email,
                $selOpenH . ":" . $selOpenM . ":00",
                $selCloseH . ":" . $selCloseM . ":00",
                $lat,
                $lng

            ));
    }catch(DBALException $e){
        //echo "<pre>";
        //echo $e->getTraceAsString();
        //echo "</pre>";
    }



    //echo "<br/><br/><br/>Last ID: ".$app['db']->lastInsertId();
    $last_id = $app['db']->lastInsertId();



    $file = $request->files->all();
    // could throw a Symfony\Component\HttpFoundation\File\Exception\FileException
    // will overwrite existing file
    foreach($file as $k => $v){
        if (exif_imagetype($v->getRealPath()) == IMAGETYPE_JPEG) {
            $ext =  '.jpg';
        }elseif(exif_imagetype($v->getRealPath()) == IMAGETYPE_PNG){
            $ext = '.png';
        }elseif(exif_imagetype($v->getRealPath()) == IMAGETYPE_GIF){
            $ext =  '.gif';
        }

        $v->move($app['app.files_path'], $last_id.$ext);
    }


    $u = "UPDATE establecimientos SET foto = ? WHERE idestablecimiento = ?";
    $app['db']->executeQuery($u,array($last_id.$ext,$last_id));


    foreach($categorias as $c){
        $sql = "SELECT idsubcategoria, idcategoria FROM subcategorias WHERE idsubcategoria = ?";
        $sc = $app['db']->fetchAll($sql, array($c));
        foreach ($sc as $cat){
            $batch[] = "(".implode(",",$cat).",".$last_id.")";
        }
    }
    $s2 = "INSERT INTO cate_local (idsubcategoria, idcategoria, idestablecimiento) VALUES ".implode(",",$batch);
    $app['db']->executeQuery($s2);
    //return $last_id;


    #Resize Batch
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => 'http://dtodoaqui.com/services/api/resize/'.$last_id.'?type=refresh',
        CURLOPT_USERAGENT => 'Codular Sample cURL Request'
    ));
    $resp = curl_exec($curl);
    curl_close($curl);

    #FB Batch
    /*$curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => 'https://www.facebook.com/sharer/sharer.php?app_id=1036314233122698&sdk=joey&u=http%3A%2F%2Fdtodo-phpserv.rhcloud.com%2Festablishments%2F'.$last_id.'&display=popup&ref=plugin&src=share_button',
        CURLOPT_USERAGENT => 'Codular Sample cURL Request'
    ));
    $resp = curl_exec($curl);
    curl_close($curl);*/

    return $app->json($last_id);
});

$app->match('/list/est', function (Request $request) use ($app) {
    if ($request->isMethod("get")) {

        //sleep(2);
        $offset = $request->get('offset');
        $page = $request->get('page');
        $type= $request->get('type');
        if($offset == null ) $offset = 18;
        if($page == null ) $page = 1;


        switch($type){
            case 'e': $sql = "SELECT idestablecimiento as cod, nom_comercial as name, foto FROM establecimientos WHERE iddistribuidor = ?"; break;
            case 'r': $sql = "SELECT idestablecimiento as cod, nom_comercial as name, foto FROM establecimientos WHERE idregistrante = ?"; break;
            case 'f': $sql = "SELECT e.idestablecimiento as cod, e.nom_comercial as name, e.foto FROM favoritos f INNER JOIN establecimientos e
                                ON f.idestablecimiento = e.idestablecimiento
                                WHERE f.idusuario = ?"; break;
        }

        $jwt = $request->get('token');
        $user = $request->get('user');

        JWT::$leeway = 60;
        try {
            $decoded = JWT::decode($jwt, $app['jwt.secret'], array('HS256'));
            $decoded_array = (array)$decoded;
            $uid = $decoded_array['user']->idusuario;


            if(!empty($user)){
                $f = $app['db']->fetchAll($sql, array($user));
            }else {
                $f = $app['db']->fetchAll($sql, array($uid));
            }

            if($offset == 0){
                $d = array('count' => count($f),'elem' => $f);
            }else{
                $d = array('count' => count($f),'elem' => array_slice($f,($page-1) * $offset,$offset));
            }


            $data = array('code' => 200, 'data' => $d);
            return $app->json($data);
        } catch (DomainException $e) {
            $data = array('code' => 501, 'data' => 'Hey! No hagas eso.');
            return $app->json($data);
        }
    }else{
        return 0;
    }



    /*$offset = $request->get('offset');
    $page = $request->get('page');
    if($offset == null ) $offset = 18;
    if($page == null ) $page = 1;

    try{
        $sql = "SELECT idestablecimiento as cod, nom_comercial as name, foto FROM establecimientos ORDER BY idestablecimiento ASC";
        $e = $app['db']->fetchAll($sql);//, array($decoded_array['user']->idusuario));


        if($offset == 0){
            $d = array('count' => count($e),'elem' => $e);
        }else{
            $d = array('count' => count($e),'elem' => array_slice($e,($page-1) * $offset,$offset));
        }

        $data = array('code' => 200, 'data' => $d);
        return $app->json($data);
    }catch (DomainException $e){
        $data = array('code' => 501, 'data' => 'Hey! No hagas eso.');
        return $app->json($data);
    }*/
});

$app->match('/list/friend', function (Request $request) use ($app) {




    if ($request->isMethod("get")) {

        $offset = $request->get('offset');
        $page = $request->get('page');
        if($offset == null ) $offset = 18;
        if($page == null ) $page = 1;


        $jwt = $request->get('token');
        $user = $request->get('user');
        JWT::$leeway = 60;
        try {
            $decoded = JWT::decode($jwt, $app['jwt.secret'], array('HS256'));
            $decoded_array = (array)$decoded;
            $uid = $decoded_array['user']->idusuario;
            $sql = "SELECT u.idusuario as cod, CONCAT(u.nombre,' ',u.apellido) as name, u.foto
                FROM amigos a INNER JOIN usuarios u
                    ON u.idusuario = a.idamigo
                WHERE a.idusuario = ? AND a.estado = 'A'
                UNION
                SELECT  u.idusuario as cod, CONCAT(u.nombre,' ',u.apellido) as name, u.foto
                FROM amigos a INNER JOIN usuarios u
                    ON u.idusuario = a.idusuario
                WHERE a.idamigo = ? AND a.estado = 'A' ";

            if(!empty($user)){
                $f = $app['db']->fetchAll($sql, array($user, $user));
            }else {
                $f = $app['db']->fetchAll($sql, array($uid, $uid));
            }

            if($offset == 0){
                $d = array('count' => count($f),'elem' => $f);
            }else{
                $d = array('count' => count($f),'elem' => array_slice($f,($page-1) * $offset,$offset));
            }


            $data = array('code' => 200, 'data' => $d);
            return $app->json($data);
        } catch (DomainException $e) {
            $data = array('code' => 501, 'data' => 'Hey! No hagas eso.');
            return $app->json($data);
        }
    }else{
        return 0;
    }



    /*try{
        //$decoded = JWT::decode($jwt, $app['jwt.secret'], array('HS256'));
        //$decoded_array = (array) $decoded;
        //$uid = $decoded_array['user']->idusuario;
        //var_dump($decoded_array['user']->idusuario);

        $sql = "SELECT idusuario as cod, CONCAT(nombre,' ',apellido) as name, foto FROM usuarios ORDER BY idusuario ASC";

        $f = $app['db']->fetchAll($sql);

        if($offset == 0){
            $d = array('count' => count($f),'elem' => $f);
        }else{
            $d = array('count' => count($f),'elem' => array_slice($f,($page-1) * $offset,$offset));
        }


        $data = array('code' => 200, 'data' => $d);

        return $app->json($data);
    }catch (DomainException $e){
        $data = array('code' => 501, 'data' => 'Hey! No hagas eso.');
        return $app->json($data);
    }*/
});
$app->match('/list/search', function (Request $request) use ($app) {




    if ($request->isMethod("get")) {

        $offset = $request->get('offset');
        $page = $request->get('page');
        $clue = $request->get('clue');
        if($offset == null ) $offset = 18;
        if($page == null ) $page = 1;
        if($clue == null ) $clue = "";


        $jwt = $request->get('token');
        $user = $request->get('user');
        JWT::$leeway = 60;
        try {
            $decoded = JWT::decode($jwt, $app['jwt.secret'], array('HS256'));
            $decoded_array = (array)$decoded;
            $uid = $decoded_array['user']->idusuario;
            /*$sql = "SELECT u.idusuario as cod, CONCAT(u.nombre,' ',u.apellido) as name, u.foto
                FROM amigos a INNER JOIN usuarios u
                    ON u.idusuario = a.idamigo
                WHERE a.idusuario = ? AND a.estado = 'A'
                UNION
                SELECT  u.idusuario as cod, CONCAT(u.nombre,' ',u.apellido) as name, u.foto
                FROM amigos a INNER JOIN usuarios u
                    ON u.idusuario = a.idusuario
                WHERE a.idamigo = ? AND a.estado = 'A' ";*/
            $sql = "SELECT u.idusuario as cod, CONCAT(u.nombre,' ',u.apellido) as name, u.foto
                FROM usuarios u WHERE u.nombre LIKE ? OR u.apellido LIKE ?";

            if(!empty($user)){
                $f = $app['db']->fetchAll($sql, array("%".$clue."%", "%".$clue."%"));
            }else {
                $f = $app['db']->fetchAll($sql, array("%".$clue."%", "%".$clue."%"));
            }

            if($offset == 0){
                $d = array('count' => count($f),'elem' => $f);
            }else{
                $d = array('count' => count($f),'elem' => array_slice($f,($page-1) * $offset,$offset));
            }


            $data = array('code' => 200, 'data' => $d);
            return $app->json($data);
        } catch (DomainException $e) {
            $data = array('code' => 501, 'data' => 'Hey! No hagas eso.');
            return $app->json($data);
        }
    }else{
        return 0;
    }



    /*try{
        //$decoded = JWT::decode($jwt, $app['jwt.secret'], array('HS256'));
        //$decoded_array = (array) $decoded;
        //$uid = $decoded_array['user']->idusuario;
        //var_dump($decoded_array['user']->idusuario);

        $sql = "SELECT idusuario as cod, CONCAT(nombre,' ',apellido) as name, foto FROM usuarios ORDER BY idusuario ASC";

        $f = $app['db']->fetchAll($sql);

        if($offset == 0){
            $d = array('count' => count($f),'elem' => $f);
        }else{
            $d = array('count' => count($f),'elem' => array_slice($f,($page-1) * $offset,$offset));
        }


        $data = array('code' => 200, 'data' => $d);

        return $app->json($data);
    }catch (DomainException $e){
        $data = array('code' => 501, 'data' => 'Hey! No hagas eso.');
        return $app->json($data);
    }*/
});
$app->match('/list/allfriend', function (Request $request) use ($app) {

    if ($request->isMethod("get")) {

        $offset = $request->get('offset');
        $page = $request->get('page');
        $clue = $request->get('clue');
        if($offset == null ) $offset = 18;
        if($page == null ) $page = 1;
        if($clue == null ) $clue = '';


        $jwt = $request->get('token');
        $user = $request->get('user');
        JWT::$leeway = 60;
        try {
            $decoded = JWT::decode($jwt, $app['jwt.secret'], array('HS256'));
            $decoded_array = (array)$decoded;
            $uid = $decoded_array['user']->idusuario;
            $sql = "SELECT u.idusuario as cod, CONCAT(u.nombre,' ',u.apellido) as name, u.foto
                FROM usuarios u WHERE u.nombre LIKE ? OR u.apellido LIKE ? LIMIT 10 ";

            if(!empty($user)){
                $f = $app['db']->fetchAll($sql,array("%".$clue."%","%".$clue."%"));
            }else {
                $f = $app['db']->fetchAll($sql,array("%".$clue."%","%".$clue."%"));
            }

            if($offset == 0){
                $d = array('count' => count($f),'elem' => $f);
            }else{
                $d = array('count' => count($f),'elem' => array_slice($f,($page-1) * $offset,$offset));
            }


            $data = array('code' => 200, 'data' => $d);
            return $app->json($data);
        } catch (DomainException $e) {
            $data = array('code' => 501, 'data' => 'Hey! No hagas eso.');
            return $app->json($data);
        }
    }else{
        return 0;
    }

});

$app->match('/user/uid', function (Request $request) use ($app) {

    if ($request->isMethod("post")) {

        $uid = $request->get('uid');
        $jwt = $request->get('token');

        JWT::$leeway = 60;
        try {
            $decoded = JWT::decode($jwt, $app['jwt.secret'], array('HS256'));
            $decoded_array = (array)$decoded;
            $id_user = $decoded_array['user']->idusuario;

            if($uid == $id_user){
                return $app->json(1);
            }else{
                return $app->json(0);
            }


        } catch (DomainException $e) {
            $data = array('code' => 501, 'data' => 'Hey! No hagas eso.');
            return $app->json($data);
        }
    }else{
        return 0;
    }
});

$app->match('/user/est/fav', function (Request $request) use ($app) {

    if ($request->isMethod("post")) {

        $user = $request->get('user');
        $jwt = $request->get('token');
        $est = $request->get('e');

        JWT::$leeway = 60;
        try {


            $decoded = JWT::decode($jwt, $app['jwt.secret'], array('HS256'));
            $decoded_array = (array)$decoded;
            $uid = $decoded_array['user']->idusuario;

            $sql = "DELETE FROM favoritos WHERE idestablecimiento = ? AND idusuario = ?";



            if(empty($user)){

                $app['db']->executeQuery($sql, array($est, $uid) );

            }else{

                $app['db']->executeQuery($sql, array($est, $user) );

            }

            return $app->json(200);
        } catch (DomainException $e) {
            $data = array('code' => 501, 'data' => 'Hey! No hagas eso.');
            return $app->json($data);
        }
    }else{
        return 0;
    }
});

$app->match('/user/est/reg', function (Request $request) use ($app) {

    if ($request->isMethod("post")) {

        $user = $request->get('user');
        $jwt = $request->get('token');
        $est = $request->get('e');

        JWT::$leeway = 60;
        try {
            $decoded = JWT::decode($jwt, $app['jwt.secret'], array('HS256'));
            $decoded_array = (array)$decoded;
            $uid = $decoded_array['user']->idusuario;

            $sql = "UPDATE establecimientos
                    SET idregistrante = ''
                    WHERE idestablecimiento = ? AND idregistrante = ?";



            if(empty($user)){
                //echo $sql;
                $app['db']->executeQuery($sql, array($est, $uid) );
            }else{

                $app['db']->executeQuery($sql, array($est, $user) );
            }
            return $app->json(200);

        } catch (DomainException $e) {
            $data = array('code' => 501, 'data' => 'Hey! No hagas eso.');
            return $app->json($data);
        }
    }else{
        return 0;
    }
});
$app->match('/user/est/titu', function (Request $request) use ($app) {

    if ($request->isMethod("post")) {

        $user = $request->get('user');
        $jwt = $request->get('token');
        $est = $request->get('e');

        JWT::$leeway = 60;
        try {
            $decoded = JWT::decode($jwt, $app['jwt.secret'], array('HS256'));
            $decoded_array = (array)$decoded;
            $uid = $decoded_array['user']->idusuario;

            $sql = "UPDATE establecimientos
                    SET iddistribuidor = ''
                    WHERE idestablecimiento = ? AND iddistribuidor = ?";



            if(empty($user)){
                echo $sql;

                $app['db']->executeQuery($sql, array($est, $uid) );
            }else{

                $app['db']->executeQuery($sql, array($est, $user) );
            }

        } catch (DomainException $e) {
            $data = array('code' => 501, 'data' => 'Hey! No hagas eso.');
            return $app->json($data);
        }
    }else{
        return 0;
    }
});


$app->match('/est/notify', function (Request $request) use ($app) {

    if ($request->isMethod("post")) {

        $type = $request->get('type');
        $jwt = $request->get('token');
        $idest = $request->get('idest');

        JWT::$leeway = 60;
        try {
            $decoded = JWT::decode($jwt, $app['jwt.secret'], array('HS256'));
            $decoded_array = (array)$decoded;
            $uid = $decoded_array['user']->idusuario;

            switch($type){
                case "fav": $sql = "SELECT * FROM favoritos WHERE idestablecimiento = :idest AND idusuario = :iud"; break;
                case "rep": $sql = "SELECT * FROM solicitudes WHERE idestablecimiento = :idest AND idusuario = :iud AND detalle = 'REPORTE'"; break;
                case "req": $sql = "SELECT * FROM solicitudes WHERE idestablecimiento = :idest AND idusuario = :iud AND detalle = 'TITULARIDAD'"; break;
                default: $sql ="";
            }

            if(!empty($sql)){
                $c = $app['db']->fetchAll($sql, array("iud"=> $uid,"idest"=>$idest) );


                switch($type){
                    case "fav": $msg = "Este establecimiento ya se encuentra en favoritos"; break;
                    case "rep": $msg = "Este establecieminto ya ha sido reportado"; break;
                    case "req": $msg = "La solicitud de titularidad esta pendiente"; break;
                }

                if(count($c) > 0) {
                    $data = array('code' => 201, 'data' => $msg);
                    return $app->json($data);
                }

            }


            switch($type){
                case "fav": $sql = "INSERT INTO favoritos VALUES (NULL,:idest ,:iud,CURDATE(),CURTIME())"; break;
                case "rep": $sql = "INSERT INTO solicitudes VALUES (NULL,:iud,:idest,'REPORTE',CURDATE(),'A')"; break;
                case "req": $sql = "INSERT INTO solicitudes VALUES (NULL,:iud,:idest,'TITULARIDAD',CURDATE(),'A')"; break;
                default: $sql ="";
            }




            if(!empty($sql)){

                switch($type){
                    case "fav": $msg = "Este establecimiento ha sido agregado a tus favoritos"; break;
                    case "rep": $msg = "Este establecieminto ha sido reportado"; break;
                    case "req": $msg = "La solicitud de titularidad ha sido enviada"; break;
                }


                $app['db']->executeQuery($sql, array("iud"=> $uid,"idest"=>$idest) );
                $data = array('code' => 200, 'data' => $msg);
                return $app->json($data);
            }else{
                return 0;

            }

        } catch (DomainException $e) {
            $data = array('code' => 501, 'data' => 'Hey! No hagas eso.');
            return $app->json($data);
        }
    }else{
        return 0;
    }
});


$app->get('/pass', function (Request $request) use ($app) {

    $p = $request->get("p");

    return password_hash($p,PASSWORD_DEFAULT);
});

$app->get('/share/{id}', function (Request $request, $id) use ($app) {


    if (
        strpos($_SERVER["HTTP_USER_AGENT"], "facebookexternalhit/") !== false ||
        strpos($_SERVER["HTTP_USER_AGENT"], "Facebot") !== false
    ) {

        //$_SERVER['HTTP_REFERER']
        $image = "http://dtodoaqui.com/services/api/resize/{$id}";

        $sql = "SELECT * FROM establecimientos WHERE idestablecimiento = :idest";
        $e = $app['db']->fetchAssoc($sql, array("idest"=>$id) );

        $s2 = "SELECT sc.idcategoria, sc.idsubcategoria as subcategoria, subcategoria as des
            FROM subcategorias sc INNER JOIN categorias c
            ON sc.idcategoria = c.idcategoria
              INNER JOIN cate_local cl ON cl.idsubcategoria = sc.idsubcategoria AND cl.idestablecimiento =:idest";

        $cats = $app['db']->fetchAll($s2, array("idest"=>$id) );

        $array_cat = [];
        foreach($cats as $c){
            $array_cat[] = $c['des'];
        }
        $des_cats = (count($array_cat)>0)?join(",",$array_cat):"";

        $sql2 = "SELECT * FROM ubigeo WHERE iddistrito  = ?";
        $u = $app['db']->fetchAssoc($sql2, array($e['iddistrito']));

        $desc = "";

        $desc .=($e['direccion']==null)?'':($e['direccion']." ".$u['distrito'].", ".$u['cuidad'].", ".$u['pais']."\n\n\t");

        $desc .= (" | ".$des_cats.". \n\n\t");

        $desc .=($e['descripcion']==null)?'':(" | ".$e['descripcion'].". \n\n\t");



        return $app['twig']->render('share.html.twig',
            array('titulo'=>$e['nom_comercial'],'descripcion'=>$desc,'url_imagen'=>$image, "id" => $id)
        );

    }
    else {
        header("Location: http://dtodoaqui.com/establishments/{$id}");
        die();

    }




});

$app->get('/resize/{id}', function (Request $request, $id) use ($app) {
    ini_set('memory_limit', -1);
    $refresh = false;
    if($request->get("type")=="refresh") $refresh = true;

    $sql = "SELECT foto FROM establecimientos WHERE idestablecimiento = :idest";


    $e = $app['db']->fetchAssoc($sql, array("idest"=>$id) );

    //return __DIR__."/../files/".$e["foto"];
    $path = __DIR__."/../files/";
    $path = "/home/dtaks/public_html/services/files/";

    $imgSrc = $path.$e["foto"];
    $ext = pathinfo($imgSrc, PATHINFO_EXTENSION);
    $filename = pathinfo($imgSrc, PATHINFO_FILENAME);


    if(!$refresh && file_exists($path.$filename."_s.".$ext)){
        if($ext=="jpg"){
            header('Content-type: image/jpeg');
            imagejpeg($path.$filename."_s.".$ext);
            //imagejpeg($thumb,"small.".$ext);

        }elseif($ext=="png"){
            header('Content-Type: image/png');
            imagepng($path.$filename."_s.".$ext);
            //imagepng($thumb,"small.".$ext);
        }
        elseif($ext=="gif"){
            header("Content-type: image/gif");
            imagegif($path.$filename."_s.".$ext);
            //imagegif($thumb,"small2.".$ext);
        }

    }


//echo $ext;

//getting the image dimensions
    list($width, $height) = getimagesize($imgSrc);

//saving the image into memory (for manipulation with GD Library)
    if($ext=="jpg") {
        $myImage = imagecreatefromjpeg($imgSrc);
    }elseif($ext=="png"){
        $myImage = imagecreatefrompng($imgSrc);
    }elseif($ext=="gif"){
        $myImage = imagecreatefromgif($imgSrc);
    }
// calculating the part of the image to use for thumbnail
    if ($width > $height) {
        $y = 0;
        $x = ($width - $height) / 2;
        $smallestSide = $height;
    } else {
        $x = 0;
        $y = ($height - $width) / 2;
        $smallestSide = $width;
    }

// copying the part into thumbnail
    $thumbSize = 240;
    $thumb = imagecreatetruecolor($thumbSize, $thumbSize);

    imagecopyresampled($thumb, $myImage, 0, 0, $x, $y, $thumbSize, $thumbSize, $smallestSide, $smallestSide);


    try{
//final output
        if($ext=="jpg"){
            header('Content-type: image/jpeg');
            imagejpeg($thumb);
            imagejpeg($thumb,$path.$filename."_s.".$ext);


        }elseif($ext=="png"){
            header('Content-Type: image/png');
            imagepng($thumb);
            imagepng($thumb,$path.$filename."_s.".$ext);
        }
        elseif($ext=="gif"){
            header("Content-type: image/gif");
            imagegif($thumb);
            imagegif($thumb,$path.$filename."_s.".$ext);
        }

        imagedestroy($thumb);
    }
    catch(Exception $e){
        echo $e->getMessage();
        die();
    }
});

$app->match('/anuncio', function (Request $request) use ($app) {

    if ($request->isMethod("post")) {

        $type = $request->get('type');
        $jwt = $request->get('token');
        $idest = $request->get('idest');

        JWT::$leeway = 60;
        try {
            $decoded = JWT::decode($jwt, $app['jwt.secret'], array('HS256'));
            $decoded_array = (array)$decoded;
            $uid = $decoded_array['user']->idusuario;


            $sql ="SELECT * FROM anuncios ORDER BY RAND() LIMIT 1";
            //echo $sql;
            $a = $app['db']->fetchAll($sql);

            return $app->json($a);
        } catch (DomainException $e) {
            $data = array('code' => 501, 'data' => 'Hey! No hagas eso.');
            return $app->json($data);
        }
    }else{
        return 0;
    }
});

$app->match('/allcat', function (Request $request) use ($app) {

    //if ($request->isMethod("get")) {


            $sql ="SELECT  COUNT(c.idestablecimiento) as total, c.idcategoria,
                    c.categoria, c.img  FROM (SELECT DISTINCT e.idestablecimiento, e.nom_comercial,cat.idcategoria,
                    cat.categoria, cat.imagen as img
                    FROM categorias cat
                    INNER JOIN cate_local cl ON cat.idcategoria = cl.idcategoria
                    INNER JOIN establecimientos e ON e.idestablecimiento = cl.idestablecimiento) as c
                    GROUP BY c.idcategoria";
            //echo $sql;
            $cats = $app['db']->fetchAll($sql);

            $a =[];
            foreach($cats as $cat){

                $s ="SELECT *
                    FROM  (SELECT DISTINCT e.idestablecimiento,e.foto as img,
                    e.nom_comercial,IF(e.total_votos>0, e.total_votos/e.votos, e.total_votos ) as rating,
                    e.fecha_regis,
                    e.hora_regis,
                    e.direccion
                    FROM categorias cat
                    INNER JOIN cate_local cl ON cat.idcategoria = cl.idcategoria
                    INNER JOIN establecimientos e ON e.idestablecimiento = cl.idestablecimiento
                    WHERE cat.idcategoria = :idcat) as l
                    ORDER BY l.rating DESC,
                    l.fecha_regis DESC ,
                    l.hora_regis DESC
                    LIMIT 8";

                $e = $app['db']->fetchAll($s,array( "idcat" => $cat['idcategoria']));

                $a[] = array("id" => $cat['idcategoria'],"name" =>$cat['categoria'],"img" =>$cat['img'], "count" =>$cat['total'], "est" => $e);
            }


            return $app->json($a);

    //}else{
    //    return 0;
    //}
});

$app->match('/public/support', function (Request $request) use ($app) {

    if ($request->isMethod("post")) {

        $type = $request->get('type');
        $jwt = $request->get('token');
        $mensaje = $request->get('mensaje');
        $asunto = $request->get('asunto');
        //$asunto = '';

        JWT::$leeway = 60;
        try {
            $decoded = JWT::decode($jwt, $app['jwt.secret'], array('HS256'));
            $decoded_array = (array)$decoded;
            $uid = $decoded_array['user']->idusuario;
            $uid = 17;

            $sql = "SELECT CONCAT(nombre,' ',apellido) as name, idusuario as uid, email FROM usuarios WHERE idusuario = ?";
            $a = $app['db']->fetchAssoc($sql,array($uid));

            if($a){
                $uid = $a['uid'];
                $message = \Swift_Message::newInstance()
                    ->setSubject(' dtodoaqui.com - SOPORTE ')
                    ->setFrom(array('robot@dtodoaqui.com'))
                    ->setTo(array(/*'itsudatte01@gmail.com',*/'soporte@dtodoaqui.com'))
                    ->setBody($app['twig']->render('support.html.twig',
                        array('idusuario'=>$uid,
                            'email' => $a['email'],
                            'name' => $a['name'],
                            'asunto' => $asunto,
                            'mensaje' => $mensaje)
                    ), 'text/html');

                $app['mailer']->send($message);

                $a = array('code' => 200, 'data' => 'Mensaje enviado :).');

            }else{
                $a = array('code' => 404, 'data' => 'Ese usuario existe!?.');
            }


            return $app->json($a);
        } catch (DomainException $e) {
            $data = array('code' => 501, 'data' => 'Hey! No hagas eso.');
            return $app->json($data);
        }
    }else{
        return 0;
    }


    $email = $request->request->get("email");
    $hash = password_hash($email,PASSWORD_DEFAULT);

    if($_SERVER['HTTP_HOST'] == "localhost"){
        $api = "http://localhost/old_aqui/services/api";
    }else{
        $api = "http://dtodoaqui.com/services/api";
    }







});$app->match('/public/hello', function (Request $request) use ($app) {
    return "Hello bitch";
});


$app->match('/public/testmail', function (Request $request) use ($app) {

    if ($request->isMethod("get")) {

        $email = "itsudatte01@gmail.com";



        $message = \Swift_Message::newInstance()
            ->setSubject('Test mail')
            ->setFrom(array('noreply@dtodoaqui.com'))
            ->setTo(array($email))
            ->setBody($app['twig']->render('invitation.html.twig',
                array( 'name' => "tineo bot", 'email'=> $email)
            ), 'text/html');



        $app['mailer']->send($message);

        $data = array('code' => 200, 'data' => 'Enviado');
        return new Response();




    }else{
        return 0;
    }


});



/*
$app->error(function (\Exception $e, Request $request, $code) use ($app) {
    return new Response($code);
});
*/
