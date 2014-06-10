<?php  if ( ! defined('REVSHIP')) exit('No direct script access allowed');

/**
 * Router Class
 *
 * Parses URIs and determines routing
 *
 * @package        Revship
 * @subpackage    Library
 * @author        Lijun Shen <lijunshen@revship.com>
 * @category        Libraries
 * @link        
 */
class Revship_Router {

    var $_config;
    var $_uri;
    var $routes            = array();
    var $error_routes    = array();
    var $class            = '';
    var $method            = 'index';
    var $directory        = '';
    var $default_controller;
    
    /**
     * Constructor
     *
     * Runs the route mapping function.
     */
    function __construct()
    {
        $this->_config = & Revship::lib('config');
        $this->_uri = & Revship::lib('uri');
        
    }

    // --------------------------------------------------------------------

    /**
     * Set the route mapping
     *
     * This function determines what should be served based on the URI request,
     * as well as any "routes" that have been set in the routing config file.
     *
     * @return    void
     */
    public function _set_routing()
    {
        // Are query strings enabled in the config file?  Normally CI doesn't utilize query strings
        // since URI segments are more search-engine friendly, but they can optionally be used.
        // If this feature is enabled, we will gather the directory/class/method a little differently
        $segments = array();
        if ($this->_config->getItem('routes.enable_query_strings') === TRUE AND isset($_GET[$this->_config->getItem('routes.controller_trigger')]))
        {
            if (isset($_GET[$this->_config->getItem('routes.directory_trigger')]))
            {
                $this->set_directory(trim($this->_uri->_filter_uri($_GET[$this->_config->getItem('routes.directory_trigger')])));
                $segments[] = $this->fetch_directory();
            }

            if (isset($_GET[$this->_config->getItem('routes.controller_trigger')]))
            {
                $this->set_class(trim($this->_uri->_filter_uri($_GET[$this->_config->getItem('routes.controller_trigger')])));
                $segments[] = $this->fetch_class();
            }

            if (isset($_GET[$this->_config->getItem('routes.function_trigger')]))
            {
                $this->set_method(trim($this->_uri->_filter_uri($_GET[$this->_config->getItem('routes.function_trigger')])));
                $segments[] = $this->fetch_method();
            }
        }
        
        // Load the routes.php file.
        
        $this->routes = $this->_config->getItem('routes');
        // Set the default controller so we can display it in the event
        // the URI doesn't correlated to a valid controller.
        $this->default_controller = ( ! isset($this->routes['default_controller']) OR $this->routes['default_controller'] == '') ? FALSE : strtolower($this->routes['default_controller']);

        // Were there any query string segments?  If so, we'll validate them and bail out since we're done.
        if (count($segments) > 0)
        {
            return $this->_validate_request($segments);
        }

        // Fetch the complete URI string
        $this->_uri->_fetch_uri_string();
        
        // Is there a URI string? If not, the default controller specified in the "routes" file will be shown.
        if ($this->_uri->uri_string == '')
        {
            return $this->_set_default_controller();
        }

        // Do we need to remove the URL suffix?
        //$this->_uri->_remove_url_suffix();

        // Compile the segments into an array
        $this->_uri->_explode_segments();
    
        // Parse any custom routing that may exist
        // INCLUDES CUSTOMED URIS 
        $this->_parse_routes();
        // Re-index the segment array so that it starts with 1 rather than 0
        $this->_uri->_reindex_segments();
        
    }

    // --------------------------------------------------------------------

    /**
     * Set the default controller
     *
     * @access    private
     * @return    void
     */
    private function _set_default_controller()
    {
        if ($this->default_controller === FALSE)
        {
            new Revship_Exception(500, "Unable to determine what should be displayed. A default route has not been specified in the routing file.");
        }
        // Is the method being specified?
        if (strpos($this->default_controller, '/') !== FALSE)
        {
            $x = explode('/', $this->default_controller);

            $this->set_class($x[0]);
            $this->set_method($x[1]);
            $this->_set_request($x);
        }
        else
        {
            $this->set_class($this->default_controller);
            $this->set_method('index');
            $this->_set_request(array($this->default_controller, 'index'));
        }

        // re-index the routed segments array so it starts with 1 rather than 0
        $this->_uri->_reindex_segments();

    }

    // --------------------------------------------------------------------

    /**
     * Set the Route
     *
     * This function takes an array of URI segments as
     * input, and sets the current class/method
     *
     * @access    private
     * @param    array
     * @param    bool
     * @return    void
     */
    private function _set_request($segments = array())
    {
        $segments = $this->_validate_request($segments);

        if (count($segments) == 0)
        {
            return $this->_set_default_controller();
        }

        $this->set_class($segments[0]);

        if (isset($segments[1]))
        {
            // A standard method request
            $this->set_method($segments[1]);
        }
        else
        {
            // This lets the "routed" segment array identify that the default
            // index method is being used.
            $segments[1] = 'index';
        }

        // Update our "routed" segment array to contain the segments.
        // Note: If there is no custom routing, this array will be
        // identical to $this->_uri->segments
        $this->_uri->rsegments = $segments;
    }

    // --------------------------------------------------------------------

