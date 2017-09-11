# Vega multi view server

## Table of Contents

   * [Vega multi view server](#vega-multi-view-server)
      * [Introduction](#introduction)
      * [How it works](#how-it-works)
         * [Difference between 'specs' and 'runtimes'](#difference-between-specs-and-runtimes)
         * [Global runtime configuration](#global-runtime-configuration)
         * [View specific runtime configuration](#view-specific-runtime-configuration)
            * [Leaflet](#leaflet)
            * [Publish and subscribe signals](#publish-and-subscribe-signals)
            * [Tooltips](#tooltips)

## Introduction

This project is a Silex server that serves pages with multiple separated Vega views. For rendering the views to the page I use [vega-multi-view](https://github.com/abudaan/vega-multi-view) which is a wrapper for the Vega runtime that allows separated Vega views to listen to each other's signals. Separated means that each spec is rendered in a separate HTML element.

For creating the Vega specifications (specs) I use tools from a related project: [vega-specs](https://github.com/abudaan/vega-specs).

Here is a [live example](http://app4.bigdator.nl/6a/6b/4b/8a/8b). The first spec listens to signals of the second spec, the third spec is stand-alone spec and the fourth spec listens to signals of the fifth spec.


## How it works

The url is parsed into segments and each segment is interpreted as the id of a Vega spec that needs added to the page. For instance:
```javascript
https://app4.bigdator.nl/6a/6b
```
tells the server to load the Vega specs with id `6a` and `6b`. The order of the ids in the url is also the order of where they appear on the page.


### Difference between 'specs' and 'runtimes'

A `spec` is a Vega specification, it tells the [Vega runtime](https://github.com/vega/vega/wiki/Runtime) what to render on the page. A `runtime` is a small configuration object that tells the `vega-multi-view` wrapper how the rendered Vega view must be connected to other views.

A runtime can be added to a spec or you can provide a runtime separately. You can also use no runtime configuration at all: then the view will be rendered with the global settings of `vega-multi-view`.

### Global runtime configuration

The global runtime configuration can be a javascript object, a JSON string, a uri of a JSON file or a uri of a YAML file. Let's see what it looks like:

```yaml
---
# Log helpful information to the browser console. Defaults to false.
debug: false

# The element where all Vega view will be rendered to. Note that inside this
# element every view lives in its own containing HTML element, see below.
# If the element does not exist a div will be created and added to the body of
# the document. You can either specify an id (string) or a HTML element.
element: id | HTMLElement

# Path to data sets and images that the Vega spec need to load.
dataPath: ./assets/data
imagePath: ./assets/img

# The css class or array of css classes that will be added to the view's containing
# HTML element, unless overridden by a view specific runtime configuration.
cssClass: view

# The renderer that will used for all views, unless overridden by a view specific
# runtime configuration.
renderer: canvas

# Whether or not to call the run() method of a view after is has been added to
# the page. Defaults to true and can be overridden by the view specific runtime
# configuration
run: true

# Array or a single spec, can be a uri of JSON or YAML file, a javascript object
# or a JSON string
specs: [{...}, ../specs/spec1.yaml, ../specs/spec2.vg.json]

# Array or a single runtime configuration, can be a uri of JSON or YAML file,
# a javascript object or a JSON string
runtimes: [null, ../specs/runtime.yaml, ../specs/runtime.json]
```

Note that only the `specs` entry is mandatory. That is, you can leave it out but then nothing will be rendered.

The `specs` array and the `runtimes` array share their indexes; the runtime at slot 2 in the runtimes array will be applied to the spec at slot 2 in the specs array. In the example above the first spec does not have an accompanying runtime configuration but the 2nd and 3rd do so we add `null` to the first slot of the array.


### View specific runtime configuration

The view specific runtime configuration overrides settings with the same name in the global runtime configuration. It looks like this:

```yaml
---
# The renderer to use this view
renderer: canvas

# The element where the view will be rendered to. If the element does not exist a div
# will be created and added to the body of the document.
element: id | HTMLElement

# The css class or array of css classes that will be added to the containing HTML element.
cssClass: view | [class1, class2]

# Whether or not to call the run() method of the view after it has been added to the page.
# Defaults to true.
run: true

# Whether or not the Vega view should be added as a layer to a Leaflet map. Defaults to
# false.
leaflet: false

# A signal or array of signals that will be available for the other views to listen to.
publish:
    - signal: internalSignalName1
      as: externalSignalName1
    - signal: internalSignalName2
      as: externalSignalName2

# A signal or array of signals originating from another view that this view wants to
# listen to.
subscribe:
    signal: externalSignalNameOfAnotherView
    as: internalSignalName2

# The options that will be passed on to vega-tooltip for rendering custom tooltips on
# top of the view.
tooltipOptions:
    showAllFields: false
    fields:
        - formatType: string
          field: name
          title: Hood
        - formatType: number
          field: rpts
          title: Reports
        - formatType: number
          field: dmps
          title: Dumps

```

#### Leaflet

Vega does not support tile maps but by using a custom version of [leaflet-vega](https://github.com/nyurik/leaflet-vega) we can render a Vega view to a layer in Leaflet. If you want to render your spec to a Leaflet layer your spec must define the signals `zoom` and `latitude` and `longitude`. You can read more about zoom, latitude and longitude in the Leaflet [documentation](http://leafletjs.com/examples/zoom-levels/)

The `vega-multi-view` adds a Leaflet map to the HTML element as specified in the runtime configuration and adds a Vega view layer to the map. If your spec does not specify one or all of the mandatory signals an error will be logged to the browser console and nothing will be rendered.

#### Publish and subscribe signals

This is the core functionality of `vega-multi-view` that makes signals of views available for each other despite the fact that they all live in a separate HTML element. Both publish and subscribe use aliases so as to avoid name clashes.

For instance if 2 specs both have a signal named `hover` you can publish them with an alias to keep them apart, you could use the aliases `hover_spec1` and `hover_spec2`. Now other views can subscribe to the signal they are interested in.

A common scenario is when a mouse over event in one view should trigger the hover of another view as well or when one spec sets a range in the data that is rendered by another spec.

Note that you define publish and subscribe aliases in the runtime configuration of a view. This means that it is possible that when you add the view to a page it might be possible that another spec has defined aliases with the same name. Therefor I recommend to use the name or filename of the spec as a prefix or suffix of your aliases.

#### Tooltips

The `vega-multi-view` uses Vega-tooltip, for more informations see the [documentation](https://github.com/vega/vega-tooltip)
