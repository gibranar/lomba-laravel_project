<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;

class ForgotPasswordController extends Controller
{
    public function showForm()
    {
        return view('auth.forgot');
    }

    public function sendEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $email = $request->input('email');

        $resetLink = 'http://127.0.0.1:8000/reset/' . base64_encode($email);

        Mail::send('auth.password_reset', ['url' => $resetLink], function ($message) use ($email) {
            $message->to($email)
                ->subject('Password Reset');
        });

        return redirect()->route('forgot.password')->with('success', 'Email has been sent successfully');
    }

    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'password' => 'required|confirmed|min:8',
        ]);

        $user = User::where('reset_token', $request->token)->first();

        if (!$user) {
            return redirect()->route('login')->with('error', 'Invalid token.');
        }

        $user->password = Hash::make($request->password);
        $user->reset_token = null;
        $user->save();

        return redirect()->route('login')->with('success', 'Password updated successfully.');
    }
}
