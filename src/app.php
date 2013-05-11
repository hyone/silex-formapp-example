<?php

require_once __DIR__ . "/../vendor/autoload.php";

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints as Assert;

use Hyone\FormExample\Form\Type\MainType;


define("CSV_FILE", __DIR__."/../data.csv");


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
        new MainType(), $data, array()
    );

    if ('POST' === $request->getMethod()) {
        $form->bind($request);

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

    process($app, $form, $data);

    // clear session
    $app['session']->remove('data');

    return $app['twig']->render('success.html.twig', array());
})
->bind('success');


// data processing when success
// example: append to CSV
function process($app, $form, $data) {
    $fh = fopen(CSV_FILE, 'a');
    if ($fh === false) {
        throw new \Exception("Can't open file: " . CSV_FILE);
    }
    if (flock($fh, LOCK_EX)) {
        fputcsv($fh, $data);
        flock($fh, LOCK_UN);
        fclose($fh);
    } else {
        fclose($fh);
        throw new \Exception("Can't lock csv file: " . CSV_FILE);
    }
}

return $app;
