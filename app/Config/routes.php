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
    $ws = 'api|webservice';

    Router::parseExtensions('rss', 'xml', 'json');

    Router::connect('/:ws/vote/vote_up/*',
        array(  'controller' => 'votes',
                'action' => 'vote_up',
                '[method]' => 'GET'),
        array('ws' => $ws));

    Router::connect('/:ws/vote/vote_down/*',
        array(  'controller' => 'votes',
                'action' => 'vote_down',
                '[method]' => 'GET'),
        array('ws' => $ws));

    Router::connect('/:ws/vote/vote_cancel/*',
        array(  'controller' => 'votes',
                'action' => 'vote_cancel',
                '[method]' => 'GET'),
        array('ws' => $ws));

    Router::connect('/:ws/offer/activate/*',
        array(  'controller' => 'offers',
                'action' => 'activate',
                '[method]' => 'GET'),
        array('ws' => $ws));

    Router::connect('/:ws/offer/terminate/*',
        array(  'controller' => 'offers',
                'action' => 'terminate',
                '[method]' => 'GET'),
        array('ws' => $ws));


    Router::connect('/:ws/offer/*',
        array(  'controller' => 'offers',
                'action' => 'view',
                '[method]' => 'GET'),
        array('ws' => $ws));

    Router::connect('/:ws/offers/happyhour/*',
        array(  'controller' => 'offers',
                'action' => 'happyhour',
                '[method]' => 'GET'),
        array('ws' => $ws));

    Router::connect('/:ws/offers/coupons/*',
        array(  'controller' => 'offers',
                'action' => 'coupons',
                '[method]' => 'GET'),
        array('ws' => $ws));

    Router::connect('/:ws/offers/limited/*',
        array(  'controller' => 'offers',
                'action' => 'limited',
                '[method]' => 'GET'),
        array('ws' => $ws));

    Router::connect('/:ws/offers/category/*',
        array(  'controller' => 'offers',
                'action' => 'category',
                '[method]' => 'GET'),
        array('ws' => $ws));

    Router::connect('/:ws/offers/statistics/*',
        array(  'controller' => 'offers',
                'action' => 'statistics',
                '[method]' => 'GET'),
        array('ws' => $ws));

    Router::connect('/:ws/offers/*',
        array(  'controller' => 'offers',
                'action' => 'index',
                '[method]' => 'GET'),
        array('ws' => $ws));

    Router::connect('/:ws/offer/*',
        array(  'controller' => 'offers',
                'action' => 'edit',
                '[method]' => 'PUT'),
        array('ws' => $ws));

    Router::connect('/:ws/offer',
        array(  'controller' => 'offers',
                'action' => 'webservice_add',
                '[method]' => 'POST'),
        array('ws' => $ws));

    Router::connect('/:ws/users/login',
        array(  'controller' => 'users',
                'action' => 'login',
                '[method]' => 'POST'),
        array('ws' => $ws));

    Router::connect('/:ws/users/coordinates/*',
        array(  'controller' => 'users',
                'action' => 'coords',
                '[method]' => 'GET'),
        array('ws' => $ws));

    Router::connect('/:ws/users/radius/*',
        array(  'controller' => 'users',
                'action' => 'radius',
                '[method]' => 'GET'),
        array('ws' => $ws));

    Router::connect('/:ws/coupons',
        array(  'controller' => 'coupons',
                'action' => 'index',
                '[method]' => 'GET'),
        array('ws' => $ws));

    Router::connect('/:ws/coupons/reinsert/*',
        array(  'controller' => 'coupons',
                'action' => 'reinsert',
                '[method]' => 'GET'),
        array('ws' => $ws));

    Router::connect('/:ws/coupon/*',
        array(  'controller' => 'coupons',
                'action' => 'view',
                '[method]' => 'GET'),
        array('ws' => $ws));

    Router::connect('/:ws/coupon/*',
        array(  'controller' => 'coupons',
                'action' => 'add',
                '[method]' => 'POST'),
        array('ws' => $ws));

    Router::connect('/:ws/search/*',
        array(  'controller' => 'offers',
                'action' => 'search',
                '[method]' => 'GET'),
        array('ws' => $ws));

/**
 * Load the CakePHP default routes. Remove this if you do not want to use
 * the built-in default routes.
 */
    require CAKE . 'Config' . DS . 'routes.php';
