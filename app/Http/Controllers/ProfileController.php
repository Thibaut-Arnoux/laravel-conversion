<?php

namespace App\Http\Controllers;

use App\Http\Requests\Profile\ProfileDestroyRequest;
use App\Http\Requests\Profile\ProfileUpdateRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ProfileController extends Controller
{
    /**
     * Update the specified resource in storage.
     */
    public function update(ProfileUpdateRequest $request): JsonResponse
    {
        $user = $request->user();
        $user->fill($request->validated());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return UserResource::make($user)
            ->toResponse($request);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProfileDestroyRequest $request): JsonResponse
    {
        $user = $request->user();
        $user->tokens()->delete();
        $user->delete();

        return new JsonResponse(
            [],
            Response::HTTP_NO_CONTENT,
        );
    }
}
