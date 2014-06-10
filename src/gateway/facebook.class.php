<?php

defined('REVSHIP') or exit('Access Denied!');
/**
 * Revship Facebook Connect
 * @author SLJ
 *
 */

if (!file_exists( LIB_PATH . 'facebook' . DS . 'src' . DS . 'facebook.php'))
{
    new Revship_Exception(404, 'Unable to load Facebook SDK');
}
require_once(LIB_PATH . 'facebook' . DS . 'src' . DS . 'facebook.php');

class Revship_Gateway_Facebook
{
    protected $obj = null;
    protected $fb_userId = null;
    protected $fb_userArray = array();
    public function __construct()
    {
        $appId = Revship::lib('config')->getItem('site.facebookAppId');
        $appSecret = Revship::lib('config')->getItem('site.facebookAppSecret');        
        if(empty($appId) || empty($appSecret))
        {
            throw new Revship_Exception(501,'Facebook API Key or Secret is not set.');
        }
        $this->obj = new Facebook(array( 'appId'  => $appId, 'secret' => $appSecret, ));
    }
    
    public function fetchInfoArray()
    {
        $this->fb_userId = $this->obj->getUser();

        if ($this->fb_userId) {
        try {
            // Proceed knowing you have a logged in user who's authenticated.
            $this->fb_userArray = $this->obj->api('/me');
        } catch (FacebookApiException $e) {
            error_log($e);
            $user = null;
            }
        }
        return $this->fb_userArray;
    }
	public function getUserId()
	{
		return $this->fb_userId;
	}
	public function getInfoArray()
	{
		return $this->fb_userArray;
	}
	public function getLoginUrl($scope='email')
	{
        $domainProtocol = Revship::lib('config')->getItem('site.domainProtocol');
        $domainUrl = Revship::lib('config')->getItem('site.domainUrl');
        $urlPrefix = $domainProtocol . '://' . $domainUrl . '/' ;
        
        $params['scope'] = $scope;
        $params['redirect_uri'] = $urlPrefix . 'user/fb'; //this is fb feedback receiver
        return $this->obj->getLoginUrl($params);
	}
	public function getLogoutUrl()
	{
		return $this->obj->getLogoutUrl();
	}
}
