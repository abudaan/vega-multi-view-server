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

<small>toc created by [gh-md-toc](https://github.com/ekalinin/github-markdown-toc)</small>

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
