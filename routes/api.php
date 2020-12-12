<?php

Route::get('/{resource}/attachable/{relationship}', '\NovaAttachPivot\Http\Controllers\AttachController@create');
Route::get('/{resource}/{resourceId}/attachable/{relationship}', '\NovaAttachPivot\Http\Controllers\AttachController@edit');
