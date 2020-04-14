<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$udpDirectory  =  "CrypticVPN UDP 1194 Configs" ;
$tcpDirectory  =  "CrypticVPN TCP 443 Configs" ;
if (! file_exists ( $udpDirectory )) {
     mkdir ( $udpDirectory );
}
if (! file_exists ( $tcpDirectory )) {
     mkdir ( $tcpDirectory );
}
$directory  =  dirname ( __FILE__ ). '/../configs/' ;
$dir  = new  DirectoryIterator ( $directory );
foreach ( $dir  as  $fileinfo ) {
    if (! $fileinfo -> isDot ()) {
        if( strpos ( $directory . '/' . $fileinfo -> getFilename (),  "udp1194" ) !==  false ) {
             copy ( $directory . '/' . $fileinfo -> getFilename (),  $udpDirectory . '/' . $fileinfo -> getFilename ());
        } elseif( strpos ( $directory . '/' . $fileinfo -> getFilename (),  "tcp443" ) !==  false ) {
             copy ( $directory . '/' . $fileinfo -> getFilename (),  $tcpDirectory . '/' . $fileinfo -> getFilename ());
        }
    }
}
// Initialize archive object
$zip  = new  ZipArchive ();
$zip -> open ( '../configs.zip' ,  ZipArchive :: CREATE  |  ZipArchive :: OVERWRITE );
addFolderToZip ( $zip ,  $udpDirectory );
addFolderToZip ( $zip ,  $tcpDirectory );
// Zip archive will be created only after closing object
$zip -> close ();
removeDir ( $udpDirectory );
removeDir ( $tcpDirectory );
function  removeDir ( $dir ) {
     $files  =  array_diff ( scandir ( $dir ), array( '.' , '..' )); 
    foreach ( $files  as  $file ) { 
      ( is_dir ("$dir/$file")) ?  delTree ("$dir/$file") :  unlink ("$dir/$file" ); 
    } 
     rmdir ( $dir ); 
}
function  addFolderToZip ( $zip ,  $folder ) {
     $folderReal  =  realpath ( $folder );
     $files  = new  RecursiveIteratorIterator (
        new  RecursiveDirectoryIterator ( $folder ),
         RecursiveIteratorIterator :: LEAVES_ONLY
     );
    foreach ( $files  as  $name  =>  $file )
    {
         // Skip directories (they would be added automatically)
         if (! $file -> isDir ())
        {
             // Get real and relative path for current file
             $filePath  =  $file -> getRealPath ();
             $relativePath  =  substr ( $filePath ,  strlen ( $folderReal ) +  1 );
             // Add current file to archive
             $zip -> addFile ( $filePath ,  $folder . '/' . $relativePath );
        }
    }
} 
