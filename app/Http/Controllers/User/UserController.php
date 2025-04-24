<?php

namespace App\Http\Controllers\User;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use App\Http\Resources\User\UserResource;
use App\Http\Resources\User\UserCollection;
use App\Http\Requests\User\UserStoreRequest;
use App\Http\Requests\User\UserUpdateRequest;
use App\Services\Pagination\PaginationFacade;
use Symfony\Component\HttpFoundation\Response;
use App\Services\FilterAndSort\FilterAndSortFacade;
use Illuminate\Http\Resources\Json\ResourceCollection;

class UserController extends Controller
{
    private const CACHE_KEY         = 'users';
    private const USER_CACHE_KEY    = 'user.%d';

    /**
     * Helper method to generate single user cache key
     */
    private function getUserCacheKey(int $userId): string
    {
        return sprintf(self::USER_CACHE_KEY, $userId);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(User $user): ResourceCollection
    {
        $start = microtime(true);
        $source = Cache::has(self::CACHE_KEY) ? 'cache' : 'database';

        $users = Cache::remember(self::CACHE_KEY, config('constants.CACHE_TTL'), function () use($user) {
            Log::info('Fetching users from database');
            return $user->all();
        });

        $time = number_format((microtime(true) - $start) * 1000, 2);
        Log::info("Users fetched from {$source} in {$time}ms");

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
        
        Cache::remember(self::CACHE_KEY, config('constants.CACHE_TTL'), function () {
            return User::all();
        });
        
        return new UserResource($user);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user): UserResource
    {
        $start = microtime(true);
        $cacheKey = $this->getUserCacheKey($user->id);
        $source = Cache::has($cacheKey) ? 'cache' : 'database';

        $user = Cache::remember($cacheKey, config('constants.CACHE_TTL'), function () use ($user) {
            Log::info('Fetching user from database: ' . $user->id);
            return $user->fresh();
        });

        $time = number_format((microtime(true) - $start) * 1000, 2);
        Log::info("User {$user->id} fetched from {$source} in {$time}ms");

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

        Cache::put(self::CACHE_KEY, User::all(), config('constants.CACHE_TTL'));
        
        $cacheKey = $this->getUserCacheKey($user->id);
        Cache::put($cacheKey, $user->fresh(), config('constants.CACHE_TTL'));

        return new UserResource($user);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user): UserResource
    {
        $user->delete();

        Cache::put(self::CACHE_KEY, User::all(), config('constants.CACHE_TTL'));
        
        $cacheKey = $this->getUserCacheKey($user->id);
        Cache::forget($cacheKey);        // OR Cache::forget("user.{$user->id}");

        return new UserResource($user);
    }
}
