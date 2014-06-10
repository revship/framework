<?php
/** 
 * Revship Auth
 * 
 * @author SLJ
 * 
 * 
 */

if(!class_exists('User'))
{
    Revship::model('User');
}
class Revship_Auth
{
    const TOKEN_COOKIE_KEY = "_login_token_";
    protected $isGuest = true;
    protected $isAdmin = false;
    protected $user = null;
    /**
     * Return user login status
     * 
     * @return bool
     */
    public function isGuest()
    {
        return $this->isGuest;
    }
    
    public function isAdmin()
    {
        return $this->isAdmin;
    }
    /**
     * Set array $user 
     * 
     * @param $user
     */
    public function setLogin($user)
    {
        Revship::session()->set('user',$user);
        $this->resetStatus();
        return true;
    }
    public function setLogout()
    {
        $this->removeAutoLogin();
        $this->isGuest=true;
        $this->isAdmin=false;
        Revship::session()->remove('user');
        return true;
    }
    /**
     * 
     */
    public function __construct ()
    {
        $this->resumeStatus();
    }
    /**
     * Recover user status from session
     */
    public function resumeStatus()
    {
        // Session Login
        if( ! $this->resetStatus() )
        {
            // Auto login from cookie if possible
            $this->doAutoLoginFromCookie();
        }
        return true;
    }
    protected function resetStatus()
    {
        $this->user = Revship::session()->get('user');
        if (is_array($this->user) && isset($this->user['user_id']) && is_numeric($this->user['user_id']) && $this->user['user_id'] > 0 )
        {
            /*
            // Has Session? but Auto login cookie is missing?
            if( ! isset( $_COOKIE[ self::TOKEN_COOKIE_KEY]  ) )
            {
                $this->setLogout();
                return false;
            }
            */
            $this->isGuest = false;
        }
        else
        {
            return false;
        }
        if($this->user['user_type']>=99)
        {
            $this->isAdmin = true;
        }
        return true;
    }
    public function genAndSaveAutoLogin( $duration )
    {
        if(!$this->user)
        {
            return false;
        }
        Revship::model('UserToken');
        // If wanting unique login, need to delete existing, else not.
        $token_content = Revship::model('UserToken')->insert( $this->user['user_id'], null, UserToken::TOKEN_TYPE_WEBSITE_LOGIN, $duration);
        $this->saveAutoLoginCookie($token_content , $duration);
        return true;    
    }
    /**
     * Save Auto Login Cookie
     * @param $token_content    token
     * @param $duration             expire after seconds
     */
    protected function saveAutoLoginCookie( $token_content , $duration )
    {
        $this->removeAutoLoginCookie();
        setcookie(self::TOKEN_COOKIE_KEY, $token_content, time()+$duration, '/', Revship::lib('http')->getRootDomain()  ); 
    }
    protected function removeAutoLogin( $token_content=null, $token_type=null )
    {
        if( ( ! $token_content || ! $token_type ) && $this->user['user_id'] )
        {
            Revship::model('UserToken')->deleteByUserId( $this->user['user_id'] );
        }
        else
        {
            Revship::model('UserToken')->deleteByTokenContent( $token_content, $token_type );
        }
        $this->removeAutoLoginCookie();
    }
    protected function removeAutoLoginCookie()
    {
        setcookie(self::TOKEN_COOKIE_KEY, "", time()-3600, '/', Revship::lib('http')->getRootDomain());
    }
    protected function doAutoLoginFromCookie( $force = false )
    {
        // If it's logged in, skip.  (while it's not $force)
        if( ! $force && $this->isGuest == false )
        {
            return false;
        }
        // Else, ask User model to resume user
        if( isset( $_COOKIE[ self::TOKEN_COOKIE_KEY] ) && !empty( $_COOKIE[ self::TOKEN_COOKIE_KEY] ) )
        {
            Revship::model('UserToken');
            $token = Revship::model('UserToken')->getTokenByTokenContent( $_COOKIE[ self::TOKEN_COOKIE_KEY] , UserToken::TOKEN_TYPE_WEBSITE_LOGIN );
            $validToken = Revship::model('UserToken')->validate( $_COOKIE[ self::TOKEN_COOKIE_KEY] , UserToken::TOKEN_TYPE_WEBSITE_LOGIN ); 
            if( ! $validToken || empty($token) || ! $token['token_user_id']  )
            {
                // Not exists in DB, then kill cookie
                $this->removeAutoLoginCookie();
            }
            else
            {
                $user = Revship::model('User')->getUserById( $token['token_user_id'] );
                if( empty($user) || $user['user_type'] == User::TYPE_MANUAL_BLOCKED )
                {
                    // Invalid user or fake cookie.  Both DB and Cookie
                    $this->removeAutoLogin( $_COOKIE[ self::TOKEN_COOKIE_KEY] , UserToken::TOKEN_TYPE_WEBSITE_LOGIN );
                }
                else
                {
                    $this->setLogin($user);
                }
            }
        }
    }
    public function getUser($field=null)
    {
        if($field) return $this->user[$field];
        return $this->user;
    }
    public function getCookieToken()
    {
        return isset($_COOKIE[ self::TOKEN_COOKIE_KEY])?$_COOKIE[ self::TOKEN_COOKIE_KEY]:null;
    }
}
