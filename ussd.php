<?php
/**
 * Created by PhpStorm.
 * User: kulemantu
 * Date: 8/27/14
 * Time: 7:04 PM
 */

const DEBUG_MODE = false;

require_once 'ussdaemon.php';

$actions = require_once 'ussdactions.php';

// Initialise new USSDaemon
$ussd = new USSDaemon();

// Set Debug mode to TRUE to see extra data
$ussd->debug = DEBUG_MODE;

// Load actions into the USSDaemon
$ussd->actions = $actions;

// Build the route to get the active menu
$ussd->build_route();

echo "<pre>";
echo $ussd->render_menu();
echo "</pre><hr/>";

// Show the current route
echo $ussd->get_current_route();