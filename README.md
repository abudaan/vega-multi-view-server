# Vega multi view server

## Table of Contents


<small>toc created by [gh-md-toc](https://github.com/ekalinin/github-markdown-toc)</small>

## Introduction

This project is a simple Silex server that serves pages with multiple separated Vega views. For rendering the views to the page I use [vega-multi-view](https://github.com/abudaan/vega-multi-view) which is a wrapper for the Vega runtime that allows separate Vega views to listen to each other's signals. Separate means that each spec is rendered in a separate HTML element. It is recommended to read the [documentation](https://github.com/abudaan/vega-multi-view/README.md) first.

For creating the Vega specifications (specs) I use tools from a related project: [vega-specs](https://github.com/abudaan/vega-specs).

Here is a [live example](http://app4.bigdator.nl/6a/6b/4b/8a/8b). The first spec listens to signals of the second spec, the third spec is stand-alone spec and the fourth spec listens to signals of the fifth spec.


## How it works

The url is parsed into segments and each segment is interpreted as the id of a Vega spec that will be added to the page. For instance:
```javascript
https://app4.bigdator.nl/6a/6b
```
tells the server to load the Vega specs with id `6a` and `6b`. The order of the ids in the url is also the order of where they appear on the page.

## Step 1

The server first start to look for a runtime configuration file that belongs the spec with the requested id. A runtime configuration may or may not define the spec it belongs to. If it does the server tries to find that spec and once the spec is found it will be coupled with the runtime.

If a spec is defined but it cannot be found the server tries to find a spec with the requested id. If found it will be added with no coupled runtime.

Coupling means that the server fills 2 arrays: `$specs` and `$runtimes` and a spec at slot 2 of the `$specs` array is coupled to the runtime at slot 2 in the `$runtimes` array. If a spec has no runtime, `null` will be stored at the corresponding slot in the `$runtimes` array,


## Step 2

Now that the server has collected all specs and runtimes

