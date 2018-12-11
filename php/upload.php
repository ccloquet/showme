<?php

    // ---------------------------------------------------------------------
    // purpose: using this script, the citizen can upload an image or video 
    // so that the dispatcher can see it
    // ---------------------------------------------------------------------

    // security measures to avoid rogue software being uploaded & executed on the machine, and to avoid exploiting the user's browser
    // - only allowed citizens (through the key mechanism) will be able to upload data to the server
    // - only a tiny white list of mime types is possible. Files that are stored on the server will be given an extension derived from that type. Executable files are not in that list.
    // - when possible, the files content is checked
    // - the .htaccess mechanism prevents the data to be accessed directly from the file tree 

    require_once('../includes.php');

    // verifies that the file exists and is not too large
    if (!isset($_FILES['upload_file'])) {
        returns_error();
    }
    if ($_FILES["upload_file"]["size"] > 50000000) { // 50 Mb... not so much for a video...
        returns_error();
    }

    // assess that the image comes from an url generated by a dispatcher
    if (!isset($_GET['key'])) {
        returns_error();
    }
    if (!verify_key($_GET['key'])) {
        returns_error();
    }
   
    // gets the key and deduces the userid
    $key      = $_GET['key'];
    $userid   = get_user_from_key($key);

    $tmp_name = $_FILES['upload_file']['tmp_name'];

    // applies further checks in function of the declared mime type
    $mime_type = get_mime_type ($tmp_name);
    $file_ext  = '';

    switch($mime_type) {
        case 'image/jpeg':
    
            // image checks
            if (extension_loaded('exif') && exif_imagetype($tmp_name) != IMAGETYPE_JPEG) {
                returns_error();
            }

            if (extension_loaded('imagick')) {
                $img = new Imagick();
                $img->readImageFile($tmp_name);
                /* -- from here does not work
                 $data = $img->identifyImage();
                 echo json_encode($data);

                 if (!$data || strpos(strtolower($data['format']), 'jpeg') === FALSE)
                 {
                     returns_error();
                 }*/
            }
 
            $type = getimagesize($tmp_name);

            if (($type === false) || (!in_array($type[2], [IMAGETYPE_JPEG]))) {
                returns_error();
            }
   
            $file_ext = '.jpg';
            break;

        case 'video/mp4':
            // video checks
            // to be done
            // currently, will return an error
            returns_error();
            $file_ext = '.mp4';
            break;

        default:
            // otherwise: reject everything that is not image or vide extension
            returns_error();
    }

    // create the folder if does not exists
    if (!is_dir(BASE_FOLDER))						
    {
	mkdir(BASE_FOLDER, 0777, true);
    }

    if (!file_exists(BASE_FOLDER . '/' . $userid)) {
        mkdir(BASE_FOLDER . '/' . $userid, 0777, true);
    }

    // craft the new name. The extension reflects the file extension, and comes from a closed list (cf the switch)
    $new_name = BASE_FOLDER . '/' . $userid . '/' . $key . bin2hex(random_bytes(13)) . $file_ext;

    if (move_uploaded_file($tmp_name, $new_name)) {
        returns_ok();
    } else {
        returns_error();
    }
  
    
?>

