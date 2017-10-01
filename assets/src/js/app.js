import { addViews, removeViews, version } from 'vega-multi-view';

console.log(version);
// get the dataset from the body
const config = document.body.dataset.vmv;

// try to parse it to javascript
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
        // setTimeout(() => {
        //     removeViews(0, 1);
        // }, 1000);
    });

