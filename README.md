# Vega multi view server

## Table of Contents

   * [Vega multi view server](#vega-multi-view-server)
      * [Table of Contents](#table-of-contents)
      * [Introduction](#introduction)
      * [How it works](#how-it-works)
         * [Step 1](#step-1)
         * [Step 2](#step-2)
         * [Step 3](#step-3)
      * [Live example](#live-example)

<small>(toc created by [gh-md-toc](https://github.com/ekalinin/github-markdown-toc))</small>

## Introduction

This project is a simple REST API server built with Silex that can be used as starting point for a back-end for a [vega-multi-view](https://github.com/abudaan/vega-multi-view) application.

`vega-multi-view` is a wrapper for the Vega runtime that allows Vega views that live in separate HTML elements to listen to each other's signals. If you haven't already, you might want to read the [documentation](https://github.com/abudaan/vega-multi-view/README.md) first.

The server has a single endpoint that returns a JSON global configuration object based on the ids you pass to the server as parameters. For instance this url:

<http://app4.bigdator.nl/json/4a/4b>

returns a global configuration object as string for the specs with ids `4a` and `4b`. You can click on the url to see the result.

For creating the Vega specifications (specs) I use tools from a related project: [vega-specs](https://github.com/abudaan/vega-specs).

The server can render a Twig template containing the Vega views as well.

Here is a [live example](http://app4.bigdator.nl/6a/6b/4b/8a/8b) of a Twig template. The first view listens to signals of the second view, the third view is stand-alone view and the fourth view listens to signals of the fifth view.

## How it works

The url is parsed into segments and each segment is interpreted as the id of a Vega spec that will be added to the page. The order of the ids in the url is also the order of their appearance on the page.

### Step 1

The server first starts to look for a view specific configuration that belongs the spec with the requested id.

If this configuration is found, the server looks for a `spec` entry. A view specific configuration may or may not define the spec it belongs to, if it does the server tries to find that spec.

If a spec is defined but it cannot be found, the server tries to find a spec with the requested id.

If no file is found the server continues to the next requested id or returns if it was the last id.

If a spec file is found, it gets loaded if necessary and inlined in the configuration object.

### Step 2

Now that the server has collected the specs it loops over them to replace the paths to data sets and images. The spec files on the server are actually templates in YAML format and the paths in these specs are set like this:

```yaml
# data path
data:
  - name: sp500
    url: $_DATA_PATH/sp500.csv
    format:
      type: csv
      parse:
        price: number
        date: date

# image path
  - type: image
    encode:
      enter:
        url:
          value: $_IMAGE_PATH/litter.png
        x:
          field: x
        y:
          field: y
```

The global configuration file that the server loads as soon as it starts, sets the data and image path parameters according the folder structure. This way it is very easy to reuse specs in different server environments. You could replace the paths on the client if necessary though I think this is more a task for the server.

### Step 3

The server builds a `vega-multi-view` global configuration for the client and encodes it to a JSON string. Then the server returns this string or renders a Twig template

#### Twig template

The Twig template adds the string to the dataset `data-vmv` of the body element:

```html
<body data-vmv='{"debug":true,"element":"app","dataPath":"\/assets\/data","imagePath":"\/assets\/img","specs ....}'>
```

As soon as javascript starts on the client it queries the config from the dataset of the body element, parses it back to JSON and loads it into `vega-multi-view`:

```javascript
import { addViews } from 'vega-multi-view';

// get the dataset from the body
const config = document.body.dataset.vegamultiview;

// try to parse it to a javascript object
let data;
try {
    data = JSON.parse(config);
} catch (e) {
    console.error(e);
}

// create the views
addViews(data)
    .then((result) => {
        // do other stuff
        console.log(result);
    });
```

This way we don't need to load the configuration via an extra HTTP call. Live example [here](http://app4.bigdator.nl/6a/6b/4b/8a/8b).

#### JSON string

You can process the JSON string to your liking. Here are 2 examples of making an REST API call and process the configuration with `vega-multi-view`. I use the UMD module approach so I can show you both the HTML and the javascript in a single file that doesn't need to be transpiled.

##### Example #1

```html
<!doctype html>
<html>

<head>
    <title>vega</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0">
    <link rel="stylesheet" type="text/css" href="https://cdn.rawgit.com/abudaan/vega-multi-view/v1.1.3/browser/vmv.css" />
    <script src="https://cdn.rawgit.com/abudaan/vega-multi-view/v1.1.3/browser/vmv.js"></script>
</head>

<body>
    <div id="container"></div>
    <script> // es5
        // vega-multi-view is available via the global variable vmv
        var addViews = window.vmv.addViews;

        // feed the endpoint '/json' the parameters '6a' and '6b'
        var restApiUrl = '/json/6a/6b';

        // parse the global configuration and render the views
        addViews(restApiUrl)
            .then(function (result) {
                console.log(result);
            }).catch(function (error) {
                console.error(error);
            });
    </script>
</body>

</html>
```

Live example [here](http://app4.bigdator.nl/rest/simple.html).

This is example uses a hard-coded API call url. If you want more flexibility, you could for instance create a Twig template from this file so you can set the url dynamically. Or you can pass the API call url via the browser url, see next example.

##### Example #2

```html
<!doctype html>
<html>

<head>
    <title>vega</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0">
    <link rel="stylesheet" type="text/css" href="https://cdn.rawgit.com/abudaan/vega-multi-view/v1.1.3/browser/vmv.css" />
    <script src="https://cdn.rawgit.com/abudaan/vega-multi-view/v1.1.3/browser/vmv.js"></script>
</head>

<body>
    <div id="container"></div>
    <script> // es5
        // vega-multi-view is available via the global variable vmv
        var addViews = window.vmv.addViews;

        // empty rest api url, nothing will be rendered
        var restApiUrl = '';

        // get the rest api url from the hash
        var hash = location.hash.substring(1);
        if (hash.length > 0) {
            if (restApiUrl.indexOf('/json/') === -1) {
                restApiUrl = '/json/' + hash;
            } else {
                restApiUrl = hash;
            }
        }

        // parse the global configuration and render the views
        addViews(restApiUrl)
            .then(function (result) {
                console.log(result);
            }).catch(function (error) {
                console.error(error);
            });
    </script>
</body>

</html>
```

Live example [here](http://app4.bigdator.nl/rest/#4a/4b), you can pass any id listed below. Don't forget the # in the url! You can use cleaner urls if you use the [History API](https://developer.mozilla.org/en-US/docs/Web/API/History) but that is beyond the scope of this example.

## Live examples

You can use a few test specs when playing around with the live examples. I have created these specs in [this project](https://github.com/abudaan/vega-specs). You can choose one of the following ids to add to the url:

- `4`: A plain Vega map without tiles
- `4a`: Vega as Leaflet layer, this spec has its configuration inlined
- `4b`: Same as 4a but with a separate configuration
- `5a`: Webfont test
- `6a`: Area chart
- `6b`: Range controller of 6a
- `8a`: Scatter plot
- `8b`: Range controller of 8a

More specs will be added in due course.
