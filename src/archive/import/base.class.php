<?php 
defined('REVSHIP') or exit('Access Denied!');

/**
 * Revship Import Abstract Class
 * @author Lijun Shen <lijunshen@revship.com>
 * @since 1.0.0
 */

abstract class Revship_Archive_Import_Base
{
    protected $cache = array(
        'LocationCountry' => array(),
        'LocationAdmin1' => array(),
        'LocationLocation' => array(),
        'User' => array(),
        'BusinessCategory' => array(),
        'Business'=>array(),
    );
    protected $originalDomain = null;
    /**
     * Convert from source to revship data format
     */
    abstract public function start();
    abstract protected function convertLocation();
    abstract protected function convertUser();
    abstract protected function convertUserPhoto($user_id);
    abstract protected function convertAdmin(); 
    abstract protected function convertBusinessCategory();
    abstract protected function convertBusiness();
    abstract protected function convertBusinessPhoto($business_id);
    abstract protected function convertBusinessReview();
    abstract protected function convertBusinessPromo();
    abstract protected function convertBookmark();
    
    /**
     * Return cached value
     */
    protected function getCache($cacheTable, $pk, $field = null)
    {
        if(isset($this->cache[$cacheTable][$pk]))
        {
            if($field != null)
            {
                if( isset( $this->cache[$cacheTable][$pk][$field] ) && !empty($this->cache[$cacheTable][$pk][$field]) )
                {
                    return $this->cache[$cacheTable][$pk][$field];
                }
                else
                {
                    return '';
                }
            }
            else
            {
                return $this->cache[$cacheTable][$pk];
            }
        }
        return '';
    }
    /**
     * Save cache value
     */
    protected function setCache($cacheTable, $pk, $value, $field = null)
    {
        if($field != null)
        {
            $this->cache[$cacheTable][$pk][$field] = $value;
        }
        else
        {
            $this->cache[$cacheTable][$pk] = $value;
        }
    }
    /*
     * Save to revship DB actions.
     */
    /**
     * Save Location Country
     * @param array $array
     * @code
     * $array = array(
                'country_code',
                'country_name',
                'country_capital',
                'continent_code',
                'continent_name',
                'currency',
                );
     * @endcode
     */
    protected function saveLocationCountry(array $array)
    {
        return Revship::model('LocationCountry')->insertDb($array);
    }
    /**
     * array(
                'country_code',
                'admin1_code',
                'admin1_name',
                'latitude',
                'longitude',
                'timezone',
                );
     */
    protected function saveLocationAdmin1(array $array)
    {
        return Revship::model('LocationAdmin1')->insertDb($array);
    }
    /**
     * array(
                'location_id',
                'location_name',
                'country_code',
                'admin1_code',
                'latitude',
                'longitude',
                'timezone',
                'important_city',
                );
     */
    protected function saveLocationLocation(array $array)
    {
        return Revship::model('LocationLocation')->insertDb($array);
    }
    /**
     * array(
        'user_id',
        'first_name',
        'last_name',
        'email',
        'username',
        'passwd',
        'zip',
        'gender',
        'birthday',
        'avatar_url',
        'reg_time',
        'headline',
        'location_id',
        'city_state_name',
        'friend_num',
        'review_num',
        'vote_good',
        'vote_bad',
        'compliment_num',
        'last_login_time',
        'last_login_ip',
        'fb_user_id',
        'token',
        'token_time',
        'user_type',
    );
     */
    protected function saveUser(array $array)
    {
        return Revship::model('User')->insertDb($array);
    }
    
    /**
     * Insert User Photo From Remote
     */
    protected function saveUserPhoto($user_id, $url)
    {
        return Revship::model('UserPhoto')->fetchPhotoFromRemote($user_id, $url);
    }
    /**
     * array(
            'category_id',
            'category_name',
            'category_level',
            'category_permalink',
            'parent_category_id',
            'custom_option_ids',
            'total_listing_num',
            'total_review_num',
            );
     */
    protected function saveBusinessCategory(array $array)
    {
        return Revship::model('BusinessCategory')->insertDb($array);
    }
    
    /**
     * array(
            'business_id',
            'business_name',
            'business_add1',
            'business_add2',
            'business_phone',
            'business_fax',
            'website_url',
            'location_id',
            'city_zip',
            'city_state_name',
            'cover_photo_file',
            'cat_1_level1_id',
            'cat_1_level1_name',
            'cat_1_level1_permalink',
            'cat_1_level2_id',
            'cat_1_level2_name',
            'cat_1_level2_permalink',
            'cat_1_level3_id',
            'cat_1_level3_name',
            'cat_1_level3_permalink',
            'cat_2_level1_id',
            'cat_2_level1_name',
            'cat_2_level1_permalink',
            'cat_2_level2_id',
            'cat_2_level2_name',
            'cat_2_level2_permalink',
            'cat_2_level3_id',
            'cat_2_level3_name',
            'cat_2_level3_permalink',
            'cat_3_level1_id',
            'cat_3_level1_name',
            'cat_3_level1_permalink',
            'cat_3_level2_id',
            'cat_3_level2_name',
            'cat_3_level2_permalink',
            'cat_3_level3_id',
            'cat_3_level3_name',
            'cat_3_level3_permalink',
            'owner_user_id',
            'create_user_id',
            'create_ip',
            'create_time',
            'modify_time',
            'listing_status',
            'premium_level',
            'premium_expire_time',
            'open_hours',
            'custom_options',
            'custom_option_1',
            'custom_option_2',
            'custom_option_3',
            'custom_option_4',
            'custom_option_5',
            'custom_option_6',
            'custom_option_7',
            'average_rating',
            'average_cost',
            'cost_currency',
            'click_num',
            'review_num',
            'map_lat',
            'map_lng',
            'map_zoom',
            'video_url',
            'brief_description',
            'long_description',
            'business_permalink',
            'promo_num',
            'opening_time'
            );
     */
    protected function saveBusiness(array $array)
    {
        return Revship::model('Business')->insertDb($array);
    }
    /**
     * array(
                'review_id',
                'business_id',
                'create_user_id',
                'create_user_name',
                'create_time',
                'create_ip',
                'review_status',
                'vote_1_num',
                'vote_2_num',
                'rating',
                'cost',
                'review_message',
                'business_location_id',
                'owner_reply',
                'owner_reply_time',
                );
     */
    protected function saveBusinessReview(array $array)
    {
        return Revship::model('BusinessReview')->insertDb($array);
    }
    /**
     * Fetch Photo From Remote
     */
    protected function saveBusinessPhoto($business_id, $url)
    {
        return Revship::model('BusinessPhoto')->fetchPhotoFromRemote($business_id, $url);
    }
    /**
     * array(
                'bookmark_id',
                'bookmark_type',
                'target_id',
                'target_location_id',
                'user_id',
                'create_time',
                );
     */
    protected function saveBookmark(array $array)
    {
        return Revship::model('Bookmark')->insertDb($array);
    }
    /**
     *  array(
                    'promo_id',
                    'business_id',
                    'promo_title',
                    'promo_cover_photo_file',
                    'promo_description',
                    'promo_url',
                    'promo_phone',
                    'promo_addr',
                    'promo_map_lat',
                    'promo_map_lng',
                    'promo_map_zoom',
                    'create_time',
                    'valid_from_time',
                    'valid_to_time',
                    'click_num',
                    'promo_status',
                    );
     */
    protected function saveBusinessPromo(array $array)
    {
        return Revship::model('BusinessPromo')->insertDb($array);
    }
}