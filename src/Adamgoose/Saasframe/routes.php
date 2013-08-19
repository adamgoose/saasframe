<?php

Route::controller(Config::get('saasframe::route'), Config::get('saasframe::webhook_controller'));

Route::get('test', function(){
  return str_replace(" ", "", lcfirst(ucwords(str_replace(".", " ", "test.one.two"))));
});