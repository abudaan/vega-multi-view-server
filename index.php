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

$app->get('/{views}', function ($views) use ($app, $twig, $yamlParser, $yamlDumper, $config) {
    $ids = explode('/', $views);
    $result = '';
    $i = 1;
    $specs = array();

    $add = function ($specId, $runtime) use (&$specs, $yamlParser, $config) {
        $spec = file_get_contents(__DIR__.'/specs/spec'.$specId.'.yaml');
        if ($spec != null) {
            $spec = str_replace('$_DATA_PATH', $config['dataPath'], $spec);
            $spec = str_replace('$_IMAGE_PATH', $config['imagePath'], $spec);
            $spec = $yamlParser->parse($spec);
            if ($runtime == null) {
                $specs[] = $spec;
            } else {
                $specs[] = array($spec, $runtime);
            }
            error_log($specId . ' > ' . sizeof($specs) . "\n", 3, __DIR__.'/php.log');
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
    // use bson if the spec has inlined data containing huge number, for instance topo,json data
    // $config = MongoDB\BSON\fromPHP($config);
    // $config = bin2hex($config);
    // yaml is sometimes a bit smaller compared to json
    // $config = $yamlDumper->dump($config, 1);
    // return $config;
    return $twig->render('index.html', array('config' => $config));
})
->assert('views', '.*');

$app->run();
