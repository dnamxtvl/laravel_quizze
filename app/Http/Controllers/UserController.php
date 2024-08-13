<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function test(int $id): void
    {
        broadcast(new MessageSent('ahihi test nhan tin hieu tu backend'))->toOthers();
        dd("da chay event");
    }
}
