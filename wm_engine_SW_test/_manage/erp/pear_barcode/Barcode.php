<?
require_once 'PEAR.php';

class wingBarcode extends PEAR {
    function &draw($text, $type = 'int25', $imgtype = 'png', $bSendToBrowser = true) {

        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $type)) {
            return PEAR::raiseError('Invalid barcode type ' . $type);
        }
        if (!include_once($GLOBALS['engine_dir'].'/_manage/erp/pear_barcode/' . $type . '.php')) {
            return PEAR::raiseError($type . ' barcode is not supported');
        }

        $classname = 'Image_Barcode_' . $type;

        if (!in_array('draw',get_class_methods($classname))) {
            return PEAR::raiseError("Unable to find draw method in '$classname' class");
        }

        @$obj = new $classname();

        $img = $obj->draw($text, $imgtype);

        if (PEAR::isError($img)) {
            return $img;
        }

        if ($bSendToBrowser) {
            // Send image to browser
            switch ($imgtype) {
                case 'gif':
                    header('Content-type: image/gif');
                    imagegif($img);
                    imagedestroy($img);
                    break;

                case 'jpg':
                    header('Content-type: image/jpg');
                    imagejpeg($img);
                    imagedestroy($img);
                    break;

                default:
                    header('Content-type: image/png');
                    imagepng($img);
                    imagedestroy($img);
                    break;
            }
        } else {
            return $img;
        }
    }
}
?>
