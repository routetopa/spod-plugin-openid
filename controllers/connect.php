<?php
/**
 * Created by PhpStorm.
 * User: Donato Pirozzi
 * Date: 16/12/2015
 * Time: 12.44
 */

$LIB_PATH = OW::getPluginManager()->getPlugin('openidconnect')->getRootDir() . DS . 'libs';
$chdirsuccess = chdir($LIB_PATH);

require_once 'Auth' . DS . 'OpenID' . DS . 'Consumer.php';
require_once 'Auth' . DS . 'OpenID' . DS . 'FileStore.php';
require_once 'Auth' . DS . 'OpenID' . DS . 'SReg.php';
require_once 'Auth' . DS . 'OpenID' . DS . 'PAPE.php';

class OPENIDCONNECT_CTRL_Connect extends OW_ActionController
{

    //private $OPENIDPROVIDER = "https://openid.stackexchange.com/";
    private $OPENIDPROVIDER = "http://spod.routetopa.eu/openid/server.php"; //"http://spod.routetopa.eu/openid/spodadmin@routetopa.eu";
    private $consumer = null;

    public  function init() {
        $this->consumer = $this->getConsumer();
    }//EndFunction.

    public function login( $params )
    {
        //Begin the OpenID authentication process.
        $auth_request = $this->consumer->begin($this->OPENIDPROVIDER);
        $errorRedirectAddress = '/';

        //It it is null, the user cannot begin a OpenID auth request.
        if (!$auth_request) {
            //echo '<pre>';
            //print_r($this->consumer);

            echo $this->consumer->consumer->fetcher->data;
            //var_dump($auth_request);
            //TODO: error.
            //echo "error";

            OW::getFeedback()->error("Error in contacting the OPENID Provider " . $this->OPENIDPROVIDER);
            $this->redirect($errorRedirectAddress);
            return;
        }

        //Nickname required, email optional ...
        $sreg_request = Auth_OpenID_SRegRequest::build(array('nickname', 'email'), array('fullname'));

        if ($sreg_request) {
            $auth_request->addExtension($sreg_request);
        }

        $policy_uris = null;
        if (isset($_GET['policies'])) {
            $policy_uris = $_GET['policies'];
        }

        $pape_request = new Auth_OpenID_PAPE_Request($policy_uris);
        if ($pape_request) {
            $auth_request->addExtension($pape_request);
        }

        // Redirect the user to the OpenID server for authentication.
        // Store the token for this authentication so we can verify the
        // response.

        // For OpenID 1, send a redirect.  For OpenID 2, use a Javascript
        // form to send a POST request to the server.
        //if ($auth_request->shouldSendRedirect()) {
            $redirect_url = $auth_request->redirectURL($this->getTrustRoot(), $this->getReturnTo());

            // If the redirect URL can't be built, display an error message.
            if (Auth_OpenID::isFailure($redirect_url)) {
                echo "Could not redirect to server: " . $redirect_url->message;
                displayError("Could not redirect to server: " . $redirect_url->message);
            } else {
                // Send redirect.
                //header("Location: ".$redirect_url);

                $this->redirect($redirect_url);
            }
        /*} else {
            // Generate form markup and render it.
            $form_id = 'openid_message';
            $form_html = $auth_request->htmlMarkup(getTrustRoot(), getReturnTo(),
                false, array('id' => $form_id));

            // Display an error if the form markup couldn't be generated;
            // otherwise, render the HTML.
            if (Auth_OpenID::isFailure($form_html)) {
                displayError("Could not redirect to server: " . $form_html->message);
            } else {
                print $form_html;
            }
        }//EndElse*/

        //Register if not registered.

    }//EndFunction.

