# Vega multi view server

## Table of Contents

   * [Vega multi view server](#vega-multi-view-server)
      * [Introduction](#introduction)
      * [How it works](#how-it-works)
         * [Step 1](#step-1)
         * [Step 2](#step-2)
         * [Step 3](#step-3)
      * [Live example](#live-example)

<small>(toc created by [gh-md-toc](https://github.com/ekalinin/github-markdown-toc))</small>

## Introduction

This project is a simple Silex server that serves pages with multiple separated Vega views. For rendering the views to the page I use [vega-multi-view](https://github.com/abudaan/vega-multi-view) which is a wrapper for the Vega runtime that allows separate Vega views to listen to each other's signals. Separate means that each spec is rendered in a separate HTML element. It is recommended to read this [documentation](https://github.com/abudaan/vega-multi-view/README.md) first.

For creating the Vega specifications (specs) I use tools from a related project: [vega-specs](https://github.com/abudaan/vega-specs).

Here is a [live example](http://app4.bigdator.nl/6a/6b/4b/8a/8b). The first spec listens to signals of the second spec, the third spec is stand-alone spec and the fourth spec listens to signals of the fifth spec.


## How it works

The url is parsed into segments and each segment is interpreted as the id of a Vega spec that will be added to the page. For instance:
```javascript
https://app4.bigdator.nl/6a/6b
```
tells the server to load the Vega specs with id `6a` and `6b`. The order of the ids in the url is also the order of their appearance on the page.

### Step 1

The server first starts to look for a runtime configuration file that belongs the spec with the requested id. If no file is found the server continues to the next requested id.

If a runtime file is found, the server looks for a `spec` entry. A runtime configuration may or may not define the spec it belongs to, if it does the server tries to find that spec and once the spec is found it will be coupled with the runtime.

If a spec is defined but it cannot be found, the server tries to find a spec with the requested id. If found it will be added with no coupled runtime. Note that this doesn't mean that the spec has no runtime configuration; it can be inlined in the spec file as well.

Coupling means that the server populates 2 arrays: `$specs` and `$runtimes` and a spec at slot 2 of the `$specs` array is coupled to the runtime at slot 2 in the `$runtimes` array. If a spec has no runtime, `null` will be stored at the corresponding slot in the `$runtimes` array.

```php
// only the third spec has a coupled runtime configuration
$specs = array (
    $spec1,
    $spec2,
    $spec3
);

$runtimes = array(
    null,
    null,
    $runtime3
);
```

### Step 2

Now that the server has collected all specs and runtimes it loops over all specs to replace the paths to data sets and images. The spec files on the server are actually templates in YAML format and the paths in these specs are set like this:

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

The global configuration file that gets loaded as soon as the server starts, sets the data and image path parameters according the folder structure. This way it is very easy to reuse specs in different server environments. You could replace the paths on the client if necessary though I think this is more a task for the server.

### Step 3

The server builds a `vega-multi-view` global runtime configuration for the client and encodes it to a JSON string. The server renders a twig template and the serialized config object is added as dataset to the body element:

```html
<body data-vegamultiview='{"debug":true,"element":"app","dataPath":"\/assets\/data","imagePath":"\/assets\/img","specs ....}'>
```

As soon as javascript starts on the client it queries the config from the body element, parses it back to JSON and loads it into `vega-multi-view`:
```javascript
import createViews from 'vega-multi-view';

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
createViews(data)
    .then((result) => {
        // do other stuff
        console.log(result);
    });
```
This way we don't need to load the runtime config via an extra HTTP call.

## Live example

You can play around with the [live example](http://app4.bigdator.nl). On the server I have put a few test specs that I created in [this project](https://github.com/abudaan/vega-specs). You can choose one of the following ids to add to the url:

- `4`: A plain Vega map without tiles
- `4a`: Vega as Leaflet layer, this spec has its runtime inlined
- `4b`: Same as 4a but with a separate runtime config
- `5a`: Webfont test
- `6a`: Area chart
- `6b`: Range controller of 6a
- `8a`: Scatter plot
- `8b`: Range controller of 8a

I will soon add more specs examples. And I will add another client that shows that is also very easy to load additional specs after the page has rendered.

