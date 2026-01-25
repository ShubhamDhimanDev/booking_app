<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TestController extends Controller
{
  public function index()
  {

  }

  public function welcome()
  {
    return view('welcome');
  }

  public function testEmailForm()
  {
  }

  public function sendTestEmail(Request $request)
  {

  }

  public function sendAllTestEmails(Request $request)
  {

  }
}
