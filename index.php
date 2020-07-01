<?php
session_start();
session_unset();
session_destroy();

// Destruir Cookie
setcookie("mscIdUsuario",$mscIdUsuario,time()-1);
setcookie("lgSion",$lgSion,time()-1);

include("include/db_mysqli.inc");
include("include/template.inc");
//include("include/confGral.php");


$t = new Template("templates", "keep");    
//$db = new DB_Sql;
//$db->connect("MSC_USUARIO", "localhost", "sionca_root", "sionca2016");
$db = new DB_Sql;
$db->connect("MSC_CUSTOMER_NI", "localhost", "sionca_root", "sionca2016");
$db2 = new DB_Sql;
$db2->connect("MSC_CUSTOMER_NI", "localhost", "sionca_root", "sionca2016");
$dbX = new DB_Sql;
$dbX->connect("MSC_CUSTOMER_NI", "localhost", "sionca_root", "sionca2016");

// fffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffff    
function usrPerms($id){
    global $db2;
    $sql="select distinct id_permiso from REL_USR_PERM where id_usuario='$id' ";
    $db2->query($sql);
    while($db2->next_record()){
        $arrPerms[]=$db2->f(id_permiso);
    }
    return $arrPerms;
}   
// fffffffffffffffffffffffffffffffffffffffffffffffffffffffffffff 
function showForm($data="",$msg=""){
    global $db,$t,$PHP_SELF;
    $t->set_file("page", "index.inc.html");

    //$pais = getValueTable("pais","INFO","id_info",1);

    $t->set_var(array(
        "ACTION"=>$PHP_SELF,
        "MENSAJE"=>"",
        "ALERT_ST"=>"hide"
    ));
    //"PAIS"=>$pais,

    // echo "MENSAJE".$msg."<br>";
    // -------------------------------------------
    //  Control de mensajes
    // -------------------------------------------
    if(!empty($msg)){
        $canMsg=count($msg);
        if($canMsg>0){
            foreach($msg as $val){
                $cadMsg.=$val ." <br>";
            }
            $t->set_var(array(
                "ALERT_ST"=>"",
                "MENSAJE"=>$cadMsg,
            ));
        }
    }
    $t->pparse("out","page");
}
// ffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffff
function getValueTable($campo,$tabla,$idTabla,$idEnviado){
    /*
    * @return Info de un campo especifico
    * @param string $campo
    * @param string $tabla
    * @param string $idTabla
    * @param string $idEnviado
    * @desc Esta funcion sirve para traer informacion de x campo de cualquier tabla.
    */
    global $dbX;
    $sql="select $campo from $tabla where $idTabla='$idEnviado'";
    $dbX->query($sql);
    while($dbX->next_record()){
        $valor=$dbX->f($campo);
        return $valor;  // No mover el return, podria ser peligroso!.
    }
}

// ----------------------------------------------------------------
// ----------------------------------------------------------------

//echo $modo."<br>";
$modo = $_REQUEST['modo'];
$nomUsr = $_REQUEST['nomUsr'];
$login = $_REQUEST['login'];
$password = $_REQUEST['password'];

//echo $modo."<br>";
//echo $login."<br>";
//echo $password."<br>";

switch($modo){
    case "accesar":
        $sql = "select * from USUARIO where login='$login'";
        $db->query($sql);
        $nr= $db->num_rows();
        while( $db->next_record() ){
            $l= $db->f(login);
            $p= $db->f(password);
            $sesIdUsuario= $db->f(id_usuario);
            $sesUsuario= getValueTable("usuario","USUARIO","id_usuario",$sesIdUsuario);                
            $sesIdOficina= $db->f(id_oficina);
            $sesArrPerms= usrPerms($sesIdUsuario);



            $pass= md5($password);

            // Validacion
            if( $l!=$login ){
                // echo "LOGIN : Incorrecto.";
                $msg[]="LOGIN : Incorrecto.";
            }                
            if( $p!=$pass ){
                $msg[]="PASSWORD : Incorrecto.";
            }

            if ( ($l==$login) && ($pass==$p) ){                                             
                // Registra las variables de sesion.
                $_SESSION['sesIdUsuario'] = $sesIdUsuario ;
                $_SESSION['sesArrPerms'] = $sesArrPerms;
                $_SESSION['sesUsuario'] = $sesUsuario;
                $_SESSION['sesIdOficina'] = $sesIdOficina;




                // Antes-No descomentar.
                // session_register("sesIdUsuario");
                //session_register("sesArrPerms");
                // session_register("sesUsuario");


                header("Location: http://".$_SERVER['HTTP_HOST']
                    .dirname($_SERVER['PHP_SELF'])
                    ."/index.html");  




            }
            else{                    
                showForm($data,$msg);
            }                   
        }
        if( $nr==0 || empty($nr) ){
            $msg[]="[Error] Login, el usuario no se encuentra registrado.";
            showForm($data,$msg);
        }                  
        break;


    case "registrar":

        $data['nomUsr']=$nomUsr;
        echo "ENTRA A REGISTRAR"."<br>";


        header("Location: http://".$_SERVER['HTTP_HOST']
            .dirname($_SERVER['PHP_SELF'])
            ."/actions/registroUsuario.php?nomUsr=$nomUsr");  


        showForm($data);
        break;  
    default:
        showForm($data);
        break;
}


?>