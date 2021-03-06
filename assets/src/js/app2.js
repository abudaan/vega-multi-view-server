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

