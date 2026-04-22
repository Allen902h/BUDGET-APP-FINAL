<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfilePasswordRequest;
use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    public function update(UpdateProfileRequest $request)
    {
        $user = $request->user();

        $data = $request->validated();
        unset($data['profile_photo']);

        if ($request->hasFile('profile_photo')) {
            $data['profile_photo_path'] = $this->storeProfilePhoto($request->file('profile_photo'), $user->profile_photo_path);
        }

        $user->update($data);

        return $this->dashboardRedirect($request->input('return_to'), route('dashboard').'#settings')
            ->with('success', 'Profile updated successfully.');
    }

    public function updatePassword(UpdateProfilePasswordRequest $request)
    {
        $request->user()->update([
            'password' => Hash::make($request->validated()['password']),
        ]);

        return $this->dashboardRedirect($request->input('return_to'), route('dashboard').'#settings')
            ->with('success', 'Password updated successfully.');
    }

    public function removePhoto()
    {
        $user = request()->user();

        $this->deleteProfilePhoto($user->profile_photo_path);

        $user->update([
            'profile_photo_path' => null,
        ]);

        return $this->dashboardRedirect(request()->input('return_to'), route('dashboard').'#settings')
            ->with('success', 'Profile photo removed successfully.');
    }

    protected function storeProfilePhoto($file, ?string $oldPath = null): string
    {
        $directory = public_path('profile-photos');

        if (! File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $filename = Str::uuid()->toString().'.'.$file->getClientOriginalExtension();
        $file->move($directory, $filename);

        $this->deleteProfilePhoto($oldPath);

        return 'profile-photos/'.$filename;
    }

    protected function deleteProfilePhoto(?string $path): void
    {
        if (! $path) {
            return;
        }

        $fullPath = public_path($path);

        if (File::exists($fullPath) && str_starts_with(str_replace('\\', '/', $fullPath), str_replace('\\', '/', public_path('profile-photos')))) {
            File::delete($fullPath);
        }
    }
}
