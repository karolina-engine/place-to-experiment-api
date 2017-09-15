# place-to-experiment-frontend-components
Frontend components for Place to experiment backend API.

## Usage

1. Run `npm install` to get dependencies.
2. Run `npm run dev` (runs "webpack --watch") for a continuous DEV build.
  OR
2. Run `npm run once` to make a one-time DEV build.
3. Run `npm run build` to produce a minified build with the following name structure: "<file-name>.<version-number>.<hash>.min.js". Version number is set in package.json. The build process separates vendor and app code and splits code into several modules that are asynchronously loaded when needed.
  OR
3. Run `npm run build --report` to produce a minified build AND to see a detailed visual report of the bundle made with Webpack Bundle Analyzer

## Script import on deployment

### Development

The development deployment requires 2 imports:

* **place-to-experiment-asset-dev-path**: A file/script that allows you to override the path from which the on-demand assets are loaded. The default path for on-demand assets is `/js/`.
  * To keep the default path, set the variable to `window.placeToExperimentAssetPath = '/'`
  * Example override:
  window.placeToExperimentAssetPath = '/place-to-experiment/js/dev/'
* **place-to-experiment**: The whole application (includes manifest, dependencies and the logic).

Example of deployment for production:

  <!-- DEV -->
  <script type="text/javascript" src="/place-to-experiment/js/dev/place-to-experiment-asset-dev-path.js"></script>
  <script type="text/javascript" src="/place-to-experiment/js/dev/place-to-experiment.js"></script>

### Production

Deploying production build requires import of 4 files. Note that the order **matters**.
* **place-to-experiment-asset-path**: A file/script that allows you to override the path from which the on-demand assets are loaded. The default path for on-demand assets is `/js/`.
  * To keep the default path, set the variable to `window.placeToExperimentAssetPath = '/'`
  * Example override:
  window.placeToExperimentAssetPath = '/place-to-experiment/'
* **manifest**: The application manifest that contains references to all of the other build files that are loaded on demand.
* **vendor**: The application dependencies that are separated to improve caching (as they rarely change).
* **place-to-experiment**: The application logic.

Example of deployment for production:

  <!-- PROD -->
  <script type="text/javascript" src="/place-to-experiment/js/place-to-experiment-asset-path.js"></script>
  <script type="text/javascript" src="/place-to-experiment/js/manifest.1.0.0.dbff38945dbc3d62cb76.min.js"></script>
  <script type="text/javascript" src="/place-to-experiment/js/vendor.1.0.0.ef19d15309aa7a351e60.min.js"></script>
  <script type="text/javascript" src="/place-to-experiment/js/place-to-experiment.1.0.0.5c2ca9f9e7390932286e.min.js"></script>
