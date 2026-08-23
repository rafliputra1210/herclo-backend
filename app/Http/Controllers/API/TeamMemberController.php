<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\TeamMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class TeamMemberController extends Controller
{
    // Mengambil semua data tim (Untuk Publik & Admin)
    public function index()
    {
        return response()->json(['data' => TeamMember::latest()->get()]);
    }

    // Menambah Anggota Tim Baru
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $data = $request->only(['name', 'role']);

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('uploads/team'), $imageName);
            $data['image_path'] = '/uploads/team/' . $imageName;
        }

        TeamMember::create($data);
        return response()->json(['message' => 'Anggota tim berhasil ditambahkan']);
    }

    // Mengupdate Data Anggota Tim
    public function update(Request $request, $id)
    {
        $member = TeamMember::findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $data = $request->only(['name', 'role']);

        if ($request->hasFile('image')) {
            // Hapus gambar lama jika ada
            if ($member->image_path && File::exists(public_path($member->image_path))) {
                File::delete(public_path($member->image_path));
            }
            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('uploads/team'), $imageName);
            $data['image_path'] = '/uploads/team/' . $imageName;
        }

        $member->update($data);
        return response()->json(['message' => 'Data tim berhasil diperbarui']);
    }

    // Menghapus Anggota Tim
    public function destroy($id)
    {
        $member = TeamMember::findOrFail($id);
        if ($member->image_path && File::exists(public_path($member->image_path))) {
            File::delete(public_path($member->image_path));
        }
        $member->delete();
        return response()->json(['message' => 'Anggota tim dihapus']);
    }
}