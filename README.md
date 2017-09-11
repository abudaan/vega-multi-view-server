# Vega multi view server

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


### Difference between `specs` and `runtimes`

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
