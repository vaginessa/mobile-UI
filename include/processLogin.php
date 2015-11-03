<?php
        
    if (isset($_POST["centreon_token"])
        || (isset($_GET["autologin"]) && $_GET["autologin"] && isset($_GET["p"]) && $_GET["autologin"] && isset($generalOptions["enable_autologin"]) && $generalOptions["enable_autologin"])
        || (isset($_POST["autologin"]) && $_POST["autologin"] && isset($_POST["p"]) && isset($generalOptions["enable_autologin"]) && $generalOptions["enable_autologin"])
        || (!isset($generalOptions['sso_enable']) || $generalOptions['sso_enable'] == 1)) {

        /*
         * Init log class
         */
        $CentreonLog = new CentreonUserLog(-1, $pearDB);

        if (isset($_POST['p'])) {
            $_GET["p"] = $_POST["p"];			
        }

        /*
         * Check first for Autologin or Get Authentication
         */
        isset($_GET["autologin"]) ? $autologin = $_GET["autologin"] : $autologin = 0;
        isset($_GET["useralias"]) ? $useraliasG = $_GET["useralias"] : $useraliasG = NULL;
        isset($_GET["password"]) ? $passwordG = $_GET["password"] : $passwordG = NULL;
        
        
        $useraliasP = null;
        $passwordP = null;
        if ($loginValidate) {
            $useraliasP = $form->getSubmitValue('useralias');
            $passwordP = $form->getSubmitValue('password');
        }
        
        $useraliasG ? $useralias = $useraliasG : $useralias = $useraliasP;
        $passwordG ? $password = $passwordG : $password = $passwordP;

        $token = "";
        if (isset($_REQUEST['token']) && $_REQUEST['token']) {
            $token = $_REQUEST['token'];
        }

        if (!isset($encryptType)) {
            $encryptType = 1;
        }

        $centreonAuth = new CentreonAuthSSO($useralias, $password, $autologin, $pearDB, $CentreonLog, $encryptType, $token, $generalOptions);

        if ($centreonAuth->passwdOk == 1) {

            $centreon = new Centreon($centreonAuth->userInfos, $generalOptions["nagios_version"]);
            $_SESSION["centreon"] = $centreon;
            $pearDB->query("INSERT INTO `session` (`session_id` , `user_id` , `current_page` , `last_reload`, `ip_address`) VALUES ('".session_id()."', '".$centreon->user->user_id."', '1', '".time()."', '".$_SERVER["REMOTE_ADDR"]."')");
            if (!isset($_POST["submit"]))	{
                $args = NULL;
                foreach ($_GET as $key => $value) {
                    $args ? $args .= "&".$key."=".$value : $args = $key."=".$value;					
                }
                header("Location: ./main.php?p=10&".$args."");
            } else {
                header("Location: ./main.php?p=10");
            }
            $connect = true;
        } else {
            $connect = false;	    	
        }
    }
    