<?php
/**
 * Routes configuration
 *
 * In this file, you set up routes to your controllers and their actions.
 * Routes are very important mechanism that allows you to freely connect
 * different urls to chosen controllers and their actions (functions).
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2011, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2011, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Config
 * @since         CakePHP(tm) v 0.2.9
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
/**
 * Here, we are connecting '/' (base path) to controller called 'Pages',
 * its action called 'display', and we pass a param to select the view file
 * to use (in this case, /app/View/Pages/home.ctp)...
 */
    Router::connect('/', array('controller' => 'Offers', 'action' => 'index'));
    Router::connect('/index', array('controller' => 'Offers', 'action' => 'index'));
    Router::connect('/faq', array('controller'=>'Users', 'action' => 'faq') );
    Router::connect('/termsofuse', array('controller'=>'Users', 'action' => 'terms') );
/**
 * ...and connect the rest of 'Pages' controller's urls.
 */
    Router::connect('/pages/*', array('controller' => 'pages', 'action' => 'display'));

/**
 * Load all plugin routes.  See the CakePlugin documentation on
 * how to customize the loading of plugin routes.
 */
    CakePlugin::routes();

    // webservice aliases
    $ws = '[api|webservice]';

    Router::parseExtensions('rss', 'xml', 'json');

    Router::connect('/:ws/offer/activate/*',
        array(  'controller' => 'offers',
                'action' => 'activate',
                'ws' => $ws,
                '[method]' => 'GET'));

    Router::connect('/:ws/offer/terminate/*',
        array(  'controller' => 'offers',
                'action' => 'terminate',
                'ws' => $ws,
                '[method]' => 'GET'));

    Router::connect('/:ws/offer/*',
        array(  'controller' => 'offers',
                'action' => 'view',
                'ws' => $ws,
                '[method]' => 'GET'));

    Router::connect('/:ws/offers/*',
        array(  'controller' => 'offers',
                'action' => 'index',
                'ws' => $ws,
                '[method]' => 'GET'));

    Router::connect('/:ws/offer/*',
        array(  'controller' => 'offers',
                'action' => 'edit',
                'ws' => $ws,
                '[method]' => 'PUT'));

    Router::connect('/:ws/offer',
        array(  'controller' => 'offers',
                'action' => 'webservice_add',
                'ws' => $ws,
                '[method]' => 'POST'));

    Router::connect('/:ws/users/login',
        array(  'controller' => 'users',
                'action' => 'login',
                'ws' => $ws,
                '[method]' => 'POST'));

/**
 * Load the CakePHP default routes. Remove this if you do not want to use
 * the built-in default routes.
 */
    require CAKE . 'Config' . DS . 'routes.php';
