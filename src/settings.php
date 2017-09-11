<?php
return [
  'settings' => [
    'displayErrorDetails' => true,
    'addContentLengthHeader' => false,
    'renderer' => [
      'template_path' => __DIR__ . '/../templates/',
    ],
    'logger' => [
      'name' => 'slim-app',
      'path' => __DIR__ . '/../logs/app.log',
      'level' => \Monolog\Logger::DEBUG,
    ],
  ],
];
