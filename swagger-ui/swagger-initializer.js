window.onload = function() {
  //<editor-fold desc="Changeable Configuration Block">

  const folderName = 'ReqScapeNew';
  const firstSegment = window.location.pathname.split('/')[1];
  const containsPrefix = firstSegment === folderName;

  let url = '/api/docs';

  if (containsPrefix) {
    url = `/${firstSegment}/api/docs`;
  }

  // the following lines will be replaced by docker/configurator, when it runs in a docker-container
  window.ui = SwaggerUIBundle({
    url: url,  // URL del endpoint que sirve el JSON
    dom_id: '#swagger-ui',
    deepLinking: true,
    presets: [
      SwaggerUIBundle.presets.apis,
      SwaggerUIStandalonePreset
    ],
    plugins: [
      SwaggerUIBundle.plugins.DownloadUrl
    ],
    layout: "StandaloneLayout"
  });

  //</editor-fold>
};
