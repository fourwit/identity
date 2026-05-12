<?php

namespace Modules\Identity\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Modules\Identity\Models\User;
use Modules\Identity\Transformers\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // return view('user::index');
        $user = Auth::user();
        return view('user::customer.profile', compact('user'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('user::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {}

    /**
     * Show the specified resource.
     */
    public function show()
    {
        $user = Auth::user();
        return view('user::customer.profile', compact('user'));
    }

    

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('user::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id) {
        $user = User::find($id);
        $user->update($request->only(['name', 'first_name', 'last_name', 'phone']));
        return new UserResource($user);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id) {}
}
