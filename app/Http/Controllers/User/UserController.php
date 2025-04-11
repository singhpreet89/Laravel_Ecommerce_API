<?php

namespace App\Http\Controllers\User;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Resources\User\UserResource;
use App\Http\Resources\User\UserCollection;
use App\Http\Requests\User\UserStoreRequest;
use App\Http\Requests\User\UserUpdateRequest;
use App\Services\Pagination\PaginationFacade;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Symfony\Component\HttpFoundation\Response;
use App\Services\FilterAndSort\FilterAndSortFacade;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(User $user): ResourceCollection
    {
        $users = $user->all();
        $filteredAndSortedUsers = FilterAndSortFacade::apply($users, $user);
        $paginatedUsers = PaginationFacade::apply($filteredAndSortedUsers);

        return UserCollection::collection($paginatedUsers);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UserStoreRequest $request): UserResource
    {
        $data = $request->all();
        $data['password'] = bcrypt($request->password);
        $data['verified'] = User::UNVERIFIED_USER;
        $data['verification_token'] = User::generateVerificationCode();
        $data['admin'] = User::REGULAR_USER;

        $user = User::create($data);    // Create is used in Mass assignment while Save() can be used without mass assignment and in the Update method
        return new UserResource($user);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user): UserResource
    {
        return new UserResource($user);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UserUpdateRequest $request, User $user): UserResource
    {
        if ($request->has('name')) {
            $user->name = $request->name;
        }

        if ($request->has('email') && $user->email != $request->email) {
            $user->verified = User::UNVERIFIED_USER;
            $user->verification_token = User::generateVerificationCode();
            $user->email = $request->email;
        }

        if ($request->has('password')) {
            $user->password = bcrypt($request->password);
        }

        if ($request->has('admin')) {
            $user->admin = $request->admin;
        }

        $user->save();
        return new UserResource($user);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user): UserResource
    {
        $user->delete();
        return new UserResource($user);
    }
}
