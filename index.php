<?php
require_once __DIR__.'/vendor/autoload.php';
use Symfony\Component\HttpFoundation\Response;

$useTwig = getenv('TWIG') == 1;
// error_log("[twig] $useTwig \n", 3, __DIR__.'/php.log');

$loader = new Twig_Loader_Filesystem( __DIR__.'/twig');
$twig = new Twig_Environment($loader, array(
    // 'cache' =>  __DIR__.'/./twig/cache',
    'autoescape' => false,
));

use Symfony\Component\Yaml\Parser;

$yaml = new Parser();
$config = $yaml->parse(file_get_contents(__DIR__.'/config.yml'));

$app = new Silex\Application();

$app->get('/{views}', function ($views) use ($app, $twig, $yaml, $config) {
    $ids = explode('/', $views);
    $result = '';
    $i = 1;
    $specs = array();
    $runtimes = array();

    foreach($ids as $id){
        if($id != '') {
            $spec = file_get_contents(__DIR__.'/specs/spec'.$id.'.yml');
            if ($spec != null) {
                $spec = str_replace('$DATA_PATH', $config['dataPath'], $spec);
                $specs[] = $yaml->parse($spec);
                // $specs[] = $spec;
                $runtime = file_get_contents(__DIR__.'/specs/runtime'.$id.'.yml');
                if ($runtime !== null) {
                    $runtimes[] = $yaml->parse($runtime);
                    // $runtimes[] = $runtime;
                } else {
                    $runtimes[] = null;
                }
            }
        }
    }

    $config['specs'] = $specs;
    $config['runtimes'] = $runtimes;
    $config = json_encode($config);
    $config = str_replace('\'', '\"', $config); // to fix for instance: "span(brush) ? invert('xOverview', brush) : null"
    return $twig->render('index.html', array('config' => $config));
})
->assert('views', '.*');

$app->run();