    function loginSuccess() {
        // Complete the authentication process using the server's response.
        $return_to = $this->getReturnTo();
        $response = $this->consumer->complete($return_to);

        $redirectAddress = '/';
        // Check the response status.
        if ($response->status == Auth_OpenID_CANCEL) {
            // This means the authentication was cancelled.
            $msg = 'Verification cancelled.';
            OW::getFeedback()->error($msg);
            $this->redirect($redirectAddress);
            return;
        } else if ($response->status == Auth_OpenID_FAILURE) {
            // Authentication failed; display the error message.
            $msg = "OpenID authentication failed: " . $response->message;
            OW::getFeedback()->error($msg);
            $this->redirect($redirectAddress);
            return;
        } else if ($response->status == Auth_OpenID_SUCCESS) {
            // This means the authentication succeeded; extract the identity URL and Simple Registration
            // data (if it was returned).
            $openid = $response->getDisplayIdentifier();
            $esc_identity = $this->escape($openid);

            $success = sprintf('You have successfully verified ' .
                '<a href="%s">%s</a> as your identity.',
                $esc_identity, $esc_identity);

            if ($response->endpoint->canonicalID) {
                $escaped_canonicalID = escape($response->endpoint->canonicalID);
                $success .= '  (XRI CanonicalID: '.$escaped_canonicalID.') ';
            }

            $sreg_resp = Auth_OpenID_SRegResponse::fromSuccessResponse($response);

            $sreg = $sreg_resp->contents();

            print_r($sreg);

            $data_email = @$sreg['email'];
            $data_username = @$sreg['nickname'];
            $data_fullname = @$sreg['fullname'];
            $data_password = uniqid();

            //***ONLY FOR TESTING
            if (strpos($data_email, 'donatopirozzi@libero.it') === true) {
                $userByEmail = BOL_UserService::getInstance()->findByEmail($data_email);
                if ($userByEmail != null)
                    $deleted = BOL_UserService::getInstance()->deleteUser($userByEmail->getId());
            }
            //***ONLY FOR TESTING

            $userByEmail = BOL_UserService::getInstance()->findByEmail($data_email);

            //$userByEmail = BOL_UserService::getInstance()->findByUsername("sbiricuda");
            //$userByEmail = BOL_UserService::getInstance()->findByUsername("spodadmin");

            if ($userByEmail != null) {
                OW::getUser()->login($userByEmail->id);
                $msg = $userByEmail->getUserName($userByEmail->id) . " ti sei loggato.";
                OW::getFeedback()->info($msg);
                $this->redirect($redirectAddress);
                return;
            }

            if ($userByEmail == null) { //The user is not registered.
                //TODO: check the data from the openid provider. If they are null, then retrieve an error.

                //If the user does not provided a valid user name in its own profile, then
                //it creates a new user profile.
                $validUsername = UTIL_Validator::isUserNameValid($data_username);
                if (!$validUsername) {
                    $mailExploded = explode("@", $data_email);
                    if (count($mailExploded) != 2) {
                        OW::getFeedback()->error("First time login failed, not valid mail address " . $data_email);
                        $this->redirect("/");
                        return;
                    }

                    $validUsername = UTIL_Validator::isUserNameValid($mailExploded[0]);
                    if (!$validUsername) {
                        OW::getFeedback()->error("First time login failed, not valid mail address " . $data_email);
                        $this->redirect("/");
                        return;
                    }

                    //Checks if the user name has already used.
                    $data_username = $mailExploded[0];
                    $tmpuser = BOL_UserService::getInstance()->findByUsername($data_username);
                    if ($tmpuser != null) $validUsername = false; //it creates any way the user and concats the id.
                }

                try {

                    $tmpusername = $validUsername == true ? $data_username : $data_username . "____";
                    $createdUser = BOL_UserService::getInstance()->createUser($tmpusername, $data_password, $data_email, null, true);

                    if (!$validUsername) {
                        $createdUser->username = $data_username . $createdUser->id;
                        BOL_UserService::getInstance()->saveOrUpdate($createdUser);
                    }


                } catch (Exception $e) {
                    switch ( $e->getCode() )
                    {
                        case BOL_UserService::CREATE_USER_DUPLICATE_USERNAME:
                            OW::getFeedback()->error("Duplicate username.");
                            $this->redirect($redirectAddress);
                            break;

                        case BOL_UserService::CREATE_USER_DUPLICATE_EMAIL:
                            OW::getFeedback()->error("Duplicate email.");
                            $this->redirect($redirectAddress);
                            break;

                        case BOL_UserService::CREATE_USER_INVALID_USERNAME:
                            OW::getFeedback()->error("Invalid user name.");
                            $this->redirect($redirectAddress);
                            break;

                        default:
                            OW::getFeedback()->error("Join incomplete " . $e->getMessage());
                            $this->redirect($redirectAddress);
                            return;
                    }
                }//EndTryCatch.

                //http://192.168.20.130/openid-connect/loginSuccess?
                //janrain_nonce=2015-12-16T17%3A03%3A07ZMwd3Fa
                //&openid.error=return_to%20%27http%3A%2F%2F192.168.20.130%2Fopenid-connect%2FloginSuccess%3Fjanrain_nonce%3D2015-12-16T17%3A03%3A07ZMwd3Fa%27%20not%20under%20realm%20%27http%3A%2F%2F192.168.20.130%2F%2F%27.
                //&openid.mode=error
                //&openid.ns=http%3A%2F%2Fspecs.openid.net%2Fauth%2F2.0


                //loginSuccess?
                //janrain_nonce=2015-12-17T12%3A09%3A29ZhAZDCw
                //&openid.claimed_id=https%3A%2F%2Fopenid.stackexchange.com%2Fuser%2F0be091aa-0ce6-44b4-8d5a-1b76efd26d52
                //&openid.identity=https%3A%2F%2Fopenid.stackexchange.com%2Fuser%2F0be091aa-0ce6-44b4-8d5a-1b76efd26d52
                //&openid.sig=3TcPscI6JPT2IXxzAYaYB%2FXmfGwdzxc03dOgjydOF04%3D
                //&openid.signed=claimed_id%2Cidentity%2Cassoc_handle%2Cop_endpoint%2Creturn_to%2Cresponse_nonce%2Cns.sreg%2Csreg.email%2Csreg.fullname
                //&openid.assoc_handle=HxGh%21IAAAANxR9fdqW3qesmGgFqyKk5BeAabK3POKC9-0BfhOo9bLQQAAAAFzL2Le-IolbXoLJuqPgK2v9e3SB4VG722XDesQl042D7ZkkEio-lL9VKMAWBkRrnA2q-HwoinbG12S41R4KnI6
                //&openid.invalidate_handle=Silq%21IAAAADcV1L4kef0O_oM0MYUL0qeiYDHOZbAX9rHHc0KVJCGmMQAAAAE7kW4aXwAvsF_3Scv9duov8R-UxfC_k3i4CI6E8L3QdYSI2tRPhODweHQgEc9MltU
                //&openid.op_endpoint=https%3A%2F%2Fopenid.stackexchange.com%2Fopenid%2Fprovider
                //&openid.return_to=http%3A%2F%2F192.168.20.130%3A80%2Fopenid-connect%2FloginSuccess%3Fjanrain_nonce%3D2015-12-17T12%253A09%253A29ZhAZDCw
                //&openid.response_nonce=2015-12-17T11%3A15%3A31ZsKAxJPZv
                //&openid.mode=id_res&openid.ns=http%3A%2F%2Fspecs.openid.net%2Fauth%2F2.0
                //&openid.ns.sreg=http%3A%2F%2Fopenid.net%2Fextensions%2Fsreg%2F1.1
                //&openid.sreg.email=donatopirozzi%40gmail.com
                //&openid.sreg.fullname=Donato%20Pirozzi

                $authAdapter = new OPENIDCONNECT_CLASS_AuthAdapter($createdUser->getId());
                $authAdapter->register($createdUser->getId());
                $authResult = OW_Auth::getInstance()->authenticate($authAdapter);
                if ( $authResult->isValid() )
                {
                    $event = new OW_Event(OW_EventManager::ON_USER_REGISTER, array(
                        'method' => 'openid',
                        'userId' => $createdUser->id,
                        'params' => $_GET
                    ));
                    OW::getEventManager()->trigger($event);

                    OW::getFeedback()->info("Join success" . $data_username);
                    $this->redirect("/user/" . $data_username);
                    return;
                }
                else
                {
                    OW::getFeedback()->error("Join failure");
                    $this->redirect($redirectAddress);
                }

            }//EndIf

        }


        /*$userByEmail = BOL_UserService::getInstance()->findByUsername("spodadmin");
        var_dump($userByEmail);
        if ($userByEmail) {
            //OW::getUser()->login($userByEmail->id);
            //OW::getFeedback()->info("Sbiricuda ti sei loggato");
            //$this->redirect("/");
        }*/
    }//EndFunction.

