import createViews from 'vega-multi-view';

// get the dataset from the body
const config = document.body.dataset.vegamultiview;
console.log(`[CONFIG] ${config}`);
// try to parse it to javascript
let data;
try {
    data = JSON.parse(config);
} catch (e) {
    console.error(e);
}
console.log(`[DATA] ${data}`);
// create the views
createViews(data)
    .then((result) => {
        // do other stuff
        console.log(`[RESULT] ${result}`);
    });

