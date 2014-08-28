<?php
/**
 * Created by PhpStorm.
 * User: kulemantu
 * Date: 8/28/14
 * Time: 9:47 AM
 */

class USSDaemon
{
    public $debug = false;

    public $actions;
    public $msisdn;
    public $session_id;
    public $service_code;
    public $ussd_string;

    protected $ussd_parts;
    protected $route;
    protected $menu;
    protected $parents;

    protected $get_vars = array(
        'MSISDN',
        'service_code',
        'session_id',
        'ussd_string',
    );

    function __get($key)
    {
        if (null !==
            $get_var = $this->get_vars($key)
        ) {
            return $get_var;
        }
    }

    /**
     * Get key value from $_GET variable if key is contained in $this->get_vars
     *  e.g. $this->ussd_string
     *
     * @param $key
     * @return null
     */
    function get_vars($key)
    {
        return in_array($key, $this->get_vars)
            ? (isset($this->$key)
                ? $this->$key
                : $this->$key = $_GET[$key]
            ) : null;
    }

    /**
     * Get the current action from the USSD String
     * @return string Route that has been calculated
     */
    function build_route()
    {
        $route = array();

        foreach ((array)$this->get_ussd_parts() as $part) {
            if ($part === '0') {
                if (count($route)) {
                    array_pop($route);
                }
                if ($this->debug) echo " < Back ";
                continue;
            }
            if ($this->debug) echo " > $part ";
            $route[] = $part;
        }

        return $this->route = implode(".", $route);
    }

    /**
     * Retrieve USSD parts from the USSD String
     * @return array
     */
    function get_ussd_parts()
    {
        return $this->ussd_parts = explode("*", $this->ussd_string);
    }

    /**
     * Show menu items passed or active menu on USSDaemon
     * @param array $menu_items
     * @return string
     */
    function render_menu($menu_items = null)
    {
        if (!$menu_items) {
            $menu_items = $this->get_active_menu();
        }

        $menu_text = array();

        foreach ((array)$menu_items as $index => $item) {
            if (!is_numeric($index) && !$this->debug) continue;
            if ($index == 0) $item = strtoupper($item);
            $menu_text[] = ($index) ? $index . ": " : null;
            $menu_text[] = (is_array($item)) ? $item[0] : $item;
            $menu_text[] = "\n";
        }

        if (!empty($this->route)) {
            $menu_text[] = "0: Back";
        }

        return implode("", $menu_text);
    }

    protected function get_active_menu()
    {
        $this->actions = static::build_actions($this->actions);

        if (empty($this->route)) {
            return $this->actions;
        }

        if ($active_menu = array_get($this->actions, $this->route)) {
            return $this->active_menu = $active_menu;
        }

        $route_parts = explode(".", $this->route);
        array_pop($route_parts);
        $this->route = implode(".", $route_parts);

        return $this->get_active_menu();
    }

    /**
     * Generate meta_data for a tree of actions
     *
     * @param $actions
     * @param int $level
     * @param null $parent
     * @param string $ref
     * @return mixed
     */
    static function build_actions($actions, $level = 0, $parent = null, $ref = '')
    {
        $actions['_ref'] = $ref;
        $actions['_level'] = $level;
        $actions['_parent'] = $parent;

        foreach ($actions as $index => $child_actions) {
            if (is_numeric($index) && is_array($child_actions)) {
                $actions[$index] = static::build_actions(
                    $child_actions,
                    $level + 1,
                    static::get_title($actions),
                    trim("{$actions['_ref']}.$index", ".")
                );
            }
        }

        return $actions;
    }

    /**
     * Get title for the menu option or menu option array
     * @param $array
     * @return mixed
     */
    static function get_title($array)
    {
        return is_array($array) ? @$array[0] : $array;
    }

    /**
     * Get title of active menu
     *
     * @return mixed
     */
    public function get_current_route()
    {
        return $this->get_title($this->get_active_menu());
    }

}

if (!function_exists('array_get')) {
    function array_get($array, $key, $default = null)
    {
        if (is_null($key)) return $array;

        // To retrieve the array item using dot syntax, we'll iterate through
        // each segment in the key and look for that value. If it exists, we
        // will return it, otherwise we will set the depth of the array and
        // look for the next segment.
        foreach (explode('.', $key) as $segment) {
            if (!is_array($array) or !array_key_exists($segment, $array)) {
                return value($default);
            }

            $array = $array[$segment];
        }

        return $array;
    }
}

if (!function_exists('value')) {
    function value($value)
    {
        return (is_callable($value) and !is_string($value)) ? call_user_func($value) : $value;
    }
}