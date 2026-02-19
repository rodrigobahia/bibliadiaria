<?php

return [
  // Origin(s) allowed in production. Keep as single string for backward compatibility.
  'allowed_origin' => 'https://bibliadiaria.myrotech.com',
  // Development: you can add localhost entries here when running locally.
  'allowed_origins' => [
    'https://bibliadiaria.myrotech.com',
    'https://localhost:8888',
    'http://localhost',
    'http://localhost:8888',
    'http://localhost:8000'
  ],
  // Shared secret used by the frontend to authenticate requests coming from the served `index.php`.
  // Change this value for production; treat it as a server-side secret.
  'frontend_secret' => 'dev-local-secret-change-me',
  'http' => [],
  'sources' => [],
  'fallback' => []
];
