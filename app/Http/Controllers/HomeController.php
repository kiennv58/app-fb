<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Session;
use SammyK\LaravelFacebookSdk\LaravelFacebookSdk;

class HomeController extends Controller
{
    protected $fb;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(LaravelFacebookSdk $fb)
    {
        $this->fb = $fb;
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $token = Session()->get('facebook_access_token');
        $this->fb->setDefaultAccessToken($token);
        try {
            $response = $this->fb->get('/me?fields=groups');
        } catch (Facebook\Exceptions\FacebookSDKException $e) {
            dd($e->getMessage());
        }
        
        if (array_key_exists('groups', $response->getGraphObject()->all())) {
            $groups = $response->getGraphObject()['groups']->all();
            return view('admin.index', compact('groups'));
        } 
        return view('admin.no_group');
    }

    public function post(Request $request)
    {
        // dd($request->all());
        $token = Session()->get('facebook_access_token');
        $this->fb->setDefaultAccessToken($token);
        try {
            $response = $this->fb->get('/' . $request->fb_id . '/feed?limit=10');
        } catch (Facebook\Exceptions\FacebookSDKException $e) {
            dd($e->getMessage());
        }
        $posts = $response->getGraphEdge()->all();
        return view('admin.post', compact('posts'));
    }
}
