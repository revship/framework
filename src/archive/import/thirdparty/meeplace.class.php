<?php 
defined('REVSHIP') or exit('Access Denied!');

/**
 * Revship Import From Meeplace DB
 * @author Lijun Shen <lijunshen@revship.com>
 * @since 1.0.0
 */
ini_set("max_execution_time", 20000);
ini_set ('memory_limit', '1024M');
error_reporting(E_ALL ^ E_NOTICE);ini_set('display_errors', true);

Revship::getLibClass('revship.archive.import.base');
class Revship_Archive_Import_Thirdparty_Meeplace extends Revship_Archive_Import_Base
{
    protected $sourceDb = null;
    protected $meeSiteConfig = array();
    /**
     * 
     * @param array $dbConfig
    'table_prefix' => 'rs_',
    'host' => 'localhost',
    'user' => 'revship',
    'password' => 'revship',
    'port' => 3306,
    'dbname' => 'revship',
    'encoding' => 'utf8',
     */
    public function __construct($sourceDbConfig)
    {
        $this->sourceDb = Revship::lib('database.driver.mysql', $sourceDbConfig);
        if($this->sourceDb->isConnected())
        {
            $this->initMeeSiteConfig();
        }
        else
        {
            throw new Exception(Revship::l('Wrong database config or cannot be connected.') . $sourceDbConfig['user'] . '@' . $sourceDbConfig['host']);
        }
    }
    public function initMeeSiteConfig()
    {
        Revship::log('Reading Configure....','TRACE');
        $configs = $this->sourceDb->getAll("SELECT * FROM `page` ;");
        if(!count($configs))
        {
            return false;
        }
        foreach ($configs as $conf)
        {
            $this->meeSiteConfig[$conf['page']] = $conf['html'];
        }
        Revship::log('Domain:: '.$this->meeSiteConfig['domain'],'TRACE');
        Revship::log('End Configure.','TRACE');
    }
    public static function testConversionAbility($dbConfig)
    {
        ini_set("max_execution_time", 10);
        ini_set ('memory_limit', '32M');
        try{
            Revship::lib('database.driver.mysql',$dbConfig);
        }
        catch (Exception $e)
        {
            throw new Exception(Revship::l('Wrong database config or cannot be connected.'));
        }
        $obj = new Revship_Archive_Import_Thirdparty_Meeplace($dbConfig);
        if(!$obj->testGetSourceDomain())
        {
            throw new Exception(Revship::l('Failed to fetch the domain name info of source site from database.'));
        }
        if(!$obj->testDownloadPicture())
        {
            throw new Exception(Revship::l('Failed to fetch image files on source site like following:'). "<br><a style='font-size:9px' href='http://{$_SESSION['temp']}' target='_blank'>http://{$_SESSION['temp']}</a>");
        }
        return true;
    }
    protected function testGetSourceDomain()
    {
        if(isset($this->meeSiteConfig['domain']) && !empty($this->meeSiteConfig['domain']))
        {
            return $this->meeSiteConfig['domain'];
        }
        else
        {
            return false;
        }
    }
    protected function testDownloadPicture()
    {
        $sql = "SELECT photo_url FROM `business_photo` ORDER BY id DESC limit 1;";
        $photoUrl = $this->sourceDb->getOne($sql);
        $url = 'http://'.$this->meeSiteConfig['domain'] . '/images/business/b_' . $photoUrl;
        Revship::log('Test Pic:: '.$url,'TRACE');
        $httpCode = Revship::lib('curl')->getHttpCode($url);
        if($httpCode == 404)
        {
            $_SESSION['temp']=$url;
            return false;
        }
        return true;
    }
    public function start()
    {
        //Revship::lib('config')->clearCache();
        $this->convertLocation();
        $this->convertUser();
        $this->convertAdmin();
        $this->convertBusinessCategory();
        $this->convertBusiness();
        $this->convertBusinessReview();
        $this->convertBusinessPromo();
        $this->convertBookmark();
        
        //Final Process
        Revship::model('Business')->recountAllBusinessStatistic();
        //Revship::model('Business')->refreshAllBusinessCategory();
        Revship::model('Business')->refreshAllBusinessLocation();
        Revship::model('BusinessCategory')->recountTotalListingNum();
        Revship::model('BusinessCategory')->recountTotalReviewNum();
        Revship::model('LocationLocation')->recountTotalListingNum();
        Revship::model('LocationLocation')->recountTotalReviewNum();
        
        return true;
    }
    protected function convertLocation()
    {
        Revship::log('Import Country Started.','TRACE');
        //country
        $country = array(
                'country_code'=>'NA',
                'country_name'=>'Unknown',
                'country_capital'=>'Unknown',
                'continent_code'=>'NA',
                'continent_name'=>'North America',
                'currency'=>'USD',
                );
        $ok = $this->saveLocationCountry($country);
        Revship::log('Insert: '.$country['country_name']. ': ' . $ok,'TRACE');
        $this->setCache('LocationCountry', $country['country_code'], $country);
        Revship::lib('config')->setItems('site', 
                array(
                'locationStartLevel' => 'admin1',  //continent','country','admin1','location
                'locationStartParentCode' => $country['country_code'], 
                ));
        Revship::log('Import Country End.','TRACE');
        //province
        Revship::log('Import Admin1 Started.','TRACE');
        $sql = 'SELECT * FROM `province`';
        if ($result = $this->sourceDb->getResultResource($sql))
        {
            while($row = mysql_fetch_assoc($result))
            {
                $new = array(
                'country_code' => $country['country_code'],
                'admin1_code' => $row['province_id'],
                'admin1_name' => $row['province_name'],
               // 'latitude' => 0, 'longitude' => 0, 'timezone',
                );
                $ok = $this->saveLocationAdmin1($new);
                Revship::log('Insert: '.$new['admin1_name']. ': ' . $ok,'TRACE');
                $this->setCache('LocatioAdmin1', $row['province_id'], $new);
            }
            mysql_free_result($result);
        } 
        //city
        Revship::log('Import Location Started.','TRACE');
        $sql = 'SELECT * FROM `city`';
        if ($result = $this->sourceDb->getResultResource($sql))
        {
            while($row = mysql_fetch_assoc($result))
            {
                $new = array(
                'location_id' => $row['city_id'],
                'location_name' => $row['city_name'],
                'country_code' => $country['country_code'],
                'admin1_code' => $row['city_state'],
                //'latitude',   'longitude',   'timezone',   'important_city',
                );
                $this->saveLocationLocation($new);
                Revship::log('Insert: '.$new['location_name']. ': ' . $ok,'TRACE');
                $this->setCache('LocationLocation', $row['city_id'], $new);
            }
            mysql_free_result($result);
        } 
        Revship::log('Import Location Finished.','TRACE');
    }
    protected function convertUser()
    {
        Revship::log('Import User Started.','TRACE');
        $sql = 'SELECT * FROM `user`';
        if ($result = $this->sourceDb->getResultResource($sql))
        {
            while($row = mysql_fetch_assoc($result))
            {
                $new = array(
                    'user_id' => $row['user_id'],
                    'first_name' => $row['first_name'],
                    'last_name' => $row['last_name'],
                    'email' => $row['email'],
                    'username' => $row['nickname'],
                    'passwd' => Revship::model('User')->encodePasswordFromMd5Passsword($row['password']),
                    'zip' => $row['zip'],
                    'gender' => ($row['gender']==1)?1:0,
                    'birthday' => strtotime($row['birthday']),
                    'avatar_url' => '',
                    'reg_time' => strtotime($row['reg_date']),
                    'headline' => $row['headline'],
                    'location_id' => $row['city'],
                    'city_state_name' => $this->getCache('LocationLocation', $row['city'], 'location_name'),
                    'friend_num' => $row['friends_num'],
                    'review_num' => $row['reviews_num'],
                    // 'vote_good',  'vote_bad',  'compliment_num',
                    'last_login_time' => strtotime($row['last_login_date']),
                    //'last_login_ip',
                    'fb_user_id' => $row['fb_user'],
                    // 'token', 'token_time',  'user_type',
                );
                $ok = $this->saveUser($new);
                Revship::log('Insert: '.$new['email']. ': ' . $ok,'TRACE');
                $this->setCache('User', $row['user_id'], $new);
                $this->convertUserPhoto($row['user_id']);
            }
            mysql_free_result($result);
        } 
        Revship::log('Import User Finished.','TRACE');
    }
    protected function convertUserPhoto($user_id)
    {
        $sql = "SELECT * FROM `user_photo` WHERE user_id = {$user_id};";
        if ($result = $this->sourceDb->getResultResource($sql))
        {
            while($row = mysql_fetch_assoc($result))
            {
                $url = 'http://'.$this->meeSiteConfig['domain'] . '/images/photos/p_' . $row['photo_url'];
                $ok = $this->saveUserPhoto($user_id, $url);
                Revship::log('Insert: '.$url. ': ' . $ok,'TRACE');
            }
            mysql_free_result($result);
        } 
    }
    protected function convertAdmin()
    {
        Revship::log('Import Admin Started.','TRACE');
        $sql = "SELECT * FROM `admin` ";
        if ($result = $this->sourceDb->getResultResource($sql))
        {
            while($row = mysql_fetch_assoc($result))
            {
                if(Revship::model('User')->emailExists($row['adminuser']))
                {
                    $fields = array('user_type' => UserBase::TYPE_ADMIN);
                    Revship::model('User')->updateDb( $row['adminuser'], $fields, 'email');
                }
                else
                {
                    $new = array(
                    'first_name' => 'Admin',
                    'email' => $row['adminuser'],
                    'passwd' => Revship::model('User')->encodePasswordFromMd5Passsword($row['password']),
                    'gender' => 1,
                    'birthday' => strtotime('today'),
                    'avatar_url' => '',
                    'reg_time' => strtotime('today'),
                    'headline' => '',
                    'location_id' => 0,
                    'city_state_name' => '',
                    /*
                    'friend_num' => $row['friends_num'],
                    'review_num' => $row['reviews_num'],
                     'vote_good',  'vote_bad',  'compliment_num',
                     
                    'last_login_time' => time(),
                    //'last_login_ip',
                    'fb_user_id' => $row['fb_user'],
                    // 'token', 'token_time',  
                     * */
                    'user_type' => UserBase::TYPE_ADMIN,
                );
                $ok = $this->saveUser($new);
                Revship::log('Insert: '.$new['email']. ': ' . $ok,'TRACE');
                $this->setCache('User', $row['user_id'], $new);
                }
            }
            mysql_free_result($result);
        } 
        Revship::log('Import Admin Finished.','TRACE');
    }
    protected function convertBusinessCategory()
    {
        Revship::log('Import Category Started.','TRACE');
        // Level 1
        $sql = 'SELECT * FROM `business_category`';
        if ($result = $this->sourceDb->getResultResource($sql))
        {
            while($row = mysql_fetch_assoc($result))
            {
                $new = array(
                    'category_id' => (100000 + intval($row['cat_id'])), // level 1 plus 100,000
                    'category_name' => $row['cat_name'],
                    'category_level' => 1,
                    'category_permalink' => $row['permalink'],
                    'parent_category_id' => 0,
                    //'custom_option_ids',
                    //'total_listing_num',
                    //'total_review_num',
                    );
                $ok = $this->saveBusinessCategory($new);
                Revship::log('Insert: '.$new['category_name']. ': ' . $ok,'TRACE');
                $this->setCache('BusinessCategory', $new['category_id'], $new);
            }
            mysql_free_result($result);
        }
        // Level 2
        $sql = 'SELECT * FROM `business_sub_category`';
        if ($result = $this->sourceDb->getResultResource($sql))
        {
            while($row = mysql_fetch_assoc($result))
            {
                $new = array(
                    'category_id' => (200000 + intval($row['b_sub_cat_id'])), // level 2 plus 200,000
                    'category_name' => $row['b_subcat_name'],
                    'category_level' => 2,
                    'category_permalink' => $row['permalink'],
                    'parent_category_id' => (100000 + intval($row['b_maincat_id'])),
                    //'custom_option_ids',
                    //'total_listing_num',
                    //'total_review_num',
                    );
                $ok = $this->saveBusinessCategory($new);
                Revship::log('Insert: '.$new['category_name']. ': ' . $ok,'TRACE');
                $this->setCache('BusinessCategory', $new['category_id'], $new);
            }
            mysql_free_result($result);
        }
        Revship::log('Import Category Finished.','TRACE');
    }
    protected function convertBusiness()
    {
        Revship::model('Business');
        Revship::log('Import Business Started.','TRACE');
        $sql = 'SELECT * FROM `business`';
        if ($result = $this->sourceDb->getResultResource($sql))
        {
            while($row = mysql_fetch_assoc($result))
            {
                $new =array(
                        'business_id' => $row['business_id'],
                        'business_name' => $row['business_name'],
                        'business_add1' => $row['business_add1'],
                        'business_add2' => $row['business_add2'],
                        'business_phone' => $row['business_phone'],
                        //'business_fax' => $row['business_id'],
                        'website_url' => $row['business_web'],
                        'location_id' => $row['city_id'],
                        'city_zip' => $row['zip'],
                        //'city_state_name' => $this->getCache('LocationLocation', $row['city_id'], 'location_name'),
                        //'cover_photo_file' => $row['business_id'],
                        'cat_1_level1_id' => intval(100000+intval($row['cat_id'])),
                        'cat_1_level1_name' => $this->getCache('BusinessCategory',  (100000+intval($row['cat_id'])), 'category_name'),
                        'cat_1_level1_permalink' => $this->getCache('BusinessCategory',  (100000+intval($row['cat_id'])), 'category_permalink'),
                        'cat_1_level2_id' => $row['sub_cat_id']?intval(200000+intval($row['sub_cat_id'])):0,
                        'cat_1_level2_name' => $this->getCache('BusinessCategory',  (200000+intval($row['sub_cat_id'])), 'category_name'),
                        'cat_1_level2_permalink' => $this->getCache('BusinessCategory',  (200000+intval($row['sub_cat_id'])), 'category_permalink'),
                        'cat_2_level1_id' => $row['cat_id2']?intval(100000+intval($row['cat_id2'])):0,
                        'cat_2_level1_name' => $this->getCache('BusinessCategory',  (100000+intval($row['cat_id2'])), 'category_name'),
                        'cat_2_level1_permalink' => $this->getCache('BusinessCategory',  (100000+intval($row['cat_id2'])), 'category_permalink'),
                        'cat_2_level2_id' => $row['sub_cat_id2']?intval(200000+intval($row['sub_cat_id2'])):0,
                        'cat_2_level2_name' => $this->getCache('BusinessCategory',  (200000+intval($row['sub_cat_id2'])), 'category_name'),
                        'cat_2_level2_permalink' => $this->getCache('BusinessCategory',  (200000+intval($row['sub_cat_id2'])), 'category_permalink'),
                        'cat_3_level1_id' => $row['cat_id3']?intval(100000+intval($row['cat_id3'])):0,
                        'cat_3_level1_name' => $this->getCache('BusinessCategory',  (100000+intval($row['cat_id3'])), 'category_name'),
                        'cat_3_level1_permalink' => $this->getCache('BusinessCategory',  (100000+intval($row['cat_id3'])), 'category_permalink'),
                        'cat_3_level2_id' => $row['sub_cat_id3']?intval(200000+intval($row['sub_cat_id3'])):0,
                        'cat_3_level2_name' => $this->getCache('BusinessCategory',  (200000+intval($row['sub_cat_id3'])), 'category_name'),
                        'cat_3_level2_permalink' => $this->getCache('BusinessCategory',  (200000+intval($row['sub_cat_id3'])), 'category_permalink'),
                        'owner_user_id' => $row['user_id'],
                        'create_user_id' => $row['submitter_id'],
                        //'create_ip' => $row['business_id'],
                        'create_time' => strtotime($row['submit_date']), 
                        //'modify_time' => $row['business_id'],
                        'listing_status' => ($row['approved'] == '1') ? BusinessBase::LISTING_STATUS_APPROVED : BusinessBase::LISTING_STATUS_UNAPPROVED, 
                        'premium_level' => ($row['starbiz'] == '0') ? BusinessBase::PREMIUM_LEVEL_NONE : $row['starbiz'],
                        'premium_expire_time' => strtotime('+1 year'),
                        'open_hours' => serialize(Revship::model('Business')->genOpenHoursArrayFromWeekAndHour($row['weeks'], $row['from_hour'], $row['to_hour'])),
                        'average_rating' => $row['rating'],
                        'click_num' => $row['click'],
                        'review_num' => $row['reviews_num'],
                        'map_lat' => $row['y'],
                        'map_lng' => $row['x'],
                        'map_zoom' => $row['zoom'],
                        'video_url' => $row['video_url'],
                        'brief_description' => mb_substr(str_replace("\n"," ",$row['description']), 0,70),
                        'long_description' => $row['description'],
                        'business_permalink' => $row['permalink'],
                        //'opening_time'
                        );
                $ok = $this->saveBusiness($new);
                Revship::log('Insert: '.$new['business_name']. ': ' . $ok,'TRACE');
                $this->setCache('Business', $row['business_id'], $new);
                $this->convertBusinessPhoto($row['business_id']);
            }
            mysql_free_result($result);
        } 
        Revship::log('Import Business Finished.','TRACE');
    }
    protected function convertBusinessPhoto($business_id)
    {
        $sql = "SELECT * FROM `business_photo` WHERE business_id = {$business_id};";
        if ($result = $this->sourceDb->getResultResource($sql))
        {
            while($row = mysql_fetch_assoc($result))
            {
                $url = 'http://'.$this->meeSiteConfig['domain'] . '/images/business/b_' . $row['photo_url'];
                $ok = $this->saveBusinessPhoto($business_id, $url);
                Revship::log('Insert: '.$url. ': ' . $ok,'TRACE');
            }
            mysql_free_result($result);
        } 
    }
    protected function convertBusinessReview()
    {
        Revship::model('BusinessReview');
        Revship::log('Import Business Review Started.','TRACE');
        $sql = 'SELECT * FROM `business_reviews`';
        if ($result = $this->sourceDb->getResultResource($sql))
        {
            while($row = mysql_fetch_assoc($result))
            {
                $new = array(
                    'review_id'=>$row['review_id'],
                    'business_id' =>$row['business_id'],
                    'create_user_id'=>$row['user_id'],
                    'create_user_name'=>$this->getCache('User', $row['user_id'], 'first_name').' '.$this->getCache('User', $row['user_id'], 'last_name'),
                    'create_time'=>strtotime($row['review_date']),
                    //'create_ip',
                    'review_status'=>($row['approved'] == '1') ? BusinessReviewBase::REVIEW_STATUS_APPROVED : BusinessReviewBase::REVIEW_STATUS_UNAPPROVED,
                    //'vote_1_num',
                    //'vote_2_num',
                    'rating'=>$row['rating'],
                    //'cost',
                    'review_message'=>$row['review_desc'],
                    'business_location_id'=>$row['city_id'],
                    'owner_reply'=>$row['owner_reply'],
                    'owner_reply_time'=>strtotime($row['owner_reply_date']),
                );
                $ok = $this->saveBusinessReview($new);
                Revship::log('Insert: '.$new['review_id']. ': ' . $ok,'TRACE');
                //$this->setCache('BusinessReview', $new['category_id'], $new);
            }
            mysql_free_result($result);
        }
        Revship::log('Import Business Review Finished.','TRACE');
    }
    protected function convertBusinessPromo()
    {
        Revship::model('BusinessPromo');
        Revship::log('Import Business Promotion Started.','TRACE');
        $sql = 'SELECT * FROM `special_offer`';
        if ($result = $this->sourceDb->getResultResource($sql))
        {
            while($row = mysql_fetch_assoc($result))
            {
                $new = array(
                    'promo_id' => $row['offer_id'],
                    'business_id'=> $row['business_id'],
                    'promo_title'=>$this->getCache('Business', $row['business_id'], 'business_name'),
                    'promo_cover_photo_file' => '',
                    'promo_description'=>$row['offer_description'],
                    'promo_url'=>$row['offer_url'],
                    'promo_phone'=>$row['offer_phone'],
                    'promo_addr'=>$this->getCache('Business', $row['business_id'], 'business_add1'),
                    'promo_map_lat'=>$this->getCache('Business', $row['business_id'], 'map_lat'),
                    'promo_map_lng'=>$this->getCache('Business', $row['business_id'], 'map_lng'),
                    'promo_map_zoom'=>$this->getCache('Business', $row['business_id'], 'map_zoom'),
                    'create_time'=>strtotime($row['offer_submit_time']),
                    'valid_from_time'=>time(),
                    'valid_to_time'=>strtotime('+1 year'),
                    'click_num'=>0,
                    'promo_status'=>BusinessPromoBase::STATUS_NORMAL,
                    );
                $ok = $this->saveBusinessPromo($new);
                Revship::log('Insert: '.$new['promo_title']. ': ' . $ok,'TRACE');
                //$this->setCache('BusinessReview', $new['category_id'], $new);
            }
            mysql_free_result($result);
        }
        Revship::log('Import Business Promotion Finished.','TRACE');
    }
    protected function convertBookmark()
    {
        Revship::log('Import Bookmark Started.','TRACE');
        $sql = 'SELECT * FROM `bookmark`';
        if ($result = $this->sourceDb->getResultResource($sql))
        {
            while($row = mysql_fetch_assoc($result))
            {
                $new =  array(
                'bookmark_id'=>$row['bookmark_id'],
                'bookmark_type'=>1,
                'target_id'=>$row['business_id'],
                'target_location_id'=>$row['city_id'],
                'user_id'=>$row['user_id'],
                'create_time'=>strtotime($row['bookmark_date']),
                );
                $ok = $this->saveBookmark($new);
                Revship::log('Insert: '.$new['bookmark_id']. ': ' . $ok,'TRACE');
                //$this->setCache('BusinessReview', $new['category_id'], $new);
            }
            mysql_free_result($result);
        }
        Revship::log('Import Bookmark Finished.','TRACE');
    }
}
