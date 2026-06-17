<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('chapter.{id}', function ($user, $id) {
    return true;
});
