<?php
require( 'db.php' );
$directory = dirname ( __FILE__ )."/../configs";
$bdd  = new  PDO ( "mysql:dbname=$db_name;host=$host" ,  $username ,  $password );
$dir  = new  DirectoryIterator ($directory);
foreach ( $dir  as  $fileinfo ) {
    if (! $fileinfo -> isDot ()) {
        if( strpos ( $fileinfo -> getFilename (),  "udp1194" ) !==  false ) {
             $file  =  fopen ( $directory.'/'.$fileinfo -> getFilename (),  "r" );
             $fileContent  =  fread ( $file ,  filesize ( $directory.'/'.$fileinfo -> getFilename ()));
             $hostname           =  getHostnameByLine ( explode ( "\n" ,  $fileContent )[ 3 ]);
             $certificateName  =  getCertificateName ( $hostname );
             $auth              =  getAuth ( $fileContent );
             $fqdn               =  getFQDN ( $certificateName );
             // check if hostname exist
             $req  =  $bdd -> prepare ( "SELECT * FROM radserversDEV WHERE ip=?" );
             $req -> execute (array( $hostname ));
            if( $req -> rowCount ()) {
                 $info  =  $req -> fetch ();
                 $bdd -> prepare ( "DELETE FROM radserversDEV WHERE ID=?" )-> execute (array($info['ID']));
            }
             $bdd -> prepare ( "INSERT INTO radserversDEV VALUES('', :hostname, :auth, :name, :file, 1, 0)" )
                -> execute (array(
                     'hostname'  =>  trim($hostname) ,
                     'auth'  =>  $auth ,
                     'name'  =>  $fqdn ,
                     'file'  =>  $certificateName
                 ));
        }
    }
}

$bdd->exec("INSERT INTO radserverversionDEV VALUES('', 'Auto')");

echo "Success";

function  getHostnameByLine ( $hostname ) {
     $hostname  =  str_replace ( "remote" ,  "" ,  $hostname );
     $hostname  =  str_replace ( "1194" ,  "" ,  $hostname );
    return  trim($hostname) ;
}
function  getCertificateName ( $hostname ) {
     $hostname  =  str_replace ( "crypticvpn.com" ,  "crt" ,  $hostname );
    return  trim($hostname) ;
}
function  getAuth ( $content ) {
     $regex  =  "/<ca>(.*)<\\/ca>/s" ; 
     $result  =  '' ;
     preg_match ( $regex ,  $content ,  $result );

     $result[0] = str_replace("<ca>".PHP_EOL, '', $result[0]);
     $result[0] = str_replace('</ca>', '', $result[0]);
    return  $result [ 0 ];
}
function  getFQDN ( $certificateName ) {
     $certificateName  =   preg_replace('/[0-9]+/', '',  $certificateName ); 
     $certificateName  =  str_replace ( ".crt" ,  "" ,  $certificateName );
     $information  =  explode ( '-' ,  $certificateName );
     $countryCode  =   '-' . strtoupper ( $information [ 0 ]);
    return  ucfirst ( $information [ 1 ]).", ". Locale :: getDisplayRegion ( $countryCode ,  'en' );
} 
