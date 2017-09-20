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
use Symfony\Component\Yaml\Dumper;
$yamlParser = new Parser();
$yamlDumper = new Dumper();
$config = $yamlParser->parse(file_get_contents(__DIR__.'/config.yaml'));

$app = new Silex\Application();

$getSpecs = function ($ids) use ($config, $yamlParser) {
    $result = '';
    $specs = array();

    $add = function ($specId, $runtime) use (&$specs, $yamlParser, $config) {
        $spec = file_get_contents(__DIR__.'/specs/spec'.$specId.'.yaml');
        if ($spec != null) {
            $spec = str_replace('$_DATA_PATH', $config['dataPath'], $spec);
            $spec = str_replace('$_IMAGE_PATH', $config['imagePath'], $spec);
            $spec = $yamlParser->parse($spec);
            if ($runtime == null) {
                $specs[$specId] = $spec;
            } else {
                $specs[$specId] = array($spec, $runtime);
            }
            // error_log($specId . ' > ' . sizeof($specs) . "\n", 3, __DIR__.'/php.log');
        }
    };

    foreach($ids as $id){
        if($id != '') {
            if (file_exists(__DIR__.'/specs/runtime'.$id.'.yaml')) {
                $runtime = file_get_contents(__DIR__.'/specs/runtime'.$id.'.yaml');
                $runtime =  $yamlParser->parse($runtime);
                $spec = $runtime['spec'];
                if (isset($spec)) {
                    $add($id, $runtime);
                } else if (file_exists(__DIR__.'/specs/spec'.$id.'.yaml')) {
                    $add($id, $runtime);
                }
            } else if (file_exists(__DIR__.'/specs/spec'.$id.'.yaml')) {
                $add($id, null);
            }
        }
    }

    $config['specs'] = $specs;
    $config = json_encode($config);
    $config = str_replace('\'', '\"', $config); // to fix for instance: "span(brush) ? invert('xOverview', brush) : null"
    return $config;

    // use bson if the spec has inlined data containing huge number, for instance topo,json data
    // $config = MongoDB\BSON\fromPHP($config);
    // $config = bin2hex($config);
    // yaml is sometimes a bit smaller compared to json
    // $config = $yamlDumper->dump($config, 1);
    // return $config;
};

// statically serve spec
$app->get('/specs/{file}', function ($file) use ($app) {
    $file = __DIR__.'/specs/'.$file;
    $stream = function () use ($file) {
        readfile($file);
    };
    $contentType = 'application/json';
    return $app->stream($stream, 200, array('Content-Type' => $contentType));
});

$app->get('/json/{views}', function ($views) use ($app, $twig, $getSpecs) {
    $ids = explode('/', $views);
    $config = $getSpecs($ids);
    return $config;
})
->assert('views', '.*');

$app->get('/{views}', function ($views) use ($app, $twig, $getSpecs) {
    $ids = explode('/', $views);
    $config = $getSpecs($ids);
    return $twig->render('index.html', array('config' => $config));
})
->assert('views', '.*');

$app->run();
