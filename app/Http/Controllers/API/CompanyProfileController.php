<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\CompanyProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class CompanyProfileController extends Controller
{
    // Mengambil data profil (Publik & Admin)
    public function show()
    {
        // Mengambil data pertama, jika kosong buatkan data default
        $profile = CompanyProfile::firstOrCreate(
            ['id' => 1],
            [
                'title' => 'Mendefinisikan Ulang Gaya.',
                'description' => 'Berawal dari sebuah studio kecil di Surabaya...',
            ]
        );
        return response()->json(['data' => $profile]);
    }

    // Memperbarui data profil (Hanya Admin)
    public function update(Request $request)
    {
        $profile = CompanyProfile::firstOrCreate(['id' => 1]);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        $data = $request->only(['title', 'description']);

        if ($request->hasFile('image')) {
            // Hapus gambar lama
            if ($profile->image_path && File::exists(public_path($profile->image_path))) {
                File::delete(public_path($profile->image_path));
            }
            $image = $request->file('image');
            $imageName = 'hero_' . time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('uploads/profile'), $imageName);
            $data['image_path'] = '/uploads/profile/' . $imageName;
        }

        $profile->update($data);
        return response()->json(['message' => 'Profil perusahaan berhasil diperbarui']);
    }
}