<?php
return [
  'backend' => [
    'frontName' => 'admin_backend'
  ],
  'crypt' => [
    'key' => '60c79c422d8fc119be3575c0fd4e7bb2'
  ],
  'db' => [
    'table_prefix' => '',
    'connection' => [
      'default' => [
        'host' => 'localhost',
        'dbname' => 'magento_test_224',
        'username' => 'root',
        'password' => '',
        'active' => '1'
      ]
    ]
  ],
  'resource' => [
    'default_setup' => [
      'connection' => 'default'
    ]
  ],
  'x-frame-options' => 'SAMEORIGIN',
  'MAGE_MODE' => 'default',
  'session' => [
    'save' => 'files'
  ],
  'cache_types' => [
    'config' => 1,
    'layout' => 1,
    'block_html' => 1,
    'collections' => 1,
    'reflection' => 1,
    'db_ddl' => 1,
    'eav' => 1,
    'customer_notification' => 1,
    'config_integration' => 1,
    'config_integration_api' => 1,
    'full_page' => 1,
    'translate' => 1,
    'config_webservice' => 1
  ],
  'install' => [
    'date' => 'Thu, 07 Mar 2019 18:07:47 +0000'
  ]
];
