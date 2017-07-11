<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Socialite;
use Auth;
use App\User;
use SammyK\LaravelFacebookSdk\LaravelFacebookSdk;
use Session;

class SocialAuthController extends Controller
{
	protected $fb;

	public function __construct(LaravelFacebookSdk $fb)
	{
		$this->fb = $fb;
	}
    public function redirect()
    {
    	$login_url = $this->fb->getLoginUrl(['email', 'user_managed_groups']);
    	return redirect($login_url);
    	// return Socialite::driver('facebook')->redirect(); 
    }

    public function callback()
    {
    	try {
	        $token = $this->fb->getAccessTokenFromRedirect();
	    } catch (Facebook\Exceptions\FacebookSDKException $e) {
	        dd($e->getMessage());
	    }

	    // Access token will be null if the user denied the request
	    // or if someone just hit this URL outside of the OAuth flow.
	    if (! $token) {
	        // Get the redirect helper
	        $helper = $this->fb->getRedirectLoginHelper();

	        if (! $helper->getError()) {
	            abort(403, 'Unauthorized action.');
	        }

	        // User denied the request
	        dd(
	            $helper->getError(),
	            $helper->getErrorCode(),
	            $helper->getErrorReason(),
	            $helper->getErrorDescription()
	        );
	    }

	    // if (! $token->isLongLived()) {
	    //     // OAuth 2.0 client handler
	    //     $oauth_client = $this->fb->getOAuth2Client();

	    //     // Extend the access token.
	    //     try {
	    //         $token = $oauth_client->getLongLivedAccessToken($token);
	    //     } catch (Facebook\Exceptions\FacebookSDKException $e) {
	    //         dd($e->getMessage());
	    //     }
	    // }

	    $this->fb->setDefaultAccessToken($token);

	    // Save for later
	    Session()->put('facebook_access_token', (string) $token);
	    // dd(Session()->get('facebook_access_token'));

	    // Get basic info on the user from Facebook.
	    try {
	        $response = $this->fb->get('/me?fields=id,name,email');
	    } catch (Facebook\Exceptions\FacebookSDKException $e) {
	        dd($e->getMessage());
	    }

	    // Convert the response to a `Facebook/GraphNodes/GraphUser` collection
	    $facebook_user = $response->getGraphUser();

	    // Create the user if it does not exist or update the existing entry.
	    // This will only work if you've added the SyncableGraphNodeTrait to your User model.
	    $user = $this->findOrCreateUser($facebook_user);

	    // Log the user into Laravel
	    Auth::login($user);

	    return redirect('/')->with('message', 'Successfully logged in with Facebook');
    }

    private function findOrCreateUser($providerUser) {
        $account = User::where('provider_id', $providerUser->getId())->first();

        if ($account) {
            return $account;
        }

        return User::create([
            'name'          => $providerUser->name,
            'email'         => $providerUser->email,
            'provider_id'   => $providerUser->getId(),
            'password' => '123456'
        ]);
    }
}
