<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules;

class UserManagementController extends Controller
{
    public function index()
    {
        return view('usermanagement.index');
    }
    
    public function showAcceptForm(Request $request, $token)
    {
        $email = $request->email;

        // Check if the token exists in the database
        $resetRecord = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->first();

        if (!$resetRecord || !Hash::check($token, $resetRecord->token)) {
            return view('auth.invitation-error', ['error' => 'Invalid or expired invitation link.']);
        }

        // Check if the user exists
        $user = User::where('email', $email)->first();

        if (!$user) {
            return view('auth.invitation-error', ['error' => 'User not found.']);
        }

        // Check if the user has already set a password and verified their email
        if ($user->email_verified_at !== null) {
            return view('auth.invitation-error', ['error' => 'This invitation has already been accepted.']);
        }

        return view('auth.accept-invitation', [
            'token' => $token,
            'email' => $email,
            'name' => $user->name,
        ]);
    }

    public function acceptInvitation(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Check if the token exists in the database
        $resetRecord = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$resetRecord || !Hash::check($request->token, $resetRecord->token)) {
            return back()->withErrors(['email' => 'Invalid or expired invitation link.']);
        }

        // Find the user
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'User not found.']);
        }

        // Update the user's password and mark as verified
        $user->password = Hash::make($request->password);
        $user->email_verified_at = now(); // Mark as verified since this is an invited user
        $user->save();

        // Delete the token
        DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->delete();

        // Redirect to login page with success message
        return redirect()->route('login')
            ->with('status', 'Your password has been set successfully. You can now log in.');
    }
}
