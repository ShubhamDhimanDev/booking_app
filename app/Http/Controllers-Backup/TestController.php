<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PayUService;
use App\Services\PayURefundService;
use Illuminate\Support\Facades\Log;

class TestController extends Controller
{
  public function test(PayUService $payuService)
  {
    dd(app()->runningUnitTests());
    return view('test.test');
  }
}