    function logout() {
        $this->redirect("www.repubblica.it");
    }//EndFunction

    function &getStore() {
        /**
         * This is where the example will store its OpenID information.
         * You should change this path if you want the example store to be
         * created elsewhere.  After you're done playing with the example
         * script, you'll have to remove this directory manually.
         */
        $store_path = null;
        if (function_exists('sys_get_temp_dir')) {
            $store_path = sys_get_temp_dir();
        }
        else {
            if (strpos(PHP_OS, 'WIN') === 0) {
                $store_path = $_ENV['TMP'];
                if (!isset($store_path)) {
                    $dir = 'C:\Windows\Temp';
                }
            }
            else {
                $store_path = @$_ENV['TMPDIR'];
                if (!isset($store_path)) {
                    $store_path = '/tmp';
                }
            }
        }
        $store_path .= DIRECTORY_SEPARATOR . '_php_consumer_test';

        if (!file_exists($store_path) &&
            !mkdir($store_path)) {
            print "Could not create the FileStore directory '$store_path'. ".
                " Please check the effective permissions.";
            exit(0);
        }
        $r = new Auth_OpenID_FileStore($store_path);

        return $r;
    }//EndFunction.


    function &getConsumer() {
        /**
         * Create a consumer object using the store object created
         * earlier.
         */
        $store = $this->getStore();
        $r = new Auth_OpenID_Consumer($store);
        return $r;
    }//EndFunction.

    function getReturnTo() {
        return sprintf("%s://%s:%s%s%s",
            $this->getScheme(), $_SERVER['SERVER_NAME'], $_SERVER['SERVER_PORT'], dirname($_SERVER['PHP_SELF']),
            "openid-connect/loginSuccess");
    }

    function getTrustRoot() {
        return sprintf("%s://%s:%s%s",
            $this->getScheme(), $_SERVER['SERVER_NAME'],
            $_SERVER['SERVER_PORT'],
            dirname($_SERVER['PHP_SELF']));
    }

    function getScheme() {
        $scheme = 'http';
        if (isset($_SERVER['HTTPS']) and $_SERVER['HTTPS'] == 'on') {
            $scheme .= 's';
        }
        return $scheme;
    }

    function escape($thing) {
        return htmlentities($thing);
    }

}//EndClass.
