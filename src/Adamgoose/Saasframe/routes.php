<?php

Route::controller(Config::get('saasframe::route'), 'Adamgoose\Saasframe\Controllers\WebhookController');

Route::get('test', function(){
  return str_replace(" ", "", lcfirst(ucwords(str_replace(".", " ", "test.one.two"))));
});