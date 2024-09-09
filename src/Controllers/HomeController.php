<?php
namespace ScriptingThoughts\Controllers;

class HomeController extends Controller
{
    public $pageTitle = "Home";

    public function home()
    {
        $this->view('home');
    }


}