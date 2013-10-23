<?php

require_once __DIR__ . "/../vendor/autoload.php";

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints as Assert;

use Hyone\FormExample\Util;
use Hyone\FormExample\Form\Type\MainType;


// Initialize

// date_default_timezone_set('Asia/Tokyo');

$app = new Silex\Application();
$app->register(new Silex\Provider\FormServiceProvider());
$app->register(new Silex\Provider\SessionServiceProvider());
$app->register(new Silex\Provider\TranslationServiceProvider(), array(
    // 'locale' => 'ja'
    'locale' => 'en'
));
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path'    => __DIR__ . '/../src/views/',
    'twig.options' => array('debug' => true),
));
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());
$app->register(new Silex\Provider\ValidatorServiceProvider());
$app['debug'] = true;


// Routing

$app->match('/', function (Silex\Application $app, Request $request) {
    $data = $app['session']->has('data')
        ? $app['session']->get('data')
        : array();

    $form = $app['form.factory']->create(
        new MainType(), $data,
        array()
        // // csrf_protection causes to unit test failure
        // // when we have multiple codeception functional tests for the same form.
        // array('csrf_protection' => false)
    );

    if ('POST' === $request->getMethod()) {
        $form->bind($request);

        error_log( "valid: " . $form->isValid() . "\n" );
        if ($form->isValid()) {
            $data = $form->getData();
            $app['session']->set('data', $data);
            return $app->redirect($app['url_generator']->generate('confirmation'));
        }
    }

    return $app['twig']->render('form.html.twig', array('form' => $form->createView()));
})
->bind('top');


$app->get('/confirmation', function (Silex\Application $app, Request $request) {
    if(! $app['session']->has('data')) {
        return $app->redirect($app['url_generator']->generate('top'));
    }

    $data = $app['session']->get('data');
    $form = $app['form.factory']->create(
        new MainType(), $data, array()
    );

    return $app['twig']->render('confirmation.html.twig', array(
        'form' => $form->createView(),
        'data' => $data,
    ));
})
->bind('confirmation');


$app->get('/success', function (Silex\Application $app, Request $request) {
    if(! $app['session']->has('data')) {
        return $app->redirect($app['url_generator']->generate('top'));
    }

    $data = $app['session']->get('data');
    $form = $app['form.factory']->create(
        new MainType(), $data, array()
    );

    Util::putData($app, $data, __DIR__ . '/../data.csv');

    // clear session
    $app['session']->remove('data');

    return $app['twig']->render('success.html.twig', array());
})
->bind('success');

return $app;
