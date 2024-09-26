<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use Illuminate\Support\Facades\Log;
use JetBrains\PhpStorm\NoReturn;

class UserController extends Controller
{
    #[NoReturn]
    public function test(int $id): void
    {
        Log::info(message: 'ahihi test log tu backend '.$id);
        broadcast(new MessageSent('ahihi test nhan tin hieu tu backend'))->toOthers();
        dd('da chay event');
    }
}
