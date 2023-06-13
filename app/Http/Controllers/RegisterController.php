<?php

namespace App\Http\Controllers;

// use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
    public function create()
    {
        return view('auth.register');
    }

    public function store()
    {
        $attributes = request()->validate([
            'username' => 'required|max:255|min:2',
            'name' => 'required|max:255|min:2',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|min:5|max:255',
        ]);

        $user = User::create($attributes);
        auth()->login($user);

        return redirect('/dashboard');
    }

    public function formRegis ()
    {
        return view('pages.user.add-user');
    }

    public function regisNew (Request $request)
    {
        try {

            $request->validate([
                'username' => 'required|max:255|min:2',
                'name' => 'required|max:255|min:2',
                'email' => 'required|email|max:255|unique:users,email',
                'role' => 'required|in:admin,user'
            ]);
    
            $user = User::create([
                'username' => $request->username,
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt(12345678),
                'role' => $request->role,
            ]);
    
            return redirect('/user');

        } catch (\Exception $ex) {
            return back()->withErrors([
                'email' => 'Email same with other user',
            ]);
        }
        
    }
}
