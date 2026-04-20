<?php

namespace App\Http\Controllers\Api\Store;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\EcommerceClient;
use App\Models\StoreSetting;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class AccountPagesController extends Controller
{
    public function account()
    {
        $s = StoreSetting::firstOrFail();

        return Inertia::render('Store/Account/Profile', [
            's' => $s,
            'user' => Auth::guard('store')->user()
        ]);
    }

    public function orders()
    {
        $s = StoreSetting::firstOrFail();

        return Inertia::render('Store/Account/Orders', [
            's' => $s
        ]);
    }

    public function update(Request $request)
    {
        $user = Auth::guard('store')->user();
        if (! $user) {
            return redirect()->back()->withErrors(['auth' => 'You must be signed in.']);
        }

        $data = $request->validate([
            'username' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', 'unique:ecommerce_clients,email,'.$user->id],
            'password' => ['nullable', 'confirmed', 'min:6'],
        ]);

        DB::transaction(function () use ($user, $data) {
            // Update EcommerceClient
            $user->username = $data['username'];
            $user->email = $data['email'];
            if (! empty($data['password'])) {
                $user->password = Hash::make($data['password']);
            }
            $user->save();

            // Update linked Client
            if ($user->client_id) {
                $client = Client::find($user->client_id);
                if ($client) {
                    $client->name = $data['username']; // or $data['name'] if you had it
                    $client->email = $data['email'];
                    $client->save();
                }
            }
        });

        return redirect()
            ->back()
            ->with('status', __('messages.ProfileUpdated'));
    }

    public function orderDetail($id)
    {
        $s = StoreSetting::firstOrFail();
        
        return Inertia::render('Store/Account/OrderDetail', [
            's' => $s,
            'orderId' => $id
        ]);
    }
}