    /**
     * Validates the supplied segments.  Attempts to determine the path to
     * the controller.
     *
     * @access    private
     * @param    array
     * @return    array
     */
    private function _validate_request($segments)
    {
        if (count($segments) == 0)
        {
            return $segments;
        }

        // Does the requested controller exist in the root folder?
        if (file_exists(APP_PATH.'controller/'.$segments[0].'Controller.php'))
        {
            return $segments;
        }

        // Is the controller in a sub-folder?
        if (is_dir(APP_PATH.'controller/'.$segments[0]))
        {
            // Set the directory and remove it from the segment array
            $this->set_directory($segments[0]);
            $segments = array_slice($segments, 1);

            if (count($segments) > 0)
            {
                // Does the requested controller exist in the sub-folder?
                if ( ! file_exists(APP_PATH.'controller/'.$this->fetch_directory().$segments[0].'Controller.php'))
                {
                    new Revship_Exception(404,$this->fetch_directory().$segments[0]);
                }
            }
            else
            {
                // Is the method being specified in the route?
                if (strpos($this->default_controller, '/') !== FALSE)
                {
                    $x = explode('/', $this->default_controller);

                    $this->set_class($x[0]);
                    $this->set_method($x[1]);
                }
                else
                {
                    $this->set_class($this->default_controller);
                    $this->set_method('index');
                }

                // Does the default controller exist in the sub-folder?
                if ( ! file_exists(APP_PATH.'controller/'.$this->fetch_directory().$this->default_controller.'Controller.php'))
                {
                    $this->directory = '';
                    return array();
                }

            }

            return $segments;
        }


        // If we've gotten this far it means that the URI does not correlate to a valid
        // controller class.  We will now see if there is an override
        if (!empty($this->routes['404_override']))
        {
            $x = explode('/', $this->routes['404_override']);

            $this->set_class($x[0]);
            $this->set_method(isset($x[1]) ? $x[1] : 'index');

            return $x;
        }


        // Nothing else to do at this point but show a 404
         new Revship_Exception(404, 'Cannot find controller: '.$segments[0]);
    }

    // --------------------------------------------------------------------

    /**
     *  Parse Routes
     *
     * This function matches any routes that may exist in
     * the config/routes.php file against the URI to
     * determine if the class/method need to be remapped.
     *
     * @access    private
     * @return    void
     */
    private function _parse_routes()
    {
        // Turn the segment array into a URI string
        $uri = implode('/', $this->_uri->segments);
        
        // Is there a literal match?  If so we're done
        if (isset($this->routes[$uri]))
        {
            return $this->_set_request(explode('/', $this->routes[$uri]));
        }

        // Loop through the route array looking for wild-cards
        foreach ($this->routes as $key => $val)
        {
            // Convert wild-cards to RegEx
            $key = str_replace(':any', '.+', str_replace(':num', '[0-9]+', $key));

            // Does the RegEx match?
            if (preg_match('#^'.$key.'$#', $uri))
            {
                
                // Do we have a back-reference?
                if (strpos($val, '$') !== FALSE AND strpos($key, '(') !== FALSE)
                {
                    $val = preg_replace('#^'.$key.'$#', $val, $uri);
                }
                
                // Filter params, and assign to $_GET
                if($params = strstr($val, '?'))
                {
                    $paramsString = substr($params, 1);
                    $paramsArray = explode('&', $paramsString);
                    foreach ($paramsArray as $param)
                    {
                            preg_match('/(.[^=]*)=(.[^=]*)/',$param,$matches);
                            foreach ($matches as $key => $match)
                            {
                                if(strpos($match, '=') !== FALSE)
                                {
                                    unset($matches[$key]);
                                }
                            }
                            $matches = array_chunk($matches,2,false);
                            if(!empty($matches[0][0]))
                            {
                                $_GET[$matches[0][0]] = $matches[0][1];
                            }
                    }
                    $val = preg_replace('/(.[^\?]*)\?(.*)/', "$1", $val);
                }
                return $this->_set_request(explode('/', $val));
            }
        }

        // If we got this far it means we didn't encounter a
        // matching route so we'll set the site default route
        $this->_set_request($this->_uri->segments);
    }

    // --------------------------------------------------------------------

    /**
     * Set the class name
     *
     * @access    public
     * @param    string
     * @return    void
     */
    public function set_class($class)
    {
        $this->class = str_replace(array('/', '.'), '', $class);
    }

    // --------------------------------------------------------------------

    /**
     * Fetch the current class
     *
     * @access    public
     * @return    string
     */
    public function fetch_class()
    {
        return $this->class;
    }

    // --------------------------------------------------------------------

    /**
     *  Set the method name
     *
     * @access    public
     * @param    string
     * @return    void
     */
    public function set_method($method)
    {
        $this->method = $method;
    }

    // --------------------------------------------------------------------

    /**
     *  Fetch the current method
     *
     * @access    public
     * @return    string
     */
    public function fetch_method()
    {
        if ($this->method == $this->fetch_class())
        {
            return 'index';
        }

        return $this->method;
    }

    // --------------------------------------------------------------------

    /**
     *  Set the directory name
     *
     * @access    public
     * @param    string
     * @return    void
     */
    public function set_directory($dir)
    {
        $this->directory = str_replace(array('/', '.'), '', $dir).'/';
    }

    // --------------------------------------------------------------------

    /**
     *  Fetch the sub-directory (if any) that contains the requested controller class
     *
     * @access    public
     * @return    string
     */
    public function fetch_directory()
    {
        return $this->directory;
    }

    // --------------------------------------------------------------------

    /**
     *  Set the controller overrides
     *
     * @access    public
     * @param    array
     * @return    null
     */
    public function _set_overrides($routing)
    {
        if ( ! is_array($routing))
        {
            return;
        }

        if (isset($routing['directory']))
        {
            $this->set_directory($routing['directory']);
        }

        if (isset($routing['controller']) AND $routing['controller'] != '')
        {
            $this->set_class($routing['controller']);
        }

        if (isset($routing['function']))
        {
            $routing['function'] = ($routing['function'] == '') ? 'index' : $routing['function'];
            $this->set_method($routing['function']);
        }
    }


}
// END Router Class
